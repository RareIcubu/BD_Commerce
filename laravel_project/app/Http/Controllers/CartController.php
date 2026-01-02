<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Pomocnicza funkcja do pobierania koszyka (dla zalogowanego lub gościa)
    private function getCart(Request $request)
    {
        // Zakładamy, że frontend przesyła session_id (np. UUID generowany w Svelte)
        // lub user_id jeśli jest autoryzacja. Tu upraszczamy do session_id dla demo.
        $sessionId = $request->header('X-Session-ID');

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    // TRANS. 5.5: Dodawanie produktu do koszyka
    public function add(Request $request)
    {
        $cart = $this->getCart($request);
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        // Sprawdź czy produkt istnieje w koszyku
        $existingItem = DB::table('cart_product')
            ->where('cart_id', $cart->cart_id)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            // SQL: UPDATE Cart_Item SET quantity = ...
            DB::table('cart_product')
                ->where('cart_product_id', $existingItem->cart_product_id)
                ->increment('quantity', $quantity);
        } else {
            // SQL: INSERT INTO Cart_Item ...
            DB::table('cart_product')->insert([
                'cart_id' => $cart->cart_id,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return response()->json(['message' => 'Dodano do koszyka']);
    }

    // TRANS. 5.6: Wyświetlanie koszyka i sumy
    public function index(Request $request)
    {
        $cart = $this->getCart($request);

        // Ładowanie produktów w koszyku
        $cart->load('items');

        // Wyznaczanie ostatecznej ceny (SQL: SUM(quantity * price))
        $total = $cart->items->sum(function($item) {
            return $item->pivot->quantity * $item->price;
        });

        return response()->json([
            'items' => $cart->items,
            'total' => $total
        ]);
    }

    public function destroy(Request $request, $productId)
    {
        $sessionId = $request->header('X-Session-ID');
        $cart = Cart::where('session_id', $sessionId)->first();

        if ($cart) {
            $cart->items()->detach($productId);
            return response()->json(['message' => 'Removed successfully']);
        }

        return response()->json(['error' => 'Cart not found'], 404);
    }
}
