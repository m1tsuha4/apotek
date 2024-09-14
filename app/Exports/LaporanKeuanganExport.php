<?php

namespace App\Exports;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\LaporanKeuanganMasuk;
use App\Models\LaporanKeuanganKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanKeuanganExport implements WithMultipleSheets, ShouldAutoSize
{
    public function sheets(): array
    {
        $sheets = [];

        // Add the keuangan sheet
        $sheets[] = new KeuanganSheet();

        // Add the pembelian sheet
        $sheets[] = new PembelianSheet();

        // Add the penjualan sheet
        $sheets[] = new PenjualanSheet();

        return $sheets;
    }
}

class KeuanganSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        $totalPemasukkan = LaporanKeuanganMasuk::sum('pemasukkan');
        $totalPiutang = LaporanKeuanganMasuk::sum('piutang');
        $totalPengeluaran = LaporanKeuanganKeluar::sum('pengeluaran');
        $totalUtang = LaporanKeuanganKeluar::sum('utang');

        return collect([
            ['Pemasukan', 'Pengeluaran', 'Utang', 'Piutang'],
            [
                $this->formatRupiah($totalPemasukkan),
                $this->formatRupiah($totalPengeluaran),
                $this->formatRupiah($totalUtang),
                $this->formatRupiah($totalPiutang)
            ],
        ]);
    }

    public function headings(): array
    {
        return ['Pemasukan', 'Pengeluaran', 'Utang', 'Piutang'];
    }

    public function title(): string
    {
        return 'Keuangan';
    }

    private function formatRupiah($number)
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}

class PembelianSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // Fetch the pembelian data with related vendor and sales data
        $pembelian = Pembelian::with('vendor:id,nama_perusahaan', 'sales:id,nama_sales')
            ->select('id', 'id_vendor', 'id_sales', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->get();

        // Transform the collection to include related vendor and sales names
        $transformed = $pembelian->map(function ($item) {
            return [
                'id' => $item->id,
                'vendor' => $item->vendor->nama_perusahaan ?? 'N/A',
                'sales' => $item->sales->nama_sales ?? 'N/A',
                'referensi' => $item->referensi,
                'tanggal' => $item->tanggal,
                'status' => $item->status,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'total' => $this->formatRupiah($item->total),
            ];
        });

        return $transformed;
    }

    public function headings(): array
    {
        return ['ID', 'Vendor', 'Sales', 'Referensi', 'Tanggal', 'Status', 'Jatuh Tempo', 'Total'];
    }

    public function title(): string
    {
        return 'Pembelian';
    }

    private function formatRupiah($number)
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}

class PenjualanSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // Fetch the penjualan data with related pelanggan and jenis data
        $penjualan = Penjualan::with('pelanggan:id,nama_pelanggan,no_telepon')
            ->select('id', 'id_pelanggan', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->get();

        // Transform the collection to include related pelanggan and jenis names
        $transformed = $penjualan->map(function ($item) {
            return [
                'id' => $item->id,
                'pelanggan' => $item->pelanggan->nama_pelanggan ?? 'N/A',
                'no_telepon' => $item->pelanggan->no_telepon ?? 'N/A',
                'referensi' => $item->referensi,
                'tanggal' => $item->tanggal,
                'status' => $item->status,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'total' => $this->formatRupiah($item->total),
            ];
        });

        return $transformed;
    }

    public function headings(): array
    {
        return ['ID', 'Pelanggan', 'No. Telepon', 'Referensi', 'Tanggal', 'Status', 'Tanggal Jatuh Tempo', 'Total'];
    }

    public function title(): string
    {
        return 'Penjualan';
    }

    private function formatRupiah($number)
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}
