<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
    
            $credentials = $request->only('email', 'password');
    
            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'message' => 'Email atau password salah',
                ], 401);
            }
    
            $user = Auth::user();
    
            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'nama_depan' => 'required|string|max:255',
                'nama_belakang' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'no_hp' => 'required|string|max:15',
                'gender' => 'required|in:P,L',
                'password' => 'required|string|min:6',
            ]);
    
            $user = User::create([
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'gender' => $request->gender,
                'password' => Hash::make($request->password),
                'role_type' => 0,
            ]);
    
            return response()->json([
                'message' => 'Berhasil register user',
                'user' => $user,
                'role' => $user->role_type == 0 ? 'User' : 'Admin'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422); 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat register',
                'error' => $e->getMessage(),
            ], 500); 
        }
    }

    public function logout()
    {
        try {
            Auth::logout(true);

            return response()->json([
                'message' => 'Berhasil keluar',
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not provided or invalid'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = Auth::refresh(); 
    
            return response()->json([
                'message' => 'Token refreshed successfully',
                'user' => Auth::user(),
                'authorization' => [
                    'token' => $newToken,
                    'type' => 'bearer',
                ]
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired and cannot be refreshed'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized. Invalid or expired token.',
                ], 401);
            }

            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
}
