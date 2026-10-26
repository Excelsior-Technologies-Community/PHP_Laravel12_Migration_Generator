<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrationGeneratorService
{
    /**
     * Supported column types in Laravel 12 grouped by category.
     */
    public const COLUMN_TYPES = [
        'Common / String' => [
            'string' => 'string (VARCHAR)',
            'char' => 'char (Fixed Length)',
            'text' => 'text (64KB)',
            'mediumText' => 'mediumText (16MB)',
            'longText' => 'longText (4GB)',
        ],
        'Numeric' => [
            'integer' => 'integer (INT)',
            'bigInteger' => 'bigInteger (BIGINT)',
            'tinyInteger' => 'tinyInteger (TINYINT)',
            'smallInteger' => 'smallInteger (SMALLINT)',
            'mediumInteger' => 'mediumInteger (MEDIUMINT)',
            'unsignedInteger' => 'unsignedInteger',
            'unsignedBigInteger' => 'unsignedBigInteger',
            'decimal' => 'decimal (Precision, Scale)',
            'float' => 'float',
            'double' => 'double',
            'boolean' => 'boolean (TINYINT 1)',
        ],
        'Date & Time' => [
            'date' => 'date',
            'dateTime' => 'dateTime',
            'time' => 'time',
            'timestamp' => 'timestamp',
            'timestamps' => 'timestamps (created_at & updated_at)',
            'softDeletes' => 'softDeletes (deleted_at)',
        ],
        'Identifiers & Relations' => [
            'foreignId' => 'foreignId (Constrained FK)',
            'uuid' => 'uuid',
            'ulid' => 'ulid',
            'rememberToken' => 'rememberToken',
        ],
        'Special / Structure' => [
            'json' => 'json',
            'jsonb' => 'jsonb',
            'enum' => 'enum',
            'ipAddress' => 'ipAddress',
            'macAddress' => 'macAddress',
        ],
    ];

    /**
     * Parse raw fields input (from GUI Builder JSON or raw text shorthand).
     */
    public function parseFields($fieldsInput): array
    {
        if (is_array($fieldsInput)) {
            return $this->sanitizeFieldsArray($fieldsInput);
        }

        if (is_string($fieldsInput)) {
            $trimmed = trim($fieldsInput);
            if (Str::startsWith($trimmed, '[') && Str::endsWith($trimmed, ']')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return $this->sanitizeFieldsArray($decoded);
                }
            }
            return $this->parseRawFieldString($trimmed);
        }

        return [];
    }

    /**
     * Sanitize array of fields from GUI builder.
     */
    private function sanitizeFieldsArray(array $rawFields): array
    {
        $sanitized = [];

        foreach ($rawFields as $field) {
            $name = trim($field['name'] ?? '');
            $type = trim($field['type'] ?? 'string');

            if (empty($name) && !in_array($type, ['timestamps', 'softDeletes', 'rememberToken'])) {
                continue;
            }

            $sanitized[] = [
                'name' => Str::snake($name),
                'type' => $type,
                'action' => $field['action'] ?? 'add', // add, modify, rename, drop
                'rename_to' => isset($field['rename_to']) ? Str::snake(trim($field['rename_to'])) : null,
                'length' => !empty($field['length']) ? trim($field['length']) : null,
                'precision' => !empty($field['precision']) ? (int) $field['precision'] : null,
                'scale' => isset($field['scale']) && $field['scale'] !== '' ? (int) $field['scale'] : null,
                'enum_values' => !empty($field['enum_values']) ? trim($field['enum_values']) : null,
                'nullable' => !empty($field['nullable']),
                'unique' => !empty($field['unique']),
                'index' => !empty($field['index']),
                'unsigned' => !empty($field['unsigned']),
                'default' => isset($field['default']) && $field['default'] !== '' ? $field['default'] : null,
                'comment' => !empty($field['comment']) ? trim($field['comment']) : null,
                'after' => !empty($field['after']) ? Str::snake(trim($field['after'])) : null,
                'foreign_table' => !empty($field['foreign_table']) ? Str::snake(trim($field['foreign_table'])) : null,
                'foreign_column' => !empty($field['foreign_column']) ? Str::snake(trim($field['foreign_column'])) : 'id',
                'on_delete' => !empty($field['on_delete']) ? trim($field['on_delete']) : 'cascade',
                'on_update' => !empty($field['on_update']) ? trim($field['on_update']) : null,
            ];
        }

        return $sanitized;
    }

    /**
     * Parse raw string format like:
     * "name:string:nullable, user_id:foreignId:users:cascade, price:decimal(10,2):default(0), status:enum(active,inactive):default(active)"
     */
    public function parseRawFieldString(string $fieldString): array
    {
        $fields = [];
        $tokens = explode(',', $fieldString);

        foreach ($tokens as $token) {
            $token = trim($token);
            if (empty($token)) {
                continue;
            }

            $parts = explode(':', $token);
            $rawName = trim($parts[0] ?? '');
            if (empty($rawName)) {
                continue;
            }

            $rawType = trim($parts[1] ?? 'string');

            $col = [
                'name' => Str::snake($rawName),
                'type' => 'string',
                'action' => 'add',
                'rename_to' => null,
                'length' => null,
                'precision' => null,
                'scale' => null,
                'enum_values' => null,
                'nullable' => false,
                'unique' => false,
                'index' => false,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'after' => null,
                'foreign_table' => null,
                'foreign_column' => 'id',
                'on_delete' => 'cascade',
                'on_update' => null,
            ];

            // Parse type with arguments e.g. decimal(10,2) or enum(active,pending) or string(100)
            if (preg_match('/^([a-zA-Z0-9_]+)\((.*)\)$/', $rawType, $matches)) {
                $baseType = $matches[1];
                $args = $matches[2];
                $col['type'] = $baseType;

                if ($baseType === 'decimal') {
                    $argParts = explode(',', $args);
                    $col['precision'] = isset($argParts[0]) ? (int) trim($argParts[0]) : 10;
                    $col['scale'] = isset($argParts[1]) ? (int) trim($argParts[1]) : 2;
                } elseif ($baseType === 'enum') {
                    $col['enum_values'] = $args;
                } elseif (in_array($baseType, ['string', 'char'])) {
                    $col['length'] = trim($args);
                }
            } else {
                $col['type'] = $rawType;
            }

            // Check if name is foreign key convention e.g. user_id
            if ($col['type'] === 'foreignId' || (str_ends_with($col['name'], '_id') && !in_array($col['type'], ['integer', 'bigInteger']))) {
                if ($col['type'] !== 'foreignId') {
                    $col['type'] = 'foreignId';
                }
                $suggestedTable = Str::plural(str_replace('_id', '', $col['name']));
                $col['foreign_table'] = $suggestedTable;
            }

            // Parse additional modifiers in parts[2...]
            for ($i = 2; $i < count($parts); $i++) {
                $modifier = trim($parts[$i]);
                if (empty($modifier)) {
                    continue;
                }

                if ($modifier === 'nullable') {
                    $col['nullable'] = true;
                } elseif ($modifier === 'unique') {
                    $col['unique'] = true;
                } elseif ($modifier === 'index') {
                    $col['index'] = true;
                } elseif ($modifier === 'unsigned') {
                    $col['unsigned'] = true;
                } elseif (preg_match('/^default\((.*)\)$/', $modifier, $defMatches)) {
                    $col['default'] = trim($defMatches[1], "'\" ");
                } elseif (preg_match('/^after\((.*)\)$/', $modifier, $afterMatches)) {
                    $col['after'] = trim($afterMatches[1]);
                } elseif (preg_match('/^foreign\((.*)\)$/', $modifier, $fMatches)) {
                    $col['type'] = 'foreignId';
                    $col['foreign_table'] = trim($fMatches[1]);
                } elseif ($modifier === 'cascade') {
                    $col['on_delete'] = 'cascade';
                } elseif ($modifier === 'null') {
                    $col['on_delete'] = 'null';
                } elseif (in_array($col['type'], ['foreignId']) && empty($col['foreign_table'])) {
                    $col['foreign_table'] = $modifier;
                }
            }

            $fields[] = $col;
        }

        return $fields;
    }

    /**
     * Convert fields array to raw string shorthand representation.
     */
    public function fieldsToRawString(array $fields): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $name = $field['name'] ?? '';
            $type = $field['type'] ?? 'string';

            if (empty($name) && !in_array($type, ['timestamps', 'softDeletes', 'rememberToken'])) {
                continue;
            }

            $typeStr = $type;
            if ($type === 'decimal' && !empty($field['precision'])) {
                $typeStr = "decimal({$field['precision']}," . ($field['scale'] ?? 2) . ")";
            } elseif ($type === 'enum' && !empty($field['enum_values'])) {
                $typeStr = "enum({$field['enum_values']})";
            } elseif (!empty($field['length']) && in_array($type, ['string', 'char'])) {
                $typeStr = "{$type}({$field['length']})";
            }

            $colStr = "{$name}:{$typeStr}";

            if (!empty($field['foreign_table'])) {
                $colStr .= ":foreign({$field['foreign_table']})";
                if (!empty($field['on_delete'])) {
                    $colStr .= ":{$field['on_delete']}";
                }
            }

            if (!empty($field['nullable'])) {
                $colStr .= ":nullable";
            }
            if (!empty($field['unique'])) {
                $colStr .= ":unique";
            }
            if (!empty($field['index'])) {
                $colStr .= ":index";
            }
            if (!empty($field['unsigned'])) {
                $colStr .= ":unsigned";
            }
            if (isset($field['default']) && $field['default'] !== '') {
                $colStr .= ":default({$field['default']})";
            }
            if (!empty($field['after'])) {
                $colStr .= ":after({$field['after']})";
            }

            $parts[] = $colStr;
        }

        return implode(', ', $parts);
    }

    /**
     * Build Blueprint column lines for up() method.
     */
    public function buildColumnsCode(array $fields, string $migrationType = 'create'): string
    {
        $lines = [];

        foreach ($fields as $col) {
            $action = $col['action'] ?? 'add';
            $name = $col['name'] ?? '';
            $type = $col['type'] ?? 'string';

            // Alter mode: Drop column
            if ($migrationType === 'alter' && $action === 'drop') {
                $lines[] = "\$table->dropColumn('{$name}');";
                continue;
            }

            // Alter mode: Rename column
            if ($migrationType === 'alter' && $action === 'rename') {
                $renameTo = $col['rename_to'] ?? ($name . '_renamed');
                $lines[] = "\$table->renameColumn('{$name}', '{$renameTo}');";
                continue;
            }

            // Special helper types without column name
            if ($type === 'timestamps') {
                $lines[] = "\$table->timestamps();";
                continue;
            }
            if ($type === 'softDeletes') {
                $lines[] = "\$table->softDeletes();";
                continue;
            }
            if ($type === 'rememberToken') {
                $lines[] = "\$table->rememberToken();";
                continue;
            }

            if (empty($name)) {
                continue;
            }

            // Start building column definition
            $line = '';

            if ($type === 'foreignId') {
                $line = "\$table->foreignId('{$name}')";
                if (!empty($col['nullable'])) {
                    $line .= "->nullable()";
                }
                if (!empty($col['foreign_table'])) {
                    $targetCol = !empty($col['foreign_column']) ? $col['foreign_column'] : 'id';
                    if ($targetCol === 'id') {
                        $line .= "->constrained('{$col['foreign_table']}')";
                    } else {
                        $line .= "->constrained('{$col['foreign_table']}', '{$targetCol}')";
                    }
                } else {
                    $line .= "->constrained()";
                }

                $onDelete = $col['on_delete'] ?? 'cascade';
                if ($onDelete === 'cascade' || $onDelete === 'cascadeOnDelete') {
                    $line .= "->cascadeOnDelete()";
                } elseif ($onDelete === 'null' || $onDelete === 'nullOnDelete') {
                    $line .= "->nullOnDelete()";
                } elseif ($onDelete === 'restrict' || $onDelete === 'restrictOnDelete') {
                    $line .= "->restrictOnDelete()";
                } elseif ($onDelete === 'no action' || $onDelete === 'noActionOnDelete') {
                    $line .= "->noActionOnDelete()";
                }

                if (!empty($col['on_update']) && in_array($col['on_update'], ['cascade', 'restrict'])) {
                    $line .= "->onUpdate('{$col['on_update']}')";
                }
            } elseif ($type === 'decimal') {
                $prec = $col['precision'] ?? 10;
                $scale = $col['scale'] ?? 2;
                $line = "\$table->decimal('{$name}', {$prec}, {$scale})";
            } elseif ($type === 'enum') {
                $rawVals = $col['enum_values'] ?? 'pending,completed';
                $vals = array_map(fn($v) => "'" . trim(trim($v), "'\"") . "'", explode(',', $rawVals));
                $arrayStr = '[' . implode(', ', $vals) . ']';
                $line = "\$table->enum('{$name}', {$arrayStr})";
            } elseif (!empty($col['length']) && in_array($type, ['string', 'char'])) {
                $length = (int) $col['length'];
                $line = "\$table->{$type}('{$name}', {$length})";
            } else {
                $line = "\$table->{$type}('{$name}')";
            }

            // Append modifiers (for non-foreignId or general modifiers)
            if ($type !== 'foreignId') {
                if (!empty($col['unsigned'])) {
                    $line .= "->unsigned()";
                }
                if (!empty($col['nullable'])) {
                    $line .= "->nullable()";
                }
                if (!empty($col['unique'])) {
                    $line .= "->unique()";
                }
                if (!empty($col['index'])) {
                    $line .= "->index()";
                }
                if (isset($col['default']) && $col['default'] !== '') {
                    $def = $col['default'];
                    if (is_numeric($def)) {
                        $line .= "->default({$def})";
                    } elseif (in_array(strtolower($def), ['true', 'false'])) {
                        $line .= "->default(" . strtolower($def) . ")";
                    } elseif (strtolower($def) === 'null') {
                        $line .= "->default(null)";
                    } else {
                        $escaped = addslashes($def);
                        $line .= "->default('{$escaped}')";
                    }
                }
            }

            if (!empty($col['comment'])) {
                $escaped = addslashes($col['comment']);
                $line .= "->comment('{$escaped}')";
            }

            // In Alter Mode
            if ($migrationType === 'alter') {
                if (!empty($col['after'])) {
                    $line .= "->after('{$col['after']}')";
                }
                if ($action === 'modify') {
                    $line .= "->change()";
                }
            }

            $line .= ";";
            $lines[] = $line;
        }

        return implode("\n            ", $lines);
    }

    /**
     * Build down() method code for automated rollback.
     */
    public function buildDownCode(array $fields, string $table, string $migrationType = 'create'): string
    {
        if ($migrationType === 'create') {
            return "Schema::dropIfExists('{$table}');";
        }

        // Alter Table mode: reverse operations
        $reverseLines = [];
        $addedColumns = [];

        foreach ($fields as $col) {
            $action = $col['action'] ?? 'add';
            $name = $col['name'] ?? '';
            $renameTo = $col['rename_to'] ?? '';

            if ($action === 'add' && !empty($name)) {
                $addedColumns[] = "'{$name}'";
            } elseif ($action === 'rename' && !empty($name) && !empty($renameTo)) {
                $reverseLines[] = "\$table->renameColumn('{$renameTo}', '{$name}');";
            } elseif ($action === 'drop' && !empty($name)) {
                $type = $col['type'] ?? 'string';
                $reverseLines[] = "// Re-add dropped column: \$table->{$type}('{$name}');";
            }
        }

        if (!empty($addedColumns)) {
            if (count($addedColumns) === 1) {
                $reverseLines[] = "\$table->dropColumn({$addedColumns[0]});";
            } else {
                $colsStr = '[' . implode(', ', $addedColumns) . ']';
                $reverseLines[] = "\$table->dropColumn({$colsStr});";
            }
        }

        if (empty($reverseLines)) {
            return "// Reverse operations not required or schema cannot be safely auto-reversed";
        }

        $code = "Schema::table('{$table}', function (Blueprint \$table) {\n";
        $code .= "            " . implode("\n            ", $reverseLines) . "\n";
        $code .= "        });";

        return $code;
    }

    /**
     * Generate full PHP Migration class code.
     */
    public function generateMigrationCode(string $table, array $fields, string $migrationType = 'create'): string
    {
        $columnsCode = $this->buildColumnsCode($fields, $migrationType);
        $downCode = $this->buildDownCode($fields, $table, $migrationType);

        if ($migrationType === 'create') {
            $upContent = "Schema::create('{$table}', function (Blueprint \$table) {\n";
            $upContent .= "            \$table->id();\n";
            if (!empty($columnsCode)) {
                $upContent .= "            " . $columnsCode . "\n";
            }
            $upContent .= "            \$table->timestamps();\n";
            $upContent .= "        });";
        } else {
            $upContent = "Schema::table('{$table}', function (Blueprint \$table) {\n";
            if (!empty($columnsCode)) {
                $upContent .= "            " . $columnsCode . "\n";
            }
            $upContent .= "        });";
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        {$upContent}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        {$downCode}
    }
};
PHP;
    }

    /**
     * Generate live SQL DDL preview without modifying the database.
     */
    public function generateSqlPreview(string $table, array $fields, string $migrationType = 'create'): string
    {
        try {
            $queries = DB::connection()->pretend(function () use ($table, $fields, $migrationType) {
                if ($migrationType === 'create') {
                    Schema::create($table, function (\Illuminate\Database\Schema\Blueprint $blueprint) use ($fields) {
                        $blueprint->id();
                        $this->applyBlueprintColumns($blueprint, $fields, 'create');
                        $blueprint->timestamps();
                    });
                } else {
                    Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $blueprint) use ($fields) {
                        $this->applyBlueprintColumns($blueprint, $fields, 'alter');
                    });
                }
            });

            if (!empty($queries)) {
                $sqlLines = array_map(function ($item) {
                    $query = is_array($item) ? ($item['query'] ?? '') : (string) $item;
                    return rtrim($query, ';') . ';';
                }, $queries);
                return implode("\n\n", $sqlLines);
            }
        } catch (\Throwable $e) {
            // Fallback manual DDL generator
            return $this->fallbackSqlPreview($table, $fields, $migrationType);
        }

        return $this->fallbackSqlPreview($table, $fields, $migrationType);
    }

    /**
     * Helper to apply fields onto a Blueprint instance.
     */
    private function applyBlueprintColumns(\Illuminate\Database\Schema\Blueprint $table, array $fields, string $migrationType): void
    {
        foreach ($fields as $col) {
            $name = $col['name'] ?? '';
            $type = $col['type'] ?? 'string';
            $action = $col['action'] ?? 'add';

            if ($migrationType === 'alter' && $action === 'drop' && !empty($name)) {
                $table->dropColumn($name);
                continue;
            }
            if ($migrationType === 'alter' && $action === 'rename' && !empty($name)) {
                $table->renameColumn($name, $col['rename_to'] ?? ($name . '_renamed'));
                continue;
            }

            if ($type === 'timestamps') {
                $table->timestamps();
                continue;
            }
            if ($type === 'softDeletes') {
                $table->softDeletes();
                continue;
            }
            if ($type === 'rememberToken') {
                $table->rememberToken();
                continue;
            }
            if (empty($name)) {
                continue;
            }

            $columnObj = null;
            if ($type === 'foreignId') {
                $columnObj = $table->foreignId($name);
            } elseif ($type === 'decimal') {
                $prec = $col['precision'] ?? 10;
                $scale = $col['scale'] ?? 2;
                $columnObj = $table->decimal($name, $prec, $scale);
            } elseif ($type === 'enum') {
                $rawVals = $col['enum_values'] ?? 'pending,completed';
                $vals = array_map('trim', explode(',', $rawVals));
                $columnObj = $table->enum($name, $vals);
            } elseif (!empty($col['length']) && in_array($type, ['string', 'char'])) {
                $columnObj = $table->{$type}($name, (int) $col['length']);
            } elseif (method_exists($table, $type)) {
                $columnObj = $table->{$type}($name);
            } else {
                $columnObj = $table->string($name);
            }

            if ($columnObj) {
                if (!empty($col['nullable'])) {
                    $columnObj->nullable();
                }
                if (!empty($col['unsigned'])) {
                    $columnObj->unsigned();
                }
                if (!empty($col['unique'])) {
                    $columnObj->unique();
                }
                if (!empty($col['index'])) {
                    $columnObj->index();
                }
                if (isset($col['default']) && $col['default'] !== '') {
                    $columnObj->default($col['default']);
                }
                if (!empty($col['comment'])) {
                    $columnObj->comment($col['comment']);
                }
            }
        }
    }

    /**
     * Fallback standard SQL DDL generator.
     */
    private function fallbackSqlPreview(string $table, array $fields, string $migrationType): string
    {
        if ($migrationType === 'create') {
            $cols = ["  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY"];

            foreach ($fields as $col) {
                $name = $col['name'] ?? '';
                $type = $col['type'] ?? 'string';
                if (empty($name) && !in_array($type, ['timestamps', 'softDeletes'])) {
                    continue;
                }

                if ($type === 'timestamps') {
                    $cols[] = "  `created_at` TIMESTAMP NULL DEFAULT NULL";
                    $cols[] = "  `updated_at` TIMESTAMP NULL DEFAULT NULL";
                    continue;
                }
                if ($type === 'softDeletes') {
                    $cols[] = "  `deleted_at` TIMESTAMP NULL DEFAULT NULL";
                    continue;
                }

                $sqlType = match ($type) {
                    'string' => 'VARCHAR(' . ($col['length'] ?? 255) . ')',
                    'char' => 'CHAR(' . ($col['length'] ?? 255) . ')',
                    'text' => 'TEXT',
                    'mediumText' => 'MEDIUMTEXT',
                    'longText' => 'LONGTEXT',
                    'integer' => 'INT',
                    'bigInteger' => 'BIGINT',
                    'unsignedBigInteger', 'foreignId' => 'BIGINT UNSIGNED',
                    'tinyInteger' => 'TINYINT',
                    'smallInteger' => 'SMALLINT',
                    'mediumInteger' => 'MEDIUMINT',
                    'boolean' => 'TINYINT(1)',
                    'decimal' => 'DECIMAL(' . ($col['precision'] ?? 10) . ', ' . ($col['scale'] ?? 2) . ')',
                    'float' => 'FLOAT',
                    'double' => 'DOUBLE',
                    'date' => 'DATE',
                    'dateTime' => 'DATETIME',
                    'timestamp' => 'TIMESTAMP',
                    'json', 'jsonb' => 'JSON',
                    'uuid' => 'CHAR(36)',
                    'ulid' => 'CHAR(26)',
                    'enum' => 'ENUM(' . ($col['enum_values'] ?? "'active','inactive'") . ')',
                    default => 'VARCHAR(255)',
                };

                $def = "  `{$name}` {$sqlType}";
                if (!empty($col['unsigned']) && !str_contains($sqlType, 'UNSIGNED')) {
                    $def .= " UNSIGNED";
                }
                $def .= !empty($col['nullable']) ? " NULL" : " NOT NULL";
                if (isset($col['default']) && $col['default'] !== '') {
                    $def .= " DEFAULT '{$col['default']}'";
                }
                if (!empty($col['unique'])) {
                    $def .= " UNIQUE";
                }
                if (!empty($col['comment'])) {
                    $def .= " COMMENT '{$col['comment']}'";
                }

                $cols[] = $def;

                if ($type === 'foreignId' && !empty($col['foreign_table'])) {
                    $targetCol = $col['foreign_column'] ?? 'id';
                    $cols[] = "  CONSTRAINT `fk_{$table}_{$name}` FOREIGN KEY (`{$name}`) REFERENCES `{$col['foreign_table']}` (`{$targetCol}`) ON DELETE " . strtoupper($col['on_delete'] ?? 'CASCADE');
                }
            }

            return "CREATE TABLE `{$table}` (\n" . implode(",\n", $cols) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        }

        // Alter mode
        $alterLines = [];
        foreach ($fields as $col) {
            $name = $col['name'] ?? '';
            $action = $col['action'] ?? 'add';
            if ($action === 'drop' && !empty($name)) {
                $alterLines[] = "ALTER TABLE `{$table}` DROP COLUMN `{$name}`;";
            } elseif ($action === 'rename' && !empty($name)) {
                $renameTo = $col['rename_to'] ?? ($name . '_renamed');
                $alterLines[] = "ALTER TABLE `{$table}` RENAME COLUMN `{$name}` TO `{$renameTo}`;";
            } elseif ($action === 'modify' && !empty($name)) {
                $alterLines[] = "ALTER TABLE `{$table}` MODIFY COLUMN `{$name}` VARCHAR(255);";
            } else {
                $alterLines[] = "ALTER TABLE `{$table}` ADD COLUMN `{$name}` VARCHAR(255);";
            }
        }
        return !empty($alterLines) ? implode("\n", $alterLines) : "-- No alter operations defined";
    }

    /**
     * Parse all existing migration files to generate complete ERD metadata and Mermaid diagram.
     */
    public function parseMigrationsForErd(): array
    {
        $migrationPath = database_path('migrations');
        $files = collect(File::files($migrationPath))->sortBy('filename');

        $tables = [];
        $relationships = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Match Schema::create('table_name', function ...
            // We search for Schema::create('tablename', and grab the text until closing });
            $offset = 0;
            while (($pos = stripos($content, 'Schema::create(', $offset)) !== false) {
                // Find closing paren of Schema::create
                $endPos = strpos($content, '});', $pos);
                if ($endPos === false) {
                    $offset = $pos + 14;
                    continue;
                }

                $block = substr($content, $pos, $endPos - $pos + 3);
                $offset = $endPos + 3;

                if (preg_match("/Schema::create\(\s*['\"]([^'\"]+)['\"]/i", $block, $tMatch)) {
                    $tableName = $tMatch[1];

                    if (!isset($tables[$tableName])) {
                        $tables[$tableName] = [
                            'name' => $tableName,
                            'columns' => [],
                            'file' => $file->getFilename(),
                        ];
                    }

                    // Extract columns inside this block
                    $lines = explode("\n", $block);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!str_starts_with($line, '$table->')) {
                            continue;
                        }

                        if (preg_match('/\$table->(id|bigIncrements|increments)\s*\(/i', $line)) {
                            $tables[$tableName]['columns']['id'] = [
                                'name' => 'id',
                                'type' => 'bigint',
                                'key' => 'PK',
                            ];
                            continue;
                        }

                        if (preg_match('/\$table->timestamps\s*\(/i', $line)) {
                            $tables[$tableName]['columns']['created_at'] = ['name' => 'created_at', 'type' => 'timestamp', 'key' => ''];
                            $tables[$tableName]['columns']['updated_at'] = ['name' => 'updated_at', 'type' => 'timestamp', 'key' => ''];
                            continue;
                        }

                        if (preg_match('/\$table->softDeletes\s*\(/i', $line)) {
                            $tables[$tableName]['columns']['deleted_at'] = ['name' => 'deleted_at', 'type' => 'timestamp', 'key' => ''];
                            continue;
                        }

                        if (preg_match('/\$table->rememberToken\s*\(/i', $line)) {
                            $tables[$tableName]['columns']['remember_token'] = ['name' => 'remember_token', 'type' => 'string', 'key' => ''];
                            continue;
                        }

                        // General column: $table->string('title') or $table->foreignId('user_id')
                        if (preg_match('/\$table->([a-zA-Z0-9_]+)\(\s*[\'"]([^\'"]+)[\'"]/i', $line, $cMatch)) {
                            $colType = $cMatch[1];
                            $colName = $cMatch[2];

                            $isFk = ($colType === 'foreignId' || str_ends_with($colName, '_id'));
                            $isPk = str_contains($line, '->primary(');

                            $key = '';
                            if ($isPk) {
                                $key = 'PK';
                            } elseif ($isFk) {
                                $key = 'FK';
                            }

                            $tables[$tableName]['columns'][$colName] = [
                                'name' => $colName,
                                'type' => $colType,
                                'key' => $key,
                            ];

                            // Extract relationships
                            if ($isFk) {
                                $relatedTable = Str::plural(str_replace('_id', '', $colName));
                                if (preg_match("/constrained\(\s*['\"]([^'\"]+)['\"]/i", $line, $constMatch)) {
                                    $relatedTable = $constMatch[1];
                                }

                                $relationships[] = [
                                    'from' => $relatedTable,
                                    'to' => $tableName,
                                    'column' => $colName,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Generate Mermaid ERD markup
        $mermaid = "erDiagram\n";

        foreach ($tables as $tName => $data) {
            $mermaid .= "    {$tName} {\n";
            if (empty($data['columns'])) {
                $mermaid .= "        bigint id PK\n";
            } else {
                foreach ($data['columns'] as $c) {
                    $type = preg_replace('/[^a-zA-Z0-9_]/', '', $c['type']);
                    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $c['name']);
                    $key = !empty($c['key']) ? " {$c['key']}" : "";
                    $mermaid .= "        {$type} {$name}{$key}\n";
                }
            }
            $mermaid .= "    }\n";
        }

        // Add relationships - Only link if BOTH entities are present in $tables to avoid NaN layout coordinates
        $addedRels = [];
        $validRelations = [];
        foreach ($relationships as $rel) {
            $from = $rel['from'];
            $to = $rel['to'];

            // Skip if either entity is missing from parsed migration tables
            if (!isset($tables[$from]) || !isset($tables[$to])) {
                continue;
            }

            $relKey = "{$from}->{$to}:{$rel['column']}";
            if (isset($addedRels[$relKey])) {
                continue;
            }
            $addedRels[$relKey] = true;

            $colLabel = preg_replace('/[^a-zA-Z0-9_]/', '', $rel['column']);
            $mermaid .= "    {$from} ||--o{ {$to} : {$colLabel}\n";
            $validRelations[] = $rel;
        }

        return [
            'tables' => $tables,
            'relationships' => $validRelations,
            'mermaid' => $mermaid,
            'total_tables' => count($tables),
            'total_relations' => count($validRelations),
        ];
    }

    /**
     * Get all known clean table names.
     */
    public function getKnownTables(): array
    {
        $tables = [];

        try {
            $dbTables = Schema::getTableListing();
            foreach ($dbTables as $t) {
                if (!str_contains($t, '.')) {
                    $tables[] = $t;
                }
            }
        } catch (\Throwable $e) {
            // Ignore DB exceptions
        }

        // Also gather tables from migrations
        $migrationPath = database_path('migrations');
        if (File::exists($migrationPath)) {
            foreach (File::files($migrationPath) as $file) {
                $content = File::get($file->getPathname());
                if (preg_match_all("/Schema::(?:create|table)\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                    foreach ($matches[1] as $tbl) {
                        if (!in_array($tbl, $tables) && !empty($tbl) && !str_contains($tbl, '.')) {
                            $tables[] = $tbl;
                        }
                    }
                }
            }
        }

        sort($tables);
        return array_values(array_unique($tables));
    }
}
