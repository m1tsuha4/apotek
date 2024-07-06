<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangReturPembelian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'id_retur_pembelian',
        'jumlah_retur',
        'total'
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'id_retur_pembelian');
    }
}
