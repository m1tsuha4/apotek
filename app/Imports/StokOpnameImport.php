<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\StokBarang;
use App\Models\StokOpname;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StokOpnameImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $stokBarang = StokBarang::where('batch', $row['batch'])->first();

        if(!$stokBarang){
            return null;
        }

        if (is_numeric($row['tanggal'])) {
            $tanggal = Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
        } else {
            $tanggal = $row['tanggal'];
        }
       
        StokOpname::create([
            'id_stok_barang' => $stokBarang->id,
            'sumber_stok' => $row['sumber_stok'],
            'tanggal' => $tanggal,
            'stok_tercatat' => $row['stok_tercatat'],
            'stok_aktual' => $row['stok_aktual']
        ]);
        
        if ($row['sumber_stok'] == 'Gudang') {
            $stokBarang->stok_gudang = $row['stok_aktual'];
        } elseif ($row['sumber_stok'] == 'Apotek') {
            $stokBarang->stok_apotek = $row['stok_aktual'];
        }

        $stokBarang->stok_total = $stokBarang->stok_gudang + $stokBarang->stok_apotek;
        
        $stokBarang->save();
    }
}
