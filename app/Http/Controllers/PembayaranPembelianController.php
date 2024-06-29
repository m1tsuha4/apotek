<?php

namespace App\Http\Controllers;

use App\Models\PembayaranPembelian;
use Illuminate\Http\Request;

class PembayaranPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'id_pembelian' => 'required',
            'id_metode_pembayaran' => 'required',
            'total_dibayar' => 'required',
            'tanggal_pembayaran' => 'required',
            'referensi_pembayaran' => 'sometimes',
        ]);

        $pembayaranPembelian = PembayaranPembelian::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $pembayaranPembelian,
            'message' => 'Data pembayaran pembelian ditambahkan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PembayaranPembelian $pembayaranPembelian)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }
}
