<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KategoriController;
use App\Http\Controllers\Web\TransaksiController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\PwaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rute Publik Web
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('Postlogin');

// Rute Terlindungi Web
Route::middleware('auth')->group(function () {
    // chekcrole admin
    
    Route::middleware('checkRole:admin')->group(function(){
        // dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/cetak-pdf', [DashboardController::class, 'cetakPdf'])->name('dashboard.cetakPdf');
        Route::get('/dashboard/detail/{tipe}', [DashboardController::class, 'detail'])->name('dashboard.detail');
        // Route::get('/download-laporan', [DashboardController::class, 'downloadLaporan'])->name('download-laporan');
        // CRUD Kategori
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        // CRUD Transaksi
        // Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::put('/transaksi/{id}/update', [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
        Route::patch('/transaksi/{id}/update-status', [TransaksiController::class, 'updateStatus'])->name('transaksi.update-status');
        Route::post('/transaksi/scan-ai', [TransaksiController::class, 'scanAI'])->name('transaksi.scan-ai');
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        // CRUD user
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/tarik-saldo', [UserController::class, 'tarikSaldo'])->name('users.tarik-saldo');
        Route::post('/users/{id}/topup-saldo', [UserController::class, 'topUpSaldo'])->name('users.topup-saldo');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');//ganti password user untuk mereset
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // admin lupa pw
        Route::get('/admin/ganti-password', [DashboardController::class, 'gantiPassword'])->name('admin.password.edit');
        Route::post('/admin/ganti-password', [DashboardController::class, 'updatePassword'])->name('admin.password.update');
    });

    // checkrole nasabah
   // --- GRUP KHUSUS NASABAH (PWA) ---
   Route::middleware(['checkRole:nasabah', 'isMobile'])->prefix('pwa')->group(function () {
        Route::get('/home', [PwaController::class, 'index'])->name('pwa.home');
        Route::get('/scan', [PwaController::class, 'scan'])->name('pwa.scan');
        Route::get('/riwayat', [PwaController::class, 'riwayat'])->name('pwa.riwayat');
        
        // Route untuk komunikasi ke Flask AI
        Route::post('/scan-ai', [PwaController::class, 'prosesScanAI'])->name('pwa.scan-ai');
        
        // Finalisasi simpan transaksi ke DB Laravel
        Route::post('/pwa/setor', [PwaController::class, 'simpanSetoran'])->name('pwa.setor');
        
        // Route Logout PWA
        Route::post('/pwa/logout', [AuthController::class, 'logout'])->name('pwa.logout');

        // Route untuk Ganti Password
        Route::get('/profil/ganti-password', [PwaController::class, 'gantiPassword'])->name('pwa.password.edit');
        Route::post('/profil/ganti-password', [PwaController::class, 'updatePassword'])->name('pwa.password.update');
    });
});