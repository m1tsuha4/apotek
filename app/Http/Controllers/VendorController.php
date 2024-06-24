<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendor = Vendor::all();
        return response()->json([
            'success' => true,
            'data' => $vendor,
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
        $request->validate([
            'nama_vendor' => ['required', 'string', 'max:255', 'unique:' . Vendor::class],
            'perusahaan' => ['required', 'string', 'max:255'],
            'no_telepon' => ['required', 'numeric', 'digits_between:10,13', 'unique:' . Vendor::class],
            'alamat' => ['required', 'string', 'max:255'],
        ]);

        $vendor = Vendor::create([
            'nama_vendor' => $request->nama_vendor,
            'perusahaan' => $request->perusahaan,
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat
        ]);

        if($vendor) {
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
        //
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
            'nama_vendor' => ['sometimes', 'string', 'max:255'],
            'perusahaan' => ['sometimes', 'string', 'max:255'],
            'no_telepon' => ['sometimes', 'numeric', 'digits_between:10,13'],
            'alamat' => ['sometimes', 'string', 'max:255'],
        ]);

        $vendor = Vendor::where('id', $vendor->id)->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $vendor,
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
