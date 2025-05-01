<?php

use App\Http\Controllers\AdminArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('me','me');
});

Route::post('/forgot-password', [ForgotPasswordController::class, 'VerificationCode']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::post('/postArtikel', [UserArticleController::class, 'postArtikel']); 
    Route::get('/userArtikel', [UserArticleController::class, 'artikelByUser']); 
    // 
    Route::put('/updateStatusArtikel/{id}',[AdminArtikelController::class, 'verifikasiArtikel']);
});
Route::get('/semuaArtikel', [UserArticleController::class, 'semuaArtikel']);
Route::get('/detailArtikel/{id}', [UserArticleController::class, 'detailArtikel']);