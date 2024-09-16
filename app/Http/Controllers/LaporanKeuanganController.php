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
            'pembelians.total',
            'pembelians.created_at'
        )
            ->with([
                'vendor:id,nama_perusahaan',
                'sales:id,nama_sales',
                'returPembelian' => function ($query) {
                    $query->select('id', 'id_pembelian', 'tanggal', 'total_retur');
                }
            ])
            ->orderBy('pembelians.created_at', 'desc') // Urutkan berdasarkan tanggal pembelian
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
            'penjualans.total',
            'penjualans.created_at'
        )
            ->with([
                'pelanggan:id,nama_pelanggan,no_telepon',
                'jenis:id,nama_jenis',
                'returPenjualan' => function ($query) {
                    $query->select('id', 'id_penjualan', 'tanggal', 'total_retur');
                }
            ])
            ->orderBy('penjualans.created_at', 'desc') // Urutkan berdasarkan tanggal penjualan
            ->get();

        // Menggabungkan pembelian dan penjualan ke dalam satu collection
        $combined = new Collection();
        $combined = $combined->merge($pembelian)->merge($penjualan);

        // Urutkan combined collection berdasarkan tanggal
        $sortedCombined = $combined->sortByDesc('created_at'); // Urutkan berdasarkan tanggal secara descending (terbaru)

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

    public function search(Request $request)
    {
        $numPerPage = (int)$request->input('num', 15); // Default to 15 if 'num' is not provided
    
        if ($numPerPage <= 0) {
            // Handle the case where numPerPage is zero or negative
            return response()->json([
                'success' => false,
                'message' => 'Invalid number per page!',
            ], 400);
        }
    
        // Fetch pembelian data
        $pembelian = Pembelian::select(
            'pembelians.id',
            'pembelians.id_vendor',
            'pembelians.id_sales',
            'pembelians.tanggal',
            'pembelians.referensi',
            'pembelians.status',
            'pembelians.tanggal_jatuh_tempo',
            'pembelians.total',
            'pembelians.created_at'
        )
            ->with([
                'vendor:id,nama_perusahaan',
                'sales:id,nama_sales',
                'returPembelian:id,pembelian_id,tanggal,total_retur'
            ])
            ->whereHas('vendor', function ($query) use ($request) {
                $query->where('nama_perusahaan', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('pembelians.created_at', 'desc')
            ->get();
    
        // Fetch penjualan data
        $penjualan = Penjualan::select(
            'penjualans.id',
            'penjualans.id_jenis',
            'penjualans.id_pelanggan',
            'penjualans.tanggal',
            'penjualans.referensi',
            'penjualans.status',
            'penjualans.tanggal_jatuh_tempo',
            'penjualans.total',
            'penjualans.created_at'
        )
            ->with([
                'pelanggan:id,nama_pelanggan,no_telepon',
                'jenis:id,nama_jenis',
                'returPenjualan:id,penjualan_id,tanggal,total_retur'
            ])
            ->whereHas('pelanggan', function ($query) use ($request) {
                $query->where('nama_pelanggan', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('penjualans.created_at', 'desc')
            ->get();
    
        // Merge and sort data
        $combined = $pembelian->merge($penjualan);
        $sortedCombined = $combined->sortByDesc('created_at');
    
        // Pagination
        $totalItems = $sortedCombined->count();
        $lastPage = ceil($totalItems / $numPerPage);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedItems = $sortedCombined->slice(($currentPage - 1) * $numPerPage, $numPerPage)->values();
        
        $paginated = new LengthAwarePaginator($paginatedItems, $totalItems, $numPerPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    
        // Return response
        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'last_page' => $lastPage,
            'message' => 'Data Berhasil ditemukan!'
        ]);
    }

    public function export()
    {
        return Excel::download(new LaporanKeuanganExport, 'laporan-keuangan.xlsx');
    }
}
