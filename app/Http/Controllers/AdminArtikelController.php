<?php

namespace App\Http\Controllers;

use App\Models\ArtikelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ArtikelStatusUpdated;
use Illuminate\Support\Facades\Log;

class AdminArtikelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function verifikasiArtikel(Request $request, $id)
    {
        try {
            $request->validate([
                'verifikasi_admin' => 'required|in:disetujui,ditolak',
            ]);
    
            $artikel = ArtikelModel::findOrFail($id);
            $artikel->verifikasi_admin = $request->verifikasi_admin;
            $artikel->save();

            try {
                if ($artikel->user && $artikel->user->email) {
                    // Log::debug('Email akan dikirim ke: ' . $artikel->user->email);
                    Mail::to($artikel->user->email)->send(new ArtikelStatusUpdated($artikel));
                }
            } catch (\Exception $e) {
                Log::error('Email sending failed: ' . $e->getMessage());
            }
    
            return response()->json([
                'message' => 'Status artikel berhasil diperbarui',
                'artikel' => $artikel,
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan',
            ], 404);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
