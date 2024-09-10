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
        $data = PergerakanStokPenjualan::select('id', 'id_penjualan', 'id_barang', 'id_retur_penjualan', 'harga', 'pergerakan_stok', 'stok_keseluruhan')
            ->with([
                'penjualan:id,id_pelanggan,tanggal',
                'penjualan.pelanggan:id,nama_pelanggan,no_telepon',
                'penjualan.barangPenjualan' => function ($query) use ($request) {
                    $query->select('id', 'id_penjualan', 'id_stok_barang')
                        ->where('id_barang', $request->id_barang);
                },
                'penjualan.barangPenjualan.stokBarang:id,batch',
                'barang:id,id_satuan,nama_barang',
                'barang.satuan:id,nama_satuan'
            ])
            ->orderBy('created_at', 'desc')
            ->where('id_barang', $request->id_barang)
            ->paginate(10);

        $standardizedData = $data->map(function ($item) {
            if ($item->id_retur_penjualan && $item->returPenjualan) {
                return [
                    'id' => $item->id,
                    'id_penjualan' => $item->id_retur_penjualan,
                    'id_barang' => $item->id_barang,
                    'harga' => $item->harga,
                    'pergerakan_stok' => $item->pergerakan_stok,
                    'stok_keseluruhan' => $item->stok_keseluruhan,
                    'penjualan' => [
                        'id' => $item->returPenjualan->id_penjualan ?? null,
                        'id_pelanggan' => $item->returPenjualan->penjualan->id_pelanggan ?? null,
                        'tanggal' => $item->returPenjualan->tanggal ?? null,
                        'barang_penjualan' => $item->returPenjualan->barangReturPenjualan->map(function ($barangRetur) {
                            return [
                                'id' => $barangRetur->barangPenjualan->id ?? null,
                                'id_penjualan' => $barangRetur->barangPenjualan->id_penjualan ?? null,
                                'id_stok_barang' => $barangRetur->barangPenjualan->id_stok_barang ?? null,
                                'batch' => $barangRetur->barangPenjualan->stokBarang->batch ?? null,
                            ];
                        })->toArray(),
                        'pelanggan' => $item->returPenjualan->penjualan->pelanggan ?? null,
                    ],
                    'barang' => $item->barang,
                ];
            }

            return $item;
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
