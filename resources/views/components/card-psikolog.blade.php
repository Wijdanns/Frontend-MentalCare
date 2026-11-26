@props(['psikolog'])

@php
    $id           = data_get($psikolog, 'id');
    $nama         = data_get($psikolog, 'nama', 'Psikolog');
    $email        = data_get($psikolog, 'email', '');
    $spesialisasi = data_get($psikolog, 'spesialis', '');
    $deskripsi    = data_get($psikolog, 'deskripsi_psikolog') ?: data_get($psikolog, 'deskripsi', '');
    $fotoProfil   = data_get($psikolog, 'foto_profil') ?: data_get($psikolog, 'foto');
    $tersedia     = (bool) data_get($psikolog, 'tersedia', false);

    $tarif = (int) (
        data_get($psikolog, 'tarif')
        ?: data_get($psikolog, 'biaya')
        ?: data_get($psikolog, 'biaya_konsultasi')
        ?: data_get($psikolog, 'tarif_konsultasi')
        ?: data_get($psikolog, 'harga')
        ?: 0
    );

    $inisial = \Illuminate\Support\Str::of($nama)
        ->replace(['Dr.', 'M.Psi.', ','], '')
        ->explode(' ')->filter()
        ->map(fn ($kata) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($kata, 0, 1)))
        ->take(2)->implode('') ?: 'PS';

    $fotoUrl = '';
    if ($fotoProfil) {
        $fotoUrl = \Illuminate\Support\Str::startsWith($fotoProfil, ['http://', 'https://'])
            ? $fotoProfil
            : route('foto-psikolog', basename($fotoProfil));
    }

    // Tombol "Pilih jadwal" menuju halaman konsultasi dengan psikolog terpilih
    $linkJadwal = route('konsultasi.index', array_filter(['psikolog' => $id]));

    // Data yang dikirim ke pop-up profil
    $profil = [
        'nama'         => $nama,
        'email'        => $email,
        'spesialisasi' => $spesialisasi,
        'tarif'        => 'Rp ' . number_format($tarif, 0, ',', '.'),
        'deskripsi'    => $deskripsi,
        'foto'         => $fotoUrl,
        'inisial'      => $inisial,
        'tersedia'     => $tersedia,
        'link_jadwal'  => $linkJadwal,
    ];
@endphp

<article x-data class="flex h-full flex-col overflow-hidden rounded-3xl border border-blush-200 bg-white">
    @if ($fotoUrl)
        <img src="{{ $fotoUrl }}" alt="Foto {{ $nama }}"
             class="aspect-[353/256] w-full object-cover" loading="lazy">
    @else
        <div class="flex aspect-[353/256] w-full items-center justify-center bg-gradient-to-br from-blush-100 to-blush-200">
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-white/80 text-2xl font-bold text-primary">{{ $inisial }}</span>
        </div>
    @endif

    <div class="flex flex-1 flex-col px-5 pb-5 pt-5 sm:px-6 sm:pb-6">
        @if ($tersedia)
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-blush-200/70 px-3 py-1 text-xs font-medium text-primary">
                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Tersedia
            </span>
        @else
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Tidak tersedia
            </span>
        @endif

        <h3 class="mt-3 text-lg font-bold">{{ $nama }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $spesialisasi }}</p>

        <div class="mt-auto">
            <div class="mt-6 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 text-sm">
                <span class="font-bold text-primary">Rp {{ number_format($tarif, 0, ',', '.') }}</span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm font-semibold">
                <button type="button" @click="$dispatch('buka-profil', @js($profil))"
                        class="rounded-xl border border-gray-200 px-4 py-3 text-center text-primary transition hover:border-primary">Lihat profil</button>

                @if ($tersedia)
                    <a href="{{ $linkJadwal }}" class="rounded-xl bg-primary px-4 py-3 text-center text-white transition hover:bg-primary-dark">Pilih jadwal</a>
                @else
                    <span class="cursor-not-allowed rounded-xl bg-gray-200 px-4 py-3 text-center text-gray-500">Belum tersedia</span>
                @endif
            </div>
        </div>
    </div>
</article>