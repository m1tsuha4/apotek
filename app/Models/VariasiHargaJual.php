<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariasiHargaJual extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'min_kuantitas',
        'harga',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
