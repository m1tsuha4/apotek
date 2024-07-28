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
        $data = PergerakanStokPembelian::with('pembelian:id, id_vendor,id_sales,tanggal','pembelian.vendor:id,nama_perusahaans','pembelian.sales:id,nama_sales')
        ->orderBy('created_at', 'desc')
        ->where('id_barang', $request->id_barang)->get();
        return response()->json([
            'success' => true,
            'data' => $data,
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
