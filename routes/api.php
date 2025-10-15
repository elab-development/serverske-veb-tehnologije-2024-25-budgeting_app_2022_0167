<?php

use App\Http\Controllers\AdminStatsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SplitController;
use App\Http\Controllers\SettlementController; 
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserExportController;

/* ------------------------ GUEST (no auth) ------------------------ */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/categories',           [CategoryController::class, 'index']); // javno
Route::get('/categories/{category}',[CategoryController::class, 'show']);  // javno

/* ------------------------ AUTH (any role) ------------------------ */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',          [AuthController::class, 'me']);
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
});

/* ------------------------ USER ONLY ------------------------------ */
Route::middleware(['auth:sanctum','role:user'])->group(function () {
    // Expenses – kreiranje/izmena/brisanje sopstvenih
    Route::post('/expenses',                   [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}',          [ExpenseController::class, 'update']);
    Route::patch('/expenses/{expense}',        [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}',       [ExpenseController::class, 'destroy']);

    // Splits – kreiranje/izmena/brisanje + akcije settle/unsettle
    Route::post('/splits',                     [SplitController::class, 'store']);
    Route::put('/splits/{split}',              [SplitController::class, 'update']);
    Route::patch('/splits/{split}',            [SplitController::class, 'update']);
    Route::delete('/splits/{split}',           [SplitController::class, 'destroy']);
    Route::post('/splits/{split}/settle',      [SplitController::class, 'settle']);
    Route::post('/splits/{split}/unsettle',    [SplitController::class, 'unsettle']);

    // Settlements – kreiranje/izmena/brisanje sopstvenih
    Route::post('/settlements',                [SettlementController::class, 'store']);
    Route::put('/settlements/{settlement}',    [SettlementController::class, 'update']);
    Route::patch('/settlements/{settlement}',  [SettlementController::class, 'update']);
    Route::delete('/settlements/{settlement}', [SettlementController::class, 'destroy']);
});

/* ------------------------ ADMIN ONLY ----------------------------- */
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    // Categories – CRUD (samo admin)
    Route::post('/categories',                 [CategoryController::class, 'store']);
    Route::put('/categories/{category}',       [CategoryController::class, 'update']);
    Route::patch('/categories/{category}',     [CategoryController::class, 'update']);
    Route::delete('/categories/{category}',    [CategoryController::class, 'destroy']);


    Route::get('/admin/stats/overview',     [AdminStatsController::class, 'overview']);
    Route::get('/admin/stats/debts-matrix', [AdminStatsController::class, 'debtsMatrix']);

});


/* --------- ADMIN or USER (čitanje listi i detalja) --------------- */
 
Route::middleware(['auth:sanctum','role:admin,user'])->group(function () {
    // Expenses – read
    Route::get('/expenses',           [ExpenseController::class, 'index']);
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);

    // Splits – read
    Route::get('/splits',             [SplitController::class, 'index']);
    Route::get('/splits/{split}',     [SplitController::class, 'show']);

    // Settlements – read
    Route::get('/settlements',                [SettlementController::class, 'index']);
    Route::get('/settlements/{settlement}',   [SettlementController::class, 'show']);
});


Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:6,1'); // zaštita od spam-a

Route::post('/password/reset', [PasswordResetController::class, 'reset']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}/export/transactions.csv',
        [UserExportController::class, 'exportUserTransactions']
    )->name('users.export.transactions');
});