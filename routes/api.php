<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SplitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected  
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',          [AuthController::class, 'me']);
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
});
// primer: samo admin
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
     
});

// primer: admin ili user
Route::middleware(['auth:sanctum','role:admin,user'])->group(function () {
    
});

// Public read  
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Za izmene — zaštiti Sanctum-om i ulogom (npr. admin)
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

Route::get('/expenses', [ExpenseController::class, 'index']);
Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);

// Protected writes (Sanctum + role)
Route::middleware(['auth:sanctum','role:admin,user'])->group(function () {
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::patch('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
}); 
Route::get('/splits', [SplitController::class, 'index']);
Route::get('/splits/{split}', [SplitController::class, 'show']);

// Protected writes (Sanctum + role):
Route::middleware(['auth:sanctum','role:admin,user'])->group(function () {
    Route::post('/splits', [SplitController::class, 'store']);
    Route::put('/splits/{split}', [SplitController::class, 'update']);
    Route::patch('/splits/{split}', [SplitController::class, 'update']);
    Route::delete('/splits/{split}', [SplitController::class, 'destroy']);

    // dodatne akcije za status izmirenja
    Route::post('/splits/{split}/settle',   [SplitController::class, 'settle']);
    Route::post('/splits/{split}/unsettle', [SplitController::class, 'unsettle']);
});