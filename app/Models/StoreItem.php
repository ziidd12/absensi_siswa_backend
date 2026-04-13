<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    use HasFactory;

    // Paksa Laravel nembak tabel store_items
    protected $table = 'store_items';

    // Kasih izin kolom mana wae nu bisa diisi manual
    protected $fillable = [
        'nama_item',
        'harga_poin',
        'icon',
        'warna',
        'stok',
    ];
}