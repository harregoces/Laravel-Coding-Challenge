<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodayController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\FavoritesController;

Route::get('/', fn() => redirect('/today'));
Route::get('/today', TodayController::class);
Route::get('/quotes', [QuotesController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index']);
    Route::post('/favorites', [FavoritesController::class, 'store']);
    Route::delete('/favorites/{quote}', [FavoritesController::class, 'destroy']);
});
