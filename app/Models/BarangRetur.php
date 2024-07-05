<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangRetur extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_retur_pembelian',
        'jumlah_retur',
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'id_retur_pembelian');
    }
}
