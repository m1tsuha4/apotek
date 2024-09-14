<?php

namespace App\Http\Controllers;

use App\Models\PergerakanStokPenjualan;
use Illuminate\Http\Request;

class PergerakanStokPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Ambil semua data dengan barangPenjualan dan stokBarang terkait
    $data = PergerakanStokPenjualan::select('id', 'id_penjualan', 'id_barang', 'id_retur_penjualan', 'harga', 'pergerakan_stok', 'stok_keseluruhan')
        ->with([
            'penjualan:id,id_pelanggan,tanggal',
            'penjualan.pelanggan:id,nama_pelanggan,no_telepon',
            'penjualan.barangPenjualan:id,id_penjualan,id_barang,id_stok_barang',
            'penjualan.barangPenjualan.stokBarang:id,id_stok_barang,batch',
            'barang:id,id_satuan,nama_barang',
            'barang.satuan:id,nama_satuan'
        ])
        ->orderBy('created_at', 'desc')
        ->where('id_barang', $request->id_barang)
        ->paginate(10);

    $standardizedData = $data->map(function ($item) {
        // Menyimpan batch yang sudah digunakan
        static $usedBatches = [];

        // Filter barangPenjualan untuk memastikan batch yang unik
        $barangPenjualan = $item->penjualan ? $item->penjualan->barangPenjualan : collect();

        // Ambil batch unik untuk setiap pergerakan stok penjualan
        $batchToShow = $barangPenjualan->flatMap(function ($barang) {
            return $barang->stokBarang ? [$barang->stokBarang->batch] : [];
        })->first(function ($batch) use (&$usedBatches) {
            return !in_array($batch, $usedBatches);
        });

        // Tambahkan batch yang dipilih ke array usedBatches
        if ($batchToShow) {
            $usedBatches[] = $batchToShow;
        }

        // Filter barangPenjualan untuk hanya menampilkan batch yang dipilih
        $filteredBarangPenjualan = $barangPenjualan->filter(function ($barang) use ($batchToShow) {
            return $barang->stokBarang && $barang->stokBarang->batch === $batchToShow;
        });

        // Return data yang sudah distandarisasi
        return [
            'id' => $item->id,
            'id_penjualan' => $item->penjualan ? $item->penjualan->id : $item->id_retur_penjualan,
            'id_barang' => $item->id_barang,
            'harga' => $item->harga,
            'pergerakan_stok' => $item->pergerakan_stok,
            'stok_keseluruhan' => $item->stok_keseluruhan,
            'penjualan' => $item->penjualan ? [
                'id' => $item->penjualan->id,
                'id_pelanggan' => $item->penjualan->id_pelanggan,
                'tanggal' => $item->penjualan->tanggal,
                'barang_penjualan' => $filteredBarangPenjualan->map(function ($barang) {
                    return [
                        'id' => $barang->id,
                        'id_penjualan' => $barang->id_penjualan,
                        'id_stok_barang' => $barang->id_stok_barang,
                        'batch' => $barang->stokBarang->batch ?? null,
                    ];
                })->values()->toArray(),
                'pelanggan' => $item->penjualan->pelanggan,
            ] : [
                'id' => $item->id_retur_penjualan ?? null,
                'id_pelanggan' => $item->returPenjualan->penjualan->id_pelanggan ?? null,
                'tanggal' => $item->returPenjualan->tanggal ?? null,
                'barang_penjualan' => $item->returPenjualan->barangReturPenjualan->map(function ($barangRetur) {
                    return [
                        'id' => $barangRetur->barangPenjualan->id ?? null,
                        'id_penjualan' => $barangRetur->id_retur_penjualan ?? null,
                        'id_stok_barang' => $barangRetur->barangPenjualan->id_stok_barang ?? null,
                        'batch' => $barangRetur->barangPenjualan->stokBarang->batch ?? null,
                    ];
                })->toArray(),
                'pelanggan' => $item->returPenjualan->penjualan->pelanggan ?? null,
            ],
            'barang' => $item->barang,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $standardizedData,
        'last_page' => $data->lastPage(),
        'message' => 'Data Berhasil ditemukan!',
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
    public function show(PergerakanStokPenjualan $pergerakanStokPenjualan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PergerakanStokPenjualan $pergerakanStokPenjualan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PergerakanStokPenjualan $pergerakanStokPenjualan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PergerakanStokPenjualan $pergerakanStokPenjualan)
    {
        //
    }
}
