<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_stok_barang',
        'tanggal',
        'sumber_stok',
        'tanggal',
        'stok_tercatat',
        'stok_aktual'
    ];

    public function stokBarang()
    {
        return $this->belongsTo(StokBarang::class, 'id_stok_barang');
    }
}
