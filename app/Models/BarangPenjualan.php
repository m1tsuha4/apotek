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
        'jumlah',
        'id_satuan',
        'jenis_diskon',
        'diskon',
        'harga',
        'total'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }
}
