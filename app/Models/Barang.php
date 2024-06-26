<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kategori',
        'id_satuan',
        'nama_barang',
        'harga_beli',
        'harga_jual',
    ];

    public function satuanBarang()
    {
        return $this->hasMany(SatuanBarang::class, 'id_barang');
    }

    public function variasiHargaJual()
    {
        return $this->hasMany(VariasiHargaJual::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}
