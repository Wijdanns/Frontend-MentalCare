/*
 * MentalCare - klien API untuk FRONTEND TERPISAH dari backend.
 *
 * Frontend dan backend adalah dua proyek di alamat berbeda, jadi:
 *  - data diambil dari BACKEND_URL/api lewat fetch (backend wajib mengaktifkan CORS),
 *  - login memakai token Bearer (Laravel Sanctum). Token disimpan di localStorage,
 *    tanpa cookie sesi dan tanpa CSRF.
 *
 * Objek global:
 *   Api.get('/artikel', { q: 'stres', page: 2 })
 *   Api.post('/tes-psikologi/1/submit', { jawaban: { 1: 3 } })
 *   Api.kirimForm('/profil', formData)
 *   Auth.login(email, password) / Auth.logout() / Auth.ada() / Auth.user() / Auth.wajibLogin()
 */
(function (window, document) {
    'use strict';

    const KUNCI_TOKEN = 'mc_token';
    const KUNCI_USER = 'mc_user';
    const mc = () => window.MC || {};

    /* ---------------- penyimpanan (localStorage, aman kalau diblokir) ---------------- */
    const simpanan = {
        ambil(k) { try { return window.localStorage.getItem(k); } catch (e) { return null; } },
        tulis(k, v) { try { window.localStorage.setItem(k, v); } catch (e) {} },
        hapus(k) { try { window.localStorage.removeItem(k); } catch (e) {} },
    };

    class ApiError extends Error {
        constructor(pesan, status = 0, errors = {}) {
            super(pesan);
            this.name = 'ApiError';
            this.status = status;
            this.errors = errors || {};
        }

        /** Pesan error pertama untuk satu kolom form (hasil validasi 422), atau '' */
        pesan(kolom) {
            const e = this.errors[kolom];
            return Array.isArray(e) ? (e[0] || '') : (e || '');
        }
    }

    const PESAN_STATUS = {
        401: 'Silakan masuk terlebih dahulu.',
        403: 'Kamu tidak punya akses ke data ini.',
        404: 'Data tidak ditemukan.',
        422: 'Data yang dikirim belum valid.',
        429: 'Terlalu banyak percobaan. Coba lagi sebentar lagi.',
    };

    function bangunUrl(path, params) {
        let url = (mc().api || '/api') + path;

        if (params) {
            const q = new URLSearchParams();
            Object.entries(params).forEach(([kunci, nilai]) => {
                if (nilai === '' || nilai === null || nilai === undefined || nilai === false) return;
                q.append(kunci, nilai === true ? '1' : nilai);
            });
            const teks = q.toString();
            if (teks) url += '?' + teks;
        }

        return url;
    }

    async function kirim(method, path, { params, json, form, abaikan401 = false } = {}) {
        const headers = { 'Accept': 'application/json' };
        const opsi = { method, headers };

        const token = Auth.token();
        if (token) headers['Authorization'] = 'Bearer ' + token;

        if (form) {
            opsi.body = form; // FormData: browser mengatur Content-Type sendiri
        } else if (json !== undefined) {
            headers['Content-Type'] = 'application/json';
            opsi.body = JSON.stringify(json);
        }

        let res;
        try {
            res = await fetch(bangunUrl(path, params), opsi);
        } catch (e) {
            // Biasanya: backend mati, alamat BACKEND_URL salah, atau CORS belum diaktifkan di backend
            throw new ApiError('Tidak dapat terhubung ke server. Periksa koneksi internet dan pengaturan backend.');
        }

        let isi = null;
        try { isi = await res.json(); } catch (e) { /* respons bukan JSON */ }

        if (res.ok) return isi;

        if (res.status === 401 && !abaikan401) {
            // Token tidak ada, salah, atau kedaluwarsa: hapus lalu minta login ulang
            Auth.hapus();
            Auth.keLogin();
        }

        throw new ApiError(
            (isi && isi.message) || PESAN_STATUS[res.status] || 'Terjadi kesalahan pada server.',
            res.status,
            isi && isi.errors
        );
    }

    /* ---------------- Auth ---------------- */
    // Respons login belum dipastikan bentuknya, jadi beberapa letak token dicoba
    function ambilToken(r) {
        if (!r) return '';
        const d = r.data || {};
        return d.token || d.access_token || d.plainTextToken || r.token || r.access_token || r.plainTextToken || '';
    }

    function ambilUser(r) {
        return (r && ((r.data && r.data.user) || r.user)) || null;
    }

    const Auth = {
        token: () => simpanan.ambil(KUNCI_TOKEN) || '',
        ada: () => !!simpanan.ambil(KUNCI_TOKEN),

        simpan(token, user) {
            simpanan.tulis(KUNCI_TOKEN, token);
            if (user) simpanan.tulis(KUNCI_USER, JSON.stringify(user));
        },

        hapus() {
            simpanan.hapus(KUNCI_TOKEN);
            simpanan.hapus(KUNCI_USER);
        },

        // Data pengguna dari penyimpanan (untuk tampil cepat sebelum API menjawab)
        userTersimpan() {
            try { return JSON.parse(simpanan.ambil(KUNCI_USER) || 'null'); } catch (e) { return null; }
        },

        // GET /api/user (UserResource): id, nama, email, role, profil_url
        async user() {
            const r = await kirim('GET', '/user', { abaikan401: true });
            simpanan.tulis(KUNCI_USER, JSON.stringify(r.data));
            return r.data;
        },

        // POST /api/login -> simpan token. Dipakai halaman login (tugas FE lain).
        async login(email, password) {
            const r = await kirim('POST', '/login', { json: { email, password }, abaikan401: true });
            const token = ambilToken(r);
            if (!token) throw new ApiError('Login berhasil, tetapi token tidak ditemukan pada respons API.');
            Auth.simpan(token, ambilUser(r));
            return r;
        },

        async logout() {
            try { await kirim('POST', '/logout', { abaikan401: true }); } catch (e) { /* tetap keluar di sisi frontend */ }
            Auth.hapus();
            window.location.href = (mc().url && mc().url.home) || '/';
        },

        // Halaman yang butuh login: panggil di awal. Mengembalikan false (dan mengarahkan ke login) kalau belum login.
        wajibLogin() {
            if (Auth.ada()) return true;
            Auth.keLogin();
            return false;
        },

        // Alamat halaman login + ?next= (halaman yang sedang dibuka), supaya setelah login kembali ke sini
        alamatLogin() {
            const login = (mc().url && mc().url.login) || '/login';
            const sekarang = window.location.pathname + window.location.search;
            return login + '?next=' + encodeURIComponent(sekarang);
        },

        keLogin() {
            window.location.href = Auth.alamatLogin();
        },

        // Tujuan setelah login berhasil: ?next=/profil (hanya alamat internal), kalau tidak ada ke beranda
        tujuanSetelahLogin() {
            const next = new URLSearchParams(window.location.search).get('next') || '';
            return (next.startsWith('/') && !next.startsWith('//')) ? next : ((mc().url && mc().url.home) || '/');
        },
    };

    window.Api = {
        ApiError,
        get:       (path, params) => kirim('GET', path, { params }),
        post:      (path, json)   => kirim('POST', path, { json }),
        put:       (path, json)   => kirim('PUT', path, { json }),
        hapus:     (path)         => kirim('DELETE', path),
        kirimForm: (path, form)   => kirim('POST', path, { form }),
    };
    window.Auth = Auth;
})(window, document);
