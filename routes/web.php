<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MigrationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [MigrationController::class, 'index'])->name('home');

Route::post('/generate', [MigrationController::class, 'generate'])
    ->name('migration.generate');

Route::post('/migration/preview-sql', [MigrationController::class, 'previewSql'])
    ->name('migration.preview_sql');

Route::get('/migration/code/{file}', [MigrationController::class, 'getCode'])
    ->name('migration.code');

Route::post('/migration/update/{file}', [MigrationController::class, 'updateCode'])
    ->name('migration.update');

Route::get('/migration/erd', [MigrationController::class, 'erd'])
    ->name('migration.erd');

Route::get('/migration/erd/export/{format?}', [MigrationController::class, 'exportErd'])
    ->name('migration.erd_export');

Route::get('/migration/download/{file}', [MigrationController::class, 'download'])
    ->name('migration.download');

Route::delete('/migration/delete/{file}', [MigrationController::class, 'delete'])
    ->name('migration.delete');

Route::get('/migration/trash', [MigrationController::class, 'trash'])
    ->name('migration.trash');

Route::post('/migration/restore/{file}', [MigrationController::class, 'restore'])
    ->name('migration.restore');

Route::delete('/migration/destroy/{file}', [MigrationController::class, 'destroy'])
    ->name('migration.destroy');
