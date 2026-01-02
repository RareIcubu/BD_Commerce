<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Zwraca wszystkie kategorie jako JSON
        return response()->json(Category::all());
    }
}
