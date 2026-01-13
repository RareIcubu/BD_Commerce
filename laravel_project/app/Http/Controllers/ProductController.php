<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. GŁÓWNA LISTA (Z FILTRAMI I SORTOWANIEM)
    public function index(Request $request)
    {
        $query = Product::query();
        $query->with(['category', 'tags']);
        $query->where('is_active', true);

        // --- FILTROWANIE (To już mieliśmy) ---
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.tag_id', $tags);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('tags', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // --- NOWOŚĆ: SORTOWANIE ---
        // Odbieramy parametr ?sort_by=price_asc itp.
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            // Domyślne sortowanie
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate(12));
    }

    // 2. NOWOŚĆ: SEKJA POLECANA (BESTSELLERY)
    // Zwraca 3 losowe produkty na górę strony
    public function featured()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->inRandomOrder() // Losujemy dla efektu dynamiki
            ->limit(3)
            ->get();

        return response()->json($products);
    }

    // 3. SZCZEGÓŁY PRODUKTU (Bez zmian)
    public function showOnPage($id)
    {
        $product = Product::with(['category', 'tags', 'seller'])
            ->where('is_active', true)
            ->findOrFail($id);

        $suggested = Product::where('category_id', $product->category_id)
            ->where('product_id', '!=', $id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return response()->json([
            'product' => $product,
            'suggested' => $suggested
        ]);
    }
}
