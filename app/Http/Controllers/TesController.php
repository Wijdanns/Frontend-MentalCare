<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TesController extends Controller
{
    public function index(): View
    {
        $res = ApiService::get('/tes-psikologi');
        abort_unless($res->ok, $res->status);

        $daftarTes = collect(data_get($res->data, 'data') ?? $res->data ?? []);

        return view('pasien.tes.index', compact('daftarTes'));
    }

    public function show(int $id): View|RedirectResponse
    {
        if (! session()->has('api_token')) {
            session(['url.intended' => route('tes.show', $id)]);

            return redirect()->route('login')->with('error', 'Silakan masuk untuk mengerjakan tes psikologi.');
        }

        $res = ApiService::get("/tes-psikologi/{$id}");

        if ($res->status === 401) {
            return redirect()->route('login')->with('error', 'Sesi login telah berakhir, silakan masuk kembali.');
        }

        if ($res->status === 403) {
            return redirect()->route('tes.index')->with('error', 'Fitur tes psikologi hanya tersedia untuk akun pasien.');
        }

        abort_unless($res->ok, $res->status);

        $tes = data_get($res->data, 'tes') ?? $res->data;

        return view('pasien.tes.show', compact('tes'));
    }

    public function submit(Request $request, int $id): RedirectResponse
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $res = ApiService::post("/tes-psikologi/{$id}/submit", [
            'jawaban' => $request->input('jawaban', []),
        ]);

        if (! $res->ok) {
            return back()
                ->withErrors(ApiService::errors($res))
                ->with('error', $res->message ?: 'Gagal mengirim jawaban tes.')
                ->withInput();
        }

        $hasilId = data_get($res->data, 'id') ?? data_get($res->data, 'hasil.id');

        return redirect()->route('tes.hasil', $hasilId);
    }

    public function riwayat(Request $request): View|RedirectResponse
    {
        if (! session()->has('api_token')) {
            session(['url.intended' => route('tes.riwayat')]);

            return redirect()->route('login')->with('error', 'Silakan masuk untuk melihat riwayat tes.');
        }

        $res = ApiService::get('/tes-psikologi/riwayat', array_filter([
            'page' => $request->query('page'),
        ]));

        if ($res->status === 401) {
            return redirect()->route('login')->with('error', 'Sesi login telah berakhir, silakan masuk kembali.');
        }

        if ($res->status === 403) {
            return redirect()->route('tes.index')->with('error', 'Riwayat tes hanya tersedia untuk akun pasien.');
        }

        abort_unless($res->ok, $res->status);

        $riwayat = isset($res->data->data)
            ? ApiService::paginator($res->data)
            : collect($res->data ?? []);

        return view('pasien.tes.riwayat', compact('riwayat'));
    }

    public function hasil(int $id): View|RedirectResponse
    {
        if (! session()->has('api_token')) {
            session(['url.intended' => route('tes.hasil', $id)]);

            return redirect()->route('login')->with('error', 'Silakan masuk untuk melihat hasil tes.');
        }

        $res = ApiService::get("/tes-psikologi/hasil/{$id}");

        if ($res->status === 401) {
            return redirect()->route('login')->with('error', 'Sesi login telah berakhir, silakan masuk kembali.');
        }

        if ($res->status === 403) {
            return redirect()->route('tes.index')->with('error', 'Hasil tes hanya tersedia untuk akun pasien.');
        }

        abort_unless($res->ok, $res->status);

        // Cek berbagai kemungkinan key pembungkus dari API
        $hasil = data_get($res->data, 'hasil')
            ?? data_get($res->data, 'riwayat')
            ?? data_get($res->data, 'hasil_tes')
            ?? data_get($res->data, 'riwayat_tes')
            ?? data_get($res->data, 'result');

        // Jika masih null, cari properti bertipe object di dalam $res->data yang menyimpan detail hasil
        if (! $hasil && is_object($res->data)) {
            foreach (get_object_vars($res->data) as $val) {
                if (is_object($val) && (isset($val->total_poin) || isset($val->created_at) || isset($val->id))) {
                    $hasil = $val;
                    break;
                }
            }
        }

        $hasil    = $hasil ?? $res->data;
        $poinMaks = (int) (data_get($res->data, 'poin_maks') ?? data_get($hasil, 'poin_maks', 0));

        if ($poinMaks === 0) {
            $pertanyaanList = collect(
                data_get($hasil, 'tesPsikologis.pertanyaan')
                ?? data_get($hasil, 'tes_psikologis.pertanyaan', [])
            );

            $poinMaks = $pertanyaanList->sum(function ($p) {
                $opsi = collect(data_get($p, 'jawabanPsikologis') ?? data_get($p, 'jawaban_psikologis', []));
                return (int) $opsi->max('poin');
            });
        }

        return view('pasien.tes.hasil', compact('hasil', 'poinMaks'));
    }
}