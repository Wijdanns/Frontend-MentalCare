<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MentalCare')</title>

    {{-- Font: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{--
        Tailwind lewat CDN supaya langsung jalan tanpa npm.
        Kalau tim pindah ke Vite, hapus 2 tag <script> di bawah, lalu pakai:
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        dan pindahkan warna/font di bawah ke konfigurasi Tailwind.
    --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#E0004D', dark: '#BF0041' },
                        ink: '#2A1520',
                        blush: { 50: '#FFF9FB', 100: '#FEF1F5', 200: '#FBDDE6' },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        }
    </script>

    {{--
        Konfigurasi untuk JavaScript. Alamat backend dari .env (BACKEND_URL) lewat config/mentalcare.php.
        Dimuat sebelum Alpine supaya komponen terdaftar saat Alpine mulai.
    --}}
    <script>
        window.MC = {
            backend: @js(config('mentalcare.api_url')),
            api: @js(config('mentalcare.api_url') . '/api'),
            url: {
                home: @js(url('/')),
                artikel: @js(url('/artikel')),
                psikolog: @js(url('/psikolog')),
                konsultasi: @js(url('/konsultasi')),
                tes: @js(url('/tes-psikologi')),
                profil: @js(url('/profil')),
                login: @js(url('/login')),
                register: @js(url('/register')),
            },
        };
    </script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/mentalcare.js') }}"></script>

    {{-- Alpine.js: navbar, pop-up profil psikolog, filter, dan semua halaman yang memakai API --}}
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-blush-50 font-sans text-ink antialiased">

    @include('partials.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Tombol WhatsApp (partial milik tim). Dilewati kalau file-nya belum ada. --}}
    @includeIf('partials.toggle-wa')

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
