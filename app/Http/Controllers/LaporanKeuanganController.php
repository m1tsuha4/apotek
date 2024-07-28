<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\LaporanKeuangan;
use App\Models\LaporanKeuanganMasuk;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LaporanKeuanganKeluar;
use App\Exports\LaporanKeuanganExport;

class LaporanKeuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $numPerPage = $request->num;

        $pembelian = Pembelian::select('id','id_vendor','id_sales','tanggal','status','tanggal_jatuh_tempo','total')->with('vendor:id,nama_perusahaan', 'sales:id,nama_sales')
        ->paginate($numPerPage);

        $penjualan = Penjualan::select('id', 'id_jenis', 'id_pelanggan', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->with('pelanggan:id,nama_pelanggan,no_telepon', 'jenis:id,nama_jenis')->paginate($numPerPage);

        $totalItems = $pembelian->total() + $penjualan->total();
        $lastPage = ceil($totalItems / $numPerPage);
        

        $data = [
            'pembelian' => $pembelian->items(),
            'penjualan' => $penjualan->items(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $lastPage,
            'message' => 'Data Berhasil ditemukan!'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LaporanKeuangan $laporanKeuangan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaporanKeuangan $laporanKeuangan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaporanKeuangan $laporanKeuangan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaporanKeuangan $laporanKeuangan)
    {
        //
    }

    public function export()
    {
        return Excel::download(new LaporanKeuanganExport, 'laporan-keuangan.xlsx');
    }
}
