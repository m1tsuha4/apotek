<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_kategori',
        'id_satuan',
        'nama_barang',
        'min_stok_total',
        'notif_exp',
        'harga_beli',
        'harga_jual',
    ];

    public function satuanBarang()
    {
        return $this->hasOne(SatuanBarang::class, 'id_barang');
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

    public function stokBarang()
    {
        return $this->hasMany(StokBarang::class, 'id_barang');
    }

    public function barangPembelian()
    {
        return $this->hasMany(BarangPembelian::class, 'id_barang');
    }

    public function barangPenjualan()
    {
        return $this->hasMany(BarangPenjualan::class, 'id_barang');
    }

    public function pergerakanStokPembelian()
    {
        return $this->hasMany(PergerakanStokPembelian::class, 'id_barang');
    }

    public function pergerakanStokPenjualan()
    {
        return $this->hasMany(PergerakanStokPenjualan::class, 'id_barang');
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = self::generateId();
            }
        });
    }

    protected static function generateId()
    {
        $lastRecord = self::orderBy('id', 'desc')->first();
        if (!$lastRecord) {
            return 'SKU-00001';
        }

        $lastIdNumber = intval(substr($lastRecord->id, 4));
        $newIdNumber = $lastIdNumber + 1;

        return 'SKU-' . str_pad($newIdNumber, 5, '0', STR_PAD_LEFT);
    }
}
