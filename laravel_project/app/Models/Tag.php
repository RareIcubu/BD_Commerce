<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- DODAJ TO
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory; // <--- I TO
    protected $primaryKey = 'tag_id';
    protected $guarded = [];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag', 'tag_id', 'product_id');
    }
}
