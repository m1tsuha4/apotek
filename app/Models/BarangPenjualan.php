<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penjualan',
        'id_barang',
        'id_stok_barang',
        'jumlah',
        'id_satuan',
        'jenis_diskon',
        'diskon',
        'harga',
        'total'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function stokBarang()
    {
        return $this->belongsTo(StokBarang::class, 'id_stok_barang');    
    }

    public function BarangReturPenjualan()
    {
        return $this->hasOne(BarangReturPenjualan::class, 'id_penjualan');
    }
}

