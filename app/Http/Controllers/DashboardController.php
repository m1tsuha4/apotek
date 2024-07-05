<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\StokBarang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stokBarang()
    {
        $stokBarang = StokBarang::paginate(10);

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
            'message' => 'Data Stok Barang Berhasil ditemukan!',
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
