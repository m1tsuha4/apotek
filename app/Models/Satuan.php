<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_satuan',
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'id_satuan');
    }

    public function satuanBarang()
    {
        return $this->hasMany(SatuanBarang::class, 'id_satuan');
    }

    public function barangPembelian()
    {
        return $this->hasMany(BarangPembelian::class, 'id_satuan');
    }
}
