<?php

use App\Http\Controllers\Api\ArtikelApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\KonsultasiApiController;
use App\Http\Controllers\Api\PembayaranApiController;
use App\Http\Controllers\Api\ProfilApiController;
use App\Http\Controllers\Api\PsikologApiController;
use App\Http\Controllers\Api\PsikologJadwalApiController;
use App\Http\Controllers\Api\PsikologKonsultasiApiController;
use App\Http\Controllers\Api\PsikologSayaApiController;
use App\Http\Controllers\Api\TesApiController;
use App\Http\Middleware\EnsureRole;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|-------------------------------------------------------------------------
| API MentalCare  (prefix otomatis: /api)
| Autentikasi: Bearer token Sanctum  →  Authorization: Bearer <token>
|-------------------------------------------------------------------------
*/

// ================= Publik =================
Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:10,1');

Route::get('/artikel-kategori', [ArtikelApiController::class, 'kategori']);
Route::get('/artikel', [ArtikelApiController::class, 'index']);
Route::get('/artikel/{id}', [ArtikelApiController::class, 'show']);
Route::get('/artikel/{id}/unduh', [ArtikelApiController::class, 'unduh']);

Route::get('/psikolog', [PsikologApiController::class, 'index']);
Route::get('/psikolog/{id}', [PsikologApiController::class, 'show']);

Route::get('/tes-psikologi', [TesApiController::class, 'index']);

// ================= Login (semua role) =================
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn (Request $request) => response()->json([
        'status' => 'success',
        'data'   => new UserResource($request->user()),
    ]));

    Route::post('/logout', [AuthApiController::class, 'logout']);

    // ================= Pasien =================
    Route::middleware(EnsureRole::class . ':pasien')->group(function () {

        Route::get('/profil', [ProfilApiController::class, 'index']);
        Route::post('/profil', [ProfilApiController::class, 'update']);

        // Tes psikologi
        Route::get('/tes-psikologi/riwayat', [TesApiController::class, 'riwayat']);
        Route::get('/tes-psikologi/hasil/{id}', [TesApiController::class, 'hasil']);
        Route::get('/tes-psikologi/{id}', [TesApiController::class, 'show']);
        Route::post('/tes-psikologi/{id}/submit', [TesApiController::class, 'submit']);

        // Konsultasi
        Route::get('/konsultasi', [KonsultasiApiController::class, 'index']);
        Route::get('/konsultasi/riwayat', [KonsultasiApiController::class, 'riwayat']);
        Route::post('/konsultasi', [KonsultasiApiController::class, 'store']);
        Route::get('/konsultasi/{id}', [KonsultasiApiController::class, 'show']);
        Route::post('/konsultasi/{id}/bayar', [KonsultasiApiController::class, 'bayar']);
        Route::get('/konsultasi/{id}/bukti', [KonsultasiApiController::class, 'bukti']);

        // Pembayaran (verifikasi Berhasil hanya oleh admin)
        Route::post('/konsultasi/{id}/pembayaran/konfirmasi', [PembayaranApiController::class, 'konfirmasi']);
        Route::post('/konsultasi/{id}/pembayaran/gagal', [PembayaranApiController::class, 'gagal']);
    });

    // ================= Psikolog =================
    Route::middleware(EnsureRole::class . ':psikolog')->prefix('psikolog-saya')->group(function () {

        Route::get('/profil', [PsikologSayaApiController::class, 'profil']);
        Route::post('/profil', [PsikologSayaApiController::class, 'update']); // POST agar bisa upload foto (multipart)

        Route::get('/jadwal', [PsikologJadwalApiController::class, 'index']);
        Route::post('/jadwal', [PsikologJadwalApiController::class, 'store']);
        Route::put('/jadwal/{id}', [PsikologJadwalApiController::class, 'update']);
        Route::delete('/jadwal/{id}', [PsikologJadwalApiController::class, 'destroy']);

        Route::get('/konsultasi', [PsikologKonsultasiApiController::class, 'index']);
        Route::patch('/konsultasi/{id}/selesai', [PsikologKonsultasiApiController::class, 'selesai']);
    });
});
