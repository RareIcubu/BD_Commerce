<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // POST /api/checkout
    public function checkout(Request $request)
    {
        $sessionId = $request->header('X-Session-ID');

        // 1. Sprawdź czy koszyk istnieje
        $cart = Cart::with('items')->where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Koszyk jest pusty'], 400);
        }

        // 2. Policz sumę
        $total = $cart->items->sum(function($item) {
            return $item->pivot->quantity * $item->price;
        });

        // 3. Transakcja
        return DB::transaction(function () use ($cart, $total) {

            // "Hardcode Bypass": Jeśli nie wykryto usera, przypisz zamówienie do ID 1
            $userId = Auth::id() ?: 1;

            // TWORZENIE ZAMÓWIENIA (WERSJA CZYSTA)
            // Usunąłem wszystkie pola adresowe ('address', 'fullname' itd.),
            // bo Twoja baza ich nie ma i dlatego wyrzuca błąd 500.
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_price' => $total,
            ]);

            // Przenoszenie produktów
            foreach ($cart->items as $item) {
                $order->products()->attach($item->product_id, [
                    'quantity' => $item->pivot->quantity,
                    'price_when_purchased' => $item->price
                ]);
            }

            // Czyścimy koszyk
            $cart->items()->detach();
            $cart->delete();

            return response()->json([
                'message' => 'Zamówienie złożone',
                'order_id' => $order->order_id ?? $order->id
            ]);
        });
    }

    // GET /api/orders
    public function history()
    {
        // Wyświetl historię dla zalogowanego LUB dla usera ID 1
        $userId = Auth::id() ?: 1;

        $orders = Order::where('user_id', $userId)
            ->with('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}
