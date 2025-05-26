<?php

namespace App\Http\Controllers;

use App\Models\BlogBeritaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AdminBlogBeritaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['semuaBlog', 'detailBlog']]);
    }
    public function postBlog(Request $request)
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
                    $path = $request->file('image')->storeAs('ImageBlog', $originalName, 'public');
                }
            }
    
            $user = Auth::user();
            $blog = BlogBeritaModel::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'image' => $path,
                'status' => 'onhold',
            ]);
    
            return response()->json([
                'message' => 'Blog berhasil ditambahkan',
                'blog' => $blog,
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

    public function semuaBlog()
    {
        try {
            $blog = BlogBeritaModel::with('user:id,nama_depan,nama_belakang,email')->latest()->get();

            return response()->json([
                'message' => 'List artikel berhasil diambil',
                'blog' => $blog,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil list artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detailBlog($id)
    {
        try {
            $blog = BlogBeritaModel::with('user:id,nama_depan,nama_belakang,email')->findOrFail($id);

            return response()->json([
                'message' => 'Detail blog berhasil diambil',
                'blog' => $blog,
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

    public function editBlog(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $blog = BlogBeritaModel::findOrFail($id);

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
                'status' => 'sometimes|nullable|in:onhold,onpost',
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

            $path = $blog->image;
            if ($request->hasFile('image') && !$request->removeImage) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                $originalName = $request->file('image')->getClientOriginalName();
                $path = $request->file('image')->storeAs('ImageBlog', $originalName, 'public');
                if (!$path) {
                    Log::error('Gagal menyimpan gambar baru');
                    throw new \Exception('Gagal menyimpan gambar baru');
                }
                $validated['image'] = $path; 
            }
            

            $dataToUpdate = [
                'title' => array_key_exists('title', $validated) ? $validated['title'] : $blog->title,
                'content' => array_key_exists('content', $validated) ? $validated['content'] : $blog->content,
                'status' => array_key_exists('status', $validated) ? $validated['status'] : $blog->status,
                'image' => $path,
            ];

            Log::info('Data yang akan disimpan:', $dataToUpdate);

            $blog->update($dataToUpdate);
            DB::commit();
            $updatedBlog = BlogBeritaModel::find($id);
            Log::info('Data setelah disimpan:', $updatedBlog->toArray());
            

            return response()->json([
                'message' => 'Blog berhasil diperbarui',
                'blog' => $updatedBlog,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'blog tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui blog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBlog($id)
    {
        try {
            $blog = BlogBeritaModel::findOrFail($id);

            $user = Auth::user();
            if (!$user || (int)$user->role_type !== 2) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }

            $blog->delete();

            return response()->json([
                'message' => 'Blog berhasil dihapus',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'blog tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus blog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
