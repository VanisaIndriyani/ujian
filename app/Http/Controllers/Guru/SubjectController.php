<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::where('guru_id', Auth::id())
            ->orderBy('name')
            ->paginate(12);

        return view('guru.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('guru.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
        ]);

        Subject::create($data + [
            'guru_id' => Auth::id(),
        ]);

        return redirect()->route('guru.subjects.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        $this->authorizeSubject($subject);

        return view('guru.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeSubject($subject);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
        ]);

        $subject->update($data);

        return redirect()->route('guru.subjects.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorizeSubject($subject);
        $subject->delete();

        return redirect()->route('guru.subjects.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    protected function authorizeSubject(Subject $subject): void
    {
        abort_if($subject->guru_id !== Auth::id(), 403);
    }
}