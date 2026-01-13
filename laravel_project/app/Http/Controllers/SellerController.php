<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    // 1. (NOWOŚĆ) Pobieranie listy produktów dla tabeli
    public function index()
    {
        // Pobieramy produkty sprzedawcy nr 2 (symulacja)
        // with('category') jest ważne, żeby Svelte wyświetliło nazwę kategorii w tabeli
        $products = Product::with('category')
            ->where('seller_id', 2)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products);
    }

    // 2. Dodawanie nowego produktu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|integer',
            // 'seller_id' => 'required|integer', // To bierzemy z hardcodu lub Auth
            'tags' => 'array', // (NOWOŚĆ) Walidacja tagów
            'tags.*' => 'integer|exists:tags,tag_id'
        ]);

        $product = Product::create([
            'seller_id' => 2, // Hardcoded symulacja
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->input('description', ''),
            'price' => $request->price,
            'front_image' => $request->input('front_image'),
            'is_active' => true,
        ]);

        // (NOWOŚĆ) Zapisywanie tagów do tabeli łączącej product_tag
        if ($request->has('tags')) {
            $product->tags()->attach($request->tags);
        }

        return response()->json($product, 201);
    }

    // 3. Edycja produktu
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    // 4. Usuwanie (Soft Delete)
    public function destroy($id)
    {
        // Szukamy po product_id
        $product = Product::where('product_id', $id)->firstOrFail();

        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Produkt dezaktywowany']);
    }
}
