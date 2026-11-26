@extends('layouts.app')

@php
    $isLoggedIn = session()->has('api_token');
    $role       = session('role') ?? data_get(session('user'), 'role', 'pasien');

    $psikologId   = data_get($psikolog, 'id');
    $nama         = data_get($psikolog, 'nama', 'Psikolog');
    $email        = data_get($psikolog, 'email', '-');
    $spesialisasi = data_get($psikolog, 'spesialisasi', '');
    $deskripsi    = data_get($psikolog, 'deskripsi_psikolog') ?? data_get($psikolog, 'deskripsi', '');
    $tarif        = (int) (data_get($psikolog, 'tarif') ?? data_get($psikolog, 'biaya', 0));
    $fotoProfil   = data_get($psikolog, 'foto_profil') ?? data_get($psikolog, 'foto');

    if ($fotoProfil) {
        $fotoUrl = \Illuminate\Support\Str::startsWith($fotoProfil, ['http://', 'https://'])
            ? $fotoProfil
            : route('foto-psikolog', basename($fotoProfil));
    } else {
        $fotoUrl = 'https://ui-avatars.com/api/?size=600&background=fce7ef&color=e11d48&name=' . urlencode($nama);
    }

    $jadwalPerTanggal = (array) ($jadwalPerTanggal ?? []);
    $adaJadwal        = collect($jadwalPerTanggal)->flatten(1)->isNotEmpty();
    $tersedia         = (bool) data_get($psikolog, 'tersedia', $adaJadwal);
@endphp

@section('title', $nama . ' | MentalCare')

@section('content')
<section class="mx-auto max-w-[1168px] px-5 py-12 sm:px-8 lg:py-16">

    <a href="{{ route('psikolog.index') }}" class="text-sm font-medium text-gray-500 hover:text-primary">&larr; Semua psikolog</a>

    <div class="mt-6 grid gap-8 lg:grid-cols-[360px_1fr] lg:gap-12">

        {{-- Kolom kiri: foto + ringkasan --}}
        <aside>
            <div class="overflow-hidden rounded-3xl bg-blush-200/80 p-3 shadow-2xl shadow-primary/10">
                <img src="{{ $fotoUrl }}" alt="Foto {{ $nama }}"
                     class="aspect-[4/5] w-full rounded-[22px] object-cover">
            </div>

            <div class="mt-5 rounded-2xl border border-blush-200 bg-white p-5">
                <p class="text-xs text-gray-500">Biaya per sesi</p>
                <p class="mt-1 text-2xl font-extrabold text-ink">Rp {{ number_format($tarif, 0, ',', '.') }}</p>

                <p class="mt-4 text-xs text-gray-500">Email</p>
                <p class="mt-1 break-all text-sm font-medium text-ink">{{ $email }}</p>
            </div>
        </aside>

        {{-- Kolom kanan: profil + jadwal --}}
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $nama }}</h1>

                @if ($tersedia || $adaJadwal)
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Tersedia</span>
                @else
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">Belum ada jadwal</span>
                @endif
            </div>

            <p class="mt-2 text-lg font-semibold text-primary">{{ $spesialisasi }}</p>

            <h2 class="mt-8 text-lg font-bold">Tentang</h2>
            <p class="mt-2 whitespace-pre-line leading-7 text-gray-600">
                {{ $deskripsi ?: 'Psikolog ini belum menambahkan deskripsi.' }}
            </p>

            {{-- Jadwal --}}
            <h2 class="mt-10 text-lg font-bold">Jadwal tersedia</h2>

            @if (! $isLoggedIn)
                <div class="mt-3 rounded-xl bg-blush-100 p-4 text-sm text-gray-700">
                    <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
                    atau
                    <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">daftar</a>
                    untuk memesan konsultasi.
                </div>
            @elseif ($role !== 'pasien')
                <div class="mt-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                    Pemesanan konsultasi hanya tersedia untuk akun pasien.
                </div>
            @endif

            <div class="mt-4 space-y-4">
                @forelse ($jadwalPerTanggal as $tanggal => $slots)
                    <div class="rounded-2xl border border-blush-200 bg-white p-5">
                        <p class="font-semibold text-ink">
                            {{ \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($slots as $slot)
                                @php
                                    $slotId     = data_get($slot, 'id', '');
                                    $jamMulai   = substr((string) data_get($slot, 'jam_mulai', ''), 0, 5);
                                    $jamSelesai = substr((string) data_get($slot, 'jam_selesai', ''), 0, 5);
                                    $bookingUrl = $isLoggedIn
                                        ? route('konsultasi.index', array_filter([
                                            'psikolog' => $psikologId,
                                            'jadwal'   => $slotId,
                                        ]))
                                        : route('login');
                                @endphp
                                <a href="{{ $bookingUrl }}"
                                   class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-primary hover:bg-blush-100 hover:text-primary">
                                    {{ $jamMulai }} - {{ $jamSelesai }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-blush-200 bg-white p-8 text-center text-gray-500">
                        Belum ada jadwal tersedia untuk psikolog ini. Coba lagi nanti atau
                        <a href="{{ route('psikolog.index') }}" class="font-semibold text-primary hover:underline">pilih psikolog lain</a>.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection