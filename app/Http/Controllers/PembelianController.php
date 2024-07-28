<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use App\Exports\PembelianExport;
use App\Models\LaporanKeuanganKeluar;
use App\Models\PembayaranPembelian;
use App\Models\PergerakanStokPembelian;
use App\Models\Satuan;
use App\Models\SatuanBarang;
use Maatwebsite\Excel\Facades\Excel;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pembelian = Pembelian::with('barangPembelian', 'jenis:id,nama_jenis', 'vendor:id,nama_perusahaan', 'sales:id,nama_sales')
            ->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $pembelian->items(),
            'last_page' => $pembelian->lastPage(),
            'message' => 'Data pembelian berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = Pembelian::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID pembelian berhasil digenerate',
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id_vendor' => 'required',
            'id_sales' => 'required',
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
            'barang_pembelians' => 'required|array',
            'barang_pembelians.*.id_barang' => 'required',
            'barang_pembelians.*.batch' => 'required',
            'barang_pembelians.*.exp_date' => 'required',
            'barang_pembelians.*.jumlah' => 'required',
            'barang_pembelians.*.id_satuan' => 'required',
            'barang_pembelians.*.jenis_diskon' => 'sometimes',
            'barang_pembelians.*.diskon' => 'sometimes',
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
                'jenis_diskon' => $barangPembelianData['jenis_diskon'],
                'diskon' => $barangPembelianData['diskon'],
                'harga' => $barangPembelianData['harga'],
                'total' => $barangPembelianData['total']
            ]);

            $satuanDasar = Barang::where('id', $barangPembelianData['id_barang'])->value('id_satuan');
            $hargaAsli = Barang::where('id', $barangPembelianData['id_barang'])->value('harga_beli');

            if ($barangPembelianData['harga'] != $hargaAsli) {

                $selisih = $barangPembelianData['harga'] - $hargaAsli;

                // Create a new record in the PergerakanStokPembelian table
                PergerakanStokPembelian::create([
                    'id_pembelian' => $pembelian->id,
                    'pergerakan_stok' => $selisih
                ]);

                // Update the harga_beli with the new price
                Barang::where('id', $barangPembelianData['id_barang'])->update(['harga_beli' => $barangPembelianData['harga']]);
            }

            if ($barangPembelianData['id_satuan'] == $satuanDasar) {
                StokBarang::create([
                    'id_barang' => $barangPembelianData['id_barang'],
                    'batch' => $barangPembelianData['batch'],
                    'exp_date' => $barangPembelianData['exp_date'],
                    'stok_gudang' => $barangPembelianData['jumlah'],
                    'stok_total' => $barangPembelianData['jumlah']
                ]);
            } else {
                $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
                $stok = $barangPembelianData['jumlah'] * $satuanBesar;
                StokBarang::create([
                    'id_barang' => $barangPembelianData['id_barang'],
                    'batch' => $barangPembelianData['batch'],
                    'exp_date' => $barangPembelianData['exp_date'],
                    'stok_gudang' => $stok,
                    'stok_total' => $stok
                ]);
            }
        }

        if ($validatedData['id_jenis'] == '2') {
            LaporanKeuanganKeluar::create([
                'id_pembelian' => $pembelian->id,
                'utang' => $validatedData['total']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $pembelian,
            'message' => 'Pembelian Berhasil!',
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'id_vendor' => 'required',
    //         'id_sales' => 'required',
    //         'id_jenis' => 'required',
    //         'tanggal' => 'required',
    //         'status' => 'required',
    //         'tanggal_jatuh_tempo' => 'required',
    //         'referensi' => 'sometimes',
    //         'sub_total' => 'required',
    //         'total_diskon_satuan' => 'sometimes',
    //         'diskon' => 'sometimes',
    //         'total' => 'required',
    //         'catatan' => 'sometimes',
    //         'barang_pembelians' => 'required|array',
    //         'barang_pembelians.*.id_barang' => 'required',
    //         'barang_pembelians.*.batch' => 'required',
    //         'barang_pembelians.*.exp_date' => 'required',
    //         'barang_pembelians.*.jumlah' => 'required',
    //         'barang_pembelians.*.id_satuan' => 'required',
    //         'barang_pembelians.*.jenis_diskon' => 'sometimes',
    //         'barang_pembelians.*.diskon' => 'sometimes',
    //         'barang_pembelians.*.harga' => 'required',
    //         'barang_pembelians.*.total' => 'required'
    //     ]);

    //     $pembelian = Pembelian::create($validatedData);

    //     foreach ($validatedData['barang_pembelians'] as $barangPembelianData) {
    //         $pembelian->barangPembelian()->create([
    //             'id_barang' => $barangPembelianData['id_barang'],
    //             'batch' => $barangPembelianData['batch'],
    //             'exp_date' => $barangPembelianData['exp_date'],
    //             'jumlah' => $barangPembelianData['jumlah'],
    //             'id_satuan' => $barangPembelianData['id_satuan'],
    //             'jenis_diskon' => $barangPembelianData['jenis_diskon'],
    //             'diskon' => $barangPembelianData['diskon'],
    //             'harga' => $barangPembelianData['harga'],
    //             'total' => $barangPembelianData['total']
    //         ]);

    //         $satuanDasar = Barang::where($barangPembelianData['id_barang'])->value('id_satuan');

    //         $hargaAsli = Barang::where($barangPembelianData['id_barang'])->value('harga_beli');

    //         if ($barangPembelianData['harga'] != $hargaAsli) {
    //             $selisih = $barangPembelianData['harga'] - $hargaAsli;

    //             // Create a new record in the PergerakanStokPembelian table
    //             PergerakanStokPembelian::create([
    //                 'id_pembelian' => $pembelian->id,
    //                 'pergerakan_stok' => $selisih
    //             ]);

    //             // Update the harga_beli with the new price
    //             Barang::where('id', $barangPembelianData['id_barang'])->update(['harga_beli' => $barangPembelianData['harga']]);
    //         }

    //         if ($barangPembelianData['id_satuan'] == $satuanDasar) {
    //             StokBarang::create([
    //                 'id_barang' => $barangPembelianData['id_barang'],
    //                 'batch' => $barangPembelianData['batch'],
    //                 'exp_date' => $barangPembelianData['exp_date'],
    //                 'stok_gudang' => $barangPembelianData['jumlah'],
    //                 'stok_total' => $barangPembelianData['jumlah']
    //             ]);
    //         } else {
    //             $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
    //             $stok = $barangPembelianData['jumlah'] * $satuanBesar;
    //             StokBarang::create([
    //                 'id_barang' => $barangPembelianData['id_barang'],
    //                 'batch' => $barangPembelianData['batch'],
    //                 'exp_date' => $barangPembelianData['exp_date'],
    //                 'stok_gudang' => $stok,
    //                 'stok_total' => $stok
    //             ]);
    //         }
    //     }

    //     if ($validatedData['id_jenis'] == '2') {
    //         LaporanKeuanganKeluar::create([
    //             'id_pembelian' => $pembelian->id,
    //             'utang' => $validatedData['total']
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $pembelian,
    //         'message' => 'Pembelian Berhasil!',
    //     ], 200);
    // }

    /**
     * Display the specified resource.
     */
    public function show(Pembelian $pembelian)
    {

        $pembayaranPembelian = PembayaranPembelian::where('id_pembelian', $pembelian->id)->sum('total_dibayar');

        $data = [
            'id' => $pembelian->id,
            'status' => $pembelian->status,
            'id_vendor' => $pembelian->id_vendor,
            'nama_perusahaan' => $pembelian->vendor->nama_perusahaan,
            'id_sales' => $pembelian->id_sales,
            'nama_sales' => $pembelian->sales->nama_sales,
            'tanggal' => $pembelian->tanggal,
            'tanggal_jatuh_tempo' => $pembelian->tanggal_jatuh_tempo,
            'jenis' => $pembelian->jenis->nama_jenis,
            'catatan' => $pembelian->catatan,
            'sub_total' => $pembelian->sub_total,
            'diskon' => $pembelian->diskon,
            'total' => $pembelian->total,
            'referensi' => $pembelian->referensi,
            'sisa_tagihan' => $pembelian->total - $pembayaranPembelian,
            'barangPembelian' => $pembelian->barangPembelian->map(function ($barangPembelian) {
                return [
                    'id' => $barangPembelian->id,
                    'id_barang' => $barangPembelian->id_barang,
                    'nama_barang' => $barangPembelian->barang->nama_barang,
                    'batch' => $barangPembelian->batch,
                    'exp_date' => $barangPembelian->exp_date,
                    'jumlah' => $barangPembelian->jumlah,
                    'id_satuan' => $barangPembelian->id_satuan,
                    'nama_satuan' => $barangPembelian->satuan->nama_satuan,
                    'jenis_diskon' => $barangPembelian->jenis_diskon,
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
            'id_vendor' => 'sometimes',
            'id_sales' => 'sometimes',
            'id_jenis' => 'sometimes',
            'tanggal' => 'sometimes',
            'status' => 'sometimes',
            'tanggal_jatuh_tempo' => 'sometimes',
            'referensi' => 'sometimes',
            'sub_total' => 'sometimes',
            'diskon' => 'sometimes',
            'total_diskon_satuan' => 'sometimes',
            'total' => 'sometimes',
            'catatan' => 'sometimes',
            'barang_pembelians' => 'sometimes|array',
            'barang_pembelians.*.id_barang' => 'sometimes',
            'barang_pembelians.*.batch' => 'sometimes',
            'barang_pembelians.*.exp_date' => 'sometimes',
            'barang_pembelians.*.jumlah' => 'sometimes',
            'barang_pembelians.*.id_satuan' => 'sometimes',
            'barang_pembelians.*.jenis_diskon' => 'sometimes',
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
                $barangPembelian = $pembelian->barangPembelian()->create($barangPembelianData);
            }

            $satuanDasar = Barang::where($barangPembelianData['id_barang'])->value('id_satuan');

            if ($barangPembelianData['id_satuan'] == $satuanDasar) {
                $stokBarang = StokBarang::where('id_barang', $barangPembelianData['id_barang'])
                    ->where('batch', $barangPembelianData['batch'])
                    ->first();

                if ($stokBarang) {
                    $stokBarang->update([
                        'exp_date' => $barangPembelianData['exp_date'],
                        'stok_gudang' => $barangPembelianData['jumlah'],
                        'stok_total' => $barangPembelianData['jumlah']
                    ]);
                } else {
                    StokBarang::create([
                        'id_barang' => $barangPembelianData['id_barang'],
                        'batch' => $barangPembelianData['batch'],
                        'exp_date' => $barangPembelianData['exp_date'],
                        'stok_gudang' => $barangPembelianData['jumlah'],
                        'stok_total' => $barangPembelianData['jumlah']
                    ]);
                }
            } else {
                $satuanBesar = SatuanBarang::where('id_barang', $barangPembelianData['id_barang'])->value('jumlah');
                $stok = $barangPembelianData['jumlah'] * $satuanBesar;

                $stokBarang = StokBarang::where('id_barang', $barangPembelianData['id_barang'])
                    ->where('batch', $barangPembelianData['batch'])
                    ->first();

                if ($stokBarang) {
                    $stokBarang->update([
                        'exp_date' => $barangPembelianData['exp_date'],
                        'stok_gudang' => $stok,
                        'stok_total' => $stok
                    ]);
                } else {
                    StokBarang::create([
                        'id_barang' => $barangPembelianData['id_barang'],
                        'batch' => $barangPembelianData['batch'],
                        'exp_date' => $barangPembelianData['exp_date'],
                        'stok_gudang' => $stok,
                        'stok_total' => $stok
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $pembelian->load(['barangPembelian']),
            'message' => 'Pembelian Berhasil!',
        ], 200);
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

    public function setPembelian(Pembelian $pembelian)
    {
        $pembelian->update([
            'id_jenis' => '2'
        ]);
        LaporanKeuanganKeluar::create([
            'id_pembelian' => $pembelian->id,
            'utang' => $pembelian->total
        ]);
        return response()->json([
            'success' => true,
            'data' => $pembelian,
            'message' => 'Pembelian Berhasil!',
        ], 200);
    }
}
