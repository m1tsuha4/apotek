<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Models\ReturPenjualan;
use App\Models\SatuanBarang;

class ReturPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returPenjualans = ReturPenjualan::with(['penjualan.pelanggan', 'barangReturPenjualan', 'penjualan.barangPenjualan'])->paginate($request->num);

        $data = collect($returPenjualans->items())->map(function ($returPenjualan) {
            $jumlah = $returPenjualan->penjualan->barangPenjualan->sum('jumlah');
            $jumlah_retur = $returPenjualan->barangReturPenjualan->sum('jumlah_retur');

            return [
                'id' => $returPenjualan->id,
                'id_penjualan' => $returPenjualan->id_penjualan,
                'tanggal' => $returPenjualan->tanggal,
                'id_pelanggan' => $returPenjualan->penjualan->id_pelanggan,
                'vendor' => $returPenjualan->penjualan->pelanggan->nama_pelanggan,
                'no_telepon' => $returPenjualan->penjualan->pelanggan->no_telepon,
                'referensi' => $returPenjualan->referensi,
                'jumlah' => $jumlah,
                'jumlah_retur' => $jumlah_retur,
                'total' => $returPenjualan->total_retur
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $returPenjualans->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = ReturPenjualan::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID retur penjualan berhasil digenerate',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_penjualan' => 'required',
            'tanggal' => 'required',
            'referensi' => 'sometimes',
            'total_retur' => 'required',
            'barang_retur_penjualans' => 'required|array',
            'barang_retur_penjualans.*.id_barang' => 'required',
            'barang_retur_penjualans.*.batch' => 'required',
            'barang_retur_penjualans.*.jumlah_retur' => 'required',
            'barang_retur_penjualans.*.id_satuan' => 'required',
            'barang_retur_penjualans.*.total' => 'required',
        ]);

        $returPenjualan = ReturPenjualan::create($validatedData);

        foreach ($validatedData['barang_retur_penjualans'] as $barangReturPenjualan) {
            $returPenjualan->barangReturPenjualan()->create($barangReturPenjualan);

            $stokBarang = StokBarang::where('id_barang', $barangReturPenjualan['id_barang'])->where('batch', $barangReturPenjualan['batch'])->first();

            $satuanDasar = Barang::where('id', $barangReturPenjualan['id_barang'])->value('id_satuan');

            if($stokBarang) {
                if ($barangReturPenjualan['id_satuan'] == $satuanDasar) {
                    $stokBarang->stok_apotek -= $barangReturPenjualan['jumlah_retur'];
                } else {
                    $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualan['id_barang'])->where('id_satuan', $barangReturPenjualan['id_satuan'])->value('jumlah');
                    $stokBarang->stok_apotek -= $satuanBesar * $barangReturPenjualan['jumlah_retur'];
                }
                $stokBarang->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang tidak ditemukan',
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $returPenjualan,
            'message' => 'Data Berhasil ditambahkan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->load('penjualan', 'penjualan.pelanggan', 'barangReturPenjualan', 'penjualan.barangPenjualan', 'penjualan.barangPenjualan.barang', 'penjualan.barangPenjualan.satuan');
        
        return response()->json([
            'success' => true,
            'data' => $returPenjualan,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturPenjualan $returPenjualan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReturPenjualan $returPenjualan)
    {
        $validatedData = $request->validate([
            'id_penjualan' => 'sometimes',
            'tanggal' => 'sometimes',
            'referensi' => 'sometimes',
            'total_retur' => 'sometimes',
            'barang_retur_penjualans' => 'sometimes|array',
            'barang_retur_penjualans.*.id_barang' => 'sometimes',
            'barang_retur_penjualans.*.batch' => 'sometimes',
            'barang_retur_penjualans.*.jumlah_retur' => 'sometimes',
            'barang_retur_penjualans.*.id_satuan' => 'sometimes',
            'barang_retur_penjualans.*.total' => 'sometimes',
        ]);

        $returPenjualan->update($validatedData);

        foreach ($validatedData['barang_retur_penjualans'] as $index => $barangReturPenjualanData) {
            $barangReturPenjualan = $returPenjualan->barangReturPenjualan()->get()[$index] ?? null;

            if($barangReturPenjualan) {
                $stokBarang = StokBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('batch', $barangReturPenjualanData['batch'])->first();

                if($stokBarang) {
                    $jumlahReturLama = $barangReturPenjualan->jumlah_retur;
                    $jumlahReturBaru = $barangReturPenjualanData['jumlah_retur'];

                    $satuanDasar = Barang::where('id', $barangReturPenjualanData['id_barang'])->value('id_satuan');

                    if ($barangReturPenjualanData['id_satuan'] == $satuanDasar) {
                        $stokBarang->stok_apotek += $jumlahReturLama;
                        $stokBarang->stok_apotek -= $jumlahReturBaru;
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('id_satuan', $barangReturPenjualanData['id_satuan'])->value('jumlah');
                        $stokBarang->stok_apotek += $satuanBesar * $jumlahReturLama;
                        $stokBarang->stok_apotek -= $satuanBesar * $jumlahReturBaru;
                    }

                    $stokBarang->save();

                }

                $barangReturPenjualan->update($barangReturPenjualanData);
            } else {

                $barangReturPenjualan = $returPenjualan->barangReturPenjualan()->create($barangReturPenjualanData);

                $stokBarang = StokBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('batch', $barangReturPenjualanData['batch'])->first();

                $satuanDasar = Barang::where('id', $barangReturPenjualanData['id_barang'])->value('id_satuan');

                if($stokBarang) {
                    if($barangReturPenjualanData['id_satuan'] == $satuanDasar) {
                        $stokBarang->stok_apotek -= $barangReturPenjualanData['jumlah_retur'];
                        $stokBarang->save();
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPenjualanData['id_barang'])->where('id_satuan', $barangReturPenjualanData['id_satuan'])->value('jumlah');
                        $stokBarang->stok_apotek -= $satuanBesar * $barangReturPenjualanData['jumlah_retur'];
                        $stokBarang->save();
                    }
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok barang tidak ditemukan!',
                    ]);
                }

            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data retur penjualan diperbarui!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data retur penjualan dihapus!',
        ]);
    }
}
