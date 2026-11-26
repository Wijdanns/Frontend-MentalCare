@extends('layouts.app')

@php
    $namaTes = data_get($hasil, 'tesPsikologis.nama_tes')
        ?? data_get($hasil, 'tes_psikologis.nama_tes')
        ?? data_get($hasil, 'tes.nama_tes')
        ?? data_get($hasil, 'nama_tes', 'Tes Psikologi');

    $totalPoin = (int) (
        data_get($hasil, 'total_poin')
        ?? data_get($hasil, 'poin')
        ?? data_get($hasil, 'skor', 0)
    );

    $poinMaks  = (int) ($poinMaks ?? data_get($hasil, 'poin_maks', 0));
    $persen    = $poinMaks > 0 ? round($totalPoin / $poinMaks * 100) : 0;
    $createdAt = data_get($hasil, 'created_at') ?? data_get($hasil, 'tanggal');

    $jawabanList = collect(
        data_get($hasil, 'jawabanUser')
        ?? data_get($hasil, 'jawaban_user')
        ?? data_get($hasil, 'detail_jawaban', [])
    );

    $tesId = data_get($hasil, 'id_tes')
        ?? data_get($hasil, 'tes_id')
        ?? data_get($hasil, 'tesPsikologis.id')
        ?? data_get($hasil, 'tes_psikologis.id')
        ?? data_get($hasil, 'tes.id');
@endphp

@section('title', 'Hasil ' . $namaTes . ' | MentalCare')

@section('content')
<section class="mx-auto max-w-3xl px-5 py-12 sm:px-8 lg:py-16">

    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Hasil tes</p>
    <h1 class="mt-3 text-3xl font-extrabold tracking-tight">{{ $namaTes }}</h1>
    <p class="mt-2 text-sm text-gray-500">
        Dikerjakan pada {{ $createdAt ? \Illuminate\Support\Carbon::parse($createdAt)->translatedFormat('d F Y, H:i') : '-' }}
    </p>

    {{-- Skor --}}
    <div class="mt-8 rounded-3xl bg-blush-100 p-6 sm:p-8">
        <p class="text-sm font-semibold text-gray-600">Total poin</p>
        <p class="mt-1 text-5xl font-extrabold text-primary">
            {{ $totalPoin }}
            <span class="text-xl font-semibold text-gray-400">/ {{ $poinMaks }}</span>
        </p>

        <div class="mt-5 h-3 overflow-hidden rounded-full bg-white">
            <div class="h-full rounded-full bg-primary" style="width: {{ $persen }}%"></div>
        </div>
        <p class="mt-2 text-xs text-gray-500">{{ $persen }}% dari poin maksimal</p>
    </div>

    {{-- Pernyataan penting --}}
    <div class="mt-5 rounded-2xl border border-blush-200 bg-white p-5 text-sm leading-6 text-gray-600">
        Hasil ini adalah <strong>skrining awal</strong>, bukan diagnosis. Untuk pemahaman yang lebih tepat
        tentang kondisimu, kamu bisa berbicara langsung dengan psikolog profesional.
    </div>

    {{-- Aksi --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('konsultasi.index') }}"
           class="rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary-dark">
            Konsultasi dengan psikolog
        </a>
        @if ($tesId)
            <a href="{{ route('tes.show', $tesId) }}"
               class="rounded-xl border border-gray-200 bg-white px-6 py-3 font-semibold text-ink transition hover:border-primary hover:text-primary">
                Ulangi tes
            </a>
        @endif
        <a href="{{ route('tes.riwayat') }}"
           class="rounded-xl border border-gray-200 bg-white px-6 py-3 font-semibold text-ink transition hover:border-primary hover:text-primary">
            Riwayat tesku
        </a>
    </div>

    {{-- Rincian jawaban --}}
    <h2 class="mt-12 text-xl font-bold">Rincian jawabanmu</h2>

    <div class="mt-4 divide-y divide-blush-200 overflow-hidden rounded-2xl border border-blush-200 bg-white">
        @forelse ($jawabanList as $j)
            @php
                $teksPertanyaan = data_get($j, 'pertanyaanPsikologis.pertanyaan')
                    ?? data_get($j, 'pertanyaan_psikologis.pertanyaan')
                    ?? data_get($j, 'pertanyaan', '-');
                $teksJawaban = data_get($j, 'jawabanPsikologis.jawaban')
                    ?? data_get($j, 'jawaban_psikologis.jawaban')
                    ?? data_get($j, 'jawaban', '-');
                $poinItem = data_get($j, 'poin', 0);
            @endphp
            <div class="flex items-start justify-between gap-4 p-4 sm:p-5">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">
                        <span class="text-primary">{{ $loop->iteration }}.</span>
                        {{ $teksPertanyaan }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">{{ $teksJawaban }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-blush-100 px-3 py-1 text-xs font-bold text-primary">
                    {{ $poinItem }} poin
                </span>
            </div>
        @empty
            <p class="p-5 text-center text-sm text-gray-500">Rincian jawaban tidak tersedia.</p>
        @endforelse
    </div>
</section>
@endsection