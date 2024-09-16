<?php

namespace App\Http\Controllers;

use App\Exports\TemplateKaryawanExport;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Imports\KaryawanImport;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $karyawan = Karyawan::select('id', 'nama_karyawan', 'jenis_kelamin', 'posisi', 'tanggal_bergabung', 'jumlah_gaji')->paginate($request->num);
        return response()->json([
            'success' => true,
            'data' => $karyawan->items(),
            'last_page' => $karyawan->lastPage(),
            'message' => 'Data karyawan berhasil ditemukan!'
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search'); 
        $karyawan = Karyawan::select('id', 'nama_karyawan', 'jenis_kelamin', 'posisi', 'tanggal_bergabung', 'jumlah_gaji')
            ->where('nama_karyawan', 'like', '%' . $search . '%')
            ->paginate($request->num); 

        return response()->json([
            'success' => true,
            'data' => $karyawan->items(),
            'last_page' => $karyawan->lastPage(),
            'message' => 'Data karyawan berhasil ditemukan!'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_karyawan' => 'required',
            'jenis_kelamin' => 'required',
            'posisi' => 'required',
            'tanggal_bergabung' => 'required',
            'jumlah_gaji' => 'sometimes',
        ]);

        $karyawan = Karyawan::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $karyawan,
            'message' => 'Data Karyawan ditambahkan!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Karyawan $karyawan)
    {
        $karyawan = $karyawan->only(['id', 'nama_karyawan', 'jenis_kelamin', 'posisi', 'tanggal_bergabung', 'jumlah_gaji']);

        return response()->json([
            'success' => true,
            'data' => $karyawan,
            'message' => 'Data karyawan berhasil ditemukan'
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        $validatedData = $request->validate([
            'nama_karyawan' => 'sometimes',
            'jenis_kelamin' => 'sometimes',
            'posisi' => 'sometimes',
            'tanggal_bergabung' => 'sometimes',
            'jumlah_gaji' => 'sometimes',
        ]);

        $karyawan->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => $karyawan,
            'message' => 'Data Karyawan diupdate!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan dihapus!'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        Excel::import(new KaryawanImport, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'Data Karyawan imported successfully!',
        ]);
    }

    public function downloadTemplateKaryawan()
    {
        return Excel::download(new TemplateKaryawanExport, 'TemplateKaryawan.xlsx');
    }
}
