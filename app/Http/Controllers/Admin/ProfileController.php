<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $admin = $request->user();
        return view('admin.profile.edit', compact('admin'));
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return redirect()->route('admin.profile.edit')->with('success', 'Profil admin berhasil diperbarui.');
    }
}