<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TimeBlockController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->patch('/me', [App\Http\Controllers\AuthController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/preferences', [\App\Http\Controllers\AuthController::class, 'getPreferences']);
    Route::patch('/me/preferences', [\App\Http\Controllers\AuthController::class, 'updatePreferences']);
});

Route::middleware('auth:sanctum')->apiResource('clients', ClientController::class);
Route::middleware('auth:sanctum')->apiResource('sessions', SessionController::class);
Route::middleware('auth:sanctum')->apiResource('time-blocks', TimeBlockController::class);

Route::middleware('auth:sanctum')->get('calendar-items', [SessionController::class, 'calendarItems']);
Route::middleware('auth:sanctum')->get('dashboard/statistics', [SessionController::class, 'dashboardStatistics']);
Route::middleware('auth:sanctum')->post('/upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload']);
