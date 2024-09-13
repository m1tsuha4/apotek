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
        $data = PergerakanStokPembelian::select('id', 'id_barang', 'id_pembelian', 'id_retur_pembelian', 'harga', 'pergerakan_stok', 'stok_keseluruhan')
            ->with([
                'pembelian:id,id_vendor,id_sales,tanggal',
                'pembelian.barangPembelian' => function ($query) use ($request) {
                    $query->select('id', 'id_pembelian', 'batch')
                        ->where('id_barang', $request->id_barang);
                },
                'returPembelian:id,id_pembelian,tanggal',
                'pembelian.vendor:id,nama_perusahaan',
                'pembelian.sales:id,nama_sales',
                'barang:id,id_satuan,nama_barang',
                'barang.satuan:id,nama_satuan'
            ])
            ->orderBy('created_at', 'desc')
            ->where('id_barang', $request->id_barang)
            ->paginate(10);

        // Modify data to standardize 'pembelian' and 'returPembelian'
        $standardizedData = $data->map(function ($item) {
            if ($item->id_retur_pembelian) {
                // Standardize retur to look like pembelian
                return [
                    'id' => $item->id,
                    'id_barang' => $item->id_barang,
                    'id_pembelian' => $item->id_retur_pembelian,
                    'harga' => $item->harga,
                    'pergerakan_stok' => $item->pergerakan_stok,
                    'stok_keseluruhan' => $item->stok_keseluruhan,
                    'pembelian' => [
                        'id' => $item->returPembelian->id_pembelian,
                        'id_vendor' => $item->returPembelian->pembelian->id_vendor ?? null,
                        'id_sales' => $item->returPembelian->pembelian->id_sales ?? null,
                        'tanggal' => $item->returPembelian->tanggal,
                        'barang_pembelian' => $item->returPembelian->barangReturPembelian->map(function ($barangRetur) {
                            return [
                                'id' => $barangRetur->barangPembelian->id,
                                'id_pembelian' => $barangRetur->id_retur_pembelian,
                                'batch' => $barangRetur->barangPembelian->batch
                            ];
                        })->toArray() ?? [],
                        'vendor' => $item->returPembelian->pembelian->vendor ?? null,
                        'sales' => $item->returPembelian->pembelian->sales ?? null,
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
