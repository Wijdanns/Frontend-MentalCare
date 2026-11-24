<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    /**
     * Login untuk API (menyesuaikan logika dari web)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email salah.',
            ], 422);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kata sandi salah.',
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil masuk! Mari lanjutkan langkah kecil untuk kesehatan mentalmu hari ini.',
            'data'    => [
                'user'       => new UserResource($user),
                'role'       => $user->role,
                'token'      => $user->createToken('api')->plainTextToken,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Register untuk API (menyesuaikan logika dari web)
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'pasien',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran berhasil! Langkah awal yang baik untuk perjalanan kesehatan mentalmu.',
            'data'    => [
                'user'       => new UserResource($user),
                'role'       => $user->role,
                'token'      => $user->createToken('api')->plainTextToken,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Logout untuk API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesi diakhiri. Sampai jumpa lagi, semoga harimu menyenangkan!',
            'data'    => null,
        ], 200);
    }
}