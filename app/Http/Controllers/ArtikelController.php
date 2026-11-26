<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtikelController extends Controller
{
    public function index(Request $request): View
    {
        $q          = trim((string) $request->query('q', ''));
        $kategoriId = $request->integer('kategori') ?: null;

        $res = ApiService::get('/artikel', array_filter([
            'q'        => $q,
            'kategori' => $kategoriId,
            'page'     => $request->query('page'),
        ]));
        abort_unless($res->ok, $res->status);

        $artikels       = ApiService::paginator($res->data);
        $daftarKategori = collect(ApiService::get('/artikel-kategori')->data ?? []);
        $kategoriAktif  = $kategoriId ? $daftarKategori->firstWhere('id', $kategoriId) : null;

        return view('artikel.index', compact('artikels', 'daftarKategori', 'kategoriAktif', 'q'));
    }

    public function show(int $id): View
    {
        $res = ApiService::get("/artikel/{$id}");
        abort_unless($res->ok, $res->status);

        $artikel = $res->data;

        // Artikel terkait: satu kategori, selain artikel ini, maksimal 3
        $terkait = collect();
        if (! empty($artikel->kategori)) {
            $r = ApiService::get('/artikel', ['kategori' => $artikel->kategori->id]);
            if ($r->ok) {
                $terkait = collect($r->data->data)
                    ->reject(fn ($a) => $a->id === $artikel->id)
                    ->take(3)
                    ->values();
            }
        }

        return view('artikel.show', compact('artikel', 'terkait'));
    }

    /** PDF dibuat oleh backend; frontend cukup mengarahkan browser ke endpoint-nya. */
    public function unduh(int $id): RedirectResponse
    {
        return redirect()->away(ApiService::apiUrl() . "/artikel/{$id}/unduh");
    }
}