<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AuthController;
// --- PRODUKTY (Dla wszystkich) ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// --- KOSZYK ---
Route::get('/cart', [CartController::class, 'index']); // Pobierz koszyk
Route::post('/cart', [CartController::class, 'add']);  // Dodaj do koszyka
Route::delete('/cart/{product_id}', [CartController::class, 'destroy']); // Usuń z koszyka

// --- ZAMÓWIENIA ---
Route::post('/checkout', [OrderController::class, 'checkout']); // Kup
Route::get('/orders', [OrderController::class, 'history']);     // Historia

// --- PANEL SPRZEDAWCY ---
Route::post('/seller/products', [SellerController::class, 'store']);
Route::put('/seller/products/{id}', [SellerController::class, 'update']);
Route::delete('/seller/products/{id}', [SellerController::class, 'destroy']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
