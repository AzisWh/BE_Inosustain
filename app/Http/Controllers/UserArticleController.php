<?php

namespace App\Http\Controllers;

use App\Mail\ArtikelMenungguVerifikasi;
use App\Models\ArtikelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['semuaArtikel', 'detailArtikel']]);
    }

    public function postArtikel(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
    
            $path = null;
    
            if ($request->has('image')) {
                if (filter_var($request->image, FILTER_VALIDATE_URL)) {
                    $path = $request->image;
                } elseif ($request->hasFile('image')) {
                    $originalName = $request->file('image')->getClientOriginalName();
                    $path = $request->file('image')->storeAs('ImageArtikel', $originalName, 'public');
                }
            }
    
            $user = Auth::user();
            $artikel = ArtikelModel::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'image' => $path,
                'verifikasi_admin' => 'menunggu',
            ]);

            $penulis = $user;
    
            Mail::to('aziswihasto@gmail.com')->send(new ArtikelMenungguVerifikasi($artikel, $penulis));
    
            return response()->json([
                'message' => 'Artikel berhasil ditambahkan',
                'artikel' => $artikel,
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function semuaArtikel()
    {
        try {
            $artikels = ArtikelModel::with('user:id,nama_depan,nama_belakang,email')->latest()->get();

            return response()->json([
                'message' => 'List artikel berhasil diambil',
                'artikels' => $artikels,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil list artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detailArtikel($id)
    {
        try {
            $artikel = ArtikelModel::with('user:id,nama_depan,nama_belakang,email')->findOrFail($id);

            return response()->json([
                'message' => 'Detail artikel berhasil diambil',
                'artikel' => $artikel,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil detail artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function artikelByUser()
    {
        try {
            $user = Auth::user();
            $artikels = ArtikelModel::where('user_id', $user->id)
            ->with('user:id,nama_depan,nama_belakang,email')
            ->latest()->get();

            return response()->json([
                'message' => 'List artikel berhasil diambil',
                'artikels' => $artikels,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil list artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
