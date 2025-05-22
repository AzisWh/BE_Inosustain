<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function allUser()
    {
        $users = User::select('id', 'nama_depan', 'nama_belakang', 'email')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua user',
            'data' => $users
        ], 200);
    }

    public function userDetail($id)
    {
        $user = User::select('id', 'nama_depan', 'nama_belakang', 'email')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail user ditemukan',
            'data' => $user
        ], 200);
    }
}
