<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Exports\PembelianExport;
use App\Models\PembayaranPembelian;
use Maatwebsite\Excel\Facades\Excel;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembelian = Pembelian::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian','jenis','sales','sales.vendor']),
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_sales' => 'required',
            'id_jenis' => 'required',
            'tanggal' => 'required',
            'status' => 'required',
            'tanggal_jatuh_tempo' => 'required',
            'referensi' => 'sometimes',
            'sub_total' => 'required',
            'diskon' => 'sometimes',
            'total' => 'required',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'required|array',
            'barang_pembelians.*.id_barang' => 'required',
            'barang_pembelians.*.batch' => 'required',
            'barang_pembelians.*.exp_date' => 'required',
            'barang_pembelians.*.jumlah' => 'required',
            'barang_pembelians.*.id_satuan' => 'required',
            'barang_pembelians.*.diskon' => 'required',
            'barang_pembelians.*.harga' => 'required',
            'barang_pembelians.*.total' => 'required'
        ]);

        $pembelian = Pembelian::create($validatedData);

        foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
            $pembelian->barangPembelian()->create([
                'id_barang' => $barangPembelianData['id_barang'],
                'batch' => $barangPembelianData['batch'],
                'exp_date' => $barangPembelianData['exp_date'],
                'jumlah' => $barangPembelianData['jumlah'],
                'id_satuan' => $barangPembelianData['id_satuan'],
                'diskon' => $barangPembelianData['diskon'],
                'harga' => $barangPembelianData['harga'],
                'total' => $barangPembelianData['total']
            ]);

            StokBarang::create([
                'id_barang' => $barangPembelianData['id_barang'],
                'batch' => $barangPembelianData['batch'],
                'exp_date' => $barangPembelianData['exp_date'],
                'stok_gudang' => $barangPembelianData['jumlah']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian']),
            'message' => 'Pembelian Berhasil!',
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pembelian $pembelian)
    {

        $pembayaranPembelian = PembayaranPembelian::where('id_pembelian', $pembelian->id)->sum('total_dibayar');

        $data = [
            'id' => $pembelian->id,
            'status' => $pembelian->status,
            'nama_sales' => $pembelian->sales->vendor->nama_perusahaan,
            'tanggal' => $pembelian->tanggal,
            'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo,
            'catatan' => $pembelian->catatan,
            'sub_total' => $pembelian->sub_total,
            'diskon' => $pembelian->diskon,
            'total' => $pembelian->total,
            'sisa_tagihan' => $pembelian->total - $pembayaranPembelian,
            'barangPembelian' => $pembelian->barangPembelian->map(function ($barangPembelian) {
                return [
                    'id' => $barangPembelian->id,
                    'id_barang' => $barangPembelian->id_barang,
                    'nama_barang' => $barangPembelian->barang->nama_barang,
                    'batch' => $barangPembelian->batch,
                    'jumlah' => $barangPembelian->jumlah,
                    'id_satuan' => $barangPembelian->id_satuan,
                    'nama_satuan' => $barangPembelian->satuan->nama_satuan,
                    'diskon' => $barangPembelian->diskon,
                    'harga' => $barangPembelian->harga,
                    'total' => $barangPembelian->total
                ];
            }),
            'pembayaranPembelian' => $pembelian->pembayaranPembelian->map(function ($pembayaranPembelian) {
                return [
                    'id' => $pembayaranPembelian->id,
                    'id_pembelian' => $pembayaranPembelian->id_pembelian,
                    'tanggal_pembayaran' => $pembayaranPembelian->tanggal_pembayaran,
                    'metode_pembayaran' => $pembayaranPembelian->metodePembayaran->nama_metode,
                    'total_dibayar' => $pembayaranPembelian->total_dibayar,
                    'referensi_pembayaran' => $pembayaranPembelian->referensi_pembayaran
                ];
            })
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pembelian $pembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pembelian $pembelian)
    {
        $validatedData = $request->validate([
            'id_sales' => 'sometimes',
            'id_jenis' => 'sometimes',
            'tanggal' => 'sometimes',
            'status' => 'sometimes',
            'tanggal_jatuh_tempo' => 'sometimes',
            'referensi' => 'sometimes',
            'sub_total' => 'sometimes',
            'diskon' => 'sometimes',
            'total' => 'sometimes',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'sometimes|array',
            'barang_pembelians.*.id_barang' => 'sometimes',
            'barang_pembelians.*.batch' => 'sometimes',
            'barang_pembelians.*.exp_date' => 'sometimes',
            'barang_pembelians.*.jumlah' => 'sometimes',
            'barang_pembelians.*.id_satuan' => 'sometimes',
            'barang_pembelians.*.diskon' => 'sometimes',
            'barang_pembelians.*.harga' => 'sometimes',
            'barang_pembelians.*.total' => 'sometimes'
        ]);

        $pembelian->update($validatedData);

        foreach ($validatedData['barang_pembelians'] as $index => $barangPembelianData) {
            $barangPembelian = $pembelian->barangPembelian()->get()[$index] ?? null;
            if ($barangPembelian) {
                $barangPembelian->update($barangPembelianData);
            } else {
                $pembelian->barangPembelian()->create($barangPembelianData);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian']),
            'message' => 'Pembelian Berhasil!',
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pembelian $pembelian)
    {
        $pembelian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }

    public function export()
    {
        return Excel::download(new PembelianExport, 'pembelian.xlsx');
    }

    public function returPembelian(Pembelian $pembelian)
    {
        $data = [
            'id' => $pembelian->id,
            'barangPembelian' => $pembelian->barangPembelian->map(function ($barangPembelian) {
                return [
                    'id' => $barangPembelian->id,
                    'id_barang' => $barangPembelian->id_barang,
                    'nama_barang' => $barangPembelian->barang->nama_barang,
                    'batch' => $barangPembelian->batch,
                    'jumlah' => $barangPembelian->jumlah,
                    'id_satuan' => $barangPembelian->id_satuan,
                    'nama_satuan' => $barangPembelian->satuan->nama_satuan,
                    'diskon' => $barangPembelian->diskon,
                    'harga' => $barangPembelian->harga,
                    'total' => $barangPembelian->total
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'messages' => 'Data Retur Berhasil ditampilkan!'
        ]);
    }
}
