<?php

namespace App\Http\Controllers;

use App\Models\ArtikelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ArtikelStatusUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function delArticle($id)
    {
        try {
            $artikel = ArtikelModel::findOrFail($id);
            $user = Auth::user();
            if (!$user || (int)$user->role_type !== 2) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }
            $artikel->delete();

            return response()->json([
                'message' => 'Artikel berhasil dihapus ',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function postArticle(Request $request)
    {
        try{
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'verifikasi_admin' => 'required|in:menunggu,disetujui,ditolak'
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
                'verifikasi_admin' => $request->verifikasi_admin,
            ]);

            return response()->json([
                'message' => 'Artikel berhasil ditambahkan',
                'artikel' => $artikel,
            ], 201);
        }catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan blog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function artikelEdit(Request $request, $id)
    {
        try {
            $artikel = ArtikelModel::findOrFail($id);
            Log::info('Isi artikel:', $artikel->toArray());
            // return response()->json($artikel);

            $user = Auth::user();
            if (!$user || (int)$user->role_type !== 2) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }

            $input = $request->all();
            Log::info('Raw input dari request:', $input);
            
            $validator =Validator::make($request->all(), [
                'title' => 'sometimes|nullable|string|max:255',
                'content' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
                'verifikasi_admin' => 'sometimes|nullable|in:menunggu,disetujui,ditolak',
                'user_id' => 'sometimes|nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                Log::error('Validasi gagal:', $validator->errors()->toArray());
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            Log::info('Validated input:', $validated);

            $path = $artikel->image;
            if ($request->hasFile('image') && !$request->removeImage) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                $originalName = $request->file('image')->getClientOriginalName();
                $path = $request->file('image')->storeAs('ImageArtikel', $originalName, 'public');
                if (!$path) {
                    Log::error('Gagal menyimpan gambar baru');
                    throw new \Exception('Gagal menyimpan gambar baru');
                }
                $validated['image'] = $path; 
            }
            

            $dataToUpdate = [
                'title' => array_key_exists('title', $validated) ? $validated['title'] : $artikel->title,
                'content' => array_key_exists('content', $validated) ? $validated['content'] : $artikel->content,
                'verifikasi_admin' => array_key_exists('verifikasi_admin', $validated) ? $validated['verifikasi_admin'] : $artikel->verifikasi_admin,
                'image' => $path,
                'user_id' => $validated['user_id'] ?? $artikel->user_id,
            ];

            Log::info('Data yang akan disimpan:', $dataToUpdate);

            $artikel->update($dataToUpdate);
            $updatedArtikel = ArtikelModel::find($id);
            Log::info('Data setelah disimpan:', $updatedArtikel->toArray());
            

            return response()->json([
                'message' => 'Blog berhasil diperbarui',
               'artikel' => $updatedArtikel,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'artikel tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui blog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
