<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        $classrooms = Classroom::orderBy('name')->paginate(10);
        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create(): View
    {
        return view('admin.classrooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:classrooms,name',
        ]);

        Classroom::create($data);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('admin.classrooms.edit', compact('classroom'));
    }

    public function show(Classroom $classroom): View
    {
        $students = User::query()
            ->where('role', 'murid')
            ->where('classroom', $classroom->name)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.classrooms.show', compact('classroom', 'students'));
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:classrooms,name,' . $classroom->id,
        ]);

        $classroom->update($data);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();
        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}