<?php

namespace App\Exports;

use App\Models\Penjualan;
use App\Models\PembayaranPenjualan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class InvoiceExport implements FromView, WithCustomCsvSettings
{
    protected $penjualan;

    public function __construct(Penjualan $penjualan)
    {
        $this->penjualan = $penjualan;
    }

    public function view(): View
    {
        $penjualan = $this->penjualan;

        $pembayaranPenjualan = PembayaranPenjualan::where('id_penjualan', $penjualan->id)->sum('total_dibayar');

        $data = [
            'id_penjualan' => $penjualan->id,
            'status' => $penjualan->status,
            'id_pelanggan' => $penjualan->id_pelanggan,
            'nama_pelanggan' => $penjualan->pelanggan->nama_pelanggan,
            'no_telepon' => $penjualan->pelanggan->no_telepon,
            'id_jenis' => $penjualan->id_jenis,
            'nama_jenis' => $penjualan->jenis->nama_jenis,
            'tanggal' => $penjualan->tanggal,
            'tanggal_jatuh_tempo' => $penjualan->tanggal_jatuh_tempo,
            'referensi' => $penjualan->referensi,
            'sub_total' => $penjualan->sub_total,
            'total_diskon_satuan' => $penjualan->total_diskon_satuan,
            'diskon' => $penjualan->diskon,
            'total' => $penjualan->total,
            'catatan' => $penjualan->catatan,
            'sisa_tagihan' => $penjualan->total - $pembayaranPenjualan,
            'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'id_stok_barang' => $barangPenjualan->id_stok_barang,
                    'batch' => $barangPenjualan->stokBarang->batch,
                    'exp_date' => $barangPenjualan->stokBarang->exp_date,
                    'jumlah' => $barangPenjualan->jumlah,
                    'id_satuan' => $barangPenjualan->id_satuan,
                    'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
                    'jenis_diskon' => $barangPenjualan->jenis_diskon,
                    'diskon' => $barangPenjualan->diskon,
                    'harga' => $barangPenjualan->harga,
                    'total' => $barangPenjualan->total
                ];
            }),
            'pembayaranPenjualan' => $penjualan->pembayaranPenjualan->map(function ($pembayaranPenjualan) {
                return [
                    'id' => $pembayaranPenjualan->id,
                    'id_penjualan' => $pembayaranPenjualan->id_penjualan,
                    'tanggal_pembayaran' => $pembayaranPenjualan->tanggal_pembayaran,
                    'metode_pembayaran' => $pembayaranPenjualan->metodePembayaran->nama_metode,
                    'total_dibayar' => $pembayaranPenjualan->total_dibayar,
                    'referensi_pembayaran' => $pembayaranPenjualan->referensi_pembayaran
                ];
            })
        ];

        return view('exports.invoice', compact('data'));
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ";",
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => true,
            'include_separator_line' => false,
            'excel_compatibility' => false,
        ];
    }
}
