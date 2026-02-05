<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nama_guru',
        'NIP',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
