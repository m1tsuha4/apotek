<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perusahaan',
        'no_telepon',
        'alamat'
    ];

    public function sales()
    {
        return $this->hasMany(Sales::class, 'id_vendor');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_vendor');
    }
}
