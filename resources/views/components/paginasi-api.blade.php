{{--
    Navigasi halaman untuk data dari API (paginator Laravel).
    Dipakai di dalam komponen Alpine yang punya: meta { current_page, last_page }, halaman, dan ke(n).
--}}
<nav x-show="meta.last_page > 1" x-cloak aria-label="Navigasi halaman" class="mt-8 flex flex-wrap items-center justify-center gap-2">
    <button type="button" @click="ke(meta.current_page - 1)" :disabled="meta.current_page <= 1"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 transition hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-300">
        Sebelumnya
    </button>

    <template x-for="h in halaman" :key="h.key">
        <span>
            <span x-show="h.titik" class="px-1 text-gray-400">...</span>
            <button type="button" x-show="!h.titik" @click="ke(h.n)" x-text="h.n"
                    :aria-current="h.aktif ? 'page' : null"
                    :class="h.aktif ? 'border-primary bg-primary text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary'"
                    class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-medium transition"></button>
        </span>
    </template>

    <button type="button" @click="ke(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 transition hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-300">
        Berikutnya
    </button>
</nav>
