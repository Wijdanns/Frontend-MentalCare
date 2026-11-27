<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        $res = ApiService::get('/profil');
        abort_unless($res->ok, $res->status);

        $user              = $res->data->user;
        $riwayatKonsultasi = collect($res->data->riwayat_konsultasi);
        $riwayatTes        = collect($res->data->riwayat_tes);

        return view('profil.index', compact('user', 'riwayatKonsultasi', 'riwayatTes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $files = $request->hasFile('profil') ? ['profil' => $request->file('profil')] : [];

        // Di API, update profil memakai POST agar bisa upload file (multipart)
        $res = ApiService::post('/profil', ['nama' => $request->input('nama')], $files);

        if (! $res->ok) {
            return back()->withErrors(ApiService::errors($res))->withInput();
        }

        session(['user' => $res->data]); // segarkan nama/foto di navbar

        return back()->with('success', $res->message);
    }
}