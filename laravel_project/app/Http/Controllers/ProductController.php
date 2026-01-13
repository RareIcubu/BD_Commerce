<?php

namespace App\Http\Controllers;

use App\Models\Product; // <-- Upewnij się, że to tu jest!
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. Lista produktów (API dla strony głównej)
    public function index(Request $request)
    {
        $query = Product::query();

        // Pobieramy relacje od razu (Eager Loading)
        $query->with(['category', 'tags']);

        // Tylko aktywne produkty
        $query->where('is_active', true);

        // --- FILTR KATEGORII ---
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // --- FILTR TAGÓW (Poprawiony) ---
        if ($request->filled('tags')) {
            // Frontend wysyła ID po przecinku: "1,5,8"
            $tags = explode(',', $request->tags);

            $query->whereHas('tags', function ($q) use ($tags) {
                // Szukamy po 'tag_id', a nie po 'name', bo frontend wysyła numery ID
                $q->whereIn('tags.tag_id', $tags);
            });
        }

        // --- WYSZUKIWARKA ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('tags', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Zwracamy paginację (Laravel sam zrobi JSON z polami 'data', 'links' itd.)
        return response()->json($query->paginate(12));
    }

    // 2. Metoda dla pojedynczego produktu (Szczegóły + Rekomendacje)
    public function showOnPage($id)
    {
        // Pobieramy produkt (musi być aktywny)
        $product = Product::with(['category', 'tags', 'seller'])
            ->where('is_active', true)
            ->findOrFail($id); // Jeśli nie znajdzie -> 404 (nie 500)

        // Pobieramy sugestie (inne produkty z tej samej kategorii)
        $suggested = Product::where('category_id', $product->category_id)
            ->where('product_id', '!=', $id) // Nie ten sam co główny
            ->where('is_active', true)     // Tylko dostępne
            ->inRandomOrder()              // Losowa kolejność dla lepszego UX
            ->limit(4)                     // 4 sztuki
            ->get();

        return response()->json([
            'product' => $product,
            'suggested' => $suggested
        ]);
    }
}
