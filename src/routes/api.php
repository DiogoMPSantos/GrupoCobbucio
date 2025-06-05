<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function(Request $request) {
        return $request->user();
    });

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/deposit', [WalletController::class, 'deposit']);
    Route::post('/transfer', [WalletController::class, 'transfer']);
    Route::post('/reverse', [WalletController::class, 'reverseTransaction']);

    Route::get('/transactions', [WalletController::class, 'transactions']);
});
