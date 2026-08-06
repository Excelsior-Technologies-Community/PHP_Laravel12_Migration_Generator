<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class MigrationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $migrationPath = database_path('migrations');

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
            'tables' => $files->count(),
            'latest' => optional($files->first())->getFilename(),
        ];

        // Pagination
        $perPage = 3;
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

        return view('home', compact(
            'migrations',
            'stats',
            'search'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'table' => 'required|string|alpha_dash',
            'fields' => 'required|string',
        ]);

        $table = strtolower(trim($request->table));

        if (Schema::hasTable($table)) {
            return back()->with('error', "Table '{$table}' already exists.");
        }

        $columns = $this->buildColumns($request->fields);

        if ($request->has('preview')) {

            $preview = $this->generatePreview($table, $columns);

            return back()
                ->withInput()
                ->with('preview', $preview);
        }

        Artisan::call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);

        $migrationPath = database_path('migrations');

        $files = collect(File::files($migrationPath))
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        $migration = $files->first(function ($file) use ($table) {
            return str_contains(
                $file->getFilename(),
                "create_{$table}_table"
            );
        });

        if (!$migration) {
            return back()->with('error', 'Migration not found.');
        }

        $content = File::get($migration->getPathname());

        $newContent = str_replace(
            '$table->id();',
            '$table->id();' . PHP_EOL . $columns,
            $content
        );

        File::put($migration->getPathname(), $newContent);

        return redirect('/')
            ->with('success', 'Migration generated successfully.');
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
    

    private function buildColumns($fieldString)
    {
        $columns = '';

        $fields = explode(',', $fieldString);

        foreach ($fields as $field) {

            $field = trim($field);

            if (empty($field)) {
                continue;
            }

            $parts = explode(':', $field);

            $name = trim($parts[0]);

            $type = $parts[1] ?? 'string';

            switch ($type) {

                case 'integer':
                    $columns .= "            \$table->integer('$name');\n";
                    break;

                case 'bigInteger':
                    $columns .= "            \$table->bigInteger('$name');\n";
                    break;

                case 'decimal':
                    $columns .= "            \$table->decimal('$name',10,2);\n";
                    break;

                case 'float':
                    $columns .= "            \$table->float('$name');\n";
                    break;

                case 'boolean':
                    $columns .= "            \$table->boolean('$name');\n";
                    break;

                case 'text':
                    $columns .= "            \$table->text('$name');\n";
                    break;

                case 'longText':
                    $columns .= "            \$table->longText('$name');\n";
                    break;

                case 'date':
                    $columns .= "            \$table->date('$name');\n";
                    break;

                case 'dateTime':
                    $columns .= "            \$table->dateTime('$name');\n";
                    break;

                case 'timestamp':
                    $columns .= "            \$table->timestamp('$name');\n";
                    break;

                case 'json':
                    $columns .= "            \$table->json('$name');\n";
                    break;

                default:
                    $columns .= "            \$table->string('$name');\n";
            }
        }

        return $columns;
    }

    private function generatePreview($table, $columns)
    {
        return
            "Schema::create('$table', function (Blueprint \$table) {

    \$table->id();

$columns
    \$table->timestamps();

});";
    }
}
