@extends('layouts.app')

@section('title', 'Tes Psikologi | MentalCare')

@section('content')
{{-- Soal diambil dari API: GET /api/tes-psikologi/{id}. Jawaban dikirim ke POST /api/tes-psikologi/{id}/submit --}}
<section x-data="tesKerjakan({{ (int) $id }})" class="mx-auto max-w-3xl px-5 py-12 sm:px-8 lg:py-16">

    <a href="{{ route('tes.index') }}" class="text-sm font-medium text-gray-500 hover:text-primary">&larr; Semua tes</a>

    <div x-show="memuat" class="mt-6 space-y-4">
        <div class="h-9 w-1/2 animate-pulse rounded bg-blush-100"></div>
        <div class="h-40 animate-pulse rounded-2xl bg-blush-100"></div>
        <div class="h-40 animate-pulse rounded-2xl bg-blush-100"></div>
    </div>

    <div x-show="galat" x-cloak class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-center">
        <p class="text-sm text-red-700" x-text="galat"></p>
        <a href="{{ route('tes.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">Kembali ke daftar tes</a>
    </div>

    <template x-if="tes">
        <div>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight" x-text="tes.nama"></h1>
            <p x-show="tes.deskripsi" class="mt-3 text-gray-600" x-text="tes.deskripsi"></p>

            {{-- Progress (menempel di bawah navbar saat scroll) --}}
            <div class="sticky top-16 z-10 -mx-5 mt-6 border-b border-blush-200 bg-white/95 px-5 py-3 backdrop-blur sm:top-20 sm:mx-0 sm:rounded-xl sm:border sm:px-4">
                <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                    <span>Terjawab <span x-text="terjawab"></span> dari <span x-text="pertanyaan.length"></span></span>
                    <span x-text="persen + '%'"></span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-blush-100">
                    <div class="h-full rounded-full bg-primary transition-all duration-300" :style="'width: ' + persen + '%'"></div>
                </div>
            </div>

            <div x-show="galatKirim" x-cloak x-text="galatKirim" class="mt-6 rounded-xl bg-red-50 p-4 text-sm text-red-700"></div>

            <form @submit.prevent="kirim()" class="mt-6 space-y-5">
                <template x-for="(p, i) in pertanyaan" :key="p.id">
                    <fieldset :id="'soal-' + p.id" class="rounded-2xl border border-blush-200 bg-white p-5 sm:p-6">
                        <legend class="sr-only" x-text="'Pertanyaan ' + (i + 1)"></legend>

                        <p class="font-semibold text-ink">
                            <span class="mr-1 text-primary" x-text="(i + 1) + '.'"></span><span x-text="p.teks"></span>
                        </p>

                        <div class="mt-4 grid gap-2">
                            <template x-for="j in p.pilihan" :key="j.id">
                                <label class="block cursor-pointer">
                                    <input type="radio" :name="'jawaban-' + p.id" :value="j.id" x-model.number="jawaban[p.id]"
                                           class="peer sr-only">
                                    <span class="block rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 transition
                                                 hover:border-primary
                                                 peer-checked:border-primary peer-checked:bg-blush-100 peer-checked:font-semibold peer-checked:text-primary
                                                 peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40"
                                          x-text="j.teks"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                </template>

                <button type="submit" :disabled="mengirim"
                        class="w-full rounded-xl bg-primary px-6 py-3.5 font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary-dark disabled:cursor-wait disabled:opacity-60"
                        x-text="mengirim ? 'Menghitung hasil...' : 'Lihat hasil'"></button>

                <p class="text-center text-xs text-gray-400">
                    Hasil tes ini adalah skrining awal dan bukan diagnosis.
                </p>
            </form>
        </div>
    </template>
</section>
@endsection
