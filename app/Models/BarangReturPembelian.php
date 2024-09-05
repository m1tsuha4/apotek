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
        'id_barang_pembelian',
        'jumlah_retur',
        'total'
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'id_retur_pembelian');
    }
    
    public function barangPembelian()
    {
        return $this->belongsTo(BarangPembelian::class, 'id_barang_pembelian');
    }
}
