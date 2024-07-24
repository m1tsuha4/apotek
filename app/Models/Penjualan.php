<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_pelanggan',
        'id_jenis',
        'tanggal',
        'status',
        'tanggal_jatuh_tempo',
        'referensi',
        'sub_total',
        'total_diskon_satuan',
        'diskon',
        'total',
        'catatan',
    ];

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
            return 'SO-00001';
        }

        $lastIdNumber = intval(substr($lastRecord->id, 3));
        $newIdNumber = $lastIdNumber + 1;

        return 'SO-' . str_pad($newIdNumber, 5, '0', STR_PAD_LEFT);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function barangPenjualan()
    {
        return $this->hasMany(BarangPenjualan::class, 'id_penjualan');
    }

    public function pembayaranPenjualan()
    {
        return $this->hasMany(PembayaranPenjualan::class, 'id_penjualan');
    }

    public function jenis() {
        return $this->belongsTo(Jenis::class, 'id_jenis');
    }
}
