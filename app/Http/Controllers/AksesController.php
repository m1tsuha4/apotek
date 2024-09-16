<?php

namespace App\Http\Controllers;

use App\Models\Akses;
use App\Models\User;
use Illuminate\Http\Request;

class AksesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Akses::select('id', 'hak_akses')->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => 'Data Berhasil ditemukan!'
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $users = User::with('akses')
            ->where('username', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->paginate($request->num);
            
        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'access_count' => $user->akses->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
            'last_page' => $users->lastPage(),
            'message' => 'Data Berhasil ditemukan!'
        ]);
    }

    public function getUsers(Request $request)
    {
        $users = User::with('akses')->paginate($request->num);

        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'access_count' => $user->akses->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
            'last_page' => $users->lastPage(),
            'message' => 'Data Berhasil ditemukan!'
        ]);
    }

    public function getAksesUser(Request $request)
    {
        $users = User::with('akses')->where('id', $request->user_id)->get();

        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'access_count' => $user->akses->count(),
                'access_rights' => $user->akses->map(function ($akses) {
                    return [
                        'id' => $akses->id,
                        'hak_akses' => $akses->hak_akses,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => 'Data Berhasil ditemukan!'
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
            'user_id' => 'required|exists:users,id',
            'akses_id' => 'required|array',
            'akses_id.*' => 'exists:akses,id',
        ]);

        $user = User::find($request->user_id);
        $aksesIds = $request->akses_id;

        if ($user) {
            $user->akses()->attach($aksesIds);
            return response()->json([
                'success' => true,
                'message' => 'Penambahan hak akses berhasil ditambahkan!'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan!',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Akses $akses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Akses $akses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Akses $akses)
    {
        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'akses_id' => 'sometimes|array',
            'akses_id.*' => 'exists:akses,id',
        ]);

        $user = User::find($request->user_id);
        $aksesIds = $request->akses_id;

        if ($user) {
            $user->akses()->sync($aksesIds);
            return response()->json([
                'success' => true,
                'message' => 'Penambahan hak akses berhasil diupdate!'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil dihapus!'
        ]);
    }
}
