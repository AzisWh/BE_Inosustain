<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;

class ForgotPasswordController extends Controller
{
    public function VerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $verification_code = rand(100000, 999999);

        $user->verification_code = $verification_code;
        $user->save();

        Mail::to($user->email)->send(new ForgotPasswordMail($verification_code));

        return response()->json([
            'message' => 'Kode verifikasi berhasil dikirim ke email.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed', 
        ]);

        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->verification_code)
                    ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Kode verifikasi salah atau tidak ditemukan.',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->verification_code = null; 
        $user->save();

        return response()->json([
            'message' => 'Password berhasil direset.',
        ], 200);
    }
}
