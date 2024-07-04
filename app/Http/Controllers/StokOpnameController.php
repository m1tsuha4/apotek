<?php

namespace App\Http\Controllers;

use App\Models\StokBarang;
use App\Models\StokOpname;
use Illuminate\Http\Request;

class StokOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stokOpname = StokOpname::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $stokOpname->load(['stokBarang','stokBarang.barang','stokBarang.barang.kategori']),
            'message' => 'Data Stok Opname Berhasil ditemukan!',
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
            'id_stok_barang' => 'required',
            'sumber_stok' => 'required',
            'tanggal' => 'required',
            'stok_tercatat' => 'required',
            'stok_aktual' => 'required',
        ]);

        StokOpname::create($validatedData);

        $stokBarang = StokBarang::findOrFail($request->id_stok_barang);
        if ($request->sumber_stok == 'Gudang') {
            $stokBarang->stok_gudang = $request->stok_aktual;
        } elseif ($request->sumber_stok == 'Apotek') {
            $stokBarang->stok_apotek = $request->stok_aktual;
        }
        $stokBarang->save();

        return response()->json([
            'success' => true,
            'message' => 'Stok barang berhasil disesuaikan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(StokOpname $stokOpname)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokOpname $stokOpname)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokOpname $stokOpname)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokOpname $stokOpname)
    {
        $stokOpname->delete();
        return response()->json([
            'success' => true,
            'message' => 'Stok Opname Berhasil dihapus!',
        ]);
    }
}
