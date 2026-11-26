@extends('layouts.app')

@section('title', 'Artikel | MentalCare')

@php
    $kategoriAktifId   = data_get($kategoriAktif, 'id');
    $kategoriAktifNama = data_get($kategoriAktif, 'nama_kategori');
@endphp

@section('content')
<section class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Artikel</p>
    <h1 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
        {{ $kategoriAktifNama ?: 'Bacaan untuk kesehatan mentalmu' }}
    </h1>
    <p class="mt-3 max-w-xl text-gray-600">
        Tulisan ringan seputar stres, kecemasan, relasi, dan cara merawat diri.
    </p>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('artikel.index') }}" class="mt-8 flex gap-2">
        @if ($kategoriAktifId)
            <input type="hidden" name="kategori" value="{{ $kategoriAktifId }}">
        @endif

        <input type="text" name="q" value="{{ $q }}" placeholder="Cari artikel..."
               class="w-full max-w-md rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
        <button type="submit"
                class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            Cari
        </button>
        @if ($q !== '')
            <a href="{{ route('artikel.index', array_filter(['kategori' => $kategoriAktifId])) }}"
               class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-primary hover:text-primary">
                Reset
            </a>
        @endif
    </form>

    {{-- Filter kategori --}}
    <div class="mt-5 flex flex-wrap gap-2">
        <a href="{{ route('artikel.index', array_filter(['q' => $q])) }}"
           class="rounded-full px-4 py-1.5 text-sm font-medium transition
                  {{ $kategoriAktifId ? 'border border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary' : 'bg-primary text-white' }}">
            Semua
        </a>

        @foreach ($daftarKategori as $kategori)
            @php
                $katId    = data_get($kategori, 'id');
                $katNama  = data_get($kategori, 'nama_kategori', '');
                $katCount = data_get($kategori, 'artikel_count');
                $aktif    =$kategoriAktifId && $kategoriAktifId ==$katId;
            @endphp
            <a href="{{ route('artikel.index', array_filter(['kategori' => $katId, 'q' =>$q])) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium transition
                      {{ $aktif ? 'bg-primary text-white' : 'border border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary' }}">
                {{ $katNama }}
                @if (! is_null($katCount))
                    <span class="{{ $aktif ? 'text-white/80' : 'text-gray-400' }}">({{ $katCount }})</span>
                @endif
            </a>
        @endforeach
    </div>

    <p class="mt-6 text-sm text-gray-500">Menampilkan {{ $artikels->total() }} artikel</p>

    {{-- Daftar --}}
    <div class="mt-4 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($artikels as $artikel)
            @php
                $artikelId    = data_get($artikel, 'id');$judul        = data_get($artikel, 'judul', '');$ringkasan    = data_get($artikel, 'ringkasan', '');$waktuBaca    = data_get($artikel, 'waktu_baca', 1);$thumbnail    = data_get($artikel, 'thumbnail');$idKategori   = data_get($artikel, 'id_kategori') ?? data_get($artikel, 'kategori.id');
                $namaKategori = data_get($artikel, 'kategori.nama_kategori', 'Umum');
                $penulis      = data_get($artikel, 'user.nama');
                $tglPublikasi = data_get($artikel, 'tanggal_publikasi');

                $thumbnailUrl = '';
                if ($thumbnail) {
                    $thumbnailUrl = \Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://'])
                        ? $thumbnail
                        : route('foto-artikel', basename($thumbnail));
                }

                $detailUrl = $artikelId ? route('artikel.show', $artikelId) : '#';
            @endphp

            <article class="flex flex-col overflow-hidden rounded-2xl border border-blush-200 bg-white transition hover:border-primary hover:shadow-lg">

                <a href="{{ $detailUrl }}" class="block">
                    @if ($thumbnailUrl)
                        <img src="{{ $thumbnailUrl }}" alt="{{ $judul }}"
                             class="aspect-[16/10] w-full object-cover" loading="lazy">
                    @else
                        <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-blush-100 via-blush-200 to-primary/20">
                            <svg class="h-12 w-12 text-primary/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                        </div>
                    @endif
                </a>

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-center gap-2 text-xs">
                        <a href="{{ route('artikel.index', array_filter(['kategori' => $idKategori])) }}"
                           class="rounded-full bg-blush-100 px-2.5 py-1 font-semibold text-primary hover:bg-blush-200">
                            {{ $namaKategori }}
                        </a>
                        <span class="text-gray-400">{{ $waktuBaca }} menit baca</span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold leading-snug text-ink">
                        <a href="{{ $detailUrl }}" class="hover:text-primary">{{ $judul }}</a>
                    </h2>

                    <p class="mt-2 flex-1 text-sm leading-6 text-gray-600">{{ $ringkasan }}</p>

                    <p class="mt-4 border-t border-blush-200 pt-3 text-xs text-gray-500">
                        {{ $tglPublikasi ? \Illuminate\Support\Carbon::parse($tglPublikasi)->translatedFormat('d F Y') : '-' }}
                        @if ($penulis) &middot; {{$penulis }} @endif
                    </p>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-blush-200 bg-white p-10 text-center">
                <p class="text-gray-500">Belum ada artikel yang sesuai.</p>
                <a href="{{ route('artikel.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">
                    Tampilkan semua artikel
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $artikels->links() }}
    </div>
</section>
@endsection
