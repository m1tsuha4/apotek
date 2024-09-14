<?php

namespace App\Exports;

use App\Models\StokOpname;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class StokOpnameExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function query()
    {
        return StokOpname::query();
    }

    public function map($stokOpname): array
    {
        $stokTercatat = !empty($stokOpname->stok_tercatat) ? (string) $stokOpname->stok_tercatat : '0';
        $stokAktual = !empty($stokOpname->stok_aktual) ? (string) $stokOpname->stok_aktual : '0';
        return [
            $stokOpname->StokBarang->id_barang,
            $stokOpname->StokBarang->barang->nama_barang,
            $stokOpname->StokBarang->batch,
            $stokOpname->StokBarang->exp_date,
            $stokOpname->StokBarang->barang->kategori->nama_kategori,
            $stokOpname->tanggal,
            $stokOpname->sumber_stok,
            $stokTercatat,
            $stokAktual,
        ];
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Barang',
            'Batch',
            'Exp Date',
            'Kategori',
            'Tanggal',
            'Sumber Stok',
            'Stok Tercatat',
            'Stok Aktual',
        ];
    }
}
