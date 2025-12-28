<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Publiczne trasy
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Tu w przyszłości dodasz trasy chronione (np. koszyk, zamówienia)
// Route::middleware('auth:sanctum')->group(function () { ... });
