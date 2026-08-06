<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MigrationController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'table' => 'required|string|alpha_dash',
            'fields' => 'required|string',
        ]);

        $table = strtolower(trim($request->table));

        // Check if table already exists
        if (Schema::hasTable($table)) {
            return back()->with('error', "Table '{$table}' already exists.");
        }

        // Parse fields
        $columns = $this->buildColumns($request->fields);

        // Preview only
        if ($request->has('preview')) {

            $preview = $this->generatePreview($table, $columns);

            return back()
                ->withInput()
                ->with('preview', $preview);
        }

        // Generate migration
        Artisan::call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table
        ]);

        // Find latest migration
        $migrationPath = database_path('migrations');
        $files = collect(File::files($migrationPath))
            ->sortByDesc(fn($file) => $file->getMTime());

        $migration = $files->first(function ($file) use ($table) {
            return str_contains($file->getFilename(), "create_{$table}_table");
        });

        if (!$migration) {
            return back()->with('error', 'Migration file not found.');
        }

        $content = File::get($migration->getPathname());

        // Insert generated columns immediately after $table->id();
        $newContent = str_replace(
            "\$table->id();",
            "\$table->id();\n" . $columns,
            $content
        );

        File::put($migration->getPathname(), $newContent);

        return back()->with(
            'success',
            "Migration generated successfully! Run: php artisan migrate"
        );
    }

    /**
     * Build migration columns
     */
    private function buildColumns($fieldString)
    {
        $columns = "";

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
                    $columns .= "            \$table->integer('{$name}');\n";
                    break;

                case 'bigInteger':
                    $columns .= "            \$table->bigInteger('{$name}');\n";
                    break;

                case 'decimal':
                    $columns .= "            \$table->decimal('{$name}',10,2);\n";
                    break;

                case 'float':
                    $columns .= "            \$table->float('{$name}');\n";
                    break;

                case 'boolean':
                    $columns .= "            \$table->boolean('{$name}');\n";
                    break;

                case 'text':
                    $columns .= "            \$table->text('{$name}');\n";
                    break;

                case 'longText':
                    $columns .= "            \$table->longText('{$name}');\n";
                    break;

                case 'date':
                    $columns .= "            \$table->date('{$name}');\n";
                    break;

                case 'dateTime':
                    $columns .= "            \$table->dateTime('{$name}');\n";
                    break;

                case 'timestamp':
                    $columns .= "            \$table->timestamp('{$name}');\n";
                    break;

                case 'json':
                    $columns .= "            \$table->json('{$name}');\n";
                    break;

                default:
                    $columns .= "            \$table->string('{$name}');\n";
            }
        }

        return $columns;
    }

    /**
     * Preview code
     */
    private function generatePreview($table, $columns)
    {
        return
            "Schema::create('{$table}', function (Blueprint \$table) {
    \$table->id();
{$columns}    \$table->timestamps();
});";
    }
}
