<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vendor = Vendor::select('id', 'nama_perusahaan', 'no_telepon', 'alamat')
            ->with(['sales:id,id_vendor,nama_sales,no_telepon'])
            ->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $vendor->items(),
            'last_page' => $vendor->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $vendor = Vendor::select('id', 'nama_perusahaan', 'no_telepon', 'alamat')
            ->where('nama_perusahaan', 'like', '%' . $search . '%')
            ->with(['sales:id,id_vendor,nama_sales,no_telepon'])
            ->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $vendor->items(),
            'last_page' => $vendor->lastPage(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getVendor()
    {
        $vendor = Vendor::select('id', 'nama_perusahaan')->with('sales:id,id_vendor,nama_sales')->get();
        return response()->json([
            'success' => true,
            'data' => $vendor,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_perusahaan' => ['required', 'string', 'max:255', 'unique:' . Vendor::class],
            'no_telepon' => ['required', 'unique:' . Vendor::class],
            'alamat' => ['required', 'string', 'max:255'],
            'sales' => 'sometimes|array',
            'sales.*.nama_sales' => ['sometimes', 'string', 'max:255'],
            'sales.*.no_telepon' => ['sometimes'],
        ]);

        $vendor = Vendor::create($validatedData);

        if (isset($validatedData['sales'])) {
            foreach ($validatedData['sales'] as $salesData) {
                $vendor->sales()->create([
                    'nama_sales' => $salesData['nama_sales'],
                    'no_telepon' => $salesData['no_telepon'],
                ]);
            }
        }


        if ($vendor) {
            return response()->json([
                'success' => true,
                'data' => $vendor,
                'message' => 'Data vendor ditambahkan!',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data vendor gagal ditambahkan!',
        ], 409);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        $vendor = Vendor::select('id', 'nama_perusahaan', 'no_telepon', 'alamat')
            ->with(['sales:id,id_vendor,nama_sales,no_telepon'])
            ->findOrFail($vendor->id);

        return response()->json([
            'success' => true,
            'data' => $vendor,
            'message' => 'Data Berhasil ditemukan!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validatedData = $request->validate([
            'nama_perusahaan' => ['sometimes', 'string', 'max:255'],
            'no_telepon' => ['sometimes'],
            'alamat' => ['sometimes', 'string', 'max:255'],
            'sales' => 'sometimes|array',
            'sales.*.nama_sales' => ['sometimes', 'string', 'max:255'],
            'sales.*.no_telepon' => ['sometimes'],
        ]);

        $vendor->update($validatedData);

        if (isset($validatedData['sales'])) {
            foreach ($validatedData['sales'] as $index => $salesData) {
                $sales = $vendor->sales()->get()[$index] ?? null;
                if ($sales) {
                    $sales->update($salesData);
                } else {
                    $vendor->sales()->create($salesData);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $vendor->load(['sales']),
            'message' => 'Data vendor diupdate!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!',
        ]);
    }
}
