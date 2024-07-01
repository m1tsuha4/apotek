<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'batch',
        'exp_date',
        'stok_gudang',
        'min_stok_gudang',
        'notif_exp',
        'stok_apotek',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function stokOpname(){
        return $this->hasMany(StokOpname::class, 'id_stok_barang');
    }
}
