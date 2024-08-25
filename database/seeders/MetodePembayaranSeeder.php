<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MetodePembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('metode_pembayarans')->insert(
            [
                ["nama_metode" => "Retur"],
                ["nama_metode" => "BNI"],
                ["nama_metode" => "BSI"],
                ["nama_metode" => "Qris"],
            ]
        );
    }
}
