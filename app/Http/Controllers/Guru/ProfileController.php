<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $guru = $request->user();
        return view('guru.profile.edit', compact('guru'));
    }

    public function update(Request $request): RedirectResponse
    {
        $guru = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $guru->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        } else {
            unset($data['password']);
        }

        $guru->update($data);

        return redirect()->route('guru.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}