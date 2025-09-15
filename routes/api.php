<?php

use App\Http\Controllers\AdminArtikelController;
use App\Http\Controllers\AdminBlogBeritaController;
use App\Http\Controllers\AdminBukuController;
use App\Http\Controllers\AdminUserController;
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
    //
    Route::get('/allUsers', [AdminUserController::class, 'allUser']);
    Route::get('/detailUsers/{id}', [AdminUserController::class, 'userDetail']);
    //
    Route::post('/postArtikel', [UserArticleController::class, 'postArtikel']); 
    Route::get('/userArtikel', [UserArticleController::class, 'artikelByUser']); 
    // 
    Route::put('/updateStatusArtikel/{id}',[AdminArtikelController::class, 'verifikasiArtikel']);
    Route::post('/postArticle', [AdminArtikelController::class, 'postArticle']);
    Route::delete('/delArticle/{id}', [AdminArtikelController::class, 'delArticle']);
    Route::post('/editArticle/{id}', [AdminArtikelController::class, 'artikelEdit']);
    // 
    Route::post('/postBlogBerita',[AdminBlogBeritaController::class,'postBlog']);
    Route::post('/addImageBlog/{id}', [AdminBlogBeritaController::class, 'addBlogImage']);
    Route::delete('/deleteImageBlog/{id}', [AdminBlogBeritaController::class, 'deleteImageBlog']);
    Route::get('/getBlogImages/{id}', [AdminBlogBeritaController::class, 'showImageBlog']);
    Route::post('/editBlog/{id}', [AdminBlogBeritaController::class, 'editBlog']);
    Route::delete('/deleteBlog/{id}', [AdminBlogBeritaController::class, 'deleteBlog']);
    // 
    Route::post('/addBuku', [AdminBukuController::class, 'postBuku']);
    Route::post('/editBuku/{id}', [AdminBukuController::class, 'editBuku']);
    Route::delete('/deleteBuku/{id}', [AdminBukuController::class, 'deleteBuku']);
});
Route::get('/semuaBuku', [AdminBukuController::class, 'getAllBuku']);
Route::get('/detailBuku/{id}', [AdminBukuController::class, 'detailBuku']);
Route::get('/semuaBlog',[AdminBlogBeritaController::class,'semuaBlog']);
Route::get('/detailBlog/{id}',[AdminBlogBeritaController::class,'detailBlog']);
Route::get('/semuaArtikel', [UserArticleController::class, 'semuaArtikel']);
Route::get('/detailArtikel/{id}', [UserArticleController::class, 'detailArtikel']);