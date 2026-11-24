@extends('layouts.app')

@section('title', 'Daftar Psikolog | MentalCare')

@section('content')
{{-- Data dari API: GET /api/psikolog (q, spesialis, tersedia, page) --}}
<section x-data="psikologIndex" class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Psikolog</p>
    <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Temukan psikolog yang cocok</h1>
    <p class="mt-3 max-w-xl text-gray-600">
        Cari berdasarkan nama atau bidang yang kamu butuhkan, lalu lihat jadwal yang tersedia.
    </p>

    {{-- Filter --}}
    <form @submit.prevent="cari()"
          class="mt-8 grid gap-3 rounded-2xl border border-blush-200 bg-white p-4 md:grid-cols-[1fr_240px_auto_auto] md:items-center">

        <input type="text" x-model="q" placeholder="Cari nama atau spesialisasi..."
               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">

        <select x-model="spesialis" @change="cari()"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="">Semua spesialisasi</option>
            <template x-for="s in daftarSpesialis" :key="s">
                <option :value="s" :selected="s === spesialis" x-text="s"></option>
            </template>
        </select>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" x-model="tersedia" @change="cari()"
                   class="rounded border-gray-300 text-primary focus:ring-primary">
            Ada jadwal
        </label>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Cari
            </button>
            <button type="button" x-show="adaFilter" x-cloak @click="reset()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                Reset
            </button>
        </div>
    </form>

    <p class="mt-6 text-sm text-gray-500">
        Menampilkan <span x-text="meta.total"></span> psikolog
    </p>

    {{-- Daftar --}}
    <div class="mt-4 grid gap-5 md:grid-cols-2 lg:grid-cols-3">

        <div x-show="memuat" class="col-span-full grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div class="h-96 animate-pulse rounded-2xl bg-blush-100"></div>
            <div class="h-96 animate-pulse rounded-2xl bg-blush-100"></div>
            <div class="h-96 animate-pulse rounded-2xl bg-blush-100"></div>
        </div>

        <div x-show="galat" x-cloak class="col-span-full rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
            <p class="text-sm text-red-700" x-text="galat"></p>
            <button type="button" @click="muat()" class="mt-3 text-sm font-semibold text-primary hover:underline">Coba lagi</button>
        </div>

        <template x-for="p in items" :key="p.id">
            <article class="flex flex-col overflow-hidden rounded-2xl border border-blush-200 bg-white transition hover:border-primary hover:shadow-lg">

                <a :href="p.link" class="block bg-blush-100">
                    <img :src="p.foto || $mc.avatar(p.nama)" :alt="'Foto ' + p.nama"
                         class="aspect-[4/3] w-full object-cover" loading="lazy">
                </a>

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="font-bold leading-snug text-ink">
                            <a :href="p.link" class="hover:text-primary" x-text="p.nama"></a>
                        </h2>

                        <span x-show="p.tersedia" class="shrink-0 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">Tersedia</span>
                        <span x-show="!p.tersedia" class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">Penuh</span>
                    </div>

                    <p class="mt-1 text-sm font-medium text-primary" x-text="p.spesialis"></p>

                    <p x-show="p.deskripsi" class="mt-3 flex-1 text-sm leading-6 text-gray-600" x-text="$mc.batasi(p.deskripsi, 100)"></p>
                    <div x-show="!p.deskripsi" class="flex-1"></div>

                    <div class="mt-5 flex items-center justify-between border-t border-blush-200 pt-4">
                        <div>
                            <p class="text-xs text-gray-500">Biaya per sesi</p>
                            <p class="font-bold text-ink" x-text="$mc.rupiah(p.biaya)"></p>
                        </div>
                        <a :href="p.link"
                           class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Lihat profil
                        </a>
                    </div>
                </div>
            </article>
        </template>

        <div x-show="!memuat && !galat && items.length === 0" x-cloak
             class="col-span-full rounded-2xl border border-dashed border-blush-200 bg-white p-10 text-center">
            <p class="text-gray-500">Tidak ada psikolog yang sesuai dengan pencarianmu.</p>
            <button type="button" @click="reset()" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">
                Tampilkan semua psikolog
            </button>
        </div>
    </div>

    <x-paginasi-api />
</section>
@endsection