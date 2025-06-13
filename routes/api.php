<?php

use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/wishlist', [WishlistController::class, 'index'])->name('show');
Route::get('/wishlist/create', [WishlistController::class, 'create'])->name('create');
Route::post('/wishlist/store', [WishlistController::class, 'store'])->name('store');

Route::POST('/wishlist/edit/{id}', [WishlistController::class, 'update'])->name('update');

Route::delete('/wishlist/delete/{id}', [WishlistController::class, 'destroy'])->name('destroy');
