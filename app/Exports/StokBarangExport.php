<?php

namespace App\Exports;

use App\Models\StokBarang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StokBarangExport implements FromQuery, WithMapping, WithHeadings
{
    private $rowNumber = 1;
    public function query()
    {
        return StokBarang::query();
    }

    public function map($stokBarang): array
    {
        return [
            $this->rowNumber++,
            $stokBarang->batch,
            $stokBarang->barang->nama_barang,
            $stokBarang->barang->kategori->nama_kategori,
            $stokBarang->exp_date,
            $stokBarang->notif_exp,
            $stokBarang->barang->satuan->nama_satuan,
            $stokBarang->stok_gudang,
            $stokBarang->min_stok_gudang,
            $stokBarang->stok_apotek,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Batch',
            'Nama Barang',
            'Kategori',
            'Exp Date',
            'Notif Exp',
            'Satuan',
            'Stok Gudang',
            'Min Stok Gudang',
            'Stok Apotek',
        ];
    }
}
