<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SplitController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\AdminStatsController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserExportController;
use App\Http\Controllers\RatesController;

/*
|--------------------------------------------------------------------------
| PUBLIC (bez autentikacije)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

/* Resource primer (read-only, javno) */
Route::apiResource('categories', CategoryController::class)->only(['index','show']);

/* Reset lozinke (public, throttled) */
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:6,1');
Route::post('/password/reset',  [PasswordResetController::class, 'reset']);

/* Javni kursni list (proxy ka spoljnjem servisu) */
Route::get('/rates', [RatesController::class, 'latest']); // ?from=EUR&to=USD,RSD

/*
|--------------------------------------------------------------------------
| AUTH (bilo koja uloga)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',          [AuthController::class, 'me']);
    Route::post('/logout',     [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    /* Export transakcija – dozvoljeno vlasniku ili adminu (provera u kontroleru) */
    Route::get('/users/{user}/export/transactions.csv',
        [UserExportController::class, 'exportUserTransactions']
    )->name('users.export.transactions');
});

/*
|--------------------------------------------------------------------------
| USER (krajnji korisnik)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','role:user'])->group(function () {
    // Expenses – create/update/delete svojih
    Route::post('/expenses',             [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}',    [ExpenseController::class, 'update']);
    Route::patch('/expenses/{expense}',  [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Splits – create/update/delete + settle/unsettle
    Route::post('/splits',                     [SplitController::class, 'store']);
    Route::put('/splits/{split}',              [SplitController::class, 'update']);
    Route::patch('/splits/{split}',            [SplitController::class, 'update']);
    Route::delete('/splits/{split}',           [SplitController::class, 'destroy']);
    Route::post('/splits/{split}/settle',      [SplitController::class, 'settle']);
    Route::post('/splits/{split}/unsettle',    [SplitController::class, 'unsettle']);

    // Settlements – create/update/delete svojih
    Route::post('/settlements',                [SettlementController::class, 'store']);
    Route::put('/settlements/{settlement}',    [SettlementController::class, 'update']);
    Route::patch('/settlements/{settlement}',  [SettlementController::class, 'update']);
    Route::delete('/settlements/{settlement}', [SettlementController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    // Categories – CRUD (admin-only)
    Route::post('/categories',                 [CategoryController::class, 'store']);
    Route::put('/categories/{category}',       [CategoryController::class, 'update']);
    Route::patch('/categories/{category}',     [CategoryController::class, 'update']);
    Route::delete('/categories/{category}',    [CategoryController::class, 'destroy']);

    // Admin statistika
    Route::get('/admin/stats/overview',        [AdminStatsController::class, 'overview']);
    Route::get('/admin/stats/debts-matrix',    [AdminStatsController::class, 'debtsMatrix']);
});

/*
|--------------------------------------------------------------------------
| READ (oba tipa: admin ili user) – liste i detalji
|--------------------------------------------------------------------------
*/
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
