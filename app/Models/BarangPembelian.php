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
        'batch',
        'exp_date',
        'jumlah',
        'jenis_diskon',
        'id_satuan',
        'jenis_diskon',
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

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function BarangReturPembelian()
    {
        return $this->hasOne(BarangReturPembelian::class, 'id_barang_pembelian');
    }
}
