<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem Akademik - Teknik Radiologi Pencitraan STIKES</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        emerald: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' },
                        purple: { 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9' },
                        sky: { 500: '#0ea5e9', 600: '#0284c7' },
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui; } </style>
</head>
<body class="bg-slate-100">
<div class="min-h-screen flex flex-col justify-center items-center py-12">
    <div class="bg-white shadow-2xl rounded-2xl w-full max-w-3xl overflow-hidden grid md:grid-cols-2">
        <div class="relative hidden md:block bg-gradient-to-br from-emerald-500 to-emerald-700">
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30"></div>
            <div class="relative h-full flex items-center justify-center p-8 text-white">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Radiologi" class="h-20 w-20 rounded-full bg-white/90 p-1 shadow-lg">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-emerald-100">Teknik Radiologi Pencitraan</p>
                        <p class="text-xs text-emerald-100 mt-1">STIKES DIAN HUSADA MOJOKERTO</p>
                        <h2 class="text-2xl font-semibold mt-2">Sistem Akademik Terpadu</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-8">
            <div class="md:hidden flex items-center gap-3 mb-6">
                <img src="{{ asset('img/logo.png') }}" alt="Logo Radiologi" class="h-12 w-12 rounded-full bg-white shadow">
                <div>
                    <p class="text-xs uppercase tracking-widest text-emerald-600">Teknik Radiologi Pencitraan</p>
                    <p class="text-xs text-emerald-600 mt-1">STIKES DIAN HUSADA MOJOKERTO</p>
                    <h1 class="text-base font-semibold text-slate-800 mt-2">Sistem Akademik Terpadu</h1>
                </div>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Masuk ke Akun</h2>

            <form action="{{ route('login.perform') }}" method="POST" class="space-y-5" id="loginForm">
                @csrf

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600">Email / NIP / NIM</label>
                        <input type="text" name="login" value="{{ old('login') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="admin@example.com atau 19750101 atau 23011001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600">Kata Sandi</label>
                        <input type="password" name="password" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="••••••••">
                    </div>
                    <label class="inline-flex items-center text-sm text-slate-500">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow transition">Masuk Sekarang</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>