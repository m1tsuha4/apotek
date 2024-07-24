<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Exports\PenjualanExport;
use Illuminate\Support\Facades\DB;
use App\Models\PembayaranPenjualan;
use Maatwebsite\Excel\Facades\Excel;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $penjualan = Penjualan::select('id', 'id_jenis', 'id_pelanggan', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->with('pelanggan:id,nama_pelanggan,no_telepon', 'jenis:id,nama_jenis')->paginate($request->num);

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
            'barang_penjualans.*.jumlah' => 'required|integer',
            'barang_penjualans.*.id_satuan' => 'required|integer',
            'barang_penjualans.*.jenis_diskon' => 'sometimes',
            'barang_penjualans.*.diskon' => 'sometimes|integer',
            'barang_penjualans.*.harga' => 'required|integer',
            'barang_penjualans.*.total' => 'required|integer'
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            // Buat penjualan baru
            $penjualan = Penjualan::create($validatedData);

            // Proses setiap barang dalam penjualan
            foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
                $jumlah = $barangPenjualanData['jumlah'];
                $idBarang = $barangPenjualanData['id_barang'];

                // Ambil satuan dasar barang
                $satuanDasar = Barang::where('id', $idBarang)->value('id_satuan');

                // Dapatkan stok barang yang tersedia berdasarkan barang dan satuan
                $stokBarangs = StokBarang::where('id_barang', $idBarang)
                    ->where('stok_apotek', '>', 0)
                    ->orderBy('exp_date', 'asc')
                    ->get();

                foreach ($stokBarangs as $stokBarang) {
                    if ($jumlah <= 0) {
                        break;
                    }

                    $stokTersedia = $stokBarang->stok_apotek;

                    if ($barangPenjualanData['id_satuan'] == $satuanDasar) {
                        // Jika satuan yang digunakan adalah satuan dasar
                        $stokPengurangan = min($stokTersedia, $jumlah);
                        $jumlah -= $stokPengurangan;
                    } else {
                        // Jika satuan yang digunakan adalah satuan besar
                        $satuanBesarJumlah = SatuanBarang::where('id_barang', $idBarang)
                            ->where('id_satuan', $barangPenjualanData['id_satuan'])
                            ->value('jumlah');

                        $stokPengurangan = min($stokTersedia, $jumlah * $satuanBesarJumlah);
                        $jumlah -= intval(ceil($stokPengurangan / $satuanBesarJumlah));
                    }

                    // Buat entri barang penjualan
                    $penjualan->barangPenjualan()->create([
                        'id_barang' => $idBarang,
                        'jumlah' => $stokPengurangan,
                        'id_satuan' => $barangPenjualanData['id_satuan'],
                        'id_stok_barang' => $stokBarang->id,
                        'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                        'diskon' => $barangPenjualanData['diskon'] ?? 0,
                        'harga' => $barangPenjualanData['harga'],
                        'total' => $barangPenjualanData['total'],
                    ]);

                    // Kurangi stok barang
                    $stokBarang->stok_apotek -= $stokPengurangan;
                    $stokBarang->stok_total -= $stokPengurangan;
                    $stokBarang->save();
                }

                if ($jumlah > 0) {
                    // Rollback transaction if stock is insufficient
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi untuk jumlah yang diminta untuk barang ID: ' . $idBarang,
                    ], 400);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil ditambahkan dan stok diperbarui',
                'data' => $penjualan
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan)
    {
        $pembayaranPenjualan = PembayaranPenjualan::where('id_penjualan', $penjualan->id)->sum('total_dibayar');

        $data = [
            'id' => $penjualan->id,
            'status' => $penjualan->status,
            'id_pelanggan' => $penjualan->id_pelanggan,
            'nama_pelanggan' => $penjualan->pelanggan->nama_pelanggan,
            'no_telepon' => $penjualan->pelanggan->no_telepon,
            'id_jenis' => $penjualan->id_jenis,
            'nama_jenis' => $penjualan->jenis->nama_jenis,
            'tanggal' => $penjualan->tanggal,
            'tanggal_jatuh_tempo' => $penjualan->tanggal_jatuh_tempo,
            'referensi' => $penjualan->referensi,
            'sub_total' => $penjualan->sub_total,
            'total_diskon_satuan' => $penjualan->total_diskon_satuan,
            'diskon' => $penjualan->diskon,
            'total' => $penjualan->total,
            'catatan' => $penjualan->catatan,
            'sisa_tagihan' => $penjualan->total - $pembayaranPenjualan,
            'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'jumlah' => $barangPenjualan->jumlah,
                    'id_satuan' => $barangPenjualan->id_satuan,
                    'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
                    'jenis_diskon' => $barangPenjualan->jenis_diskon,
                    'diskon' => $barangPenjualan->diskon,
                    'harga' => $barangPenjualan->harga,
                    'total' => $barangPenjualan->total
                ];
            }),
            'pembayaranPenjualan' => $penjualan->pembayaranPenjualan->map(function ($pembayaranPenjualan) {
                return [
                    'id' => $pembayaranPenjualan->id,
                    'id_penjualan' => $pembayaranPenjualan->id_penjualan,
                    'tanggal_pembayaran' => $pembayaranPenjualan->tanggal_pembayaran,
                    'metode_pembayaran' => $pembayaranPenjualan->metodePembayaran->nama_metode,
                    'total_dibayar' => $pembayaranPenjualan->total_dibayar,
                    'referensi_pembayaran' => $pembayaranPenjualan->referensi_pembayaran
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data penjualan berhasil ditemukan',
        ]);
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
            'barang_penjualans.*.jumlah' => 'required|integer',
            'barang_penjualans.*.id_satuan' => 'required|integer',
            'barang_penjualans.*.jenis_diskon' => 'sometimes',
            'barang_penjualans.*.diskon' => 'sometimes|integer',
            'barang_penjualans.*.harga' => 'required|integer',
            'barang_penjualans.*.total' => 'required|integer'
        ]);

        // Find existing penjualan
        $penjualan = Penjualan::findOrFail($penjualan->id);

        // Update penjualan data
        $penjualan->update($validatedData);

        // Process each barang in penjualan
        foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
            $idBarang = $barangPenjualanData['id_barang'];
            $jumlahBaru = $barangPenjualanData['jumlah'];

            // Find existing barang penjualan
            $existingBarangPenjualan = $penjualan->barangPenjualan()->where('id_barang', $idBarang)->first();

            if ($existingBarangPenjualan) {
                // Calculate the difference between old and new quantity
                $jumlahLama = $existingBarangPenjualan->jumlah;
                $jumlahDifference = $jumlahBaru - $jumlahLama;
            } else {
                $jumlahDifference = $jumlahBaru;
            }

            // Get satuan dasar
            $satuanDasar = Barang::where('id', $idBarang)->value('id_satuan');

            // Get available stock
            $stokBarangs = StokBarang::where('id_barang', $idBarang)
                ->where('stok_apotek', '>', 0)
                ->orderBy('exp_date', 'asc')
                ->get();

            foreach ($stokBarangs as $stokBarang) {
                if ($jumlahDifference <= 0) {
                    break;
                }

                $stokTersedia = $stokBarang->stok_apotek;

                if ($barangPenjualanData['id_satuan'] == $satuanDasar) {
                    // If using base unit
                    $stokPengurangan = min($stokTersedia, $jumlahDifference);
                    $jumlahDifference -= $stokPengurangan;
                } else {
                    // If using larger unit
                    $satuanBesarJumlah = SatuanBarang::where('id_barang', $idBarang)
                        ->where('id_satuan', $barangPenjualanData['id_satuan'])
                        ->value('jumlah');

                    $stokPengurangan = min($stokTersedia, $jumlahDifference * $satuanBesarJumlah);
                    $jumlahDifference -= intval(ceil($stokPengurangan / $satuanBesarJumlah));
                }

                if ($existingBarangPenjualan) {
                    // Update existing barang penjualan
                    $existingBarangPenjualan->update([
                        'jumlah' => $jumlahBaru,
                        'id_satuan' => $barangPenjualanData['id_satuan'],
                        'id_stok_barang' => $stokBarang->id,
                        'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                        'diskon' => $barangPenjualanData['diskon'] ?? 0,
                        'harga' => $barangPenjualanData['harga'],
                        'total' => $barangPenjualanData['total'],
                    ]);
                } else {
                    // Create new barang penjualan
                    $penjualan->barangPenjualan()->create([
                        'id_barang' => $idBarang,
                        'jumlah' => $stokPengurangan,
                        'id_satuan' => $barangPenjualanData['id_satuan'],
                        'id_stok_barang' => $stokBarang->id,
                        'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                        'diskon' => $barangPenjualanData['diskon'] ?? 0,
                        'harga' => $barangPenjualanData['harga'],
                        'total' => $barangPenjualanData['total'],
                    ]);
                }

                // Update stock
                $stokBarang->stok_apotek -= $stokPengurangan;
                $stokBarang->stok_total -= $stokPengurangan;
                $stokBarang->save();
            }

            if ($jumlahDifference > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk jumlah yang diminta untuk barang ID: ' . $idBarang,
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil diperbarui dan stok diperbarui',
            'data' => $penjualan
        ]);
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

    public function returPenjualan(Penjualan $penjualan)
    {
        $data = [
            'id' => $penjualan->id,
            'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'batch' => $barangPenjualan->batch,
                    'jumlah' => $barangPenjualan->jumlah,
                    'id_satuan' => $barangPenjualan->id_satuan,
                    'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
                    'diskon' => $barangPenjualan->diskon,
                    'harga' => $barangPenjualan->harga,
                    'total' => $barangPenjualan->total
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'messages' => 'Data Retur Berhasil ditampilkan!'
        ]);
    }

    public function setPenjualan(Penjualan $penjualan)
    {
        $penjualan->update([
            'id_jenis' => '3',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan berhasil diperbarui!'
        ]);
    }

    public function export()
    {
        return Excel::download(new PenjualanExport, 'penjualan.xlsx');
    }
}
