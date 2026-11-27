<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KonsultasiController extends Controller
{
    public function index(): View|RedirectResponse
    {
        // Endpoint API ini butuh login pasien, jadi tamu diarahkan ke halaman login
        if (! session()->has('api_token')) {
            session(['url.intended' => route('konsultasi.index')]);

            return redirect()->route('login')->with('error', 'Silakan masuk untuk membuat jadwal konsultasi.');
        }

        $res = ApiService::get('/konsultasi');
        abort_unless($res->ok, $res->status);

        $psikologs = collect($res->data); // tiap item punya ->jadwal_tersedia[] dan ->tersedia

        return view('konsultasi.index', compact('psikologs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $res = ApiService::post('/konsultasi', $request->only('id_psikolog', 'id_jadwal', 'metode_pembayaran'));

        if (! $res->ok) {
            return back()
                ->withErrors(ApiService::errors($res))
                ->with('error', $res->message)
                ->withInput();
        }

        toast($res->message, 'success');

        return redirect()->route('konsultasi.konfirmasi', $res->data->id);
    }

    public function konfirmasi(int $id): View|RedirectResponse
    {
        $res = ApiService::get("/konsultasi/{$id}");

        if ($res->status === 400) { // batas waktu pembayaran habis
            return redirect()->route('konsultasi.riwayat')->with('error', $res->message);
        }
        abort_unless($res->ok, $res->status);

        $konsultasi = $res->data;

        return view('konsultasi.konfirmasi', compact('konsultasi'));
    }

    public function bayar(Request $request, int $id): RedirectResponse
    {
        $files = $request->hasFile('bukti_pembayaran')
            ? ['bukti_pembayaran' => $request->file('bukti_pembayaran')]
            : [];

        $res = ApiService::post("/konsultasi/{$id}/bayar", [], $files);

        if (! $res->ok) {
            if ($res->status === 400) {
                return redirect()->route('konsultasi.riwayat')->with('error', $res->message);
            }

            return back()->withErrors(ApiService::errors($res))->with('error', $res->message);
        }

        return redirect()->route('konsultasi.riwayat')->with('success', $res->message);
    }

    public function konfirmasiPembayaran(int $id): RedirectResponse
    {
        $res = ApiService::post("/konsultasi/{$id}/pembayaran/konfirmasi");

        return redirect()
            ->route('konsultasi.riwayat')
            ->with($res->ok ? 'success' : 'error', $res->message);
    }

    public function riwayat(): View
    {
        $res = ApiService::get('/konsultasi/riwayat');
        abort_unless($res->ok, $res->status);

        $konsultasi = collect($res->data);

        return view('konsultasi.riwayat', compact('konsultasi'));
    }

    /** Bukti pembayaran di backend butuh token, jadi diteruskan (proxy) lewat frontend. */
    public function bukti(int $id)
    {
        $res = ApiService::client()->get("/konsultasi/{$id}/bukti");
        abort_unless($res->successful(), 404);

        return response($res->body(), 200)
            ->header('Content-Type', $res->header('Content-Type'));
    }
}