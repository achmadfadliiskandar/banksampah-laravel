<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSetoran extends Model
{
    use HasFactory;
    protected $table = 'detail_setorans';
    protected $fillable = ['setoran_id', 'kategori_id', 'berat', 'subtotal'];

    public function setoran()
    {
        return $this->belongsTo(Setoran::class, 'setoran_id');
    }
    
    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_id');
    }
    protected $casts = [
        'berat' => 'float',
        'subtotal' => 'float',
    ];
}
