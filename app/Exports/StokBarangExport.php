<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\StokBarang;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class StokBarangExport implements FromQuery, WithMapping, WithHeadings
{
    public function query()
    {
        return Barang::select('id', 'id_kategori', 'id_satuan', 'nama_barang')
            ->with(['kategori:id,nama_kategori', 'satuan:id,nama_satuan', 'stokBarang:id,batch,exp_date,id_barang,stok_total']);
    }

    public function map($barang): array
    {
        $total_stok = StokBarang::where('id_barang', $barang->id)->sum('stok_total');

        return [
            $barang->id,
            $barang->nama_barang,
            $barang->kategori->nama_kategori,
            $barang->satuan->nama_satuan,
            $total_stok,
        ];
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Stok Total',
        ];
    }
}
