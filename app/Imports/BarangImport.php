<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Kategori;
use App\Models\SatuanBarang;
use App\Models\VariasiHargaJual;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Find id_kategori using nama_kategori
        $kategori = Kategori::where('nama_kategori', $row['nama_kategori'])->first();
        if (!$kategori) {
            // Handle the case where the kategori is not found
            return null; // or throw an exception, or handle accordingly
        }

        // Find id_satuan using nama_satuan
        $satuan = Satuan::where('nama_satuan', $row['nama_satuan'])->first();
        $satuanBarang = Satuan::where('nama_satuan', $row['satuan_barangs_nama_satuan'])->first();
        if (!$satuan || !$satuanBarang) {
            // Handle the case where the satuan is not found
            return null; // or throw an exception, or handle accordingly
        }

        // Update or create Barang
        $barang = Barang::updateOrCreate(
            ['nama_barang' => $row['nama_barang']],
            [
                'id_kategori' => $kategori->id,
                'id_satuan' => $satuan->id,
                'min_stok_total' => $row['min_stok_total'],
                'notif_exp' => $row['notif_exp'],
                'harga_beli' => $row['harga_beli'],
                'harga_jual' => $row['harga_jual'],
            ]
        );

        // Handle variations
        for ($i = 1; $i <= 5; $i++) { // Assuming up to 5 variations for simplicity
            if (isset($row["variasi_min_kuantitas_$i"]) && isset($row["variasi_harga_$i"])) {
                VariasiHargaJual::updateOrCreate(
                    [
                        'id_barang' => $barang->id,
                        'min_kuantitas' => $row["variasi_min_kuantitas_$i"]
                    ],
                    [
                        'harga' => $row["variasi_harga_$i"],
                    ]
                );
            }
        }

        // Update or create SatuanBarang
        SatuanBarang::updateOrCreate(
            [
                'id_barang' => $barang->id,
                'id_satuan' => $satuanBarang->id,
            ],
            [
                'jumlah' => $row['satuan_barangs_jumlah'],
                'harga_beli' => $row['satuan_barangs_harga_beli'],
                'harga_jual' => $row['satuan_barangs_harga_jual'],
            ]
        );
    }
}
