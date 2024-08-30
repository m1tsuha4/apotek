<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'batch',
        'exp_date',
        'stok_gudang',
        'min_stok_gudang',
        'notif_exp',
        'stok_apotek',
        'stok_total'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function stokOpname(){
        return $this->hasMany(StokOpname::class, 'id_stok_barang');
    }

    public function penjualan(){
        return $this->hasMany(Penjualan::class, 'id_stok_barang');
    }

    public function barangPenjualan(){
        return $this->hasMany(BarangPenjualan::class, 'id_stok_barang');
    }
}
