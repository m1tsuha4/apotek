<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pelanggan = Pelanggan::paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $pelanggan->items(),
            'last_page' => $pelanggan->lastPage(),
            'message' => 'Data pelanggan berhasil ditemukan',
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $pelanggan = Pelanggan::select('id', 'nama_pelanggan', 'no_telepon', 'alamat')
            ->where('nama_pelanggan', 'like', '%' . $search . '%')
            ->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $pelanggan->items(),
            'last_page' => $pelanggan->lastPage(),
            'message' => 'Data pelanggan berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getPelanggan()
    {
        $pelanggan = Pelanggan::select('id', 'nama_pelanggan')->get();

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
            'message' => 'Data pelanggan berhasil ditemukan',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:255', 'unique:' . Pelanggan::class],
            'no_telepon' => ['required', 'unique:' . Pelanggan::class],
            'alamat' => ['required', 'string', 'max:255'],
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
        ]);

        if ($pelanggan) {
            return response()->json([
                'success' => true,
                'data' => $pelanggan,
                'message' => 'Data pelanggan ditambahkan!',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data pelanggan gagal ditambahkan!',
        ], 409);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pelanggan $pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($pelanggan->id);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
            'message' => 'Data pelanggan berhasil ditemukan',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pelanggan $pelanggan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validatedData = $request->validate([
            'nama_pelanggan' => ['sometimes', 'string', 'max:255'],
            'no_telepon' => ['sometimes'],
            'alamat' => ['sometimes', 'string', 'max:255'],
        ]);

        $pelanggan = Pelanggan::where('id', $pelanggan->id)->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $pelanggan,
            'message' => 'Data pelanggan diupdate!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan dihapus!',
        ]);
    }
}
