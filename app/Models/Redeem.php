<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redeem extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'store_item_id',
        'poin_dikeluarkan',
        'status',
    ];

    // Ieu meh engke di Flutter bisa nempo ngaran barangna
    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}