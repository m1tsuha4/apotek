<?php

namespace App\Http\Controllers;

use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Exports\StokBarangExport;
use Maatwebsite\Excel\Facades\Excel;

class StokBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stok = StokBarang::paginate(10);

        return response()->json([
            'success' => true,
            'data' => $stok->load(['barang','barang.kategori','barang.satuan']),
            'total' => $stok->total(),
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
        $validatedData = $request->validate([
            'id' => 'required',
            'jumlah' => 'required',
            'dari' => 'required',
            'ke' => 'required',
        ]);

        if ($request->dari === $request->ke) {
            return response()->json([
                'success' => false,
                'message' => 'Sumber dan tujuan harus berbeda',
            ], 400);
        }

        $stokBarang = StokBarang::findOrFail($request->id);

        if ($request->dari == 'gudang') {
            if ($stokBarang->stok_gudang < $request->jumlah) {
                return response()->json([
                  'success' => false,
                  'message' => 'Stok gudang tidak mencukupi',
                ], 400);
            }
            $stokBarang->stok_gudang -= $request->jumlah;
            $stokBarang->stok_apotek += $request->jumlah;
        } else {
            if ($stokBarang->stok_apotek < $request->jumlah) {
                return response()->json([
                  'success' => false,
                  'message' => 'Stok apotek tidak mencukupi',
                ], 400);
            }
            $stokBarang->stok_apotek -= $request->jumlah;
            $stokBarang->stok_gudang += $request->jumlah;
        }

        $stokBarang->save();

        return response()->json([
            'success' => true,
            'message' => 'Stok barang berhasil ditransfer!',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(StokBarang $stokBarang)
    {
        $stok = StokBarang::findOrFail($stokBarang->id);

        return response()->json([
            'success' => true,
            'data' => $stok->load(['barang','barang.kategori','barang.satuan']),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokBarang $stokBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokBarang $stokBarang)
    {
        $validatedData = $request->validate([
            'min_stok_gudang' => 'sometimes',
            'notif_exp' => 'sometimes',
        ]);

        $stokBarang->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $stokBarang->load(['barang','barang.kategori','barang.satuan']),
            'message' => 'Data Berhasil diperbarui!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokBarang $stokBarang)
    {
        $stokBarang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }

    public function export()
    {
        return Excel::download(new StokBarangExport, 'StokBarang.xlsx');
    }

}
