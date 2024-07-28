<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Models\LaporanKeuanganMasuk;
use App\Models\PembayaranPenjualan;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class PembayaranPenjualanController extends Controller
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
            'id_penjualan' => 'required',
            'id_metode_pembayaran' => 'required',
            'total_dibayar' => 'required|numeric',
            'tanggal_pembayaran' => 'required',
            'referensi_pembayaran' => 'sometimes',
        ]);

        // Retrieve the total amount of the purchase
        $penjualan = Penjualan::findOrFail($validatedData['id_penjualan']);

        // Sum the current total payments
        $total_dibayar = PembayaranPenjualan::where('id_penjualan', $validatedData['id_penjualan'])->sum('total_dibayar');

        // Calculate new total after adding the new payment
        $new_total_dibayar = $total_dibayar + $validatedData['total_dibayar'];

        $laporanKeuangan = LaporanKeuanganMasuk::where('id_penjualan', $validatedData['id_penjualan'])->firstOrFail();

        $current_pemasukkan = $laporanKeuangan->pemasukkan;
        $current_piutang = $laporanKeuangan->piutang;        

        // Check if the new total exceeds the purchase total
        if ($new_total_dibayar > $penjualan->total) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran melebihi total tagihan!',
            ], 400);
        }

        // Create the new payment
        $pembayaranPenjualan = PembayaranPenjualan::create($validatedData);

        // Update the status of the purchase
        if ($new_total_dibayar == $penjualan->total) {
            $penjualan->update([
                'status' => 'Lunas',
            ]);
            $laporanKeuangan->update([
                'piutang' => 0,
                'pemasukkan' => $current_pemasukkan + $validatedData['total_dibayar'],
            ]);
            $status_message = 'Data pembayaran penjualan diperbarui menjadi Lunas!';
        } else {
            $penjualan->update([
                'status' => 'Dibayar Sebagian',
            ]);
            $laporanKeuangan->update([
                'piutang' => $current_piutang - $validatedData['total_dibayar'],
                'pemasukkan' => $current_pemasukkan + $validatedData['total_dibayar'],
            ]);
            $status_message = 'Data pembayaran penjualan diperbarui menjadi Pembayaran Sebagian!';
        }

        return response()->json([
            'success' => true,
            'data' => $pembayaranPenjualan,
            'message' => 'Data pembayaran penjualan ditambahkan! ' . $status_message,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PembayaranPenjualan $pembayaranPenjualan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PembayaranPenjualan $pembayaranPenjualan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $total_dibayar = PembayaranPenjualan::where('id_penjualan', $id)->sum('total_dibayar');
        $penjualan = Penjualan::findOrFail($id);

        if ($total_dibayar == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada pembayaran yang dilakukan.',
            ]);
        } elseif ($total_dibayar == $penjualan->total) {
            $penjualan->update([
                'status' => 'Lunas',
            ]);
            return response()->json([
                'success' => true,
                'data' => $penjualan->status,
                'message' => 'Data pembayaran penjualan diperbarui menjadi Lunas!',
            ]);
        } elseif ($total_dibayar < $penjualan->total) {
            $penjualan->update([
                'status' => 'Pembayaran Sebagian',
            ]);
            return response()->json([
                'success' => true,
                'data' => $penjualan->status,
                'message' => 'Data pembayaran penjualan diperbarui menjadi Pembayaran Sebagian!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam pembaruan status pembayaran!',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PembayaranPenjualan $pembayaranPenjualan)
    {
        //
    }
}
