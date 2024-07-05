<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangExport implements FromQuery, WithMapping, WithHeadings
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private $rowNumber = 1;

    public function query()
    {
        return Barang::query();
    }

    /**
     * @param \App\Models\Barang $barang
     * @return array
     */
    public function map($barang): array
    {
        return [
            $this->rowNumber++,
            $barang->nama_barang,
            $barang->kategori->nama_kategori,
            $barang->satuan->nama_satuan,
            $barang->harga_beli,
            $barang->harga_jual,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
        ];
    }
}
