<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $penjualan = Penjualan::with('pelanggan:id,nama_pelanggan,no_telepon', 'jenis:id,nama_jenis')->paginate($request->num);

        return response()->json([
            'success' => true,
            'data' => $penjualan->items(),
            'last_page' => $penjualan->lastPage(),
            'message' => 'Data penjualan berhasil ditemukan!'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = Penjualan::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID penjualan berhasil digenerate',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pelanggan' => 'required',
            'id_jenis' => 'required',
            'tanggal' => 'required',
            'status' => 'required',
            'tanggal_jatuh_tempo' => 'required',
            'referensi' => 'sometimes',
            'sub_total' => 'required',
            'total_diskon_satuan' => 'sometimes',
            'diskon' => 'sometimes',
            'total' => 'required',
            'catatan' => 'sometimes',
            'barang_penjualans' => 'required|array',
            'barang_penjualans.*.id_barang' => 'required',
            'barang_penjualans.*.jumlah' => 'required',
            'barang_penjualans.*.id_satuan' => 'required',
            'barang_penjualans.*.jenis_diskon' => 'sometimes',
            'barang_penjualans.*.diskon' => 'sometimes',
            'barang_penjualans.*.harga' => 'required',
            'barang_penjualans.*.total' => 'required'
        ]);

        $penjualan = Penjualan::create($validatedData);

        foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
            $penjualan->barangPenjualan()->create($barangPenjualanData);

            

    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penjualan $penjualan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penjualan $penjualan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan berhasil dihapus!'
        ]);
    }
}
