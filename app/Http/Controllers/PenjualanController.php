<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Exports\InvoiceExport;
use App\Models\ReturPenjualan;
use App\Exports\PenjualanExport;
use Illuminate\Support\Facades\DB;
use App\Models\PembayaranPenjualan;
use App\Models\LaporanKeuanganMasuk;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PergerakanStokPenjualan;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $penjualan = Penjualan::select('id', 'id_jenis', 'id_pelanggan', 'tanggal', 'status', 'tanggal_jatuh_tempo', 'total')
            ->with('pelanggan:id,nama_pelanggan,no_telepon', 'jenis:id,nama_jenis')
            ->orderBy('created_at', 'desc')
            ->paginate($request->num);

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

    public function getStockDetails(Request $request)
    {
        $validatedData = $request->validate([
            'id_barang' => 'required',
            'jumlah' => 'required|integer',
            'id_satuan' => 'required|integer',
        ]);

        $idBarang = $validatedData['id_barang'];
        $jumlah = $validatedData['jumlah'];
        $idSatuan = $validatedData['id_satuan'];

        $barang = Barang::find($idBarang);

        try {
            // Check if the unit is basic (e.g., pieces) or larger (e.g., box)
            $isBasicUnit = $idSatuan == $barang->id_satuan;
            $conversionRate = $isBasicUnit ? 1 : $barang->satuanBarang->jumlah; // Assume conversion_rate is the number of pieces per box

            // Convert the requested quantity to the basic unit if needed
            $requestedQuantityInBasicUnit = $jumlah * $conversionRate;
            // Get the stock items ordered by expiration date
            $stokBarangs = StokBarang::where('id_barang', $idBarang)
                ->where('stok_apotek', '>', 0)
                ->orderBy('exp_date', 'asc')
                ->get();

            $totalStockInBasicUnit = $stokBarangs->sum('stok_apotek');
            $totalStockInRequestedUnit = $isBasicUnit ? $totalStockInBasicUnit : $totalStockInBasicUnit / $conversionRate;


            $stockDetails = [];
            $remainingQuantity = $requestedQuantityInBasicUnit;

            foreach ($stokBarangs as $stokBarang) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $stokTersedia = $stokBarang->stok_apotek;
                $stokPengurangan = min($stokTersedia, $remainingQuantity);

                $stockDetails[] = [
                    'id_stok_barang' => $stokBarang->id,
                    'batch' => $stokBarang->batch,
                    'exp_date' => $stokBarang->exp_date,
                    'stok_apotek' => $stokBarang->stok_apotek,
                    'stok_diambil' => $stokPengurangan,
                ];

                $remainingQuantity -= $stokPengurangan;
            }

            if ($remainingQuantity > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk jumlah yang diminta.',
                    'required_quantity' => $jumlah,
                    'remaining_quantity' => $remainingQuantity / $conversionRate,
                    'stock_details' => $stockDetails,
                    'total_stok' => $totalStockInRequestedUnit,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail stok barang yang diambil berhasil diambil.',
                'data' => $stockDetails,
                'total_stok' => $totalStockInRequestedUnit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTotalStok(Request $request)
    {
        $validatedData = $request->validate([
            'id_barang' => 'required',
            'id_satuan' => 'required',
        ]);

        $idBarang = $validatedData['id_barang'];
        $idSatuan = $validatedData['id_satuan'];

        $barang = Barang::find($idBarang);
        $isBasicUnit = $idSatuan == $barang->id_satuan;
        $conversionRate = $isBasicUnit ? 1 : $barang->satuanBarang->jumlah; // Assume conversion_rate is the number of pieces per box

        $stokBarangs = StokBarang::where('id_barang', $idBarang)
            ->where('stok_apotek', '>', 0)
            ->orderBy('exp_date', 'asc')
            ->get();

        $totalStockInBasicUnit = $stokBarangs->sum('stok_apotek');
        $totalStockInRequestedUnit = $isBasicUnit ? $totalStockInBasicUnit : $totalStockInBasicUnit / $conversionRate;

        return response()->json([
            'success' => true,
            'message' => 'Total stok barang',
            'total_stok' => $totalStockInRequestedUnit,
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
            'net_termin' => 'required',
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

        // Start transaction
        DB::beginTransaction();

        try {
            // Buat penjualan baru
            $penjualan = Penjualan::create($validatedData);

            if ($validatedData['id_jenis'] == 3) {
                // Proses setiap barang dalam penjualan
                foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
                    $jumlah = $barangPenjualanData['jumlah'];
                    $idBarang = $barangPenjualanData['id_barang'];

                    // Ambil satuan dasar barang
                    $satuanDasar = Barang::where('id', $idBarang)->value('id_satuan');
                    $hargaAsli = Barang::where('id', $idBarang)->value('harga_jual');
                    $totalStok = StokBarang::where('id_barang', $idBarang)->sum('stok_total');

                    if ($barangPenjualanData['harga'] != $hargaAsli) {
                        Barang::where('id', $idBarang)->update([
                            'harga_jual' => $barangPenjualanData['harga']
                        ]);
                    }

                    // Dapatkan stok barang yang tersedia berdasarkan barang dan satuan
                    $stokBarangs = StokBarang::where('id_barang', $idBarang)
                        ->where('stok_apotek', '>', 0)
                        ->orderBy('exp_date', 'asc')
                        ->get();

                    // Initialize $stokPengurangan
                    $stokPengurangan = 0;

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

                        $total = $barangPenjualanData['harga'] * $stokPengurangan;

                        // Buat entri barang penjualan
                        $penjualan->barangPenjualan()->create([
                            'id_barang' => $idBarang,
                            'jumlah' => $stokPengurangan,
                            'id_satuan' => $barangPenjualanData['id_satuan'],
                            'id_stok_barang' => $stokBarang->id,
                            'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                            'diskon' => $barangPenjualanData['diskon'] ?? 0,
                            'harga' => $barangPenjualanData['harga'],
                            'total' => $total,
                        ]);

                        // Kurangi stok barang
                        $stokBarang->stok_apotek -= $stokPengurangan;
                        $stokBarang->stok_total -= $stokPengurangan;
                        $stokBarang->save();
                    }

                    PergerakanStokPenjualan::create([
                        'id_penjualan' => $penjualan->id,
                        'id_barang' => $idBarang,
                        'id_stok_barang' => $stokBarang->id,
                        'harga' => $barangPenjualanData['harga'],
                        'pergerakan_stok' => $stokPengurangan,
                        'stok_keseluruhan' => $totalStok - $stokPengurangan
                    ]);

                    if ($jumlah > 0) {
                        // Rollback transaction if stock is insufficient
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Stok tidak mencukupi untuk jumlah yang diminta untuk barang ID: ' . $idBarang,
                        ], 400);
                    }
                }

                LaporanKeuanganMasuk::create([
                    'id_penjualan' => $penjualan->id,
                    'piutang' => $penjualan->total,
                ]);
            } else if ($validatedData['id_jenis'] == 1) {
                // Hanya buat entri barang penjualan tanpa pengurangan stok
                foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
                    $penjualan->barangPenjualan()->create([
                        'id_barang' => $barangPenjualanData['id_barang'],
                        'jumlah' => $barangPenjualanData['jumlah'],
                        'id_satuan' => $barangPenjualanData['id_satuan'],
                        'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                        'diskon' => $barangPenjualanData['diskon'] ?? 0,
                        'harga' => $barangPenjualanData['harga'],
                        'total' => $barangPenjualanData['total'],
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil ditambahkan' . ($validatedData['id_jenis'] == 3 ? ' dan stok diperbarui' : ''),
                'data' => $penjualan
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan data tidak lengkap!',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan)
    {
        $pembayaranPenjualan = PembayaranPenjualan::where('id_penjualan', $penjualan->id)->sum('total_dibayar');
        $sisa_tagihan = $penjualan->total - $pembayaranPenjualan;
        if ($sisa_tagihan < 0) {
            $sisa_tagihan = 0;
        }
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
            'net_termin' => $penjualan->net_termin,
            'referensi' => $penjualan->referensi,
            'sub_total' => $penjualan->sub_total,
            'total_diskon_satuan' => $penjualan->total_diskon_satuan,
            'diskon' => $penjualan->diskon,
            'total' => $penjualan->total,
            'catatan' => $penjualan->catatan,
            'sisa_tagihan' => $sisa_tagihan,
            'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'id_stok_barang' => $barangPenjualan->id_stok_barang ?? null,
                    'batch' => $barangPenjualan->stokBarang->batch ?? null,
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
            }),
            'returPenjualan' => $penjualan->returPenjualan->map(function ($returPenjualan) {
                return [
                    'id' => $returPenjualan->id,
                    'total_retur' => $returPenjualan->total_retur
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

        DB::beginTransaction();

        try {
            // Update penjualan data
            $penjualan->update($validatedData);

            if ($validatedData['id_jenis'] == 3) {
                // Process each barang in penjualan when id_jenis is 3
                foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
                    $idBarang = $barangPenjualanData['id_barang'];
                    $jumlahBaru = $barangPenjualanData['jumlah'];

                    // Find existing barang penjualan
                    $existingBarangPenjualan = $penjualan->barangPenjualan()->where('id_barang', $idBarang)->first();
                    $jumlahDifference = $existingBarangPenjualan ? $jumlahBaru - $existingBarangPenjualan->jumlah : $jumlahBaru;

                    // Pengembalian stok jika jumlah barang berkurang
                    if ($jumlahDifference < 0) {
                        $jumlahDifference = abs($jumlahDifference);

                        // Ambil kembali stok dari yang sudah dijual
                        $stokBarangs = StokBarang::where('id_barang', $idBarang)
                            ->orderBy('exp_date', 'asc')
                            ->get();

                        foreach ($stokBarangs as $stokBarang) {
                            if ($jumlahDifference <= 0) break;

                            $stokKembalian = min($stokBarang->stok_apotek, $jumlahDifference);
                            $stokBarang->stok_apotek += $stokKembalian;
                            $stokBarang->stok_total += $stokKembalian;
                            $stokBarang->save();

                            $jumlahDifference -= $stokKembalian;
                        }
                    } else {
                        // Logika pengurangan stok
                        $stokBarangs = StokBarang::where('id_barang', $idBarang)
                            ->where('stok_apotek', '>', 0)
                            ->orderBy('exp_date', 'asc')
                            ->get();

                        foreach ($stokBarangs as $stokBarang) {
                            if ($jumlahDifference <= 0) break;

                            $stokTersedia = $stokBarang->stok_apotek;

                            // Get satuan dasar
                            $satuanDasar = Barang::where('id', $idBarang)->value('id_satuan');
                            $hargaAsli = Barang::where('id', $idBarang)->value('harga_jual');
                            $totalStok = StokBarang::where('id_barang', $idBarang)->sum('stok_total');

                            if ($barangPenjualanData['harga'] != $hargaAsli) {
                                Barang::where('id', $idBarang)->update([
                                    'harga_jual' => $barangPenjualanData['harga']
                                ]);
                            }

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

                            // Update stock
                            $stokBarang->stok_apotek -= $stokPengurangan;
                            $stokBarang->stok_total -= $stokPengurangan;
                            $stokBarang->save();

                            PergerakanStokPenjualan::updateOrCreate(
                                ['id_penjualan' => $penjualan->id, 'id_barang' => $idBarang, 'id_stok_barang' => $stokBarang->id,],
                                ['harga' => $barangPenjualanData['harga'], 'pergerakan_stok' => $barangPenjualanData['jumlah'], 'stok_keseluruhan' => $totalStok - $barangPenjualanData['jumlah']]
                            );
                        }

                        if ($jumlahDifference > 0) {
                            // Rollback transaction if stock is insufficient
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Stok tidak mencukupi untuk jumlah yang diminta untuk barang ID: ' . $idBarang,
                            ], 400);
                        }
                    }

                    $total = $barangPenjualanData['harga'] * $stokPengurangan;

                    // Update or create barang penjualan
                    if ($existingBarangPenjualan) {
                        $existingBarangPenjualan->update([
                            'jumlah' => $barangPenjualanData['jumlah'],
                            'id_satuan' => $barangPenjualanData['id_satuan'],
                            'id_stok_barang' => $stokBarang->id ?? null,
                            'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                            'diskon' => $barangPenjualanData['diskon'] ?? 0,
                            'harga' => $barangPenjualanData['harga'],
                            'total' => $total,
                        ]);
                    } else {
                        $penjualan->barangPenjualan()->create([
                            'id_barang' => $idBarang,
                            'jumlah' => $barangPenjualanData['jumlah'],
                            'id_satuan' => $barangPenjualanData['id_satuan'],
                            'id_stok_barang' => $stokBarang->id ?? null,
                            'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                            'diskon' => $barangPenjualanData['diskon'] ?? 0,
                            'harga' => $barangPenjualanData['harga'],
                            'total' => $total,
                        ]);
                    }
                }

                LaporanKeuanganMasuk::updateOrCreate(
                    ['id_penjualan' => $penjualan->id],
                    ['piutang' => $penjualan->total]
                );
            } else if ($validatedData['id_jenis'] == 1) {
                // Update only penjualan and barang_penjualan without affecting stock
                foreach ($validatedData['barang_penjualans'] as $barangPenjualanData) {
                    $existingBarangPenjualan = $penjualan->barangPenjualan()->where('id_barang', $barangPenjualanData['id_barang'])->first();

                    if ($existingBarangPenjualan) {
                        // Update existing barang penjualan
                        $existingBarangPenjualan->update([
                            'jumlah' => $barangPenjualanData['jumlah'],
                            'id_satuan' => $barangPenjualanData['id_satuan'],
                            'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                            'diskon' => $barangPenjualanData['diskon'] ?? 0,
                            'harga' => $barangPenjualanData['harga'],
                            'total' => $barangPenjualanData['total'],
                        ]);
                    } else {
                        // Create new barang penjualan
                        $penjualan->barangPenjualan()->create([
                            'id_barang' => $barangPenjualanData['id_barang'],
                            'jumlah' => $barangPenjualanData['jumlah'],
                            'id_satuan' => $barangPenjualanData['id_satuan'],
                            'jenis_diskon' => $barangPenjualanData['jenis_diskon'] ?? null,
                            'diskon' => $barangPenjualanData['diskon'] ?? 0,
                            'harga' => $barangPenjualanData['harga'],
                            'total' => $barangPenjualanData['total'],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil diperbarui' . ($validatedData['id_jenis'] == 3 ? ' dan stok diperbarui' : ''),
                'data' => $penjualan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan data tidak lengkap',
            ], 500);
        }
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
                $jumlah_retur = ReturPenjualan::where('id_penjualan', $barangPenjualan->id_penjualan)
                    ->join('barang_retur_penjualans', 'retur_penjualans.id', '=', 'barang_retur_penjualans.id_retur_penjualan')
                    ->where('barang_retur_penjualans.id_barang_penjualan', $barangPenjualan->id)
                    ->sum('barang_retur_penjualans.jumlah_retur');
                $jumlah_bisa_retur = $barangPenjualan->jumlah - $jumlah_retur;
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'id_stok_barang' => $barangPenjualan->id_stok_barang,
                    'batch' => $barangPenjualan->StokBarang->batch,
                    'jumlah' => $barangPenjualan->jumlah,
                    'jumlah_bisa_retur' => $jumlah_bisa_retur,
                    'id_satuan' => $barangPenjualan->id_satuan,
                    'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
                    'jenis_diskon' => $barangPenjualan->jenis_diskon,
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
        DB::beginTransaction();

        try {
            // Update penjualan to set id_jenis to 3
            $penjualan->update([
                'id_jenis' => '3',
            ]);

            // Process each barang in penjualan
            foreach ($penjualan->barangPenjualan as $barangPenjualan) {
                $idBarang = $barangPenjualan->id_barang;
                $jumlahPenjualan = $barangPenjualan->jumlah;

                // Get satuan dasar and original stock values
                $satuanDasar = Barang::where('id', $idBarang)->value('id_satuan');
                $totalStok = StokBarang::where('id_barang', $idBarang)->sum('stok_total');

                // Get available stock
                $stokBarangs = StokBarang::where('id_barang', $idBarang)
                    ->where('stok_apotek', '>', 0)
                    ->orderBy('exp_date', 'asc')
                    ->get();

                $jumlahDifference = $jumlahPenjualan;

                foreach ($stokBarangs as $stokBarang) {
                    if ($jumlahDifference <= 0) {
                        break;
                    }

                    $stokTersedia = $stokBarang->stok_apotek;

                    if ($barangPenjualan->id_satuan == $satuanDasar) {
                        // If using base unit
                        $stokPengurangan = min($stokTersedia, $jumlahDifference);
                        $jumlahDifference -= $stokPengurangan;
                    } else {
                        // If using larger unit
                        $satuanBesarJumlah = SatuanBarang::where('id_barang', $idBarang)
                            ->where('id_satuan', $barangPenjualan->id_satuan)
                            ->value('jumlah');

                        $stokPengurangan = min($stokTersedia, $jumlahDifference * $satuanBesarJumlah);
                        $jumlahDifference -= intval(ceil($stokPengurangan / $satuanBesarJumlah));
                    }

                    $penjualan->barangPenjualan()->update([
                        'id_stok_barang' => $stokBarang->id
                    ]);

                    // Update stock
                    $stokBarang->stok_apotek -= $stokPengurangan;
                    $stokBarang->stok_total -= $stokPengurangan;
                    $stokBarang->save();

                    // Update pergerakan stok
                    PergerakanStokPenjualan::updateOrCreate(
                        ['id_penjualan' => $penjualan->id, 'id_barang' => $idBarang],
                        ['harga' => $barangPenjualan->harga, 'pergerakan_stok' => $barangPenjualan->jumlah, 'stok_keseluruhan' => $totalStok - $barangPenjualan->jumlah]
                    );
                }

                if ($jumlahDifference > 0) {
                    // Rollback transaction if stock is insufficient
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi untuk jumlah yang diminta untuk barang ID: ' . $idBarang,
                    ], 400);
                }
            }

            // Create or update financial report
            LaporanKeuanganMasuk::updateOrCreate(
                ['id_penjualan' => $penjualan->id],
                ['piutang' => $penjualan->total]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil diperbarui dan stok diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function export()
    {
        return Excel::download(new PenjualanExport, 'penjualan.xlsx');
    }

    public function invoice(Penjualan $penjualan)
    {
        $pembayaranPenjualan = PembayaranPenjualan::where('id_penjualan', $penjualan->id)->sum('total_dibayar');
        $total_diskon_satuan = $penjualan->barangPenjualan()->sum('diskon');

        $total_retur = ReturPenjualan::where('id_penjualan', $penjualan->id)->sum('total_retur');
        // dd($penjualan->returPenjualan->map(function ($returPenjualan) {
        //     return $returPenjualan->barangReturPenjualan;
        // }));
        $data = [
            'id_penjualan' => $penjualan->id,
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
            'diskon_keseluruhan' => $penjualan->diskon + $total_diskon_satuan,
            'total' => $penjualan->total,
            'catatan' => $penjualan->catatan,
            'sisa_tagihan' => $penjualan->total - $pembayaranPenjualan,
            'total_retur' => $total_retur,
            'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
                return [
                    'id' => $barangPenjualan->id,
                    'id_barang' => $barangPenjualan->id_barang,
                    'nama_barang' => $barangPenjualan->barang->nama_barang,
                    'id_stok_barang' => $barangPenjualan->id_stok_barang,
                    'batch' => $barangPenjualan->stokBarang->batch ?? null,
                    'exp_date' => $barangPenjualan->stokBarang->exp_date ?? null,
                    'jumlah' => $barangPenjualan->jumlah,
                    'id_satuan' => $barangPenjualan->id_satuan,
                    'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
                    'jenis_diskon' => $barangPenjualan->jenis_diskon,
                    'diskon' => $barangPenjualan->diskon,
                    'harga' => $barangPenjualan->harga,
                    'total_barang' => $barangPenjualan->total
                ];
            }),
            'barangReturPenjualan' => $penjualan->returPenjualan->flatMap(function ($returPenjualan) {
                return $returPenjualan->barangReturPenjualan->map(function ($barangReturPenjualan) {
                    return [
                        'id' => $barangReturPenjualan->id,
                        'nama_barang' => $barangReturPenjualan->barangPenjualan->barang->nama_barang,
                        'batch' => $barangReturPenjualan->barangPenjualan->stokBarang->batch ?? null,
                        'exp_date' => $barangReturPenjualan->barangPenjualan->stokBarang->exp_date ?? null,
                        'jumlah_retur' => $barangReturPenjualan->jumlah_retur,
                        'nama_satuan' => $barangReturPenjualan->barangPenjualan->satuan->nama_satuan,
                        'harga' => $barangReturPenjualan->barangPenjualan->harga,
                        'total_retur' => $barangReturPenjualan->total
                    ];
                });
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
        // return view('exports.invoice', compact('data'));
        $pdf = PDF::loadView('exports.invoice', compact('data'))
            ->setPaper([0, 0, 612, 396])
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0);
        return $pdf->download('invoice.pdf');
        // return pdf()
        //     ->view('exports.invoice', ['data' => $data])
        //     ->download(downloadName: 'Invoice_'.$data['id_penjualan'].'pdf');

        // $pdf = PDF::loadView('exports.invoice', ['data' => $data])
        //     ->setPaper('a4', 'landscape')
        //     ->setOption('enable-local-file-access', true)
        //     ->setOption('zoom', '1.3') // Meningkatkan ukuran tampilan
        //     ->setOption('no-stop-slow-scripts', true); // Hindari kesalahan pada script yang lambat

        // return $pdf->stream('Invoice_' . $data['id_penjualan'] . 'pdf');
    }

    // public function invoice(Penjualan $penjualan)
    // {
    //     // return view('exports.invoice', compact('data'));
    //     return Excel::download(new InvoiceExport($penjualan), 'invoice.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    // }

    //   public function invoice(Penjualan $penjualan)
    //   {
    //       $data = (new InvoiceExport($penjualan))->view()->getData();
    //       $pdf = new PDF($data,'exports.invoice','invoice.pdf','A4'); // Create an instance of the PDF class
    //       $pdf = $pdf->loadView('exports.invoice', $data); // Call the loadView method on the instance
    //       return $pdf->download('invoice.pdf');
    //   }
    // public function invoice(Penjualan $penjualan)
    // {
    //     // Fetch the necessary data for the invoice
    //     $pembayaranPenjualan = PembayaranPenjualan::where('id_penjualan', $penjualan->id)->sum('total_dibayar');

    //     $data = [
    //         'id_penjualan' => $penjualan->id,
    //         'status' => $penjualan->status,
    //         'id_pelanggan' => $penjualan->id_pelanggan,
    //         'nama_pelanggan' => $penjualan->pelanggan->nama_pelanggan,
    //         'no_telepon' => $penjualan->pelanggan->no_telepon,
    //         'id_jenis' => $penjualan->id_jenis,
    //         'nama_jenis' => $penjualan->jenis->nama_jenis,
    //         'tanggal' => $penjualan->tanggal,
    //         'tanggal_jatuh_tempo' => $penjualan->tanggal_jatuh_tempo,
    //         'referensi' => $penjualan->referensi,
    //         'sub_total' => $penjualan->sub_total,
    //         'total_diskon_satuan' => $penjualan->total_diskon_satuan,
    //         'diskon' => $penjualan->diskon,
    //         'total' => $penjualan->total,
    //         'catatan' => $penjualan->catatan,
    //         'sisa_tagihan' => $penjualan->total - $pembayaranPenjualan,
    //         'barangPenjualan' => $penjualan->barangPenjualan->map(function ($barangPenjualan) {
    //             return [
    //                 'id' => $barangPenjualan->id,
    //                 'id_barang' => $barangPenjualan->id_barang,
    //                 'nama_barang' => $barangPenjualan->barang->nama_barang,
    //                 'id_stok_barang' => $barangPenjualan->id_stok_barang,
    //                 'batch' => $barangPenjualan->stokBarang->batch ?? null,
    //                 'exp_date' => $barangPenjualan->stokBarang->exp_date ?? null,
    //                 'jumlah' => $barangPenjualan->jumlah,
    //                 'id_satuan' => $barangPenjualan->id_satuan,
    //                 'nama_satuan' => $barangPenjualan->satuan->nama_satuan,
    //                 'jenis_diskon' => $barangPenjualan->jenis_diskon,
    //                 'diskon' => $barangPenjualan->diskon,
    //                 'harga' => $barangPenjualan->harga,
    //                 'total' => $barangPenjualan->total
    //             ];
    //         }),
    //         'pembayaranPenjualan' => $penjualan->pembayaranPenjualan->map(function ($pembayaranPenjualan) {
    //             return [
    //                 'id' => $pembayaranPenjualan->id,
    //                 'id_penjualan' => $pembayaranPenjualan->id_penjualan,
    //                 'tanggal_pembayaran' => $pembayaranPenjualan->tanggal_pembayaran,
    //                 'metode_pembayaran' => $pembayaranPenjualan->metodePembayaran->nama_metode,
    //                 'total_dibayar' => $pembayaranPenjualan->total_dibayar,
    //                 'referensi_pembayaran' => $pembayaranPenjualan->referensi_pembayaran
    //             ];
    //         })
    //     ];

    //     // Load the view and pass the data
    //     $html = view('exports.invoice', compact('data'))->render();

    //     // Generate the PDF with Snappy
    //     return SnappyPdf::loadHTML($html)
    //         ->download('invoice.pdf');
    // }
}
