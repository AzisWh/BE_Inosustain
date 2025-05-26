<?php

namespace App\Http\Controllers;

use App\Models\BukuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminBukuController extends Controller
{
    public function getAllBuku()
    {
        try {
            $buku = BukuModel::all();

            return response()->json([
                'message' => 'List buku berhasil diambil',
                'buku' => $buku,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil list buku',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detailBuku($id)
    {
        try {
            $buku = BukuModel::findOrFail($id);

            return response()->json([
                'message' => 'Detail buku berhasil diambil',
                'buku' => $buku,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'buku tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil detail buku',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function postBuku(Request $request)
    {
        try {
            $data = $request->validate([
                'title'    => 'required|string|max:255',
                'author'   => 'required|string|max:255',
                'penerbit' => 'required|string|max:255',
                'tahun'    => 'required|string|max:255',
                'doi'      => 'required|string|max:255',
                'file'     => 'required|file|mimes:pdf,doc,docx|max:10000',
              ]);

            $buku = new BukuModel();
            $buku->title = $request->input('title');
            $buku->author = $request->input('author');
            $buku->penerbit = $request->input('penerbit');
            $buku->tahun = $request->input('tahun');
            $buku->doi = $request->input('doi');

            if ($request->hasFile('file')) {
                $file       = $request->file('file');
                $name       = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName   = Str::slug($name).'_'.time().'.'.$file->getClientOriginalExtension();
                $path       = $file->storeAs('public/FileBuku', $safeName);
                $data['file'] = Storage::url($path);
            }

            $buku = BukuModel::create($data);

            return response()->json([
                'message' => 'Buku berhasil ditambahkan',
                'buku' => $buku,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan buku',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function editBuku(Request $request, $id)
    {
        try {
            $buku = BukuModel::findOrFail($id);
            $user = Auth::user();
            if (!$user || (int)$user->role_type !== 2) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }

            $input = $request->all();
            Log::info('Raw input dari request:', $input);
            
            $validator = Validator::make($request->all(), [
                'title'    => 'sometimes|nullable|string|max:255',
                'author'   => 'sometimes|nullable|string|max:255',
                'penerbit' => 'sometimes|nullable|string|max:255',
                'tahun'    => 'sometimes|nullable|string|max:255',
                'doi'      => 'sometimes|nullable|string|max:255',
                'file'     => 'sometimes|nullable|file|mimes:pdf,doc,docx|max:10000',
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            
            $validated = $validator->validated();
            Log::info('Validated data:', $validated);


            $path = $buku->file;
            if ($request->hasFile('file') && !$request->removeImage) {
                if ($path) {
                    $oldPath = str_replace('/storage/', 'public/', $buku->file);
                    Storage::delete($oldPath);
                }
                $file     = $request->file('file');
                $name     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = Str::slug($name).'_'.time().'.'.$file->getClientOriginalExtension();
                $path     = $file->storeAs('public/FileBuku', $filename);
                if (!$path) {
                    throw new \Exception('Gagal menyimpan file baru');
                }
                $validated['file'] = Storage::url($path);
            }

            $dataToUpdate = [
                'title'    => array_key_exists('title', $validated) ? $validated['title'] : $buku->title,
                'author'   => array_key_exists('author', $validated) ? $validated['author'] : $buku->author,
                'penerbit' => array_key_exists('penerbit', $validated) ? $validated['penerbit'] : $buku->penerbit,
                'tahun'    => array_key_exists('tahun', $validated) ? $validated['tahun'] : $buku->tahun,
                'doi'      => array_key_exists('doi', $validated) ? $validated['doi'] : $buku->doi,
                'file'     => $path,
            ];

            Log::info('Data to update:', $dataToUpdate);

            $buku->update($dataToUpdate);
            $updatedBuku = BukuModel::find($id);

            // Log::info('Updated buku:', $updatedBuku);

            return response()->json([
                'message' => 'Buku berhasil diperbarui',
                'buku'    => $updatedBuku,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Buku tidak ditemukan',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui buku',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBuku($id)
    {
        try {
            $user = Auth::user();
            if (!$user || (int)$user->role_type !== 2) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus artikel ini',
                ], 403);
            }

            $buku = BukuModel::findOrFail($id);

            if ($buku->file) {
                $oldPath = str_replace('/storage/', 'public/', $buku->file);
                Storage::delete($oldPath);
            }

            $buku->delete();

            return response()->json([
                'message' => 'Buku berhasil dihapus',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Buku tidak ditemukan',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus buku',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
