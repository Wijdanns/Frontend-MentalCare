<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

/**
 * Penghubung frontend -> backend (REST API).
 *
 * Semua method mengembalikan object dengan bentuk:
 *   ok      : bool    (true jika HTTP 2xx)
 *   status  : int     (kode HTTP)
 *   message : ?string (pesan dari API)
 *   data    : mixed   (isi "data" dari API, berupa stdClass/array of stdClass
 *                      sehingga di Blade tetap bisa ditulis $psikolog->nama)
 *   errors  : array   (error validasi Laravel: field => [pesan])
 */
class ApiService
{
    /** HTTP client yang sudah membawa Bearer token dari session. */
    public static function client(): PendingRequest
    {
        $http = Http::baseUrl(self::apiUrl())
            ->acceptJson()
            ->timeout(20);

        if ($token = session('api_token')) {
            $http = $http->withToken($token);
        }

        return $http;
    }

    public static function apiUrl(): string
    {
        return rtrim(config('services.mentalcare.api_url'), '/');
    }

    public static function backendUrl(): string
    {
        return rtrim(config('services.mentalcare.backend_url'), '/');
    }

    public static function get(string $path, array $query = []): object
    {
        return self::kirim(fn () => self::client()->get($path, $query));
    }

    /**
     * POST (JSON). Jika $files diisi, otomatis dikirim sebagai multipart.
     * Contoh: ApiService::post('/profil', ['nama' => 'Budi'], ['profil' => $request->file('profil')])
     */
    public static function post(string $path, array $data = [], array $files = []): object
    {
        return self::kirim(function () use ($path, $data, $files) {
            $req = self::client();

            foreach ($files as $nama => $file) {
                if ($file instanceof UploadedFile) {
                    $req = $req->attach($nama, fopen($file->getRealPath(), 'r'), $file->getClientOriginalName());
                }
            }

            return $req->post($path, $data);
        });
    }

    /** Ubah response paginasi Laravel (JSON) menjadi paginator, agar {{ $x->links() }} tetap jalan. */
    public static function paginator(object $p): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $p->data,
            $p->total,
            $p->per_page,
            $p->current_page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /** Ubah error API menjadi array field => pesan untuk ->withErrors(). */
    public static function errors(object $res): array
    {
        $out = [];
        foreach ($res->errors as $field => $pesan) {
            $out[$field] = is_array($pesan) ? ($pesan[0] ?? '') : $pesan;
        }

        return $out ?: ['api' => $res->message ?? 'Terjadi kesalahan.'];
    }

    private static function kirim(\Closure $panggil): object
    {
        try {
            $res = $panggil();
        } catch (ConnectionException $e) {
            return (object) [
                'ok'      => false,
                'status'  => 503,
                'message' => 'Server backend tidak dapat dihubungi. Pastikan backend sudah berjalan.',
                'data'    => null,
                'errors'  => [],
            ];
        }

        // Token kedaluwarsa / tidak valid -> paksa login ulang
        if ($res->status() === 401 && session()->has('api_token')) {
            session()->flush();
            throw new HttpResponseException(
                redirect()->route('login')->with('error', 'Sesi berakhir, silakan masuk lagi.')
            );
        }

        $body = json_decode($res->body());
        if (! is_object($body)) {
            $body = new \stdClass;
        }

        return (object) [
            'ok'      => $res->successful(),
            'status'  => $res->status(),
            'message' => $body->message ?? null,
            'data'    => $body->data ?? null,
            'errors'  => (array) ($body->errors ?? []),
        ];
    }
}