<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // API sudah mengurutkan yang punya jadwal lebih dulu; ambil 3 teratas
        $res = ApiService::get('/psikolog');

        $list = data_get($res->data, 'psikologs.data')
            ?? data_get($res->data, 'data')
            ?? [];

        $psikologs = $res->ok
            ? collect($list)->take(3)
            : collect();

        return view('home', compact('psikologs'));
    }
}