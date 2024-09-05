<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangReturPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_retur_penjualan',
        'id_barang_penjualan',
        'jumlah_retur',
        'total',
    ];

    public function returPenjualan()
    {
        return $this->belongsTo(ReturPenjualan::class, 'id_retur_penjualan');
    }

    public function barangPenjualan()
    {
        return $this->belongsTo(BarangPenjualan::class, 'id_barang_penjualan');
    }
}
