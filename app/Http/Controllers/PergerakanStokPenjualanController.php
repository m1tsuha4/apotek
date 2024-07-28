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
        $data = PergerakanStokPenjualan::with('penjualan:id,id_pelanggan,tanggal','penjualan.pelanggan:id,nama_pelanggan,no_telepon')->orderBy('created_at', 'desc')->where('id_barang', $request->id_barang)->get();

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
