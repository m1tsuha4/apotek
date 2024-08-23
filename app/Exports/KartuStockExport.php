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
        $purchases = $this->barang->barangPembelian()
            ->join('pembelians', 'barang_pembelians.id_pembelian', '=', 'pembelians.id')
            ->join('satuans', 'barang_pembelians.id_satuan', '=', 'satuans.id')
            ->select('barang_pembelians.exp_date', 'pembelians.tanggal', 'barang_pembelians.batch', \DB::raw('SUM(barang_pembelians.jumlah) as masuk'))
            ->groupBy('barang_pembelians.exp_date', 'pembelians.tanggal', 'barang_pembelians.batch')
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal . '-' . $item->batch;
            });

        // $sales = Penjualan::where('id_barang', $barang->id)
        //     ->select('tanggal', 'batch', \DB::raw('SUM(jumlah) as total_sold'))
        //     ->groupBy('tanggal', 'batch')
        //     ->get()
        //     ->keyBy(function ($item) {
        //         return $item->tanggal . '-' . $item->batch;
        //     });

        $sales = $this->barang->barangPenjualan()
            ->join('penjualans', 'barang_penjualans.id_penjualan', '=', 'penjualans.id')
            ->join('satuans', 'barang_penjualans.id_satuan', '=', 'satuans.id')
            ->join('stok_barangs', 'barang_penjualans.id_stok_barang', '=', 'stok_barangs.id')
            ->select('penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date', \DB::raw('SUM(barang_penjualans.jumlah) as total_sold'))
            ->groupBy('penjualans.tanggal', 'stok_barangs.batch', 'stok_barangs.exp_date')
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal . '-' . $item->batch;
            });

        // Combine purchases and sales data
        $stockDetails = [];

        foreach ($purchases as $key => $purchase) {
            $stockDetails[$key] = [
                'exp_date' => $purchase->exp_date,
                'tanggal' => $purchase->tanggal,
                'batch' => $purchase->batch,
                'masuk' => $purchase->masuk,
                'keluar' => $sales->has($key) ? $sales[$key]->total_sold : 0,
            ];
        }

        foreach ($sales as $key => $sale) {
            if (!isset($stockDetails[$key])) {
                $stockDetails[$key] = [
                    'tanggal' => $sale->tanggal,
                    'batch' => $sale->batch,
                    'exp_date' => $sale->exp_date,
                    'masuk' => 0,
                    'keluar' => $sale->total_sold,
                ];
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
