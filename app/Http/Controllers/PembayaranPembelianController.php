<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuanganKeluar;
use App\Models\LaporanKeuanganMasuk;
use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
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
            'total_dibayar' => 'required|numeric',
            'tanggal_pembayaran' => 'required',
            'referensi_pembayaran' => 'sometimes',
        ]);

        // Retrieve the total amount of the purchase
        $pembelian = Pembelian::findOrFail($validatedData['id_pembelian']);

        // Sum the current total payments
        $total_dibayar = PembayaranPembelian::where('id_pembelian', $validatedData['id_pembelian'])->sum('total_dibayar');

        // Calculate new total after adding the new payment
        $new_total_dibayar = $total_dibayar + $validatedData['total_dibayar'];

        $laporanKeuangan = LaporanKeuanganKeluar::where('id_pembelian', $validatedData['id_pembelian'])->firstOrFail();

        $current_pengeluaran = $laporanKeuangan->pengeluaran;
        $current_utang = $laporanKeuangan->utang;

        // Check if the new total exceeds the purchase total
        if ($new_total_dibayar > $pembelian->total) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran melebihi total tagihan!',
            ], 400);
        }

        // Create the new payment
        $pembayaranPembelian = PembayaranPembelian::create($validatedData);

        // Update the status of the purchase
        if ($new_total_dibayar == $pembelian->total) {
            $pembelian->update([
                'status' => 'Lunas',
            ]);
            $laporanKeuangan->update([
                'utang' => 0,
                'pengeluaran' => $current_pengeluaran + $validatedData['total_dibayar'],
            ]);
            $status_message = 'Data pembayaran pembelian diperbarui menjadi Lunas!';
        } else {
            $pembelian->update([
                'status' => 'Dibayar Sebagian',
            ]);
            $laporanKeuangan->update([
                'utang' => $current_utang - $validatedData['total_dibayar'],
                'pengeluaran' => $current_pengeluaran + $validatedData['total_dibayar'],
            ]);
            $status_message = 'Data pembayaran pembelian diperbarui menjadi Pembayaran Sebagian!';
        }

        return response()->json([
            'success' => true,
            'data' => $pembayaranPembelian,
            'message' => 'Data pembayaran pembelian ditambahkan! ' . $status_message,
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
    public function update(Request $request, $id)
    {
        $total_dibayar = PembayaranPembelian::where('id_pembelian', $id)->sum('total_dibayar');
        $pembelian = Pembelian::findOrFail($id);

        if ($total_dibayar == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada pembayaran yang dilakukan.',
            ]);
        } elseif ($total_dibayar == $pembelian->total) {
            $pembelian->update([
                'status' => 'Lunas',
            ]);
            return response()->json([
                'success' => true,
                'data' => $pembelian->status,
                'message' => 'Data pembayaran pembelian diperbarui menjadi Lunas!',
            ]);
        } elseif ($total_dibayar < $pembelian->total) {
            $pembelian->update([
                'status' => 'Pembayaran Sebagian',
            ]);
            return response()->json([
                'success' => true,
                'data' => $pembelian->status,
                'message' => 'Data pembayaran pembelian diperbarui menjadi Pembayaran Sebagian!',
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
    public function destroy(PembayaranPembelian $pembayaranPembelian)
    {
        //
    }
}