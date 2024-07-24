<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penjualan',
        'id_metode_pembayaran',
        'total_dibayar',
        'tanggal_pembayaran',
        'referensi_pembayaran',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metode_pembayaran');
    }
}
