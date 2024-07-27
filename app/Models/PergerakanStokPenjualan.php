<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergerakanStokPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_penjualan',
        'pergerakan_stok',
        'stok_keseluruhan'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
}
