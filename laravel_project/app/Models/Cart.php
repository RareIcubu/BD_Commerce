<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_id';
    protected $fillable = ['session_id', 'user_id'];

    // Relacja Many-to-Many do Produktów
    public function items()
    {
        return $this->belongsToMany(Product::class, 'cart_product', 'cart_id', 'product_id')
                    ->withPivot(['quantity']); // Ważne: musimy mieć dostęp do ilości
    }
}
