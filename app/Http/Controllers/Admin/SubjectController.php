<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::with('guru')->paginate(10);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.subjects.create', compact('gurus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'semester' => 'required|integer|min:1|max:8',
            'sks' => 'required|integer|min:1|max:10',
            'guru_id' => 'nullable|exists:users,id',
        ]);

        Subject::create($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.subjects.edit', compact('subject', 'gurus'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'semester' => 'required|integer|min:1|max:8',
            'sks' => 'required|integer|min:1|max:10',
            'guru_id' => 'nullable|exists:users,id',
        ]);

        $subject->update($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}

