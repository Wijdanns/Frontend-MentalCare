@extends('layouts.app')

@section('title', 'Akun Saya | MentalCare')

@php
    $namaUser   = data_get($user, 'nama', 'User');
    $emailUser  = data_get($user, 'email', '-');
    $roleUser   = data_get($user, 'role', 'pasien');
    $profilUser = data_get($user, 'profil');

    $fotoProfilUrl = null;
    if ($profilUser) {
        $fotoProfilUrl = \Illuminate\Support\Str::startsWith($profilUser, ['http://', 'https://'])
            ? $profilUser
            : \App\Services\ApiService::backendUrl() . '/storage/' . ltrim($profilUser, '/');
    }

    $riwayatKonsultasi = collect($riwayatKonsultasi ?? []);
    $riwayatTes        = collect($riwayatTes ?? []);
@endphp

@section('content')
<section class="mx-auto max-w-[1168px] px-5 py-10 sm:px-8">
    <h1 class="text-3xl font-extrabold text-ink">Akun Saya</h1>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        {{-- Card Informasi Profil & Form Edit --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-blush-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col items-center text-center">
                    @if ($fotoProfilUrl)
                        <img src="{{ $fotoProfilUrl }}" alt="{{ $namaUser }}" class="h-24 w-24 rounded-full object-cover shadow">
                    @else
                        <span class="flex h-24 w-24 items-center justify-center rounded-full bg-primary text-3xl font-bold text-white shadow">
                            {{ strtoupper(mb_substr($namaUser, 0, 1)) }}
                        </span>
                    @endif
                    <h2 class="mt-4 text-xl font-bold text-ink">{{ $namaUser }}</h2>
                    <p class="text-sm text-gray-500">{{ $emailUser }}</p>
                    <span class="mt-2 inline-block rounded-full bg-blush-100 px-3 py-1 text-xs font-semibold capitalize text-primary">
                        {{ $roleUser }}
                    </span>
                </div>

                <hr class="my-6 border-blush-200">

                <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $namaUser) }}" class="mt-1 w-full rounded-xl border border-gray-200 p-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ubah Foto Profil</label>
                        <input type="file" name="profil" accept="image/*" class="mt-1 w-full text-xs text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-blush-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary hover:file:bg-blush-200">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        {{-- Tab Riwayat --}}
        <div class="space-y-8 lg:col-span-2">
            {{-- Riwayat Konsultasi --}}
            <div class="rounded-2xl border border-blush-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-ink">Riwayat Konsultasi</h3>

                <div class="mt-4 space-y-4">
                    @forelse ($riwayatKonsultasi as $konsultasi)
                        @php
                            $namaPsikolog = data_get($konsultasi, 'psikolog.nama', 'Psikolog');
                            $fotoPsikolog = data_get($konsultasi, 'psikolog.foto') ?? data_get($konsultasi, 'psikolog.foto_profil');

                            if ($fotoPsikolog) {
                                $fotoPsikologUrl = \Illuminate\Support\Str::startsWith($fotoPsikolog, ['http://', 'https://'])
                                    ? $fotoPsikolog
                                    : route('foto-psikolog', basename($fotoPsikolog));
                            } else {
                                $fotoPsikologUrl = 'https://ui-avatars.com/api/?size=128&background=fce7ef&color=e11d48&name=' . urlencode($namaPsikolog);
                            }

                            $tanggalJadwal = data_get($konsultasi, 'jadwal.tanggal');
                            $jamMulai      = data_get($konsultasi, 'jadwal.jam_mulai');
                            $status        = data_get($konsultasi, 'status_konsultasi', '-');
                            $totalBiaya    = (int) data_get($konsultasi, 'total_biaya', 0);
                        @endphp

                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-blush-200/70 p-4 hover:border-primary/50 transition">
                            <div class="flex items-center gap-3">
                                <img src="{{ $fotoPsikologUrl }}" alt="{{ $namaPsikolog }}" class="h-12 w-12 rounded-full object-cover">
                                <div>
                                    <h4 class="font-bold text-ink">{{ $namaPsikolog }}</h4>
                                    <p class="text-xs text-gray-500">
                                        {{ $tanggalJadwal ? \Carbon\Carbon::parse($tanggalJadwal)->format('d M Y') . ($jamMulai ? ' (' . substr((string) $jamMulai, 0, 5) . ')' : '') : '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $status === 'Dikonfirmasi' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $status === 'Menunggu Pembayaran' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $status === 'Batal' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $status }}
                                </span>
                                <p class="mt-1 text-sm font-bold text-primary">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-blush-200 p-6 text-center text-sm text-gray-500">
                            Belum ada riwayat konsultasi.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat Tes Psikologi --}}
            <div class="rounded-2xl border border-blush-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-ink">Riwayat Tes Psikologi</h3>

                <div class="mt-4 space-y-4">
                    @forelse ($riwayatTes as $tes)
                        @php
                            $namaTes = data_get($tes, 'tesPsikologis.nama_tes')
                                ?? data_get($tes, 'tes_psikologis.nama_tes')
                                ?? data_get($tes, 'nama_tes', 'Tes Psikologi');

                            $createdAt  = data_get($tes, 'created_at');
                            $tanggalTes = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d M Y') : '-';
                            $totalPoin  = data_get($tes, 'total_poin', 0);
                        @endphp

                        <div class="flex items-center justify-between rounded-xl border border-blush-200/70 p-4">
                            <div>
                                <h4 class="font-bold text-ink">{{ $namaTes }}</h4>
                                <p class="text-xs text-gray-500">Tanggal: {{ $tanggalTes }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-500">Total Poin</span>
                                <p class="text-lg font-bold text-primary">{{ $totalPoin }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-blush-200 p-6 text-center text-sm text-gray-500">
                            Belum ada riwayat tes psikologi.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection