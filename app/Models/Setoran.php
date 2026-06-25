<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setoran extends Model
{
    use HasFactory;
    protected $fillable = ['kode_transaksi', 'user_id', 'admin_id', 'total_berat', 'total_harga'];

    public function details() {
        return $this->hasMany(DetailSetoran::class,'setoran_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    protected $casts = [
        'total_berat' => 'float',
        'total_harga' => 'float',
    ];
}
