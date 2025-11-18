<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $murid = $request->user();
        return view('murid.profile.edit', compact('murid'));
    }

    public function update(Request $request): RedirectResponse
    {
        $murid = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $murid->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        } else {
            unset($data['password']);
        }

        $murid->update($data);

        return redirect()->route('murid.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}