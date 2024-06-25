<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('satuans')->insert(
            [
                ["nama_satuan" => "Box"],
                ["nama_satuan" => "Botol"],
                ["nama_satuan" => "Pieces"],
            ]
        );
    }
}
