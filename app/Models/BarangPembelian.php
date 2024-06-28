<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangPembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pembelian',
        'id_barang',
        'jumlah',
        'id_satuan',
        'diskon',
        'harga',
        'total',
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
