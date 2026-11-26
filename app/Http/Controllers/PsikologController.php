<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PsikologController extends Controller
{
    public function index(Request $request): View
    {
        $q         = trim((string) $request->query('q', ''));
        $spesialis = (string) $request->query('spesialis', '');

        $res = ApiService::get('/psikolog', array_filter([
            'q'         => $q,
            'spesialis' => $spesialis,
            'tersedia'  => $request->boolean('tersedia') ? 1 : null,
            'page'      => $request->query('page'),
        ]));
        abort_unless($res->ok, $res->status);

        $psikologs       = ApiService::paginator(data_get($res->data, 'psikologs') ?? $res->data);
        $daftarSpesialis = collect(data_get($res->data, 'daftar_spesialis', []));

        return view('psikolog.index', compact('psikologs', 'daftarSpesialis', 'q', 'spesialis'));
    }

    public function show(int $id): View
    {
        $res = ApiService::get("/psikolog/{$id}");
        abort_unless($res->ok, $res->status);

        $psikolog         = data_get($res->data, 'psikolog') ?? $res->data;
        $jadwalPerTanggal = (array) (data_get($res->data, 'jadwal_per_tanggal') ?? []);

        return view('psikolog.show', compact('psikolog', 'jadwalPerTanggal'));
    }
}