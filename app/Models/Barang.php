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

    public function satuanBarang(): BelongsTo
    {
        return $this->belongsTo(SatuanBarang::class);
    }

    public function variasiHargaJual(): BelongsTo
    {
        return $this->belongsTo(VariasiHargaJual::class);
    }
}
