<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'id_pembelian',
        'tanggal',
        'referensi',
        'total_retur',
    ];

    public function barangReturPembelian()
    {
        return $this->hasMany(BarangReturPembelian::class, 'id_retur_pembelian');
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    public function PergerakanStokPembelian()
    {
        return $this->hasMany(PergerakanStokPembelian::class, 'id_pembelian');
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
            return 'RP-00001';
        }

        $lastIdNumber = intval(substr($lastRecord->id, 3));
        $newIdNumber = $lastIdNumber + 1;

        return 'RP-' . str_pad($newIdNumber, 5, '0', STR_PAD_LEFT);
    }
}
