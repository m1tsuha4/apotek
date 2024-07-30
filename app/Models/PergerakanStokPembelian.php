<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergerakanStokPembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pembelian',
        'id_barang',
        'harga',
        'pergerakan_stok',
        'stok_keseluruhan'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
