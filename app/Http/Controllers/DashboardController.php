<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\StokBarang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function keuangan(){
        $data = [
            'pemasukan' => 1000000,
            'pengeluaran' => 500000,
            'utang' => 500000,
            'piutang' => 1000000
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data Keuangan Berhasil ditemukan!',
        ]);
    }
    
    public function stokBarang(Request $request)
    {
        $stokBarang = StokBarang::paginate($request->num);

        $data = $stokBarang->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_barang' => $item->barang->nama_barang,
                'kategori' => $item->barang->kategori->nama_kategori,
                'satuan' => $item->barang->satuan->nama_satuan,
                'total' => $item->stok_gudang + $item->stok_apotek
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $stokBarang->lastPage(),
            'message' => 'Data Stok Barang Berhasil ditemukan!',
        ]);
    }

    public function searchStokBarang(Request $request)
    {
        $search = $request->input('search');
        $result = StokBarang::select('id', 'id_barang', 'stok_total')
                ->with([
                    'satuan:id,nama_satuan', 
                    'kategori:id,nama_kategori', 
                ])
                ->where('nama_barang','like','%'.$search.'%')
                ->groupBy('nama_barang')
                ->get();
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Data Berhasil Ditemukan!',
        ]);
    }

    public function notifStok()
    {
        $stokBarang = StokBarang::all();

        $messages = [];

        foreach ($stokBarang as $item) {
            $total = $item->stok_gudang + $item->stok_apotek;
            if ($total <= $item->min_stok_gudang) {
                $messages[] = [
                    'nama_barang' => $item->barang->nama_barang,
                    'batch' => $item->batch,
                    'tanggal' => now()->toDateTimeString(),
                ];
            }
        }

        if (!empty($messages)) {
            return response()->json([
                'success' => true,
                'message' => 'Stok Menipis',
                'data' => $messages,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stok Aman',
        ]);
    }

    public function notifExp()
    {
        $stokBarang = StokBarang::all();

        $messages = [];

        foreach ($stokBarang as $item) {
            $expDate = Carbon::parse($item->exp_date);
            $notifDate = now()->addDays($item->notif_exp);
            if ($expDate->lessThanOrEqualTo($notifDate) && $expDate->greaterThan(now())) {
                $messages[] = [
                    'nama_barang' => $item->barang->nama_barang,
                    'batch' => $item->batch,
                    'exp_date' => $expDate->toDateString(),
                    'tanggal' => now()->toDateTimeString(),
                ];
            }
        }

        if (!empty($messages)) {
            return response()->json([
                'success' => true,
                'message' => 'Obat Mendekati Masa Expired',
                'data' => $messages,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Obat Aman',
        ]);
    }
}
