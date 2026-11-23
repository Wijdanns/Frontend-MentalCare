/*
 * MentalCare - komponen halaman (Alpine.js) yang memakai Api (api.js).
 * Alurnya: halaman Blade -> komponen Alpine -> Api.get/post -> /api/... -> JSON -> tampil di halaman.
 *
 * Bentuk JSON mengikuti Resource di backend:
 *   ArtikelResource   : id, judul, ringkasan, isi (hanya detail), gambar_url, tanggal_publikasi (Y-m-d),
 *                       waktu_baca, kategori {id, nama_kategori}, penulis
 *   PsikologResource  : id, nama, spesialis, deskripsi_psikolog, biaya, foto_url, tersedia
 *   UserResource      : id, nama, email, role, profil_url
 */
(function (window, document) {
    'use strict';

    const BAHASA = 'id-ID';
    const WIB = 'Asia/Jakarta';
    const cfgUrl = () => (window.MC && window.MC.url) || {};

    /* ---------------------------------------------------------------
     * Fungsi bantu (juga bisa dipakai di Blade lewat $mc.nama(...))
     * ------------------------------------------------------------- */
    const U = {
        batasi(teks, maks) {
            teks = String(teks || '');
            return teks.length > maks ? teks.slice(0, maks).trimEnd() + '...' : teks;
        },

        // Menerima 'YYYY-MM-DD' (tanggal saja) atau waktu ISO (ditampilkan dalam WIB)
        tanggal(nilai, bulan = 'long') {
            if (!nilai) return '';
            const s = String(nilai);
            const polos = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s);
            const d = polos ? new Date(Date.UTC(+polos[1], +polos[2] - 1, +polos[3])) : new Date(s);
            if (isNaN(d)) return '';
            return new Intl.DateTimeFormat(BAHASA, {
                day: '2-digit', month: bulan, year: 'numeric', timeZone: polos ? 'UTC' : WIB,
            }).format(d);
        },

        tanggalWaktu(iso) {
            const d = iso ? new Date(iso) : null;
            if (!d || isNaN(d)) return '';
            const b = Object.fromEntries(
                new Intl.DateTimeFormat(BAHASA, {
                    day: '2-digit', month: 'long', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: WIB,
                }).formatToParts(d).map(p => [p.type, p.value])
            );
            return `${b.day} ${b.month} ${b.year}, ${b.hour}:${b.minute}`;
        },

        // Kunci tanggal dari backend ('2026-10-14') -> 'Rabu, 14 Oktober 2026'
        tanggalKunci(kunci) {
            const [y, m, d] = String(kunci).split('-').map(Number);
            if (!y) return String(kunci);
            return new Intl.DateTimeFormat(BAHASA, {
                weekday: 'long', day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC',
            }).format(new Date(Date.UTC(y, m - 1, d)));
        },

        jam(teks) {
            return String(teks || '').slice(0, 5);
        },

        rupiah(angka, rapat = false) {
            return 'Rp' + (rapat ? '' : ' ') + Number(angka || 0).toLocaleString(BAHASA);
        },

        inisial(nama) {
            return String(nama || '')
                .replace(/Dr\.|M\.Psi\.|,/g, '')
                .split(/\s+/).filter(Boolean).slice(0, 2)
                .map(k => k[0].toUpperCase()).join('');
        },

        // Foto cadangan kalau psikolog belum punya foto (sama seperti tampilan web sebelumnya)
        avatar(nama) {
            return 'https://ui-avatars.com/api/?size=600&background=fce7ef&color=e11d48&name=' + encodeURIComponent(nama || '');
        },

        // PsikologResource -> bentuk yang dipakai tampilan. Email sengaja tidak ada (tidak dikirim publik oleh backend).
        psikolog(p) {
            return {
                id: p.id,
                nama: p.nama || '',
                email: '',
                spesialis: p.spesialis || '',
                biaya: Number(p.biaya || 0),
                deskripsi: p.deskripsi_psikolog || '',
                foto: p.foto_url || null,
                tersedia: !!p.tersedia,
                link: cfgUrl().psikolog + '/' + p.id,
            };
        },

        // Data untuk pop-up profil (komponen modal-profil-psikolog)
        profilModal(p) {
            return {
                nama: p.nama,
                email: p.email,
                spesialisasi: p.spesialis,
                tarif: U.rupiah(p.biaya, true),
                deskripsi: p.deskripsi,
                foto: p.foto || '',
                inisial: U.inisial(p.nama),
                tersedia: p.tersedia,
                link_jadwal: cfgUrl().konsultasi + '?psikolog=' + p.id,
            };
        },

        // ArtikelResource -> bentuk yang dipakai tampilan
        artikel(a) {
            return {
                id: a.id,
                judul: a.judul,
                ringkasan: a.ringkasan || '',
                waktu: a.waktu_baca,
                tanggal: U.tanggal(a.tanggal_publikasi),
                kategoriId: a.kategori ? a.kategori.id : null,
                kategoriNama: a.kategori ? a.kategori.nama_kategori : '',
                penulis: a.penulis || '',
                thumbnail: a.gambar_url || null,
                link: cfgUrl().artikel + '/' + a.id,
            };
        },

        // Daftar nomor halaman: 1 ... 4 5 [6] 7 8 ... 20
        halaman(meta) {
            const akhir = meta.last_page || 1;
            const kini = meta.current_page || 1;
            const nomor = [...new Set([1, akhir, kini - 2, kini - 1, kini, kini + 1, kini + 2])]
                .filter(n => n >= 1 && n <= akhir)
                .sort((a, b) => a - b);

            const hasil = [];
            let sebelumnya = 0;
            nomor.forEach(n => {
                if (n - sebelumnya > 1) hasil.push({ key: 'titik-' + n, titik: true });
                hasil.push({ key: 'n-' + n, n, aktif: n === kini, titik: false });
                sebelumnya = n;
            });
            return hasil;
        },

        // Sinkronkan filter dengan alamat halaman (?q=...&page=2) supaya bisa di-share dan tombol refresh aman
        bacaUrl() {
            return new URLSearchParams(window.location.search);
        },

        tulisUrl(params) {
            const q = new URLSearchParams();
            Object.entries(params).forEach(([k, v]) => {
                if (v === '' || v === null || v === undefined || v === false) return;
                if (k === 'page' && Number(v) === 1) return;
                q.set(k, v === true ? '1' : v);
            });
            const teks = q.toString();
            window.history.replaceState(null, '', window.location.pathname + (teks ? '?' + teks : ''));
        },

        kelasStatusKonsultasi(status) {
            return {
                'Dikonfirmasi': 'bg-green-100 text-green-700',
                'Menunggu Pembayaran': 'bg-amber-100 text-amber-700',
                'Batal': 'bg-red-100 text-red-700',
            }[status] || 'bg-gray-100 text-gray-700';
        },
    };

    window.MCUtil = U;

    /* ---------------------------------------------------------------
     * Komponen Alpine
     * ------------------------------------------------------------- */
    document.addEventListener('alpine:init', () => {
        const A = window.Alpine;

        A.magic('mc', () => U);

        /* ========================= NAVBAR ========================= */
        A.data('navbar', () => ({
            user: null,
            kategori: [],
            buka: false,
            login: false,   // sengaja berupa data (bukan getter) supaya tampilan ikut berubah saat token dihapus

            init() {
                this.login = Auth.ada();

                if (this.login) {
                    this.user = Auth.userTersimpan();
                    Auth.user()
                        .then(u => { this.user = u; })
                        .catch(e => {
                            // Token kedaluwarsa/dicabut: tampil sebagai tamu (tanpa dilempar ke halaman login)
                            if (e.status === 401) { Auth.hapus(); this.user = null; this.login = false; }
                        });
                }

                // GET /api/artikel-kategori -> isi dropdown Artikel
                Api.get('/artikel-kategori')
                    .then(r => { this.kategori = r.data.map(k => ({ id: k.id, nama: k.nama_kategori })); })
                    .catch(() => {});
            },

            get inisial() { return this.user ? String(this.user.nama || '?').charAt(0).toUpperCase() : ''; },

            // Menu akun menurut peran. Panel admin dan psikolog ada di backend (Filament).
            get menuAkun() {
                const role = this.user ? this.user.role : '';
                const be = window.MC.backend;
                if (role === 'admin') return [{ label: 'Panel Admin', href: be + '/admin' }];
                if (role === 'psikolog') return [{ label: 'Panel Psikolog', href: be + '/panel-psikolog' }];
                return [
                    { label: 'Akun saya', href: window.MC.url.profil },
                    { label: 'Riwayat konsultasi', href: window.MC.url.konsultasi + '/riwayat' },
                ];
            },

            aktif(path) {
                const p = window.location.pathname;
                return path === '/' ? p === '/' : p.startsWith(path);
            },

            keluar() { Auth.logout(); },
        }));

        /* ========================= HOME ========================= */
        A.data('psikologPilihan', () => ({
            daftar: [],
            memuat: true,
            galat: '',

            async init() {
                try {
                    const r = await Api.get('/psikolog');
                    this.daftar = r.data.psikologs.data.slice(0, 3).map(U.psikolog);
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },
        }));

        /* ========================= ARTIKEL ========================= */
        A.data('artikelIndex', () => ({
            kategoriList: [],
            q: '',
            kategori: '',
            page: 1,
            items: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            memuat: true,
            galat: '',
            _n: 0,

            init() {
                const p = U.bacaUrl();
                this.q = p.get('q') || '';
                this.kategori = p.get('kategori') || '';
                this.page = parseInt(p.get('page')) || 1;
                this.muatKategori();
                this.muat();
            },

            // GET /api/artikel-kategori -> tombol filter
            async muatKategori() {
                try {
                    const r = await Api.get('/artikel-kategori');
                    this.kategoriList = r.data.map(k => ({ id: k.id, nama: k.nama_kategori, jumlah: k.artikel_count ?? null }));
                } catch (e) { /* tombol kategori hanya pelengkap */ }
            },

            async muat() {
                const n = ++this._n;
                this.memuat = true;
                this.galat = '';

                try {
                    const r = await Api.get('/artikel', { q: this.q, kategori: this.kategori, page: this.page });
                    if (n !== this._n) return; // ada permintaan yang lebih baru

                    const pg = r.data;
                    this.items = pg.data.map(U.artikel);
                    this.meta = { current_page: pg.current_page, last_page: pg.last_page, total: pg.total };
                    U.tulisUrl({ q: this.q, kategori: this.kategori, page: this.page });
                } catch (e) {
                    if (n !== this._n) return;
                    this.galat = e.message;
                    this.items = [];
                } finally {
                    if (n === this._n) this.memuat = false;
                }
            },

            get aktif() {
                return this.kategoriList.find(k => String(k.id) === String(this.kategori)) || null;
            },
            get halaman() { return U.halaman(this.meta); },

            cari() { this.page = 1; this.muat(); },
            pilih(id) { this.kategori = (id === null || id === undefined || id === '') ? '' : String(id); this.page = 1; this.muat(); },
            semua() { this.q = ''; this.kategori = ''; this.page = 1; this.muat(); },
            ke(n) {
                if (n < 1 || n > this.meta.last_page || n === this.page) return;
                this.page = n;
                this.muat();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        }));

        A.data('artikelShow', (id) => ({
            id,
            a: null,
            terkait: [],
            memuat: true,
            galat: '',

            async init() {
                try {
                    const r = await Api.get('/artikel/' + this.id);
                    this.a = {
                        ...U.artikel(r.data),
                        isi: r.data.isi,
                        // Unduh PDF lewat API: GET /api/artikel/{id}/unduh
                        unduh: window.MC.api + '/artikel/' + r.data.id + '/unduh',
                        linkKategori: r.data.kategori
                            ? window.MC.url.artikel + '?kategori=' + r.data.kategori.id
                            : window.MC.url.artikel,
                    };
                    document.title = this.a.judul + ' | MentalCare';
                    this.muatTerkait();
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },

            // Artikel terkait: artikel lain dari kategori yang sama (API tidak punya endpoint khusus)
            async muatTerkait() {
                if (!this.a.kategoriId) return;
                try {
                    const r = await Api.get('/artikel', { kategori: this.a.kategoriId });
                    this.terkait = r.data.data
                        .filter(x => x.id !== this.id)
                        .slice(0, 3)
                        .map(x => ({
                            id: x.id,
                            judul: x.judul,
                            kategoriNama: x.kategori ? x.kategori.nama_kategori : '',
                            tanggal: U.tanggal(x.tanggal_publikasi, 'short'),
                            link: window.MC.url.artikel + '/' + x.id,
                        }));
                } catch (e) { /* bagian tambahan, abaikan kalau gagal */ }
            },
        }));

        /* ========================= PSIKOLOG ========================= */
        A.data('psikologIndex', () => ({
            q: '',
            spesialis: '',
            tersedia: false,
            page: 1,
            items: [],
            daftarSpesialis: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            memuat: true,
            galat: '',
            _n: 0,

            init() {
                const p = U.bacaUrl();
                this.q = p.get('q') || '';
                this.spesialis = p.get('spesialis') || '';
                this.tersedia = p.get('tersedia') === '1';
                this.page = parseInt(p.get('page')) || 1;
                this.muat();
            },

            async muat() {
                const n = ++this._n;
                this.memuat = true;
                this.galat = '';

                try {
                    const r = await Api.get('/psikolog', {
                        q: this.q, spesialis: this.spesialis, tersedia: this.tersedia, page: this.page,
                    });
                    if (n !== this._n) return;

                    const pg = r.data.psikologs;
                    this.items = pg.data.map(U.psikolog);
                    this.daftarSpesialis = r.data.daftar_spesialis || [];
                    this.meta = { current_page: pg.current_page, last_page: pg.last_page, total: pg.total };
                    U.tulisUrl({ q: this.q, spesialis: this.spesialis, tersedia: this.tersedia, page: this.page });
                } catch (e) {
                    if (n !== this._n) return;
                    this.galat = e.message;
                    this.items = [];
                } finally {
                    if (n === this._n) this.memuat = false;
                }
            },

            get adaFilter() { return this.q !== '' || this.spesialis !== '' || this.tersedia; },
            get halaman() { return U.halaman(this.meta); },

            cari() { this.page = 1; this.muat(); },
            reset() { this.q = ''; this.spesialis = ''; this.tersedia = false; this.page = 1; this.muat(); },
            ke(n) {
                if (n < 1 || n > this.meta.last_page || n === this.page) return;
                this.page = n;
                this.muat();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        }));

        A.data('psikologShow', (id) => ({
            id,
            p: null,
            jadwal: [],
            memuat: true,
            galat: '',

            get login() { return Auth.ada(); },

            async init() {
                try {
                    const r = await Api.get('/psikolog/' + this.id);
                    this.p = U.psikolog(r.data.psikolog);
                    document.title = this.p.nama + ' | MentalCare';

                    // Backend mengirim {} berisi tanggal, atau [] kalau kosong
                    const perTanggal = r.data.jadwal_per_tanggal || {};
                    this.jadwal = Object.entries(Array.isArray(perTanggal) ? {} : perTanggal)
                        .sort(([a], [b]) => a.localeCompare(b))
                        .map(([kunci, slots]) => ({
                            kunci,
                            label: U.tanggalKunci(kunci),
                            slots: slots.map(s => ({
                                id: s.id,
                                jam: s.jam || (U.jam(s.jam_mulai) + ' - ' + U.jam(s.jam_selesai)),
                            })),
                        }));
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },

            linkSlot(slot) {
                return this.login ? window.MC.url.konsultasi + '?jadwal=' + slot.id : window.MC.url.login;
            },
        }));

        /* ========================= TES PSIKOLOGI ========================= */
        A.data('tesIndex', () => ({
            daftar: [],
            memuat: true,
            galat: '',

            get login() { return Auth.ada(); },

            async init() {
                try {
                    const r = await Api.get('/tes-psikologi');
                    this.daftar = r.data.map(t => ({
                        id: t.id,
                        nama: t.nama_tes,
                        deskripsi: t.deskripsi ? U.batasi(t.deskripsi, 120) : 'Tes untuk mengenali kondisi emosionalmu.',
                        jumlah: t.pertanyaan_count,
                        link: window.MC.url.tes + '/' + t.id,
                    }));
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },
        }));

        A.data('tesKerjakan', (id) => ({
            id,
            tes: null,
            pertanyaan: [],
            jawaban: {},          // { idPertanyaan: idJawaban }
            memuat: true,
            galat: '',
            mengirim: false,
            galatKirim: '',

            async init() {
                if (!Auth.wajibLogin()) return;
                try {
                    const r = await Api.get('/tes-psikologi/' + this.id);
                    this.tes = { nama: r.data.nama_tes, deskripsi: r.data.deskripsi };
                    // Poin sengaja tidak ada di data dari backend (skor dihitung di server)
                    this.pertanyaan = r.data.pertanyaan.map(p => ({
                        id: p.id,
                        teks: p.pertanyaan,
                        pilihan: (p.jawaban_psikologis || []).map(j => ({ id: j.id, teks: j.jawaban })),
                    }));
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },

            get terjawab() { return this.pertanyaan.filter(p => this.jawaban[p.id] != null).length; },
            get persen() {
                return this.pertanyaan.length ? Math.round((this.terjawab / this.pertanyaan.length) * 100) : 0;
            },

            async kirim() {
                const belum = this.pertanyaan.findIndex(p => this.jawaban[p.id] == null);
                if (belum !== -1) {
                    this.galatKirim = 'Pertanyaan nomor ' + (belum + 1) + ' belum dijawab.';
                    const el = document.getElementById('soal-' + this.pertanyaan[belum].id);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                this.mengirim = true;
                this.galatKirim = '';

                try {
                    const r = await Api.post('/tes-psikologi/' + this.id + '/submit', { jawaban: this.jawaban });
                    try { sessionStorage.setItem('mc_flash', r.message || ''); } catch (e) {}
                    window.location.href = window.MC.url.tes + '/hasil/' + r.data.id;
                } catch (e) {
                    this.galatKirim = e.pesan('jawaban') || e.message;
                    this.mengirim = false;
                }
            },
        }));

        A.data('tesHasil', (id) => ({
            id,
            h: null,
            flash: '',
            memuat: true,
            galat: '',

            async init() {
                if (!Auth.wajibLogin()) return;
                try {
                    this.flash = sessionStorage.getItem('mc_flash') || '';
                    sessionStorage.removeItem('mc_flash');
                } catch (e) {}

                try {
                    const r = await Api.get('/tes-psikologi/hasil/' + this.id);
                    const hasil = r.data.hasil_tes;
                    const maks = Number(r.data.poin_maks) || 0;
                    const total = Number(hasil.total_poin) || 0;

                    this.h = {
                        nama: hasil.tes_psikologis ? hasil.tes_psikologis.nama_tes : 'Tes Psikologi',
                        waktu: U.tanggalWaktu(hasil.created_at),
                        total,
                        maks,
                        persen: maks > 0 ? Math.round((total / maks) * 100) : 0,
                        linkUlang: window.MC.url.tes + '/' + hasil.id_tes,
                        rincian: (hasil.jawaban_user || []).map(j => ({
                            pertanyaan: j.pertanyaan_psikologis ? j.pertanyaan_psikologis.pertanyaan : '',
                            jawaban: j.jawaban_psikologis ? j.jawaban_psikologis.jawaban : '',
                            poin: j.poin,
                        })),
                    };
                    document.title = 'Hasil ' + this.h.nama + ' | MentalCare';
                } catch (e) {
                    this.galat = e.message;
                } finally {
                    this.memuat = false;
                }
            },
        }));

        A.data('tesRiwayat', () => ({
            items: [],
            page: 1,
            meta: { current_page: 1, last_page: 1, total: 0 },
            memuat: true,
            galat: '',

            init() {
                if (!Auth.wajibLogin()) return;
                this.page = parseInt(U.bacaUrl().get('page')) || 1;
                this.muat();
            },

            async muat() {
                this.memuat = true;
                this.galat = '';
                try {
                    const r = await Api.get('/tes-psikologi/riwayat', { page: this.page });
                    const pg = r.data;
                    this.items = pg.data.map(h => ({
                        id: h.id,
                        nama: h.tes_psikologis ? h.tes_psikologis.nama_tes : 'Tes Psikologi',
                        waktu: U.tanggalWaktu(h.created_at),
                        poin: h.total_poin,
                        link: window.MC.url.tes + '/hasil/' + h.id,
                    }));
                    this.meta = { current_page: pg.current_page, last_page: pg.last_page, total: pg.total };
                    U.tulisUrl({ page: this.page });
                } catch (e) {
                    this.galat = e.message;
                    this.items = [];
                } finally {
                    this.memuat = false;
                }
            },

            get halaman() { return U.halaman(this.meta); },
            ke(n) {
                if (n < 1 || n > this.meta.last_page || n === this.page) return;
                this.page = n;
                this.muat();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        }));
    });
})(window, document);
