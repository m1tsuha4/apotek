<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_sales',
        'id_jenis',
        'tanggal',
        'status',
        'tanggal_jatuh_tempo',
        'referensi',
        'sub_total',
        'diskon',
        'total',
        'catatan',
    ];

    public function sales() {
        $this->belongsTo(Sales::class, 'id_sales');
    }

    public function jenis() {
        $this->belongsTo(Jenis::class, 'id_jenis');
    }

    public function barangPembelian() {
        return $this->hasMany(BarangPembelian::class, 'id_pembelian');
    }
}
