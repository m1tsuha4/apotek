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
    public function index()
    {
        $vendor = Vendor::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $vendor->load(['sales']),
            'total' => $vendor->total(),
            'message' => 'Data Berhasil ditemukan!',
        ]);
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
            'nama_perusahaan' => ['required', 'string', 'max:255', 'unique:' . Vendor::class],
            'no_telepon' => ['required', 'unique:' . Vendor::class],
            'alamat' => ['required', 'string', 'max:255'],
            'sales' => 'required|array',
            'sales.*.nama_sales' => ['required', 'string', 'max:255'],
            'sales.*.no_telepon' => ['required'],
        ]);

        $vendor = Vendor::create($validatedData);

        foreach ($validatedData['sales'] as $salesData) {
            $vendor->sales()->create([
                'nama_sales' => $salesData['nama_sales'],
                'no_telepon' => $salesData['no_telepon'],
            ]);
        }

        if($vendor) {
            return response()->json([
                'success' => true,
                'data' => $vendor->load(['sales']),
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
        $vendor = Vendor::findOrFail($vendor->id);
        
        return response()->json([
            'success' => true,
            'data' => $vendor->load(['sales']),
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
            'sales' => 'required|array',
            'sales.*.nama_sales' => ['required', 'string', 'max:255'],
            'sales.*.no_telepon' => ['required'],
        ]);
    
        $vendor->update($validatedData);
    
        foreach ($validatedData['sales'] as $index => $salesData) {
            $sales = $vendor->sales()->get()[$index] ?? null;
            if ($sales) {
                $sales->update($salesData);
            } else {
                $vendor->sales()->create($salesData);
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
