<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\PsikologController;
use App\Http\Controllers\TesController;
use App\Http\Middleware\CekToken;
use Illuminate\Support\Facades\Route;

// ======================= 1. PUBLIK =======================
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/psikolog', [PsikologController::class, 'index'])->name('psikolog.index');
Route::get('/psikolog/{id}', [PsikologController::class, 'show'])->whereNumber('id')->name('psikolog.show');

Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{id}', [ArtikelController::class, 'show'])->whereNumber('id')->name('artikel.show');
Route::get('/artikel/{id}/unduh', [ArtikelController::class, 'unduh'])->whereNumber('id')->name('artikel.unduh');

Route::get('/tes-psikologi', [TesController::class, 'index'])->name('tes.index');

Route::get('/konsultasi', [KonsultasiController::class, 'index'])->name('konsultasi.index');
Route::get('/konsultasi/go-login', function () {
    session(['url.intended' => route('konsultasi.index')]);

    return redirect()->route('login');
})->name('konsultasi.go-login');

// ======================= 2. AUTH =======================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(CekToken::class)
    ->name('logout');

// ======================= 3. AREA PASIEN =======================
Route::middleware(CekToken::class . ':pasien')->group(function () {

    // Tes psikologi (urutan penting: /riwayat & /hasil sebelum /{id})
    Route::prefix('tes-psikologi')->name('tes.')->group(function () {
        Route::get('/riwayat', [TesController::class, 'riwayat'])->name('riwayat');
        Route::get('/hasil/{id}', [TesController::class, 'hasil'])->whereNumber('id')->name('hasil');
        Route::get('/{id}', [TesController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/{id}', [TesController::class, 'submit'])->whereNumber('id')->name('submit');
    });

    // Profil
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Konsultasi
    Route::get('/konsultasi/riwayat', [KonsultasiController::class, 'riwayat'])->name('konsultasi.riwayat');
    Route::post('/konsultasi', [KonsultasiController::class, 'store'])->name('konsultasi.store');
    Route::get('/konsultasi/konfirmasi/{id}', [KonsultasiController::class, 'konfirmasi'])->whereNumber('id')->name('konsultasi.konfirmasi');
    Route::post('/konsultasi/{id}/bayar', [KonsultasiController::class, 'bayar'])->whereNumber('id')->name('konsultasi.bayar');
    Route::get('/konsultasi/{id}/bukti', [KonsultasiController::class, 'bukti'])->whereNumber('id')->name('konsultasi.bukti');
    Route::post('/konsultasi/{id}/pembayaran/konfirmasi', [KonsultasiController::class, 'konfirmasiPembayaran'])->whereNumber('id')->name('pembayaran.konfirmasi');
});