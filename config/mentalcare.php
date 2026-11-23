<?php

return [
    // Alamat server BACKEND (proyek Laravel yang menyediakan /api). Diisi lewat .env
    'api_url' => rtrim(env('BACKEND_URL', 'http://127.0.0.1:8000'), '/'),
];
