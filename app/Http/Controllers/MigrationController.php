<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

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
            'fields' => 'required|string'
        ]);

        $table = $request->table;
        $fields = explode(',', $request->fields);

        // Generate migration
        Artisan::call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table
        ]);

        // Get the latest migration file for this table
        $migrationPath = database_path('migrations');
        $files = File::files($migrationPath);
        
        $latestMigration = null;
        foreach ($files as $file) {
            if (str_contains($file->getFilename(), "create_{$table}_table")) {
                $latestMigration = $file->getPathname();
            }
        }

        if ($latestMigration) {
            $content = File::get($latestMigration);
            
            // Build columns string
            $columns = "";
            foreach ($fields as $field) {
                $field = trim($field);
                if (!empty($field)) {
                    $columns .= "            \$table->string('{$field}');\n";
                }
            }

            // Replace the comment with columns
            $newContent = preg_replace(
                "/Schema::create\('{$table}', function \(Blueprint \$table\) \{\s*\$table->id\(\);\s*(.*?)\s*\$table->timestamps\(\);\s*\}\);/s",
                "Schema::create('{$table}', function (Blueprint \$table) {\n            \$table->id();\n{$columns}            \$table->timestamps();\n        });",
                $content
            );

            File::put($latestMigration, $newContent);
            
            return back()->with('success', "Migration created successfully! Run: php artisan migrate");
        }

        return back()->with('error', 'Migration file not found!');
    }
}