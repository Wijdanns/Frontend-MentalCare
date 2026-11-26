@extends('layouts.app')

@php
    $tesId          = data_get($tes, 'id');
    $namaTes        = data_get($tes, 'nama_tes', 'Tes Psikologi');
    $deskripsi      = data_get($tes, 'deskripsi');
    $pertanyaanList = collect(data_get($tes, 'pertanyaan') ?? data_get($tes, 'pertanyaans', []));
    $totalSoal      = $pertanyaanList->count();
@endphp

@section('title', $namaTes . ' | MentalCare')

@section('content')
<section class="mx-auto max-w-3xl px-5 py-12 sm:px-8 lg:py-16">

    <a href="{{ route('tes.index') }}" class="text-sm font-medium text-gray-500 hover:text-primary">&larr; Semua tes</a>

    <h1 class="mt-4 text-3xl font-extrabold tracking-tight">{{ $namaTes }}</h1>
    @if ($deskripsi)
        <p class="mt-3 text-gray-600">{{ $deskripsi }}</p>
    @endif

    {{-- Progress (menempel di bawah navbar saat scroll) --}}
    <div class="sticky top-16 z-10 -mx-5 mt-6 border-b border-blush-200 bg-white/95 px-5 py-3 backdrop-blur sm:top-20 sm:mx-0 sm:rounded-xl sm:border sm:px-4">
        <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
            <span>Terjawab <span id="jumlah-terjawab">0</span> dari {{ $totalSoal }}</span>
            <span id="persen-terjawab">0%</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-blush-100">
            <div id="bar-terjawab" class="h-full w-0 rounded-full bg-primary transition-all duration-300"></div>
        </div>
    </div>

    @if (session('error'))
        <div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if ($errors->has('jawaban') || $errors->has('jawaban.*'))
        <div class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first('jawaban') ?: $errors->first('jawaban.*') }}
        </div>
    @endif

    <form id="form-tes" method="POST" action="{{ $tesId ? route('tes.submit', $tesId) : '#' }}" class="mt-6 space-y-5">
        @csrf

        @forelse ($pertanyaanList as $p)
            @php
                $pId            = data_get($p, 'id');
                $teksPertanyaan = data_get($p, 'pertanyaan', '');
                $jawabanList    = collect(
                    data_get($p, 'jawabanPsikologis') ?? data_get($p, 'jawaban_psikologis', [])
                );
                $oldJawaban = old('jawaban.' . $pId);
            @endphp

            <fieldset class="rounded-2xl border border-blush-200 bg-white p-5 sm:p-6">
                <legend class="sr-only">Pertanyaan {{ $loop->iteration }}</legend>

                <p class="font-semibold text-ink">
                    <span class="mr-1 text-primary">{{ $loop->iteration }}.</span>{{ $teksPertanyaan }}
                </p>

                <div class="mt-4 grid gap-2">
                    @foreach ($jawabanList as $j)
                        @php
                            $jId         = data_get($j, 'id');
                            $teksJawaban = data_get($j, 'jawaban', '');
                        @endphp

                        <label class="block cursor-pointer">
                            <input type="radio"
                                   name="jawaban[{{ $pId }}]"
                                   value="{{ $jId }}"
                                   class="peer sr-only"
                                   @checked($oldJawaban !== null && (int) $oldJawaban === (int) $jId)
                                   required>
                            <span class="block rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 transition
                                         hover:border-primary
                                         peer-checked:border-primary peer-checked:bg-blush-100 peer-checked:font-semibold peer-checked:text-primary
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">
                                {{ $teksJawaban }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @empty
            <div class="rounded-2xl border border-dashed border-blush-200 bg-white p-10 text-center text-gray-500">
                Belum ada pertanyaan untuk tes ini.
            </div>
        @endforelse

        @if ($totalSoal > 0)
            <button type="submit"
                    class="w-full rounded-xl bg-primary px-6 py-3.5 font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary-dark">
                Lihat hasil
            </button>
        @endif

        <p class="text-center text-xs text-gray-400">
            Hasil tes ini adalah skrining awal dan bukan diagnosis.
        </p>
    </form>
</section>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('form-tes');
    const total = {{ $totalSoal }};

    function updateProgress() {
        const terjawab = new Set(
            [...form.querySelectorAll('input[type=radio]:checked')].map(r => r.name)
        ).size;
        const persen = total > 0 ? Math.round((terjawab / total) * 100) : 0;

        document.getElementById('jumlah-terjawab').textContent = terjawab;
        document.getElementById('persen-terjawab').textContent = persen + '%';
        document.getElementById('bar-terjawab').style.width = persen + '%';
    }

    form.addEventListener('change', updateProgress);
    updateProgress();
</script>
@endpush