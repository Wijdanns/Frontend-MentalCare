@extends('layouts.app')

@section('title', 'Tes Psikologi | MentalCare')

@php
    $isLoggedIn = session()->has('api_token');
    $daftarTes  = collect($daftarTes ?? []);
@endphp

@section('content')
<section class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Tes Psikologi</p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Kenali kondisimu</h1>
            <p class="mt-3 max-w-xl text-gray-600">
                Pilih tes di bawah dan jawab dengan jujur sesuai yang kamu rasakan.
                Tidak ada jawaban benar atau salah.
            </p>
        </div>
        @if ($isLoggedIn)
            <a href="{{ route('tes.riwayat') }}"
               class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-ink transition hover:border-primary hover:text-primary">
                Riwayat tesku
            </a>
        @endif
    </div>

    @if (session('error'))
        <div class="mt-6 rounded-xl bg-blush-100 p-4 text-sm text-gray-700">
            @if (! $isLoggedIn)
                Kamu perlu <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">masuk</a>
                atau <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">daftar</a>
                untuk mengerjakan tes. Hasilnya tersimpan di riwayatmu.
            @else
                {{ session('error') }}
            @endif
        </div>
    @endif

    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($daftarTes as $tes)
            @php
                $tesId           = data_get($tes, 'id');
                $namaTes         = data_get($tes, 'nama_tes', 'Tes Psikologi');
                $deskripsi       = data_get($tes, 'deskripsi') ?? 'Tes untuk mengenali kondisi emosionalmu.';
                $pertanyaanCount = data_get($tes, 'pertanyaan_count', 0);
            @endphp

            <div class="flex flex-col rounded-2xl border border-blush-200 bg-white p-6 transition hover:border-primary hover:shadow-lg">
                <h2 class="text-lg font-bold text-ink">{{ $namaTes }}</h2>

                <p class="mt-2 flex-1 text-sm leading-6 text-gray-600">
                    {{ \Illuminate\Support\Str::limit($deskripsi, 120) }}
                </p>

                <div class="mt-5 flex items-center justify-between">
                    <span class="rounded-full bg-blush-100 px-3 py-1 text-xs font-semibold text-primary">
                        {{ $pertanyaanCount }} pertanyaan
                    </span>
                    <a href="{{ $tesId ? route('tes.show', $tesId) : '#' }}"
                       class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                        {{ $isLoggedIn ? 'Mulai tes' : 'Masuk & mulai tes' }}
                    </a>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-2xl border border-dashed border-blush-200 bg-white p-10 text-center text-gray-500">
                Belum ada tes yang tersedia.
            </p>
        @endforelse
    </div>

    <p class="mt-10 text-center text-xs text-gray-400">
        Hasil tes ini adalah skrining awal dan bukan diagnosis.
    </p>
</section>
@endsection