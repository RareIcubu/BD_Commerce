<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order; // <--- WAŻNE: Dodaj ten import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- WAŻNE
use Illuminate\Support\Facades\DB;   // <--- WAŻNE

class CartController extends Controller
{
    // Helper: Pobierz lub stwórz koszyk
    private function getCart(Request $request)
    {
        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId) return null;

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    // GET /api/cart
    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        if (!$cart) return response()->json([]);

        $items = $cart->items;

        $formattedItems = $items->map(function ($product) use ($cart) {
            return [
                'cart_id' => $cart->cart_id,
                'product_id' => $product->product_id,
                'quantity' => $product->pivot->quantity,
                'product' => $product
            ];
        });

        return response()->json($formattedItems);
    }

    // POST /api/cart
    public function add(Request $request)
    {
        $cart = $this->getCart($request);
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $existingProduct = $cart->items()->where('cart_product.product_id', $productId)->first();

        if ($existingProduct) {
            $newQuantity = $existingProduct->pivot->quantity + $quantity;
            $cart->items()->updateExistingPivot($productId, ['quantity' => $newQuantity]);
        } else {
            $cart->items()->attach($productId, ['quantity' => $quantity]);
        }

        return response()->json(['message' => 'Dodano do koszyka']);
    }

    // PUT /api/cart/{id}
    public function update(Request $request, $productId)
    {
        $cart = $this->getCart($request);
        $quantity = (int) $request->input('quantity');

        if ($cart) {
            if ($quantity <= 0) {
                $cart->items()->detach($productId);
            } else {
                $cart->items()->updateExistingPivot($productId, ['quantity' => $quantity]);
            }
        }

        return response()->json(['message' => 'Zaktualizowano ilość']);
    }

    // DELETE /api/cart/{id}
    public function destroy(Request $request, $productId)
    {
        $cart = $this->getCart($request);
        if ($cart) {
            $cart->items()->detach($productId);
        }
        return response()->json(['message' => 'Usunięto z koszyka']);
    }

    // --- NOWOŚĆ: METODA CHECKOUT ---
    // POST /api/checkout
    public function checkout(Request $request)
    {
        $sessionId = $request->header('X-Session-ID');

        // 1. Pobierz koszyk
        $cart = Cart::with('items')->where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Koszyk jest pusty'], 400);
        }

        // 2. Policz sumę
        $total = $cart->items->sum(function($item) {
            return $item->pivot->quantity * $item->price;
        });

        // 3. Zapisz zamówienie (Transakcja)
        return DB::transaction(function () use ($cart, $total) {
            $userId = Auth::id(); // Jeśli zalogowany, przypisz ID. Jeśli nie - NULL.

            // Tworzymy proste zamówienie (bez adresu, żeby nie było błędów)
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total_price' => $total,
            ]);

            // Przenosimy produkty
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
                'order_id' => $order->order_id // Upewnij się czy w modelu masz order_id czy id
            ]);
        });
    }
}
