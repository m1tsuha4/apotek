<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateBarangExport implements WithHeadings, ShouldAutoSize, WithStyles
{
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_barang',
            'nama_satuan',
            'nama_kategori',
            'harga_beli',
            'harga_jual',
            'min_stok_total',
            'notif_exp',
            'variasi_min_kuantitas_1',
            'variasi_harga_1',
            'satuan_barangs_nama_satuan',
            'satuan_barangs_jumlah',
            'satuan_barangs_harga_beli',
            'satuan_barangs_harga_jual',
        ];
    }

}
