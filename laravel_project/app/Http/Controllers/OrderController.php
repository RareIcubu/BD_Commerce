<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct; // Pamiętaj o stworzeniu pustego modelu jeśli nie ma
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // TRANS. 5.7: Finalizacja zamówienia
    public function checkout(Request $request)
    {
        // Ponownie pobieramy koszyk po ID sesji
        $sessionId = $request->header('X-Session-ID');
        $cart = Cart::where('session_id', $sessionId)->with('items')->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json(['error' => 'Koszyk jest pusty'], 400);
        }

        // Oblicz sumę
        $total = $cart->items->sum(fn($item) => $item->pivot->quantity * $item->price);

        // Rozpoczynamy transakcję bazodanową (Ważne!)
        return DB::transaction(function () use ($cart, $total, $request) {
            // 1. Stwórz zamówienie
            $order = Order::create([
                'user_id' => $request->input('user_id', 1), // Tymczasowo ID 1 (np. Guest/Test user)
                'status' => 'pending',
                'price_total' => $total,
                'ordered_at' => now(),
            ]);

            // 2. Przenieś produkty z koszyka do order_product
            foreach ($cart->items as $item) {
                // SQL: INSERT INTO Order_Product ...
                DB::table('order_product')->insert([
                    'order_id' => $order->order_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->pivot->quantity,
                    'price_when_purchased' => $item->price, // Zamrażamy cenę!
                ]);
            }

            // 3. Wyczyść koszyk (usuń pozycje)
            DB::table('cart_product')->where('cart_id', $cart->cart_id)->delete();

            return response()->json(['message' => 'Zamówienie złożone', 'order_id' => $order->order_id]);
        });
    }

    // TRANS. 5.8: Historia zamówień
    public function history(Request $request)
    {
        $userId = $request->input('user_id', 1); // Pobierane z auth w prawdziwej appce

        $orders = Order::with('products')
            ->where('user_id', $userId)
            ->orderByDesc('ordered_at')
            ->get();

        return response()->json($orders);
    }
}
