<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Mahasiswa') - Teknik Radiologi Pencitraan</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    <!-- Font Awesome 6 (via jsDelivr, tanpa SRI agar tidak diblokir) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body class="bg-gray-100 overflow-hidden">
    <div class="h-screen flex">
        <aside class="hidden lg:flex flex-col w-72 bg-emerald-700 text-white fixed left-0 top-0 h-screen">
            <div class="p-6 flex items-center gap-3 border-b border-emerald-500/50 flex-shrink-0">
                <img src="{{ asset('img/logo.png') }}" alt="Logo Radiologi" class="h-11 w-11 rounded-full bg-white p-1">
                <div>
                    <p class="text-xs uppercase tracking-widest text-emerald-200">Portal Mahasiswa</p>
                    <p class="text-lg font-semibold">Teknik Radiologi Pencitraan</p>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <x-murid-nav-item route="murid.dashboard" icon='<i class="fa-solid fa-gauge-high"></i>'>Dashboard</x-murid-nav-item>
                <x-murid-nav-item route="murid.tasks.index" icon='<i class="fa-solid fa-list-check"></i>'>Tugas</x-murid-nav-item>
            <x-murid-nav-item route="murid.exams.index" icon='<i class="fa-solid fa-laptop-file"></i>'>Ujian Matakuliah</x-murid-nav-item>
                <x-murid-nav-item route="murid.attendances.index" icon='<i class="fa-solid fa-calendar-check"></i>'>Kehadiran</x-murid-nav-item>
                <x-murid-nav-item route="murid.grades.index" icon='<i class="fa-solid fa-award"></i>'>Nilai</x-murid-nav-item>
            </nav>
            <div class="p-4 border-t border-emerald-500/50 text-sm text-emerald-100 flex-shrink-0 flex items-center gap-3">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="Foto Mahasiswa" class="h-9 w-9 rounded-full bg-white object-cover">
                <div>
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                    <p>Mahasiswa</p>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col lg:ml-72 overflow-hidden">
            <header class="bg-white shadow-sm flex-shrink-0">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
                    <div class="flex items-center gap-3">
                        <button id="mobileMenuToggle" class="lg:hidden text-emerald-600 text-2xl">☰</button>
                        <h1 class="text-xl font-semibold text-emerald-900">@yield('header', 'Ringkasan Mahasiswa')</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('murid.profile.edit') }}" title="Edit Profil" aria-label="Edit Profil" class="inline-flex items-center justify-center p-2 text-emerald-600 bg-emerald-100 rounded-lg shadow hover:bg-emerald-200">
                            <i class="fa-solid fa-user-pen"></i>
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" aria-label="Keluar" title="Keluar" class="inline-flex items-center justify-center p-2 text-white bg-emerald-600 rounded-lg shadow hover:bg-emerald-700">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 overflow-y-auto">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div id="mobileSidebar" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden">
        <div class="bg-emerald-700 text-white w-72 h-full p-6 space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Menu Mahasiswa</span>
                <button id="mobileMenuClose">✕</button>
            </div>
            <nav class="space-y-2">
                <x-murid-nav-item route="murid.dashboard" icon='<i class="fa-solid fa-gauge-high"></i>'>Dashboard</x-murid-nav-item>
                <x-murid-nav-item route="murid.tasks.index" icon='<i class="fa-solid fa-list-check"></i>'>Tugas</x-murid-nav-item>
            <x-murid-nav-item route="murid.exams.index" icon='<i class="fa-solid fa-laptop-file"></i>'>Ujian Matakuliah</x-murid-nav-item>
                <x-murid-nav-item route="murid.attendances.index" icon='<i class="fa-solid fa-calendar-check"></i>'>Kehadiran</x-murid-nav-item>
                <x-murid-nav-item route="murid.grades.index" icon='<i class="fa-solid fa-award"></i>'>Nilai</x-murid-nav-item>
            </nav>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('mobileSidebar');
            const close = document.getElementById('mobileMenuClose');

            if (toggle && sidebar && close) {
                toggle.addEventListener('click', () => sidebar.classList.remove('hidden'));
                close.addEventListener('click', () => sidebar.classList.add('hidden'));
                sidebar.addEventListener('click', (event) => {
                    if (event.target === sidebar) {
                        sidebar.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

