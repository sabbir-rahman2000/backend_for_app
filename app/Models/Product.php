<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'price',
        'description',
        'images',
        'sold',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'sold' => 'integer',
    ];
}
