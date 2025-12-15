<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- DODAJ TO
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory; // <--- I TO
    protected $primaryKey = 'category_id';
    protected $guarded = [];

}
