<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

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

        // Pobieramy produkty w koszyku
        $items = $cart->items;

        // Frontend Svelte oczekuje tablicy obiektów, gdzie każdy ma pole 'product' i 'quantity'.
        // Eloquent przy relacji belongsToMany zwraca listę produktów, gdzie ilość jest ukryta w 'pivot'.
        // Musimy to przemapować:
        $formattedItems = $items->map(function ($product) use ($cart) {
            return [
                'cart_id' => $cart->cart_id,
                'product_id' => $product->product_id,
                'quantity' => $product->pivot->quantity, // Ilość bierzemy z tabeli łączącej
                'product' => $product // Cały obiekt produktu wstawiamy jako pole 'product'
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

        // Sprawdzamy, czy ten produkt już jest w koszyku (przez relację items)
        // where('products.product_id') jest bezpieczniejsze przy joinach, ale tu wystarczy po prostu sprawdzenie kolekcji
        // lub zapytanie:
        $existingProduct = $cart->items()->where('cart_product.product_id', $productId)->first();

        if ($existingProduct) {
            // Produkt jest - aktualizujemy ilość
            // Pobieramy starą ilość z pivot i dodajemy nową
            $newQuantity = $existingProduct->pivot->quantity + $quantity;

            $cart->items()->updateExistingPivot($productId, [
                'quantity' => $newQuantity
            ]);
        } else {
            // Produktu nie ma - dodajemy (attach)
            $cart->items()->attach($productId, [
                'quantity' => $quantity
            ]);
        }

        return response()->json(['message' => 'Dodano do koszyka']);
    }
    public function update(Request $request, $productId)
    {
        $cart = $this->getCart($request);
        $quantity = (int) $request->input('quantity');

        if ($cart) {
            if ($quantity <= 0) {
                // Jeśli ilość <= 0, usuwamy produkt
                $cart->items()->detach($productId);
            } else {
                // Aktualizujemy ilość w tabeli pivot
                $cart->items()->updateExistingPivot($productId, [
                    'quantity' => $quantity
                ]);
            }
        }

        return response()->json(['message' => 'Zaktualizowano ilość']);
    }
    // DELETE /api/cart/{id}
    public function destroy(Request $request, $productId)
    {
        $cart = $this->getCart($request);

        if ($cart) {
            // Odłączamy produkt (usuwamy z tabeli pivot)
            $cart->items()->detach($productId);
        }

        return response()->json(['message' => 'Usunięto z koszyka']);
    }
}
