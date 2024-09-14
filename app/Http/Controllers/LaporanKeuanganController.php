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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LaporanKeuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $numPerPage = $request->num;

        // Mengambil data pembelian dan penjualan
        $pembelian = Pembelian::select(
            'pembelians.id',
            'pembelians.id_vendor',
            'pembelians.id_sales',
            'pembelians.tanggal',
            'pembelians.referensi',
            'pembelians.status',
            'pembelians.tanggal_jatuh_tempo',
            'pembelians.total'
        )
            ->with([
                'vendor:id,nama_perusahaan',
                'sales:id,nama_sales',
                'returPembelian' => function ($query) {
                    $query->select('id', 'id_pembelian', 'tanggal', 'total_retur');
                }
            ])
            ->orderBy('pembelians.tanggal', 'desc') // Urutkan berdasarkan tanggal pembelian
            ->get();

        // Mengambil data penjualan beserta semua retur yang terkait
        $penjualan = Penjualan::select(
            'penjualans.id',
            'penjualans.id_jenis',
            'penjualans.id_pelanggan',
            'penjualans.tanggal',
            'penjualans.referensi',
            'penjualans.status',
            'penjualans.tanggal_jatuh_tempo',
            'penjualans.total'
        )
            ->with([
                'pelanggan:id,nama_pelanggan,no_telepon',
                'jenis:id,nama_jenis',
                'returPenjualan' => function ($query) {
                    $query->select('id', 'id_penjualan', 'tanggal', 'total_retur');
                }
            ])
            ->orderBy('penjualans.tanggal', 'desc') // Urutkan berdasarkan tanggal penjualan
            ->get();

        // Menggabungkan pembelian dan penjualan ke dalam satu collection
        $combined = new Collection();
        $combined = $combined->merge($pembelian)->merge($penjualan);

        // Urutkan combined collection berdasarkan tanggal
        $sortedCombined = $combined->sortByDesc('tanggal'); // Urutkan berdasarkan tanggal secara descending (terbaru)

        // Menghitung total items dan last page
        $totalItems = $sortedCombined->count();
        $lastPage = ceil($totalItems / $numPerPage);

        // Melakukan paginasi manual pada collection yang sudah diurutkan
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedItems = $sortedCombined->slice(($currentPage - 1) * $numPerPage, $numPerPage)->values();
        $paginated = new LengthAwarePaginator($paginatedItems, $totalItems, $numPerPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // Data response
        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
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
