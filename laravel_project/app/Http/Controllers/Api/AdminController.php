<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        
        $users = User::with('role')->get();

        return response()->json($users, 200);
    }

    public function updateUserRole(Request $request, $role_id)
    {
        $request->validate(['role_id' => 'required|exists:roles,role_id']);
        $user->update(['role_id' => $request->role_id]);
        return response()->json(['message' => 'User role updated successfully']);
    }

    public function sellerProducts()
    {
        $products = Product::where('seller_id', $user->id)->get();
        return response()->json($products);
    }

    public function deleteProduct($product_id)
    {
        $product = Product::findOrFail($product_id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
