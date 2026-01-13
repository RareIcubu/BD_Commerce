<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem; // Używamy modelu OrderItem
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // POST /api/checkout
    public function checkout(Request $request)
    {
        $sessionId = $request->header('X-Session-ID');

        // Pobierz koszyk z elementami i produktami (żeby znać cenę)
        $cart = Cart::with('items.product')->where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Koszyk jest pusty'], 400);
        }

        // Oblicz sumę (CartItem ma relację 'product')
        $total = $cart->items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });

        return DB::transaction(function () use ($cart, $total, $request) {
            // 1. Stwórz zamówienie
            // Używamy Auth::id() lub fallback na 1 dla testów
            $userId = Auth::id() ?? 1;

            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_price' => $total, // W migracji dałem 'total_price', nie 'price_total'
            ]);

            // 2. Przenieś produkty z koszyka do order_items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price, // Zamrażamy cenę
                ]);
            }

            // 3. Wyczyść koszyk (usuwamy items, potem sam koszyk opcjonalnie)
            $cart->items()->delete();
            // $cart->delete(); // Można usunąć też sesję koszyka, ale nie trzeba

            return response()->json(['message' => 'Zamówienie złożone', 'order_id' => $order->id]);
        });
    }

    // GET /api/orders
    public function history(Request $request)
    {
        $userId = Auth::id() ?? 1;

        $orders = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}
