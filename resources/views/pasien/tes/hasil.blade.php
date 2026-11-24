@extends('layouts.app')

@section('title', 'Hasil Tes | MentalCare')

@section('content')
{{-- Data dari API: GET /api/tes-psikologi/hasil/{id} --}}
<section x-data="tesHasil({{ (int) $id }})" class="mx-auto max-w-3xl px-5 py-12 sm:px-8 lg:py-16">

    <div x-show="flash" x-cloak x-text="flash" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700"></div>

    <div x-show="memuat" class="space-y-4">
        <div class="h-9 w-1/2 animate-pulse rounded bg-blush-100"></div>
        <div class="h-40 animate-pulse rounded-3xl bg-blush-100"></div>
    </div>

    <div x-show="galat" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
        <p class="text-sm text-red-700" x-text="galat"></p>
        <a href="{{ route('tes.riwayat') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">Lihat riwayat tesku</a>
    </div>

    <template x-if="h">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Hasil tes</p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight" x-text="h.nama"></h1>
            <p class="mt-2 text-sm text-gray-500">Dikerjakan pada <span x-text="h.waktu"></span></p>

            {{-- Skor --}}
            <div class="mt-8 rounded-3xl bg-blush-100 p-6 sm:p-8">
                <p class="text-sm font-semibold text-gray-600">Total poin</p>
                <p class="mt-1 text-5xl font-extrabold text-primary">
                    <span x-text="h.total"></span>
                    <span class="text-xl font-semibold text-gray-400" x-text="'/ ' + h.maks"></span>
                </p>

                <div class="mt-5 h-3 overflow-hidden rounded-full bg-white">
                    <div class="h-full rounded-full bg-primary" :style="'width: ' + h.persen + '%'"></div>
                </div>
                <p class="mt-2 text-xs text-gray-500" x-text="h.persen + '% dari poin maksimal'"></p>
            </div>

            {{-- Pernyataan penting --}}
            <div class="mt-5 rounded-2xl border border-blush-200 bg-white p-5 text-sm leading-6 text-gray-600">
                Hasil ini adalah <strong>skrining awal</strong>, bukan diagnosis. Untuk pemahaman yang lebih tepat
                tentang kondisimu, kamu bisa berbicara langsung dengan psikolog profesional.
            </div>

            {{-- Aksi --}}
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url('/konsultasi') }}"
                   class="rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary-dark">
                    Konsultasi dengan psikolog
                </a>
                <a :href="h.linkUlang"
                   class="rounded-xl border border-gray-200 bg-white px-6 py-3 font-semibold text-ink transition hover:border-primary hover:text-primary">
                    Ulangi tes
                </a>
                <a href="{{ route('tes.riwayat') }}"
                   class="rounded-xl border border-gray-200 bg-white px-6 py-3 font-semibold text-ink transition hover:border-primary hover:text-primary">
                    Riwayat tesku
                </a>
            </div>

            {{-- Rincian jawaban --}}
            <h2 class="mt-12 text-xl font-bold">Rincian jawabanmu</h2>

            <div class="mt-4 divide-y divide-blush-200 overflow-hidden rounded-2xl border border-blush-200 bg-white">
                <template x-for="(j, i) in h.rincian" :key="i">
                    <div class="flex items-start justify-between gap-4 p-4 sm:p-5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink">
                                <span class="text-primary" x-text="(i + 1) + '.'"></span>
                                <span x-text="j.pertanyaan"></span>
                            </p>
                            <p class="mt-1 text-sm text-gray-600" x-text="j.jawaban"></p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blush-100 px-3 py-1 text-xs font-bold text-primary" x-text="j.poin + ' poin'"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>
</section>
@endsection