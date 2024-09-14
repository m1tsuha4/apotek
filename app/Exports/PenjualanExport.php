<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenjualanExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    public function query()
    {
        return Penjualan::query();
    }

    public function map($penjualan): array
    {
        return [
            $penjualan->id,
            $penjualan->tanggal,
            $penjualan->pelanggan->nama_pelanggan,
            $penjualan->pelanggan->no_telepon,
            $penjualan->referensi,
            $penjualan->tanggal_jatuh_tempo,
            $penjualan->status,
            $penjualan->jenis->nama_jenis,
            $penjualan->total,
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor',
            'Tanggal',
            'Nama',
            'No Telepon',
            'Referensi',
            'Tanggal Jatuh Tempo',
            'Status',
            'Jenis',
            'Total',
        ];
    }
}
