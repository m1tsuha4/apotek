<?php

namespace App\Exports;

use App\Models\StokBarang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PenyesuainStokExport implements FromQuery, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
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
        return StokBarang::select('id', 'batch', 'exp_date', 'id_barang', 'stok_gudang', 'stok_apotek')
            ->with(['barang:id,nama_barang']);
    }

    public function map($stokBarang): array
    {
        $today = Carbon::now()->format('d/m/Y'); // Format tanggal hari ini

        $stokGudang = !empty($stokBarang->stok_gudang) ? (string) $stokBarang->stok_gudang : '0';
        $stokApotek = !empty($stokBarang->stok_apotek) ? (string) $stokBarang->stok_apotek : '0';

        return [
            [
                'nama_barang'  => $stokBarang->barang->nama_barang,
                'batch'        => $stokBarang->batch,
                'sumber_stok'  => 'Gudang',
                'tanggal'      => $today,
                'stok_tercatat' => $stokGudang,
                'stok_aktual'  => $stokGudang,
            ],
            [
                'nama_barang'  => $stokBarang->barang->nama_barang,
                'batch'        => $stokBarang->batch,
                'sumber_stok'  => 'Apotek',
                'tanggal'      => $today,
                'stok_tercatat' => $stokApotek,
                'stok_aktual'  => $stokApotek,
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nama_barang',
            'batch',
            'sumber_stok',
            'tanggal',
            'stok_tercatat',
            'stok_aktual',
        ];
    }
}
