@extends('layouts.app')

@section('title', 'Riwayat Konsultasi - MentalCare')

@php
    $konsultasi = collect($konsultasi ?? []);
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10 w-full">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Konsultasi Saya</h1>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($konsultasi->isEmpty())
        <div class="text-center py-10 bg-white rounded-xl border border-gray-100">
            <p class="text-gray-500">Kamu belum pernah membuat janji konsultasi.</p>
            <a href="{{ route('konsultasi.index') }}" class="mt-4 inline-block bg-rose-600 text-white px-5 py-2 rounded-lg text-sm">Buat Jadwal Sekarang</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($konsultasi as $k)
                @php
                    $kId         = data_get($k, 'id');
                    $namaPsikolog = data_get($k, 'psikolog.nama', 'Psikolog');
                    $status      = data_get($k, 'status_konsultasi', '-');
                    $tglJadwal   = data_get($k, 'jadwal.tanggal');
                    $jamMulai    = data_get($k, 'jadwal.jam_mulai');
                    $jamSelesai  = data_get($k, 'jadwal.jam_selesai');
                    $totalBiaya  = (int) data_get($k, 'total_biaya', 0);
                    $buktiBayar  = data_get($k, 'pembayaran.bukti_pembayaran');

                    $rawBatas = data_get($k, 'batas_pembayaran');
                    $batasBayar = $rawBatas
                        ? \Illuminate\Support\Carbon::parse($rawBatas)
                        : \Illuminate\Support\Carbon::parse(data_get($k, 'created_at', now()))->addHour();
                @endphp

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-bold text-gray-900">{{ $namaPsikolog }}</h3>
                            <span class="text-xs px-2 py-1 rounded font-semibold 
                                {{ $status == 'Menunggu Pembayaran' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $status == 'Dikonfirmasi' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $status == 'Selesai' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $status == 'Batal' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $status }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            📅 {{ $tglJadwal ? \Carbon\Carbon::parse($tglJadwal)->translatedFormat('d F Y') : '-' }}
                            @if ($jamMulai && $jamSelesai)
                                | ⏰ {{ \Carbon\Carbon::parse($jamMulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jamSelesai)->format('H:i') }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Total Tagihan: Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                        
                        @if($status == 'Menunggu Pembayaran' && empty($buktiBayar))
                            <p class="text-xs text-amber-600 font-medium mt-1">
                                ⏳ Batas bayar sampai {{ $batasBayar->format('H:i') }} WIB
                            </p>
                        @endif
                    </div>

                    @if($status == 'Menunggu Pembayaran' && empty($buktiBayar) && $kId)
                        <a href="{{ route('konsultasi.konfirmasi', $kId) }}" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-5 py-2 rounded-lg text-sm transition text-center w-full md:w-auto">
                            Lanjut Pembayaran
                        </a>
                    @elseif(!empty($buktiBayar) && $status == 'Menunggu Pembayaran')
                        <div class="px-4 py-2 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg text-sm text-center w-full md:w-auto">
                            Bukti sedang dicek
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection