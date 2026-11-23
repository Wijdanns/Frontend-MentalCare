{{--
    Kartu psikolog versi API. Dipakai di dalam <template x-for="p in ..."> (butuh variabel p
    dari komponen Alpine, bentuknya sama dengan MCUtil.psikolog()).
    "Lihat profil" membuka <x-modal-profil-psikolog /> lewat event 'buka-profil'.
--}}
<article class="flex h-full flex-col overflow-hidden rounded-3xl border border-blush-200 bg-white">
    <template x-if="p.foto">
        <img :src="p.foto" :alt="'Foto ' + p.nama" class="aspect-[353/256] w-full object-cover" loading="lazy">
    </template>
    <template x-if="!p.foto">
        <div class="flex aspect-[353/256] w-full items-center justify-center bg-gradient-to-br from-blush-100 to-blush-200">
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-white/80 text-2xl font-bold text-primary" x-text="$mc.inisial(p.nama)"></span>
        </div>
    </template>

    <div class="flex flex-1 flex-col px-5 pb-5 pt-5 sm:px-6 sm:pb-6">
        <span x-show="p.tersedia" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-blush-200/70 px-3 py-1 text-xs font-medium text-primary">
            <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Tersedia
        </span>
        <span x-show="!p.tersedia" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Tidak tersedia
        </span>

        <h3 class="mt-3 text-lg font-bold" x-text="p.nama"></h3>
        <p class="mt-1 text-sm text-gray-500" x-text="p.spesialis"></p>

        <div class="mt-auto">
            <div class="mt-6 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 text-sm">
                <span class="font-bold text-primary" x-text="$mc.rupiah(p.biaya, true)"></span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm font-semibold">
                <button type="button" @click="$dispatch('buka-profil', $mc.profilModal(p))"
                        class="rounded-xl border border-gray-200 px-4 py-3 text-center text-primary transition hover:border-primary">Lihat profil</button>

                <a x-show="p.tersedia" :href="$mc.profilModal(p).link_jadwal"
                   class="rounded-xl bg-primary px-4 py-3 text-center text-white transition hover:bg-primary-dark">Pilih jadwal</a>
                <span x-show="!p.tersedia" class="cursor-not-allowed rounded-xl bg-gray-200 px-4 py-3 text-center text-gray-500">Belum tersedia</span>
            </div>
        </div>
    </div>
</article>
