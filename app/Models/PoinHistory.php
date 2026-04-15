<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoinHistory extends Model
{
    use HasFactory;

    protected $table = 'poin_histories'; 

    protected $fillable = [
    'siswa_id',
    'store_item_id', // <--- TAMBAHKEUN
    'poin_perubahan',
    'keterangan',
    'status',        // <--- TAMBAHKEUN
];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}