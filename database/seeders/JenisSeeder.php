<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JenisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('jenis')->insert(
            [
                ["nama_jenis" => "Pemesanan"],
                ["nama_jenis" => "Pembelian"],
                ["nama_jenis" => "Penjualan"],
            ]
        );
    }
}
