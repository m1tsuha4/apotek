<?php

namespace App\Http\Controllers;

use App\Models\PergerakanStokPembelian;
use Illuminate\Http\Request;

class PergerakanStokPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil semua data dengan barangPembelian terkait
        $data = PergerakanStokPembelian::select('id', 'id_barang', 'id_pembelian', 'id_retur_pembelian', 'id_stok_barang', 'harga', 'pergerakan_stok', 'stok_keseluruhan')
            ->with([
                'stokBarang:id,batch',
                'pembelian:id,id_vendor,id_sales,tanggal',
                'pembelian.barangPembelian:id,id_pembelian,batch',
                'returPembelian:id,id_pembelian,tanggal',
                'pembelian.vendor:id,nama_perusahaan',
                'pembelian.sales:id,nama_sales',
                'barang:id,id_satuan,nama_barang',
                'barang.satuan:id,nama_satuan'
            ])
            ->orderBy('created_at', 'desc')
            ->where('id_barang', $request->id_barang)
            ->paginate(10);

        // Fungsi untuk menentukan batch yang akan ditampilkan
        $standardizedData = $data->map(function ($item) {
            $pembelian = $item->pembelian;
            $barangPembelian = $pembelian ? $pembelian->barangPembelian : collect();

            // Ambil batch dari stokBarang
            $batchStokBarang = $item->stokBarang->batch ?? null;

            // Filter barang_pembelian untuk hanya menampilkan batch yang sesuai dengan stokBarang
            $filteredBarangPembelian = $barangPembelian->filter(function ($barang) use ($batchStokBarang) {
                return $barang->batch === $batchStokBarang;
            });

            // Return data yang sudah distandarisasi
            return [
                'id' => $item->id,
                'id_barang' => $item->id_barang,
                'id_pembelian' => $item->id_pembelian,
                'harga' => $item->harga,
                'pergerakan_stok' => $item->pergerakan_stok,
                'stok_keseluruhan' => $item->stok_keseluruhan,
                'pembelian' => $pembelian ? [
                    'id' => $pembelian->id,
                    'id_vendor' => $pembelian->id_vendor,
                    'id_sales' => $pembelian->id_sales,
                    'tanggal' => $pembelian->tanggal,
                    'barang_pembelian' => $filteredBarangPembelian->map(function ($barang) {
                        return [
                            'id' => $barang->id,
                            'id_pembelian' => $barang->id_pembelian,
                            'batch' => $barang->batch
                        ];
                    })->values()->toArray(),
                    'vendor' => $pembelian->vendor,
                    'sales' => $pembelian->sales,
                ] : null,
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
    public function show(PergerakanStokPembelian $pergerakanStokPembelian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PergerakanStokPembelian $pergerakanStokPembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PergerakanStokPembelian $pergerakanStokPembelian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PergerakanStokPembelian $pergerakanStokPembelian)
    {
        //
    }
}
