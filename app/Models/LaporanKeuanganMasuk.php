<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKeuanganMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penjualan',
        'pemasukkan',
        'piutang',
    ];
}
