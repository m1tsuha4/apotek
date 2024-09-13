<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KartuStockExport implements FromView
{
    protected $barang;

    public function __construct($barang)
    {
        $this->barang = $barang;
    }

    public function view(): View
    {
        $satuanDasar = $this->barang->id_satuan;
        if (isset($this->barang->satuanBarang)) {
            $satuanBesar = $this->barang->satuanBarang->id_satuan;
            $jumlahBesar = $this->barang->satuanBarang->jumlah;
        }

        // Fetch purchases
        $purchases = $this->barang->barangPembelian()
            ->join('pembelians', 'barang_pembelians.id_pembelian', '=', 'pembelians.id')
            ->join('satuans', 'barang_pembelians.id_satuan', '=', 'satuans.id')
            ->select(
                'barang_pembelians.exp_date',
                'pembelians.tanggal',
                'barang_pembelians.batch',
                \DB::raw('SUM(barang_pembelians.jumlah) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('barang_pembelians.exp_date', 'pembelians.tanggal', 'barang_pembelians.batch', 'satuans.id')
            ->get();

        // Fetch sales
        $sales = $this->barang->barangPenjualan()
            ->join('penjualans', 'barang_penjualans.id_penjualan', '=', 'penjualans.id')
            ->join('satuans', 'barang_penjualans.id_satuan', '=', 'satuans.id')
            ->join('stok_barangs', 'barang_penjualans.id_stok_barang', '=', 'stok_barangs.id')
            ->select(
                'penjualans.tanggal',
                'stok_barangs.batch',
                'stok_barangs.exp_date',
                \DB::raw('SUM(barang_penjualans.jumlah) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date', 'satuans.id')
            ->get();

        // Fetch purchase returns
        $purchaseReturns = $this->barang->barangPembelian()
            ->join('pembelians', 'barang_pembelians.id_pembelian', '=', 'pembelians.id')
            ->join('satuans', 'barang_pembelians.id_satuan', '=', 'satuans.id')
            ->join('retur_pembelians', 'barang_pembelians.id_pembelian', '=', 'retur_pembelians.id_pembelian')
            ->join('barang_retur_pembelians', 'barang_pembelians.id', '=', 'barang_retur_pembelians.id_barang_pembelian')
            ->select(
                'retur_pembelians.tanggal',
                'barang_pembelians.batch',
                'barang_pembelians.exp_date',
                \DB::raw('SUM(barang_retur_pembelians.jumlah_retur) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('retur_pembelians.tanggal', 'barang_pembelians.batch', 'barang_pembelians.exp_date', 'satuans.id')
            ->get();

        // Fetch sales returns
        $salesReturns = $this->barang->barangPenjualan()
            ->join('penjualans', 'barang_penjualans.id_penjualan', '=', 'penjualans.id')
            ->join('satuans', 'barang_penjualans.id_satuan', '=', 'satuans.id')
            ->join('stok_barangs', 'barang_penjualans.id_stok_barang', '=', 'stok_barangs.id')
            ->join('retur_penjualans', 'penjualans.id', '=', 'retur_penjualans.id_penjualan')
            ->join('barang_retur_penjualans', 'barang_penjualans.id', '=', 'barang_retur_penjualans.id_barang_penjualan')
            ->select(
                'retur_penjualans.tanggal',
                'stok_barangs.batch',
                'stok_barangs.exp_date',
                \DB::raw('SUM(barang_retur_penjualans.jumlah_retur) as jumlah'),
                \DB::raw('satuans.id as satuan_id')
            )
            ->groupBy('retur_penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date', 'satuans.id')
            ->get();

        // Combine all transactions
        $stockDetails = [];

        // Process purchases
        foreach ($purchases as $purchase) {
            $quantity = $purchase->satuan_id == $satuanDasar
                ? $purchase->jumlah
                : $purchase->jumlah * $jumlahBesar;

            $stockDetails[] = [
                'exp_date' => $purchase->exp_date,
                'tanggal' => $purchase->tanggal,
                'batch' => $purchase->batch,
                'masuk' => $quantity,
                'keluar' => 0,
                'jenis_transaksi' => 'pembelian',
            ];
        }

        // Process sales
        foreach ($sales as $sale) {
            $quantity = $sale->satuan_id == $satuanDasar
                ? $sale->jumlah
                : $sale->jumlah * $jumlahBesar;

            $stockDetails[] = [
                'exp_date' => $sale->exp_date,
                'tanggal' => $sale->tanggal,
                'batch' => $sale->batch,
                'masuk' => 0,
                'keluar' => $quantity,
                'jenis_transaksi' => 'penjualan',
            ];
        }

        // Process purchase returns
        foreach ($purchaseReturns as $return) {
            $quantity = $return->satuan_id == $satuanDasar
                ? $return->jumlah
                : $return->jumlah * $jumlahBesar;

            $stockDetails[] = [
                'exp_date' => $return->exp_date,
                'tanggal' => $return->tanggal,
                'batch' => $return->batch,
                'masuk' => 0, // Retur pembelian tidak menambah barang
                'keluar' => $quantity, // Retur pembelian mengurangi stok (keluar)
                'jenis_transaksi' => 'retur_pembelian',
            ];
        }

        // Process sales returns
        foreach ($salesReturns as $return) {
            $quantity = $return->satuan_id == $satuanDasar
                ? $return->jumlah
                : $return->jumlah * $jumlahBesar;

            $stockDetails[] = [
                'exp_date' => $return->exp_date,
                'tanggal' => $return->tanggal,
                'batch' => $return->batch,
                'masuk' => $quantity, // Retur penjualan menambah barang
                'keluar' => 0,
                'jenis_transaksi' => 'retur_penjualan',
            ];
        }

        // Sort by date (newest first)
        usort($stockDetails, function ($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });

        // Calculate remaining stock
        $totalMasuk = 0;
        $totalKeluar = 0;

        foreach ($stockDetails as &$details) {
            $totalMasuk += $details['masuk'];
            $totalKeluar += $details['keluar'];
            $details['sisa'] = $totalMasuk - $totalKeluar;
        }

        return view('exports.kartu-stock', [
            'commonData' => [
                'nama_barang' => $this->barang->nama_barang,
                'nama_satuan' => $this->barang->satuan->nama_satuan,
            ],
            'stockDetails' => $stockDetails,
        ]);
    }
}
