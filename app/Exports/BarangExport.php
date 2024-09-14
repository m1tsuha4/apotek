<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
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
            $barang->id,
            $barang->nama_barang,
            $barang->kategori->nama_kategori,
            $barang->satuan->nama_satuan,
            $barang->notif_exp,
            $barang->min_stok_total,
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
            'SKU',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Peringatan Exp',
            'Peringatan Stok',
            'Harga Beli',
            'Harga Jual',
        ];
    }
}
