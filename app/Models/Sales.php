<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_vendor',
        'nama_sales',
        'no_telepon'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_vendor');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_sales');
    }
}
