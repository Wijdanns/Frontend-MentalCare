@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran - MentalCare')

@php
    $konsultasiId     = data_get($konsultasi, 'id');
    $statusKonsultasi = data_get($konsultasi, 'status_konsultasi', '-');
    $buktiPembayaran  = data_get($konsultasi, 'pembayaran.bukti_pembayaran');
    $metodePembayaran = data_get($konsultasi, 'pembayaran.metode_pembayaran', 'Belum dipilih');
    $namaPsikolog     = data_get($konsultasi, 'psikolog.nama', '-');
    $tanggalJadwal    = data_get($konsultasi, 'jadwal.tanggal');
    $jamMulai         = data_get($konsultasi, 'jadwal.jam_mulai');
    $jamSelesai       = data_get($konsultasi, 'jadwal.jam_selesai');
    $totalBiaya       = (int) data_get($konsultasi, 'total_biaya', 0);

    $rawBatas = data_get($konsultasi, 'batas_pembayaran');
    $batasBayar = $rawBatas
        ? \Illuminate\Support\Carbon::parse($rawBatas)
        : \Illuminate\Support\Carbon::parse(data_get($konsultasi, 'created_at', now()))->addHour();
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10 w-full">
    
    <!-- HEADER -->
    <div class="text-center mb-8">
        <span class="inline-block bg-pink-100 text-rose-600 text-xs font-semibold px-4 py-1.5 rounded-full mb-4">Langkah Terakhir</span>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Selesaikan Pembayaran</h1>
        <p class="text-gray-500 text-sm max-w-md mx-auto">Selesaikan pembayaran agar sesi konselingmu dengan psikolog dapat segera dikonfirmasi.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 text-center font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- COUNTDOWN BATAS PEMBAYARAN (1 JAM) -->
    @if($statusKonsultasi === 'Menunggu Pembayaran' && empty($buktiPembayaran))
        <div x-data="{
                deadline: {{ $batasBayar->timestamp * 1000 }},
                sisaText: 'Memuat...',
                init() {
                    this.updateTimer();
                    setInterval(() => this.updateTimer(), 1000);
                },
                updateTimer() {
                    const diff = this.deadline - Date.now();
                    if (diff <= 0) {
                        this.sisaText = '00:00';
                        window.location.reload();
                        return;
                    }
                    const menit = Math.floor(diff / 60000);
                    const detik = Math.floor((diff % 60000) / 1000);
                    this.sisaText = String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');
                }
             }"
             class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm">
            <div class="text-amber-800">
                <span class="font-semibold">Batas Waktu Pembayaran:</span>
                {{ $batasBayar->translatedFormat('d M Y, H:i') }} WIB
            </div>
            <div class="bg-amber-600 text-white font-mono font-bold px-3 py-1 rounded-lg text-sm">
                Sisa Waktu: <span x-text="sisaText"></span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- BAGIAN KIRI: RINGKASAN KONSULTASI -->
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm h-fit">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-3">Ringkasan Pesanan</h3>
            
            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Psikolog</p>
                    <p class="font-semibold text-gray-800">{{ $namaPsikolog }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Jadwal Sesi</p>
                    <p class="font-semibold text-gray-800">
                        {{ $tanggalJadwal ? \Carbon\Carbon::parse($tanggalJadwal)->translatedFormat('l, d F Y') : '-' }}
                    </p>
                    @if ($jamMulai && $jamSelesai)
                        <p class="text-gray-600 mt-0.5">
                            {{ \Carbon\Carbon::parse($jamMulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($jamSelesai)->format('H:i') }} WIB
                        </p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Status</p>
                    <span class="inline-block bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded">
                        {{ $statusKonsultasi }}
                    </span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                <span class="text-gray-500 font-medium text-sm">Total Tagihan</span>
                <span class="text-xl font-bold text-rose-600">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- BAGIAN KANAN: INSTRUKSI & FORM PEMBAYARAN -->
        <div class="space-y-6">
            
            <!-- INSTRUKSI PEMBAYARAN -->
            <div class="bg-pink-50/40 p-6 rounded-2xl border border-pink-100">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Instruksi Pembayaran</h3>
                <p class="text-xs text-gray-500 mb-4">Metode yang dipilih: <strong class="text-gray-700">{{ $metodePembayaran }}</strong></p>

                @if($metodePembayaran == 'Transfer Bank')
                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-500 font-semibold mb-2">Transfer ke Rekening BCA:</p>
                        <div class="flex flex-col bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="font-mono font-bold text-gray-900 text-lg tracking-wider">123-456-7890</span>
                            <span class="text-sm text-rose-600 font-semibold">a.n MentalCare Indonesia</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-3 leading-relaxed">
                            Pastikan nominal transfer tepat hingga 3 digit terakhir. Simpan bukti transfer untuk diunggah.
                        </p>
                    </div>
                @elseif($metodePembayaran == 'E-Wallet')
                    <div class="bg-white p-4 rounded-xl border border-gray-200 text-center">
                        <p class="text-xs text-gray-500 font-semibold mb-3">Scan QRIS Berikut:</p>
                        <div class="bg-gray-100 w-40 h-40 mx-auto rounded-xl flex items-center justify-center border border-gray-200 overflow-hidden">
                            <img src="{{ asset('images/qris-dummy.png') }}" alt="QRIS Code" class="w-full h-full object-cover">
                        </div>
                        <p class="text-xs text-gray-400 mt-3 leading-relaxed">
                            Scan menggunakan aplikasi E-Wallet (Gopay, OVO, Dana) atau Mobile Banking favoritmu.
                        </p>
                    </div>
                @else
                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                        <p class="text-sm text-gray-500">Silakan hubungi admin jika metode pembayaran tidak valid.</p>
                    </div>
                @endif
            </div>

            <!-- FORM UPLOAD BUKTI -->
            <form action="{{ route('konsultasi.bayar', $konsultasiId) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                @csrf
                <h3 class="text-sm font-bold text-gray-900 mb-3">Upload Bukti Pembayaran</h3>
                
                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-2">Unggah foto/screenshot bukti pembayaran (JPG, PNG, PDF)</label>
                    <input type="file" name="bukti_pembayaran" required accept="image/*,.pdf"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-rose-600 hover:file:bg-pink-100 border border-gray-200 rounded-lg p-1 focus:outline-none">
                    @error('bukti_pembayaran')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Kirim Bukti Pembayaran
                </button>
                
                <a href="{{ route('konsultasi.riwayat') }}" class="block text-center w-full border border-gray-200 text-gray-600 hover:bg-gray-50 font-semibold py-2.5 rounded-lg text-sm transition mt-3">
                    Bayar Nanti (Lihat Riwayat)
                </a>
            </form>

        </div>
    </div>
</div>
@endsection