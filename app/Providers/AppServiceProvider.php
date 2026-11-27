<?php

namespace App\Providers;

use App\Services\ApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // $authUser tersedia di SEMUA view (pengganti auth()->user()); null jika belum login
        View::composer('*', function ($view) {
            $view->with('authUser', session('user'));
        });

        // Daftar kategori untuk dropdown navbar (di-cache 10 menit)
        View::composer('partials.navbar', function ($view) {
            $kategori = Cache::remember('kategori_artikel', 600, function () {
                $res = ApiService::get('/artikel-kategori');

                return $res->ok ? $res->data : [];
            });

            $view->with('kategoriArtikel', collect($kategori));
        });
    }
}