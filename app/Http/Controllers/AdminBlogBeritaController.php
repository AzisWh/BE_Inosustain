<?php

namespace App\Http\Controllers;

use App\Models\BlogBeritaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        try {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status' => 'sometimes|in:onhold,published,rejected',
            ]);

            $blog = BlogBeritaModel::findOrFail($id);

            $user = Auth::user();
            if ($blog->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk mengedit artikel ini',
                ], 403);
            }

            $path = $blog->image;
            if ($request->has('image')) {
                if (filter_var($request->image, FILTER_VALIDATE_URL)) {
                    $path = $request->image;
                } elseif ($request->hasFile('image')) {
                    //delete if exist
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    $originalName = $request->file('image')->getClientOriginalName();
                    $path = $request->file('image')->storeAs('ImageBlog', $originalName, 'public');
                }
            }

            $blog->fill([
                'title' => $request->input('title', $blog->title),
                'content' => $request->input('content', $blog->content),
                'image' => $path,
                'status' => $request->input('status', $blog->status),
            ]);
            $blog->save();

            return response()->json([
                'message' => 'Blog berhasil diperbarui',
                'blog' => $blog,
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
                'message' => 'Terjadi kesalahan saat memperbarui artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBlog($id)
    {
        try {
            $blog = BlogBeritaModel::findOrFail($id);

            $user = Auth::user();
            if ($blog->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }

            $blog->delete();

            return response()->json([
                'message' => 'Blog berhasil dihapus (soft delete)',
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
}
