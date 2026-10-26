<?php

namespace App\Http\Controllers;

use App\Services\MigrationGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrationController extends Controller
{
    protected MigrationGeneratorService $generatorService;

    public function __construct(MigrationGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $migrationPath = database_path('migrations');

        if (!File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        $files = collect(File::files($migrationPath))
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        // Search
        if ($search) {
            $files = $files->filter(function ($file) use ($search) {
                return stripos($file->getFilename(), $search) !== false;
            });
        }

        // Statistics
        $today = now()->format('Y_m_d');

        $stats = [
            'total' => $files->count(),
            'today' => $files->filter(function ($file) use ($today) {
                return str_contains($file->getFilename(), $today);
            })->count(),
            'tables' => count($this->generatorService->getKnownTables()),
            'latest' => optional($files->first())->getFilename(),
        ];

        // Pagination
        $perPage = 8;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $files
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $migrations = new LengthAwarePaginator(
            $currentItems,
            $files->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $columnTypes = MigrationGeneratorService::COLUMN_TYPES;
        $knownTables = $this->generatorService->getKnownTables();
        $erdData = $this->generatorService->parseMigrationsForErd();

        return view('home', compact(
            'migrations',
            'stats',
            'search',
            'columnTypes',
            'knownTables',
            'erdData'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'table' => 'required|string|regex:/^[a-zA-Z0-9_]+$/',
            'fields' => 'required',
            'migration_type' => 'nullable|in:create,alter',
        ]);

        $table = strtolower(trim($request->table));
        $migrationType = $request->input('migration_type', 'create');

        $fields = $this->generatorService->parseFields($request->fields);

        if (empty($fields)) {
            return back()->withInput()->with('error', 'Please specify at least one column for the migration.');
        }

        // If preview requested
        if ($request->has('preview') || $request->preview == '1') {
            $previewCode = $this->generatorService->generateMigrationCode($table, $fields, $migrationType);
            $previewSql = $this->generatorService->generateSqlPreview($table, $fields, $migrationType);
            $rawShorthand = $this->generatorService->fieldsToRawString($fields);

            return back()
                ->withInput()
                ->with('preview_code', $previewCode)
                ->with('preview_sql', $previewSql)
                ->with('raw_shorthand', $rawShorthand);
        }

        // Check if table already exists in create mode
        if ($migrationType === 'create') {
            try {
                if (Schema::hasTable($table)) {
                    return back()->withInput()->with('error', "Table '{$table}' already exists in the database. Use 'Alter Existing Table' mode to add or modify columns.");
                }
            } catch (\Throwable $e) {
                // Ignore DB check error if offline
            }
        }

        // Format migration filename
        $timestamp = date('Y_m_d_His');
        if ($migrationType === 'create') {
            $fileName = "{$timestamp}_create_{$table}_table.php";
        } else {
            $suffix = Str::lower(Str::random(4));
            $fileName = "{$timestamp}_alter_{$table}_table_{$suffix}.php";
        }

        $migrationPath = database_path('migrations/' . $fileName);
        $code = $this->generatorService->generateMigrationCode($table, $fields, $migrationType);

        File::put($migrationPath, $code);

        return redirect('/')
            ->with('success', "Migration '{$fileName}' generated successfully!");
    }

    public function previewSql(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'fields' => 'required',
            'migration_type' => 'nullable|in:create,alter',
        ]);

        $table = strtolower(trim($request->table));
        $migrationType = $request->input('migration_type', 'create');
        $fields = $this->generatorService->parseFields($request->fields);

        $code = $this->generatorService->generateMigrationCode($table, $fields, $migrationType);
        $sql = $this->generatorService->generateSqlPreview($table, $fields, $migrationType);
        $shorthand = $this->generatorService->fieldsToRawString($fields);

        return response()->json([
            'success' => true,
            'code' => $code,
            'sql' => $sql,
            'shorthand' => $shorthand,
            'fields_count' => count($fields),
        ]);
    }

    public function getCode($file)
    {
        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            return response()->json(['error' => 'Migration file not found.'], 404);
        }

        $content = File::get($path);

        // Attempt to extract table name and preview SQL
        $sqlPreview = '-- SQL DDL Preview available during migration execution or pretend mode';
        if (preg_match("/Schema::(create|table)\(\s*['\"]([^'\"]+)['\"]/i", $content, $match)) {
            $type = strtolower($match[1]);
            $table = $match[2];
            $sqlPreview = "-- Generated schema for table `{$table}` ({$type})\n-- Inspect PHP code editor tab for exact Blueprint operations.";
        }

        return response()->json([
            'file' => $file,
            'code' => $content,
            'sql' => $sqlPreview,
            'last_modified' => date('Y-m-d H:i:s', File::lastModified($path)),
        ]);
    }

    public function updateCode(Request $request, $file)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            return response()->json(['error' => 'Migration file not found.'], 404);
        }

        File::put($path, $request->code);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Migration '{$file}' updated successfully!",
            ]);
        }

        return back()->with('success', "Migration '{$file}' updated successfully!");
    }

    public function erd()
    {
        $erdData = $this->generatorService->parseMigrationsForErd();
        return response()->json($erdData);
    }

    public function exportErd($format = 'mermaid')
    {
        $erdData = $this->generatorService->parseMigrationsForErd();

        if ($format === 'json') {
            return response($erdData['tables'], 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="database_schema_erd.json"',
            ]);
        }

        $markdown = "# Database Schema Entity Relationship Diagram\n\n```mermaid\n" . $erdData['mermaid'] . "```\n";

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown',
            'Content-Disposition' => 'attachment; filename="database_schema_erd.md"',
        ]);
    }

    public function download($file)
    {
        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function delete($file)
    {
        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            return back()->with('error', 'Migration file not found.');
        }

        $trashPath = storage_path('app/migrations_trash');

        if (!File::exists($trashPath)) {
            File::makeDirectory($trashPath, 0755, true);
        }

        File::move(
            $path,
            $trashPath . '/' . $file
        );

        return back()->with(
            'success',
            'Migration moved to Trash successfully.'
        );
    }

    public function restore($file)
    {
        $trash = storage_path('app/migrations_trash/' . $file);

        if (!File::exists($trash)) {
            return back()->with('error', 'File not found.');
        }

        File::move(
            $trash,
            database_path('migrations/' . $file)
        );

        return back()->with(
            'success',
            'Migration restored successfully.'
        );
    }

    public function destroy($file)
    {
        $trash = storage_path('app/migrations_trash/' . $file);

        if (!File::exists($trash)) {
            return back()->with('error', 'File not found.');
        }

        File::delete($trash);

        return back()->with(
            'success',
            'Migration deleted permanently.'
        );
    }

    public function trash()
    {
        $trashPath = storage_path('app/migrations_trash');

        if (!File::exists($trashPath)) {
            File::makeDirectory($trashPath, 0755, true);
        }

        $files = collect(File::files($trashPath))
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        return view('trash', compact('files'));
    }
}
