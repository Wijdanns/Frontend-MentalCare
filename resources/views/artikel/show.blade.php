@extends('layouts.app')

@section('title', 'Artikel | MentalCare')

@section('content')
{{-- Data artikel diambil dari API: GET /api/artikel/{id} --}}
<article x-data="artikelShow({{ (int) $id }})" class="mx-auto max-w-3xl px-5 py-12 sm:px-8 lg:py-16">

    <a href="{{ route('artikel.index') }}" class="text-sm font-medium text-gray-500 hover:text-primary">&larr; Semua artikel</a>

    <div x-show="memuat" class="mt-8 space-y-4">
        <div class="h-8 w-2/3 animate-pulse rounded bg-blush-100"></div>
        <div class="h-64 animate-pulse rounded-3xl bg-blush-100"></div>
        <div class="h-4 animate-pulse rounded bg-blush-100"></div>
        <div class="h-4 w-5/6 animate-pulse rounded bg-blush-100"></div>
    </div>

    <div x-show="galat" x-cloak class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
        <p class="text-sm text-red-700" x-text="galat"></p>
        <a href="{{ route('artikel.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">Kembali ke daftar artikel</a>
    </div>

    <template x-if="a">
        <div>
            <div class="mt-6 flex flex-wrap items-center gap-2 text-xs">
                <a :href="a.linkKategori" class="rounded-full bg-blush-100 px-3 py-1 font-semibold text-primary hover:bg-blush-200" x-text="a.kategoriNama"></a>
                <span class="text-gray-400" x-text="a.waktu + ' menit baca'"></span>
            </div>

            <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl" x-text="a.judul"></h1>

            <p class="mt-4 text-sm text-gray-500">
                <span x-text="a.tanggal"></span>
                <span x-show="a.penulis">&middot; Ditulis oleh <span class="font-semibold text-ink" x-text="a.penulis"></span></span>
            </p>

            <template x-if="a.thumbnail">
                <img :src="a.thumbnail" :alt="a.judul" class="mt-8 aspect-[16/9] w-full rounded-3xl object-cover">
            </template>

            {{-- Isi artikel (HTML dari rich editor, hanya ditulis admin) --}}
            <div class="isi-artikel mt-8" x-html="a.isi"></div>

            {{-- Unduh PDF lewat API: GET /api/artikel/{id}/unduh --}}
            <div class="mt-8">
                <a :href="a.unduh"
                   class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary-dark">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Unduh artikel (PDF)
                </a>
            </div>

            <p class="mt-10 rounded-2xl border border-blush-200 bg-blush-100 p-5 text-sm leading-6 text-gray-600">
                Artikel ini bersifat edukatif dan bukan pengganti diagnosis atau perawatan profesional.
                Kalau kamu butuh bantuan, kamu bisa
                <a href="{{ route('psikolog.index') }}" class="font-semibold text-primary hover:underline">berbicara dengan psikolog</a>.
            </p>

            {{-- Artikel terkait --}}
            <div x-show="terkait.length > 0" x-cloak>
                <h2 class="mt-14 text-xl font-bold">Artikel terkait</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <template x-for="item in terkait" :key="item.id">
                        <a :href="item.link"
                           class="flex flex-col rounded-2xl border border-blush-200 bg-white p-4 transition hover:border-primary hover:shadow-lg">
                            <span class="text-xs font-semibold text-primary" x-text="item.kategoriNama"></span>
                            <span class="mt-2 flex-1 text-sm font-bold leading-snug text-ink" x-text="item.judul"></span>
                            <span class="mt-3 text-xs text-gray-500" x-text="item.tanggal"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </template>
</article>

{{-- Gaya dasar untuk isi artikel --}}
<style>
    .isi-artikel { font-size: 1.0625rem; line-height: 1.8; color: #374151; overflow-wrap: anywhere; }
    .isi-artikel > * + * { margin-top: 1.1em; }
    .isi-artikel h1, .isi-artikel h2, .isi-artikel h3 { font-weight: 800; line-height: 1.3; color: #111827; margin-top: 1.8em; }
    .isi-artikel h1 { font-size: 1.75rem; }
    .isi-artikel h2 { font-size: 1.5rem; }
    .isi-artikel h3 { font-size: 1.25rem; }
    .isi-artikel a { color: var(--color-primary, #e0004d); text-decoration: underline; }
    .isi-artikel ul { list-style: disc; padding-left: 1.5rem; }
    .isi-artikel ol { list-style: decimal; padding-left: 1.5rem; }
    .isi-artikel li + li { margin-top: .35em; }
    .isi-artikel blockquote { border-left: 4px solid #f3c1d3; padding-left: 1rem; color: #6b7280; font-style: italic; }
    .isi-artikel img { max-width: 100%; height: auto; border-radius: 1rem; }
    .isi-artikel pre, .isi-artikel code { background: #f3f4f6; border-radius: .5rem; font-size: .9em; }
    .isi-artikel pre { padding: 1rem; overflow-x: auto; }
    .isi-artikel code { padding: .1em .35em; }
</style>
@endsection