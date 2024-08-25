<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Barang;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Models\LaporanKeuanganMasuk;
use App\Models\LaporanKeuanganKeluar;

class DashboardController extends Controller
{

    public function keuangan(){
        $totalPemasukkan = LaporanKeuanganMasuk::sum('pemasukkan');
        $totalPiutang = LaporanKeuanganMasuk::sum('piutang');
        $totalPengeluaran = LaporanKeuanganKeluar::sum('pengeluaran');
        $totalUtang = LaporanKeuanganKeluar::sum('utang');

        $data = [
            'pemasukan' => $totalPemasukkan,
            'pengeluaran' => $totalPengeluaran,
            'utang' => $totalUtang,
            'piutang' => $totalPiutang
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data Keuangan Berhasil ditemukan!',
        ]);
    }
    
    public function stokBarang(Request $request)
    {
        $stok = Barang::select('id', 'id_kategori', 'id_satuan', 'nama_barang')
            ->with(['kategori:id,nama_kategori', 'satuan:id,nama_satuan', 'stokBarang:id,batch,exp_date,id_barang,stok_total'])
            ->paginate($request->num);
        
        $data = $stok->items();

        foreach ($data as $item) {
            $item->total_stok = StokBarang::where('id_barang', $item->id)->sum('stok_total');
        }

        $data = $stok->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_barang' => $item->nama_barang,
                'kategori' => $item->kategori->nama_kategori,
                'satuan' => $item->satuan->nama_satuan,
                'total' => $item->total_stok
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $stok->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);

        // $stokBarang = StokBarang::paginate($request->num);

        // $data = $stokBarang->map(function ($item) {
        //     return [
        //         'id' => $item->id,
        //         'nama_barang' => $item->barang->nama_barang,
        //         'kategori' => $item->barang->kategori->nama_kategori,
        //         'satuan' => $item->barang->satuan->nama_satuan,
        //         'total' => $item->stok_total
        //     ];
        // });

        // return response()->json([
        //     'success' => true,
        //     'data' => $data,
        //     'last_page' => $stokBarang->lastPage(),
        //     'message' => 'Data Stok Barang Berhasil ditemukan!',
        // ]);
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
            $total = $item->stok_total;
            if ($total <= $item->barang->min_stok_total) {
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
            'data' => []
        ]);
    }

    public function notifExp()
    {
        $stokBarang = StokBarang::all();

        $messages = [];

        foreach ($stokBarang as $item) {
            $expDate = Carbon::parse($item->exp_date);
            $notifDate = now()->addDays($item->barang->notif_exp);
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
            'data' => []
        ]);
    }
}
