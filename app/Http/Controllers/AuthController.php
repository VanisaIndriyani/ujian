<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input dasar tanpa memilih peran
        $data = $request->validate([
            'login' => 'required|string', // Email/NIP/NISN
            'password' => 'required|string',
        ]);

        $identifier = $data['login'];
        $password = $data['password'];

        // Tentukan field yang digunakan: email jika format valid, jika tidak coba nip lalu nisn
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : null;

        if ($field === null) {
            // Coba cocokkan NIP
            $attempted = Auth::attempt(['nip' => $identifier, 'password' => $password], $request->boolean('remember'));
            if (!$attempted) {
                // Coba cocokkan NISN
                $attempted = Auth::attempt(['nisn' => $identifier, 'password' => $password], $request->boolean('remember'));
            }

            if ($attempted) {
                $request->session()->regenerate();
                $role = Auth::user()->role;
                return redirect()->intended(match ($role) {
                    'admin' => route('admin.dashboard'),
                    'guru' => route('guru.dashboard'),
                    default => route('murid.dashboard'),
                });
            }
        } else {
            // Login menggunakan email
            if (Auth::attempt(['email' => $identifier, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                $role = Auth::user()->role;
                return redirect()->intended(match ($role) {
                    'admin' => route('admin.dashboard'),
                    'guru' => route('guru.dashboard'),
                    default => route('murid.dashboard'),
                });
            }
        }

        return back()->withErrors([
            'login' => 'Kombinasi kredensial tidak ditemukan.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

