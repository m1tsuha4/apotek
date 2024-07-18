<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_karyawan',
        'jenis_kelamin',
        'posisi',
        'tanggal_bergabung',
        'jumlah_gaji'
    ];
}
