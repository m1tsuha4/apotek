<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_penjualan',
        'tanggal',
        'referensi',
        'total_retur'
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
            return 'RS-00001';
        }

        $lastIdNumber = intval(substr($lastRecord->id, 3));
        $newIdNumber = $lastIdNumber + 1;

        return 'RS-' . str_pad($newIdNumber, 5, '0', STR_PAD_LEFT);
    }

    public function barangReturPenjualan()
    {
        return $this->hasMany(BarangReturPenjualan::class, 'id_retur_penjualan');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
}
