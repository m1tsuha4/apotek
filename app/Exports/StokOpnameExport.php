<?php

namespace App\Exports;

use App\Models\StokOpname;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StokOpnameExport implements FromQuery, WithMapping, WithHeadings
{
    private $rowNumber = 1;

    public function query()
    {
        return StokOpname::query();
    }

    public function map($stokOpname): array
    {
        $stokTercatat = !empty($stokOpname->stok_tercatat) ? (string) $stokOpname->stok_tercatat : '0';
        $stokAktual = !empty($stokOpname->stok_aktual) ? (string) $stokOpname->stok_aktual : '0';
        return [
            $this->rowNumber++,
            $stokOpname->tanggal,
            $stokOpname->StokBarang->barang->nama_barang,
            $stokOpname->StokBarang->batch,
            $stokOpname->StokBarang->barang->kategori->nama_kategori,
            $stokOpname->StokBarang->exp_date,
            $stokOpname->sumber_stok,
            $stokTercatat,
            $stokAktual,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Barang',
            'Batch',
            'Kategori',
            'Exp Date',
            'Sumber Stok',
            'Stok Tercatat',
            'Stok Aktual',
        ];
    }
}
