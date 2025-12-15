<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = []; // Pozwala na masowe przypisywanie wszystkich pól

    // Relacja z tagami (N:M)
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag','product_id','tag_id');
    }
}
