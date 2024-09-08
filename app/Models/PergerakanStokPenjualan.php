<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergerakanStokPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penjualan',
        'id_retur_penjualan',
        'id_barang',
        'harga',
        'pergerakan_stok',
        'stok_keseluruhan'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function returPenjualan()
    {
        return $this->belongsTo(ReturPenjualan::class, 'id_retur_penjualan');
    }
}
