<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    use HasFactory;

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_jenis');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'id_jenis');
    }
}
