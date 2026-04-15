<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexibilityItem extends Model
{
    use HasFactory;

    // Paksa Laravel nembak tabel store_items
    protected $table = 'flexibility_items';

    // Kasih izin kolom mana wae nu bisa diisi manual
    protected $fillable = [
        'item_name',
        'point_cost',
        'stock_limit',
    ];
}