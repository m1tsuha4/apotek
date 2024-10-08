<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\StokBarang;
use App\Models\StokOpname;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon; // Tambahkan Carbon untuk mengelola tanggal

class StokOpnameImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cari data stok barang berdasarkan batch
        $stokBarang = StokBarang::where('batch', $row['batch'])->first();

        // Jika stok barang tidak ditemukan, skip
        if (!$stokBarang) {
            return null;
        }

        // Cek apakah tanggal dalam format excel (numeric)
        if (is_numeric($row['tanggal'])) {
            // Konversi tanggal Excel menjadi format Y-m-d
            $tanggal = Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
        } else {
            // Jika tanggal dalam format d/m/y, gunakan Carbon untuk parsing
            $tanggal = Carbon::createFromFormat('d/m/Y', $row['tanggal'])->format('Y-m-d');
        }

        // Periksa apakah stok_tercatat dan stok_aktual berbeda
        if ($row['stok_tercatat'] != $row['stok_aktual']) {
            // Jika berbeda, catat di stok_opname
            StokOpname::create([
                'id_stok_barang' => $stokBarang->id,
                'sumber_stok'    => $row['sumber_stok'],
                'tanggal'        => $tanggal,
                'stok_tercatat'  => $row['stok_tercatat'],
                'stok_aktual'    => $row['stok_aktual']
            ]);

            // Update stok_barang berdasarkan sumber_stok
            if ($row['sumber_stok'] == 'Gudang') {
                $stokBarang->stok_gudang = $row['stok_aktual'];
            } elseif ($row['sumber_stok'] == 'Apotek') {
                $stokBarang->stok_apotek = $row['stok_aktual'];
            }

            // Hitung total stok baru
            $stokBarang->stok_total = $stokBarang->stok_gudang + $stokBarang->stok_apotek;

            // Simpan perubahan pada stok_barang
            $stokBarang->save();
        }
    }
}
