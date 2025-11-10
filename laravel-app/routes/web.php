<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodayController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\FavoritesController;

Route::get('/', fn() => redirect('/today'));
Route::get('/today', TodayController::class);
Route::get('/quotes', [QuotesController::class, 'index']);
Route::get('/login', fn () => response('Login', 200))->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index']);
    Route::post('/favorites', [FavoritesController::class, 'store']);
    Route::delete('/favorites/{quote}', [FavoritesController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
