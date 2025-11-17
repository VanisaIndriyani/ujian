<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'guru');
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->whereIn('role', ['guru', 'murid'])
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'search'));
    }

    public function create(): View
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('admin.users.create', compact('classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        // Validasi opsional untuk foto
        $request->validate([
            'photo' => 'sometimes|nullable|image|max:2048',
        ]);
        $plainPassword = $request->input('password');
        if (!$plainPassword) {
            $plainPassword = Str::random(10);
        }

        // Simpan foto jika diunggah
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('users/photos', 'public');
        }

        User::create($data + [
            'password' => Hash::make($plainPassword),
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.users.index', ['role' => $data['role']])
            ->with('success', 'Pengguna berhasil ditambahkan. Password: ' . $plainPassword);
    }

    public function edit(User $user): View
    {
        abort_if(!in_array($user->role, ['guru', 'murid'], true), 404);

        $classrooms = Classroom::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'classrooms'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if(!in_array($user->role, ['guru', 'murid'], true), 404);

        $data = $this->validatedData($request, $user->role, $user->id);
        // Validasi opsional untuk foto
        $request->validate([
            'photo' => 'sometimes|nullable|image|max:2048',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        // Ganti foto jika ada unggahan baru
        if ($request->hasFile('photo')) {
            if ($user->getAttribute('photo_path')) {
                try {
                    Storage::disk('public')->delete($user->getAttribute('photo_path'));
                } catch (\Throwable $e) {}
            }
            $data['photo_path'] = $request->file('photo')->store('users/photos', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.index', ['role' => $user->role])
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function show(User $user): View
    {
        abort_if(!in_array($user->role, ['guru', 'murid'], true), 404);

        if ($user->role === 'guru') {
            $subjects = $user->subjectsTeaching()->withCount('students')->orderBy('name')->get();

            $classrooms = DB::table('users')
                ->where('role', 'murid')
                ->whereIn('id', function ($q) use ($subjects) {
                    $q->select('user_id')
                        ->from('subject_user')
                        ->whereIn('subject_id', $subjects->pluck('id'));
                })
                ->whereNotNull('classroom')
                ->distinct()
                ->orderBy('classroom')
                ->pluck('classroom');

            return view('admin.users.show', [
                'user' => $user,
                'subjects' => $subjects,
                'classrooms' => $classrooms,
            ]);
        }

        // murid
        $subjects = $user->subjects()->orderBy('name')->get();

        return view('admin.users.show', [
            'user' => $user,
            'subjects' => $subjects,
            'classrooms' => collect([$user->classroom])->filter(),
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(!in_array($user->role, ['guru', 'murid'], true), 404);

        $role = $user->role;
        $user->delete();

        return redirect()->route('admin.users.index', ['role' => $role])
            ->with('success', 'Data pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user, Request $request): RedirectResponse
    {
        abort_if(!in_array($user->role, ['guru', 'murid'], true), 404);

        $plainPassword = Str::random(10);
        $user->update([
            'password' => Hash::make($plainPassword),
        ]);

        return redirect()->route('admin.users.index', ['role' => $user->role])
            ->with('success', 'Password pengguna direset. Password baru: ' . $plainPassword);
    }

    private function validatedData(Request $request, ?string $role = null, ?int $userId = null): array
    {
        $role = $role ?? $request->input('role');

        $baseRules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:guru,murid',
        ];

        if ($role === 'guru') {
            $baseRules['email'] = 'nullable|email|max:255|unique:users,email,' . $userId;
            $baseRules['nip'] = 'required|string|max:50|unique:users,nip,' . $userId;
            $baseRules['nisn'] = 'nullable';
            $baseRules['classroom'] = 'nullable|string|max:100';
        } else {
            $baseRules['email'] = 'nullable|email|max:255|unique:users,email,' . $userId;
            $baseRules['nisn'] = 'required|string|max:50|unique:users,nisn,' . $userId;
            $baseRules['nip'] = 'nullable';
            $baseRules['classroom'] = 'nullable|string|max:100';
        }

        if (!$userId || $request->filled('password')) {
            $baseRules['password'] = 'sometimes|nullable|string|min:6';
        }

        return $request->validate($baseRules);
    }
}

