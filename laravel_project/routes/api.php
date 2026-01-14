<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\AdminController;
use App\Models\Tag; // <--- WAŻNE: Dodaj ten import, żeby szybka trasa tagów zadziałała

// --- PRODUKTY (Dla wszystkich) ---
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'showOnPage']);

// --- KATEGORIE ---
Route::get('/categories', [CategoryController::class, 'index']);

// --- TAGI (Tego brakowało!) ---
Route::get('/tags', function() {
    // Zwracamy wszystkie tagi (potrzebne do checkboxów w formularzu)
    return Tag::all(['tag_id', 'name']);
});

// --- KOSZYK ---
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'add']);
Route::put('/cart/{product_id}', [CartController::class, 'update']);
Route::delete('/cart/{product_id}', [CartController::class, 'destroy']);

// --- ZAMÓWIENIA ---
Route::post('/checkout', [CartController::class, 'checkout']);
Route::get('/orders', [OrderController::class, 'history']);

// --- PANEL SPRZEDAWCY ---
// Tego brakowało najbardziej - pobieranie listy produktów do tabeli:
Route::get('/seller/products', [SellerController::class, 'index']);

Route::post('/seller/products', [SellerController::class, 'store']);
Route::put('/seller/products/{id}', [SellerController::class, 'update']);
Route::delete('/seller/products/{id}', [SellerController::class, 'destroy']);
// --- AUTORYZACJA ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- ADMIN ---
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'index']);
    Route::patch('/users/{user}/role', [AdminController::class, 'updateRole']);
});
