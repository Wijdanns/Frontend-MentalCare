<title>Registrasi - MentalCare</title>
<script src="https://cdn.tailwindcss.com"></script>

<div class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

  <!-- Card Registrasi -->
  <div class="bg-white p-8 rounded-2xl shadow-sm w-full max-w-sm relative">

    <!-- Icon Hati -->
    <div class="w-10 h-10 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center text-lg mb-5">
      ♥
    </div>

    <!-- Judul & Subjudul -->
    <h2 class="text-2xl font-bold text-gray-900 mb-1">Mulai perjalananmu</h2>
    <p class="text-gray-500 text-xs mb-6">Buat akun untuk mengakses layanan MentalCare.</p>

    <!-- Form Registrasi -->
    <form action="{{ route('register') }}" method="POST" class="space-y-4">
      @csrf

      <!-- Input Nama Lengkap -->
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Nama lengkap</label>
        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap"
          class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-pink-500 {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-200' }}" required>
        @error('nama')
          <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
        @enderror
      </div>

      <!-- Input Email -->
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
          class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-pink-500 {{ $errors->has('email') ? 'border-red-500' : 'border-gray-200' }}" required>
        @error('email')
          <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
        @enderror
      </div>

      <!-- Input Password + Icon Mata -->
      <div>
        <label class="block text-xs font-bold text-gray-800 mb-1">Kata sandi</label>
        <div class="relative">
          <input id="passwordInput" type="password" name="password" placeholder="Minimal 8 karakter"
            class="w-full pl-3 pr-10 py-2 border rounded-lg text-sm focus:outline-none focus:border-pink-500 {{ $errors->has('password') ? 'border-red-500' : 'border-gray-200' }}" required>
          <button type="button" onclick="togglePassword()" aria-label="Tampilkan atau sembunyikan kata sandi"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
            <!-- Icon Mata Terbuka -->
            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <!-- Icon Mata Tertutup (Eye-Off) -->
            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
          </button>
        </div>
        @error('password')
          <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
        @enderror
      </div>

      <!-- Tombol Daftar Sekarang -->
      <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-lg text-sm transition duration-200">
        Daftar sekarang
      </button>
    </form>

    <!-- Footer -->
    <p class="text-center text-xs text-gray-500 mt-6">
      Sudah punya akun? <a href="{{ route('login') }}" class="text-rose-600 font-semibold hover:underline">Masuk</a>
    </p>

    <p class="text-center text-xs text-gray-400 mt-3">
      <a href="{{ route('home') }}" class="hover:text-rose-600">&larr; Kembali ke beranda</a>
    </p>
  </div>

</div>

<script>
  function togglePassword() {
    const input = document.getElementById('passwordInput');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    eyeOpen.classList.toggle('hidden', isHidden);
    eyeClosed.classList.toggle('hidden', !isHidden);
  }
</script>