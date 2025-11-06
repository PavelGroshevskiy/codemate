<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/balance/{userId}', [UserController::class, 'getBalance']);
Route::post('/deposit', [UserController::class, 'addBalanceCash']);
Route::post('/withdraw', [UserController::class, 'withdrawCash']);
Route::post('/transfer', [UserController::class, 'userTransfer']);
