<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Eager loading dla wydajności (pobierz od razu kategorie i tagi)
        $query->with(['category', 'tags']);

        // Tylko aktywne produkty
        $query->where('is_active', true);

        // Filtrowanie po kategorii
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrowanie po tagach (jeśli podano listę tagów po przecinku)
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        // Wyszukiwanie (WB.03 / 5.4 ze specyfikacji)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('tags', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return response()->json($query->paginate(12));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'tags', 'seller'])->findOrFail($id);
        return response()->json($product);
    }
}
