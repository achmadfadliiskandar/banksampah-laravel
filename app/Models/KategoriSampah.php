<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_kategori', 
        'nama_jenis', 
        'tipe', 
        'harga_per_kg', 
        'deskripsi'
    ];
}
