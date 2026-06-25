<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KategoriSampah;
use App\Http\Controllers\Web\AuthController;
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

Route::post('/register', [AuthController::class, 'register']);//register
// Buat Login
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    // Buat token baru
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user
    ]);
});

// Ambil data kategori untuk PWA
Route::get('/kategori', function () {
    return response()->json(KategoriSampah::all());
});
//  Simulasi kirim hasil scan dari Python/PWA
Route::post('/scan-simulasi', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'Data diterima oleh Laravel',
        'data_yang_dikirim' => $request->all()
    ]);
});


// --- PINTU TERKUNCI (Wajib Bawa Bearer Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Cek profil user yang sedang login
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Logout (Hapus token) dan keluar
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil Logout']);
    });

    // Nanti di sini kita akan tambahkan route untuk:
    // 1. Cek daftar kategori sampah
    // 2. Kirim gambar ke AI
    // 3. Riwayat setoran nasabah
    
});
