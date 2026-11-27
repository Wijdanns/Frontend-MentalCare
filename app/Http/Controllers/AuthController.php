<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('api_token')) {
            return $this->redirectByRole();
        }

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Validasi dilakukan oleh API; frontend hanya meneruskan
        $res = ApiService::post('/login', $request->only('email', 'password'));

        if (! $res->ok) {
            $errors = $res->errors
                ? ApiService::errors($res)
                : [str_contains((string) $res->message, 'Email') ? 'email' : 'password' => $res->message];

            return back()->withErrors($errors)->onlyInput('email');
        }

        $this->simpanSesi($res->data);
        toast($res->message, 'success');

        return $this->redirectByRole();
    }

    public function showRegister()
    {
        if (session()->has('api_token')) {
            return $this->redirectByRole();
        }

        return view('register');
    }

    public function register(Request $request): RedirectResponse
    {
        $res = ApiService::post('/register', $request->only('nama', 'email', 'password'));

        if (! $res->ok) {
            return back()->withErrors(ApiService::errors($res))->onlyInput('nama', 'email');
        }

        $this->simpanSesi($res->data);
        toast($res->message, 'success');

        return $this->redirectByRole();
    }

    public function logout(Request $request): RedirectResponse
    {
        ApiService::post('/logout'); // hapus token di backend

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        toast('Sesi diakhiri. Sampai jumpa lagi, semoga harimu menyenangkan!', 'info');

        return redirect()->route('home');
    }

    private function simpanSesi(object $data): void
    {
        session([
            'api_token' => $data->token,
            'user'      => $data->user,
            'role'      => $data->role,
        ]);
        session()->regenerate();
    }

    private function redirectByRole(): RedirectResponse
    {
        // Admin & psikolog memakai panel Filament di backend
        $panel = match (session('role')) {
            'admin'    => ApiService::backendUrl() . '/admin',
            'psikolog' => ApiService::backendUrl() . '/panel-psikolog',
            default    => null,
        };

        if ($panel) {
            session()->forget('url.intended');

            return redirect()->away($panel);
        }

        return redirect()->intended(route('home'));
    }
}