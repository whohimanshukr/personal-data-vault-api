<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DataVault\PersonalDataController;
use App\Http\Controllers\DataVault\DataCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Personal Data Vault routes
    Route::apiResource('personal-data', PersonalDataController::class);
    Route::apiResource('data-categories', DataCategoryController::class);
    
    // Search and filter routes
    Route::get('/personal-data/search/{query}', [PersonalDataController::class, 'search']);
    Route::get('/personal-data/category/{category}', [PersonalDataController::class, 'getByCategory']);
    Route::get('/personal-data/export', [PersonalDataController::class, 'export']);
    Route::post('/personal-data/import', [PersonalDataController::class, 'import']);
}); 