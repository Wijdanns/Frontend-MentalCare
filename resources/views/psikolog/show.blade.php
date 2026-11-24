@extends('layouts.app')

@section('title', 'Psikolog | MentalCare')

@section('content')
{{-- Data dari API: GET /api/psikolog/{id} (profil + jadwal per tanggal) --}}
<section x-data="psikologShow({{ (int) $id }})" class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <a href="{{ route('psikolog.index') }}" class="text-sm font-medium text-gray-500 hover:text-primary">&larr; Semua psikolog</a>

    <div x-show="memuat" class="mt-8 grid gap-8 lg:grid-cols-[360px_1fr] lg:gap-12">
        <div class="h-96 animate-pulse rounded-3xl bg-blush-100"></div>
        <div class="space-y-4">
            <div class="h-10 w-2/3 animate-pulse rounded bg-blush-100"></div>
            <div class="h-6 w-1/3 animate-pulse rounded bg-blush-100"></div>
            <div class="h-24 animate-pulse rounded bg-blush-100"></div>
        </div>
    </div>

    <div x-show="galat" x-cloak class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
        <p class="text-sm text-red-700" x-text="galat"></p>
        <a href="{{ route('psikolog.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">Kembali ke daftar psikolog</a>
    </div>

    <template x-if="p">
        <div class="mt-6 grid gap-8 lg:grid-cols-[360px_1fr] lg:gap-12">

            {{-- Kolom kiri: foto + ringkasan --}}
            <aside>
                <div class="overflow-hidden rounded-3xl bg-blush-200/80 p-3 shadow-2xl shadow-primary/10">
                    <img :src="p.foto || $mc.avatar(p.nama)" :alt="'Foto ' + p.nama"
                         class="aspect-[4/5] w-full rounded-[22px] object-cover">
                </div>

                <div class="mt-5 rounded-2xl border border-blush-200 bg-white p-5">
                    <p class="text-xs text-gray-500">Biaya per sesi</p>
                    <p class="mt-1 text-2xl font-extrabold text-ink" x-text="$mc.rupiah(p.biaya)"></p>
                </div>
            </aside>

            {{-- Kolom kanan: profil + jadwal --}}
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl" x-text="p.nama"></h1>
                    <span x-show="p.tersedia" class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Tersedia</span>
                    <span x-show="!p.tersedia" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">Belum ada jadwal</span>
                </div>

                <p class="mt-2 text-lg font-semibold text-primary" x-text="p.spesialis"></p>

                <h2 class="mt-8 text-lg font-bold">Tentang</h2>
                <p class="mt-2 whitespace-pre-line leading-7 text-gray-600"
                   x-text="p.deskripsi || 'Psikolog ini belum menambahkan deskripsi.'"></p>

                {{-- Jadwal --}}
                <h2 class="mt-10 text-lg font-bold">Jadwal tersedia</h2>

                <div x-show="!login" x-cloak class="mt-3 rounded-xl bg-blush-100 p-4 text-sm text-gray-700">
                    <a href="{{ url('/login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
                    atau
                    <a href="{{ url('/register') }}" class="font-semibold text-primary hover:underline">daftar</a>
                    untuk memesan konsultasi.
                </div>

                <div class="mt-4 space-y-4">
                    <template x-for="hari in jadwal" :key="hari.kunci">
                        <div class="rounded-2xl border border-blush-200 bg-white p-5">
                            <p class="font-semibold text-ink" x-text="hari.label"></p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-for="slot in hari.slots" :key="slot.id">
                                    <a :href="linkSlot(slot)" x-text="slot.jam"
                                       class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-primary hover:bg-blush-100 hover:text-primary"></a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div x-show="jadwal.length === 0" x-cloak
                         class="rounded-2xl border border-dashed border-blush-200 bg-white p-8 text-center text-gray-500">
                        Belum ada jadwal tersedia untuk psikolog ini. Coba lagi nanti atau
                        <a href="{{ route('psikolog.index') }}" class="font-semibold text-primary hover:underline">pilih psikolog lain</a>.
                    </div>
                </div>
            </div>
        </div>
    </template>
</section>
@endsection