{{--
    Navbar versi frontend terpisah: tidak memakai @auth/@guest karena tidak ada sesi login di server ini.
    Status login dibaca dari token di browser (Auth.ada()) dan data pengguna dari GET /api/user.
    Dropdown Artikel diisi dari GET /api/artikel-kategori.
--}}
<header x-data="navbar" class="sticky top-0 z-50 border-b border-blush-200/70 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-[1168px] items-center justify-between px-5 sm:h-20 sm:px-8">

        <x-logo />

        {{-- Menu desktop --}}
        <nav class="hidden items-center gap-9 text-sm font-medium lg:flex">
            <a href="{{ url('/') }}" :class="aktif('/') ? 'text-primary' : 'text-gray-700 hover:text-primary'">Beranda</a>
            <a href="{{ url('/konsultasi') }}" :class="aktif('/konsultasi') ? 'text-primary' : 'text-gray-700 hover:text-primary'">Konsultasi</a>
            <a href="{{ url('/psikolog') }}" :class="aktif('/psikolog') ? 'text-primary' : 'text-gray-700 hover:text-primary'">Psikolog</a>

            {{-- Artikel + dropdown (muncul saat hover / fokus keyboard) --}}
            <div class="group relative">
                <a href="{{ url('/artikel') }}" class="flex items-center gap-1"
                   :class="aktif('/artikel') ? 'text-primary' : 'text-gray-700 hover:text-primary'">
                    Artikel
                    <svg class="h-3.5 w-3.5 transition group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </a>
                <div class="invisible absolute left-0 top-full pt-4 opacity-0 transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                    <div class="max-h-80 w-56 overflow-y-auto rounded-xl border border-blush-200 bg-white p-2 shadow-lg">
                        <a href="{{ url('/artikel') }}" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-blush-100 hover:text-primary">Semua</a>
                        <template x-for="k in kategori" :key="k.id">
                            <a :href="'{{ url('/artikel') }}?kategori=' + k.id" x-text="k.nama"
                               class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-blush-100 hover:text-primary"></a>
                        </template>
                    </div>
                </div>
            </div>

            <a href="{{ url('/tes-psikologi') }}" :class="aktif('/tes-psikologi') ? 'text-primary' : 'text-gray-700 hover:text-primary'">Tes Psikologi</a>
        </nav>

        {{-- Tombol auth desktop --}}
        <div class="hidden items-center gap-6 text-sm font-semibold lg:flex">
            <template x-if="!login">
                <div class="flex items-center gap-6">
                    <a href="{{ url('/login') }}" class="text-primary hover:text-primary-dark">Masuk</a>
                    <a href="{{ url('/register') }}" class="rounded-xl bg-primary px-5 py-3 text-white transition hover:bg-primary-dark">Daftar</a>
                </div>
            </template>

            <template x-if="login">
                {{-- Profil + dropdown (hover / fokus keyboard) --}}
                <div class="group relative">
                    <button type="button"
                            class="flex items-center gap-2 rounded-full border border-blush-200 bg-white py-1 pl-1 pr-3 transition hover:border-primary">
                        <template x-if="user && user.profil_url">
                            <img :src="user.profil_url" :alt="user.nama" class="h-9 w-9 rounded-full object-cover">
                        </template>
                        <template x-if="!(user && user.profil_url)">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-white" x-text="inisial || '·'"></span>
                        </template>
                        <span class="max-w-[110px] truncate text-sm font-semibold text-ink" x-text="user ? user.nama : 'Akun'"></span>
                        <svg class="h-3.5 w-3.5 text-gray-400 transition group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="invisible absolute right-0 top-full pt-3 opacity-0 transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                        <div class="w-56 overflow-hidden rounded-xl border border-blush-200 bg-white shadow-lg">
                            <div class="border-b border-blush-200 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-ink" x-text="user ? user.nama : ''"></p>
                                <p class="truncate text-xs font-normal text-gray-500" x-text="user ? user.email : ''"></p>
                            </div>

                            <div class="p-2 font-medium">
                                <template x-for="m in menuAkun" :key="m.href">
                                    <a :href="m.href" x-text="m.label" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-blush-100 hover:text-primary"></a>
                                </template>
                                <button type="button" @click="keluar()" class="block w-full rounded-lg px-3 py-2 text-left text-red-600 hover:bg-red-50">Keluar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Tombol hamburger (mobile) --}}
        <button type="button" @click="buka = !buka" :aria-expanded="buka" :aria-label="buka ? 'Tutup menu' : 'Buka menu'"
                class="rounded-lg p-2 text-ink hover:bg-blush-100 lg:hidden">
            <svg x-show="!buka" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            <svg x-show="buka" x-cloak class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    {{-- Menu mobile --}}
    <div x-show="buka" x-cloak class="border-t border-blush-200 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-[1168px] flex-col gap-1 px-5 py-4 text-sm font-medium sm:px-8">
            <a href="{{ url('/') }}" class="rounded-lg px-3 py-2.5" :class="aktif('/') ? 'bg-blush-100 text-primary' : 'text-gray-700'">Beranda</a>
            <a href="{{ url('/konsultasi') }}" class="rounded-lg px-3 py-2.5 text-gray-700">Konsultasi</a>
            <a href="{{ url('/psikolog') }}" class="rounded-lg px-3 py-2.5 text-gray-700">Psikolog</a>

            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2.5 text-gray-700 [&::-webkit-details-marker]:hidden">
                    Artikel
                    <svg class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <div class="ml-3 flex flex-col border-l border-blush-200 pl-3">
                    <a href="{{ url('/artikel') }}" class="rounded-lg px-3 py-2 text-gray-600">Semua</a>
                    <template x-for="k in kategori" :key="k.id">
                        <a :href="'{{ url('/artikel') }}?kategori=' + k.id" x-text="k.nama" class="rounded-lg px-3 py-2 text-gray-600"></a>
                    </template>
                </div>
            </details>
            <a href="{{ url('/tes-psikologi') }}" class="rounded-lg px-3 py-2.5 text-gray-700">Tes Psikologi</a>

            <div class="mt-3 border-t border-blush-200 pt-4">
                <template x-if="!login">
                    <div class="flex gap-3">
                        <a href="{{ url('/login') }}" class="flex-1 rounded-xl border border-primary px-4 py-2.5 text-center font-semibold text-primary">Masuk</a>
                        <a href="{{ url('/register') }}" class="flex-1 rounded-xl bg-primary px-4 py-2.5 text-center font-semibold text-white">Daftar</a>
                    </div>
                </template>

                <template x-if="login">
                    <div>
                        <div class="mb-3 flex items-center gap-3 px-3">
                            <template x-if="user && user.profil_url">
                                <img :src="user.profil_url" :alt="user.nama" class="h-10 w-10 rounded-full object-cover">
                            </template>
                            <template x-if="!(user && user.profil_url)">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary font-bold text-white" x-text="inisial || '·'"></span>
                            </template>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink" x-text="user ? user.nama : 'Akun'"></p>
                                <p class="truncate text-xs font-normal text-gray-500" x-text="user ? user.email : ''"></p>
                            </div>
                        </div>

                        <template x-for="m in menuAkun" :key="m.href">
                            <a :href="m.href" x-text="m.label" class="block rounded-lg px-3 py-2.5 text-gray-700 hover:bg-blush-100"></a>
                        </template>
                        <button type="button" @click="keluar()" class="block w-full rounded-lg px-3 py-2.5 text-left text-red-600 hover:bg-red-50">Keluar</button>
                    </div>
                </template>
            </div>
        </nav>
    </div>
</header>
