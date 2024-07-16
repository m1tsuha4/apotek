<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokBarang;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use App\Models\ReturPembelian;

class ReturPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returPembelians = ReturPembelian::with(['pembelian.sales.vendor', 'barangReturPembelian', 'pembelian.barangPembelian'])->paginate($request->num);

        $data = collect($returPembelians->items())->map(function ($returPembelian) {
            $jumlah = $returPembelian->pembelian->barangPembelian->sum('jumlah');
            $jumlah_retur = $returPembelian->barangReturPembelian->sum('jumlah_retur');

            return [
                'id' => $returPembelian->id,
                'id_pembelian' => $returPembelian->id_pembelian,
                'tanggal' => $returPembelian->tanggal,
                'id_sales' => $returPembelian->pembelian->id_sales,
                'nama_sales' => $returPembelian->pembelian->sales->nama_sales,
                'vendor' => $returPembelian->pembelian->sales->vendor->nama_perusahaan,
                'referensi' => $returPembelian->referensi,
                'jumlah' => $jumlah,
                'jumlah_retur' => $jumlah_retur,
                'total' => $returPembelian->total_retur
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'last_page' => $returPembelians->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function generateId()
    {
        $newId = ReturPembelian::generateId();
        return response()->json([
            'success' => true,
            'data' => $newId,
            'message' => 'ID retur pembelian berhasil digenerate',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pembelian' => ['required'],
            'tanggal' => ['required'],
            'referensi' => ['sometimes'],
            'total_retur' => ['required'],
            'barang_retur_pembelians' => 'required|array',
            'barang_retur_pembelians.*.id_barang' => ['required'],
            'barang_retur_pembelians.*.batch' => ['required'],
            'barang_retur_pembelians.*.jumlah_retur' => ['required'],
            'barang_retur_pembelians.*.id_satuan' => ['required'],
            'barang_retur_pembelians.*.total' => ['required'],
        ]);

        $returPembelian = ReturPembelian::create($validatedData);

        foreach ($validatedData['barang_retur_pembelians'] as $barangReturPembelian) {
            $returPembelian->barangReturPembelian()->create($barangReturPembelian);

            $stokBarang = StokBarang::where('id_barang', $barangReturPembelian['id_barang'])
                ->where('batch', $barangReturPembelian['batch'])
                ->first();

            $satuanDasar = Barang::where('id', $barangReturPembelian['id_barang'])->value('id_satuan');

            if ($stokBarang) {
                if ($barangReturPembelian['id_satuan'] == $satuanDasar) {
                    // Jika satuan retur sama dengan satuan dasar, kurangi langsung dengan jumlah retur
                    $stokBarang->stok_gudang -= $barangReturPembelian['jumlah_retur'];
                } else {
                    // Jika satuan retur berbeda dengan satuan dasar, konversi jumlah retur
                    $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelian['id_barang'])
                        ->where('id_satuan', $barangReturPembelian['id_satuan'])
                        ->value('jumlah');
                    $stokBarang->stok_gudang -= $barangReturPembelian['jumlah_retur'] * $satuanBesar;
                }
                $stokBarang->save();
            } else {
                // Handle the case where the stock doesn't exist
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang tidak ditemukan',
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $returPembelian->load('barangReturPembelian'),
            'message' => 'Data retur pembelian berhasil ditambahkan',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(ReturPembelian $returPembelian)
    {
        $returPembelian->load([
            'pembelian',
            'barangReturPembelian',
            'pembelian.barangPembelian',
            'pembelian.barangPembelian.satuan',
            'pembelian.barangPembelian.barang',
        ]);

        // Hapus properti created_at dan updated_at dari model utama dan relasi
        $returPembelian->makeHidden(['created_at', 'updated_at']);
        $returPembelian->pembelian->makeHidden(['created_at', 'updated_at']);
        foreach ($returPembelian->barangReturPembelian as $barangRetur) {
            $barangRetur->makeHidden(['created_at', 'updated_at']);
        }
        foreach ($returPembelian->pembelian->barangPembelian as $barangPembelian) {
            $barangPembelian->makeHidden(['created_at', 'updated_at']);
            $barangPembelian->satuan->makeHidden(['created_at', 'updated_at']);
            $barangPembelian->barang->makeHidden(['created_at', 'updated_at']);
        }

        return response()->json([
            'success' => true,
            'data' => $returPembelian,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturPembelian $returPembelian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReturPembelian $returPembelian)
    {
        $validatedData = $request->validate([
            'id_pembelian' => ['sometimes'],
            'tanggal' => ['sometimes'],
            'referensi' => ['sometimes'],
            'total_retur' => ['sometimes'],
            'barang_retur_pembelians' => 'sometimes|array',
            'barang_retur_pembelians.*.id_barang' => ['sometimes'],
            'barang_retur_pembelians.*.batch' => ['sometimes'],
            'barang_retur_pembelians.*.jumlah_retur' => ['sometimes'],
            'barang_retur_pembelians.*.id_satuan' => ['sometimes'],
            'barang_retur_pembelians.*.total' => ['sometimes'],
        ]);

        $returPembelian->update($validatedData);

        foreach ($validatedData['barang_retur_pembelians'] as $index => $barangReturPembelianData) {
            $barangReturPembelian = $returPembelian->barangReturPembelian()->get()[$index] ?? null;

            if ($barangReturPembelian) {
                // Update the existing return item
                $stokBarang = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                    ->where('batch', $barangReturPembelianData['batch'])
                    ->first();

                if ($stokBarang) {
                    // Calculate the stock difference
                    $jumlahReturLama = $barangReturPembelian->jumlah_retur;
                    $jumlahReturBaru = $barangReturPembelianData['jumlah_retur'];

                    $satuanDasar = Barang::where('id', $barangReturPembelianData['id_barang'])->value('id_satuan');

                    if ($barangReturPembelianData['id_satuan'] == $satuanDasar) {
                        $stokBarang->stok_gudang += $jumlahReturLama;
                        $stokBarang->stok_gudang -= $jumlahReturBaru;
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                            ->where('id_satuan', $barangReturPembelianData['id_satuan'])
                            ->value('jumlah');
                        $stokBarang->stok_gudang += $jumlahReturLama * $satuanBesar;
                        $stokBarang->stok_gudang -= $jumlahReturBaru * $satuanBesar;
                    }

                    $stokBarang->save();
                }

                $barangReturPembelian->update($barangReturPembelianData);
            } else {
                // Create a new return item
                $barangReturPembelian = $returPembelian->barangReturPembelian()->create($barangReturPembelianData);

                $stokBarang = StokBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                    ->where('batch', $barangReturPembelianData['batch'])
                    ->first();

                $satuanDasar = Barang::where('id', $barangReturPembelianData['id_barang'])->value('id_satuan');

                if ($stokBarang) {
                    if ($barangReturPembelianData['id_satuan'] == $satuanDasar) {
                        $stokBarang->stok_gudang -= $barangReturPembelianData['jumlah_retur'];
                    } else {
                        $satuanBesar = SatuanBarang::where('id_barang', $barangReturPembelianData['id_barang'])
                            ->where('id_satuan', $barangReturPembelianData['id_satuan'])
                            ->value('jumlah');
                        $stokBarang->stok_gudang -= $barangReturPembelianData['jumlah_retur'] * $satuanBesar;
                    }
                    $stokBarang->save();
                } else {
                    // Handle the case where the stock doesn't exist
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok barang tidak ditemukan',
                    ], 400);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $returPembelian->load('barangReturPembelian'),
            'message' => 'Data retur pembelian berhasil diupdate',
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturPembelian $returPembelian)
    {
        $returPembelian->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
