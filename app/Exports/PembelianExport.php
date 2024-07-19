<?php

namespace App\Exports;

use App\Models\Pembelian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PembelianExport implements FromQuery, WithMapping, WithHeadings
{
    public function query()
    {
        return Pembelian::query();
    }

    public function map($pembelian): array
    {
        return [
            $pembelian->id,
            $pembelian->tanggal,
            $pembelian->vendor->nama_perusahaan,
            $pembelian->referensi,
            $pembelian->tanggal_jatuh_tempo,
            $pembelian->status,
            $pembelian->jenis->nama_jenis,
            $pembelian->total,
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor',
            'Tanggal',
            'Vendor',
            'Referensi',
            'Tanggal Jatuh Tempo',
            'Status',
            'Jenis',
            'Total',
        ];
    }
}
