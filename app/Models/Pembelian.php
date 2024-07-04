<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembelian extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_sales',
        'id_jenis',
        'tanggal',
        'status',
        'tanggal_jatuh_tempo',
        'referensi',
        'sub_total',
        'diskon',
        'total',
        'catatan',
    ];

    public function sales() {
        return $this->belongsTo(Sales::class, 'id_sales');
    }

    public function jenis() {
        return $this->belongsTo(Jenis::class, 'id_jenis');
    }

    public function barangPembelian() {
        return $this->hasMany(BarangPembelian::class, 'id_pembelian');
    }

    public function pembayaranPembelian() {
        return $this->hasMany(PembayaranPembelian::class, 'id_pembelian');
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
            return 'PO-00001';
        }

        $lastIdNumber = intval(substr($lastRecord->id, 3));
        $newIdNumber = $lastIdNumber + 1;

        return 'PO-' . str_pad($newIdNumber, 5, '0', STR_PAD_LEFT);
    }
}
