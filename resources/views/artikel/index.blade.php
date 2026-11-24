@extends('layouts.app')

@section('title', 'Artikel | MentalCare')

@section('content')
{{-- Data dari API: GET /api/artikel (daftar) dan GET /api/artikel-kategori (tombol filter) --}}
<section x-data="artikelIndex" class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Artikel</p>
    <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
        x-text="aktif ? aktif.nama : 'Bacaan untuk kesehatan mentalmu'"></h1>
    <p class="mt-3 max-w-xl text-gray-600">
        Tulisan ringan seputar stres, kecemasan, relasi, dan cara merawat diri.
    </p>

    {{-- Pencarian --}}
    <form @submit.prevent="cari()" class="mt-8 flex gap-2">
        <input type="text" x-model="q" placeholder="Cari artikel..."
               class="w-full max-w-md rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
        <button type="submit"
                class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            Cari
        </button>
        <button type="button" x-show="q !== ''" x-cloak @click="q = ''; cari()"
                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
            Reset
        </button>
    </form>

    {{-- Filter kategori --}}
    <div class="mt-5 flex flex-wrap gap-2">
        <button type="button" @click="pilih('')"
                :class="aktif ? 'border border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary' : 'bg-primary text-white'"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition">
            Semua
        </button>

        <template x-for="k in kategoriList" :key="k.id">
            <button type="button" @click="pilih(k.id)"
                    :class="aktif && aktif.id === k.id ? 'bg-primary text-white' : 'border border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition">
                <span x-text="k.nama"></span>
                <span x-show="k.jumlah !== null" :class="aktif && aktif.id === k.id ? 'text-white/80' : 'text-gray-400'" x-text="'(' + k.jumlah + ')'"></span>
            </button>
        </template>
    </div>

    <p class="mt-6 text-sm text-gray-500">Menampilkan <span x-text="meta.total"></span> artikel</p>

    {{-- Daftar --}}
    <div class="mt-4 grid gap-5 md:grid-cols-2 lg:grid-cols-3">

        {{-- Memuat --}}
        <div x-show="memuat" class="col-span-full grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div class="h-80 animate-pulse rounded-2xl bg-blush-100"></div>
            <div class="h-80 animate-pulse rounded-2xl bg-blush-100"></div>
            <div class="h-80 animate-pulse rounded-2xl bg-blush-100"></div>
        </div>

        {{-- Gagal memanggil API --}}
        <div x-show="galat" x-cloak class="col-span-full rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
            <p class="text-sm text-red-700" x-text="galat"></p>
            <button type="button" @click="muat()" class="mt-3 text-sm font-semibold text-primary hover:underline">Coba lagi</button>
        </div>

        {{-- Data dari API --}}
        <template x-for="a in items" :key="a.id">
            <article class="flex flex-col overflow-hidden rounded-2xl border border-blush-200 bg-white transition hover:border-primary hover:shadow-lg">

                <a :href="a.link" class="block">
                    <template x-if="a.thumbnail">
                        <img :src="a.thumbnail" :alt="a.judul" class="aspect-[16/10] w-full object-cover" loading="lazy">
                    </template>
                    <template x-if="!a.thumbnail">
                        <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-blush-100 via-blush-200 to-primary/20">
                            <svg class="h-12 w-12 text-primary/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                        </div>
                    </template>
                </a>

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="pilih(a.kategoriId)"
                                class="rounded-full bg-blush-100 px-2.5 py-1 font-semibold text-primary hover:bg-blush-200"
                                x-text="a.kategoriNama"></button>
                        <span class="text-gray-400" x-text="a.waktu + ' menit baca'"></span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold leading-snug text-ink">
                        <a :href="a.link" class="hover:text-primary" x-text="a.judul"></a>
                    </h2>

                    <p class="mt-2 flex-1 text-sm leading-6 text-gray-600" x-text="a.ringkasan"></p>

                    <p class="mt-4 border-t border-blush-200 pt-3 text-xs text-gray-500">
                        <span x-text="a.tanggal"></span>
                        <span x-show="a.penulis" x-text="'· ' + a.penulis"></span>
                    </p>
                </div>
            </article>
        </template>

        {{-- Kosong --}}
        <div x-show="!memuat && !galat && items.length === 0" x-cloak
             class="col-span-full rounded-2xl border border-dashed border-blush-200 bg-white p-10 text-center">
            <p class="text-gray-500">Belum ada artikel yang sesuai.</p>
            <button type="button" @click="semua()" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">
                Tampilkan semua artikel
            </button>
        </div>
    </div>

    <x-paginasi-api />
</section>
@endsection