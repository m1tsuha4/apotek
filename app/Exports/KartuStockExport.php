<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\Pembelian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

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
        $satuanBesar = $this->barang->satuanBarang->id_satuan;
        $jumlahBesar = $this->barang->satuanBarang->jumlah;

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
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal . '-' . $item->batch;
            });

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
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal . '-' . $item->batch;
            });

        // Combine purchases and sales data
        $stockDetails = [];

        foreach ($purchases as $key => $purchase) {
            $quantity = $purchase->satuan_id == $satuanDasar
                ? $purchase->jumlah
                : $purchase->jumlah * $jumlahBesar;

            $stockDetails[$key] = [
                'exp_date' => $purchase->exp_date,
                'tanggal' => $purchase->tanggal,
                'batch' => $purchase->batch,
                'masuk' => ($stockDetails[$key]['masuk'] ?? 0) + $quantity,
                'keluar' => $stockDetails[$key]['keluar'] ?? 0,
            ];
        }

        foreach ($sales as $key => $sale) {
            $quantity = $sale->satuan_id == $satuanDasar
                ? $sale->jumlah
                : $sale->jumlah * $jumlahBesar;

            if (!isset($stockDetails[$key])) {
                $stockDetails[$key] = [
                    'exp_date' => $sale->exp_date,
                    'tanggal' => $sale->tanggal,
                    'batch' => $sale->batch,
                    'masuk' => 0,
                    'keluar' => $quantity,
                ];
            } else {
                $stockDetails[$key]['keluar'] += $quantity;
            }
        }

        // Calculate remaining stock
        foreach ($stockDetails as &$details) {
            $details['sisa'] = $details['masuk'] - $details['keluar'];
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
