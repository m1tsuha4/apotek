<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariasiHargaJual extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_kuantitasi',
        'harga',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
