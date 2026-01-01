<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $primaryKey = 'cart_id';
    protected $guarded = [];

    // Relacja: Koszyk ma wiele pozycji
    public function items()
    {
        return $this->belongsToMany(Product::class, 'cart_product', 'cart_id', 'product_id')
                    ->withPivot(['quantity', 'cart_product_id']);
    }
}
