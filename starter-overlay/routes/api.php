<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuotesApiController;
use App\Http\Controllers\Api\FavoritesApiController;

Route::get('/quotes', [QuotesApiController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favorites', [FavoritesApiController::class, 'index']);
    Route::post('/favorites', [FavoritesApiController::class, 'store']);
    Route::delete('/favorites', [FavoritesApiController::class, 'destroy']);
});
