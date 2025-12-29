<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    // TRANS. 5.9: Dodanie nowego produktu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|integer',
            'seller_id' => 'required|integer', // W przyszłości z Auth::id()
        ]);

        $product = Product::create(array_merge($validated, [
            'is_active' => true,
            'description' => $request->input('description', ''),
            'front_image' => $request->input('front_image'),
        ]));

        return response()->json($product, 201);
    }

    // TRANS. 5.11: Edycja produktu
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update($request->all()); // Mass assignment, uważać w produkcji!

        return response()->json($product);
    }

    // TRANS. 5.10: Usuwanie (Soft Delete przez flagę is_active)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Zgodnie z SQL w specyfikacji: UPDATE Product SET is_active = 0
        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Produkt dezaktywowany']);
    }
}
