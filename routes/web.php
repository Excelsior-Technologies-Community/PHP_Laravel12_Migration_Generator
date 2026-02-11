<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MigrationController;

Route::get('/', [MigrationController::class, 'index']);
Route::post('/generate', [MigrationController::class, 'generate']);