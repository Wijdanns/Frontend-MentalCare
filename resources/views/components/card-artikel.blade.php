@props(['artikel', 'index' => 0])

@php
    $warnaSampul = ['bg-blush-200', 'bg-[#F4EBDD]', 'bg-[#E7E5F2]', 'bg-[#E3EEF3]'][$index % 4];

    $artikelId = data_get($artikel, 'id');
    $link      = $artikelId ? route('artikel.show', $artikelId) : '#';

    $thumbnailPath = data_get($artikel, 'thumbnail');
    $thumbnail     = !empty($thumbnailPath)
        ? (\Illuminate\Support\Str::startsWith($thumbnailPath, ['http://', 'https://'])
            ? $thumbnailPath
            : \App\Services\ApiService::backendUrl() . '/storage/' . ltrim($thumbnailPath, '/'))
        : null;

    $judul        = data_get($artikel, 'judul', 'Artikel');
    $namaKategori = data_get($artikel, 'kategori.nama_kategori') ?? data_get($artikel, 'nama_kategori', '');

    $tanggal     = data_get($artikel, 'tanggal_publikasi') ?? data_get($artikel, 'created_at');
    $tanggalTeks = $tanggal
        ? \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('j F Y')
        : '';

    $isi       = data_get($artikel, 'isi') ?? data_get($artikel, 'konten', '');
    $ringkasan = data_get($artikel, 'ringkasan')
        ?: \Illuminate\Support\Str::limit(strip_tags($isi), 140);
@endphp

<article class="flex h-full flex-col overflow-hidden rounded-3xl border border-blush-200 bg-white">
    <a href="{{ $link }}" class="block" tabindex="-1" aria-hidden="true">
        @if ($thumbnail)
            <img src="{{ $thumbnail }}" alt="Sampul {{ $judul }}"
                 class="aspect-[2/1] w-full object-cover" loading="lazy">
        @else
            <div class="flex aspect-[2/1] w-full items-center justify-center {{ $warnaSampul }}">
                <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/80 text-primary shadow-sm">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col px-5 pb-5 pt-5 sm:px-6 sm:pb-6">
        <div class="flex items-center justify-between gap-3 text-xs">
            <span class="font-bold uppercase tracking-wider text-primary">{{ $namaKategori }}</span>
            <span class="shrink-0 text-gray-400">{{ $tanggalTeks }}</span>
        </div>

        <h3 class="mt-3 text-lg font-bold leading-snug">
            <a href="{{ $link }}" class="transition hover:text-primary">{{ $judul }}</a>
        </h3>

        <p class="mt-3 line-clamp-3 text-sm leading-7 text-gray-500">{{ $ringkasan }}</p>

        <div class="mt-auto pt-5">
            <div class="border-t border-gray-100 pt-4">
                <a href="{{ $link }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary-dark">
                    Baca selengkapnya
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>
</article>