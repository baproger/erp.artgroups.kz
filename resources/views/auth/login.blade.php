<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Artgroups ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }

        @keyframes fadeUp   { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeLeft { from { opacity:0; transform:translateX(-24px); } to { opacity:1; transform:translateX(0); } }
        @keyframes blob     { 0%,100% { border-radius:60% 40% 30% 70%/60% 30% 70% 40%; } 50% { border-radius:30% 60% 70% 40%/50% 60% 30% 60%; } }
        @keyframes float    { 0%,100% { transform:translateY(0px); } 50% { transform:translateY(-12px); } }
        @keyframes pulse-ring { 0% { transform:scale(.95); box-shadow:0 0 0 0 rgba(16,185,129,.5); } 70% { transform:scale(1); box-shadow:0 0 0 10px rgba(16,185,129,0); } 100% { transform:scale(.95); box-shadow:0 0 0 0 rgba(16,185,129,0); } }
        @keyframes shimmer  { 0% { background-position:-200% 0; } 100% { background-position:200% 0; } }
        @keyframes countUp  { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        .fade-up   { animation: fadeUp   0.65s cubic-bezier(.22,.68,0,1.2) both; }
        .fade-left { animation: fadeLeft 0.65s cubic-bezier(.22,.68,0,1.2) both; }
        .blob      { animation: blob 8s ease-in-out infinite; }
        .float     { animation: float 4s ease-in-out infinite; }

        .delay-1 { animation-delay:.1s; }
        .delay-2 { animation-delay:.2s; }
        .delay-3 { animation-delay:.3s; }
        .delay-4 { animation-delay:.4s; }
        .delay-5 { animation-delay:.5s; }

        .input-field {
            width:100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            font-size: 14px;
            transition: all .2s;
            background: #fafafa;
            outline: none;
        }
        .input-field:focus {
            border-color: #10b981;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(16,185,129,.1);
        }
        .input-field.error { border-color:#f87171; }
        .input-field.error:focus { box-shadow:0 0 0 4px rgba(248,113,113,.1); }

        .btn-primary {
            width:100%;
            padding:15px;
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #0d9488 100%);
            background-size: 200% auto;
            color: white;
            font-weight: 700;
            font-size: 15px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            transition: all .3s;
            box-shadow: 0 4px 20px rgba(16,185,129,.35);
        }
        .btn-primary:hover {
            background-position: right center;
            box-shadow: 0 6px 28px rgba(16,185,129,.5);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform:translateY(0); }

        .stat-card {
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 16px;
            padding: 14px 18px;
            animation: float 4s ease-in-out infinite;
        }

        .bg-mesh {
            background-color: #064e3b;
            background-image:
                radial-gradient(at 20% 30%, rgba(5,150,105,.6) 0px, transparent 50%),
                radial-gradient(at 80% 10%, rgba(13,148,136,.5) 0px, transparent 50%),
                radial-gradient(at 50% 80%, rgba(4,120,87,.4) 0px, transparent 50%),
                radial-gradient(at 90% 70%, rgba(6,95,70,.5) 0px, transparent 50%);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex">

{{-- ═══ Left panel (branding) ═══════════════════════════════════════════════ --}}
<div class="hidden lg:flex lg:w-[55%] bg-mesh relative overflow-hidden flex-col justify-between p-12">

    {{-- Decorative blobs --}}
    <div class="absolute top-[-80px] left-[-80px] w-80 h-80 bg-emerald-400/20 rounded-full blob" style="animation-delay:0s"></div>
    <div class="absolute bottom-[-60px] right-[-60px] w-64 h-64 bg-teal-400/20 rounded-full blob" style="animation-delay:3s"></div>
    <div class="absolute top-1/2 left-1/3 w-40 h-40 bg-emerald-300/10 rounded-full blob" style="animation-delay:5s"></div>

    {{-- Logo --}}
    <div class="relative fade-left">
        <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur rounded-2xl px-5 py-3 border border-white/20">
            <img src="{{ asset('images/artlogo.png') }}" alt="Artgroups" class="h-10 w-auto object-contain">
            <div>
                <div class="text-white font-bold text-lg leading-tight">Artgroups</div>
                <div class="text-emerald-300 text-xs">ERP Dashboard</div>
            </div>
        </div>
    </div>

    {{-- Main text --}}
    <div class="relative">
        <h1 class="text-5xl font-black text-white leading-tight mb-4 fade-left delay-1">
            Управление<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-teal-300">KPI показателей</span>
        </h1>
        <p class="text-emerald-200/80 text-lg leading-relaxed max-w-md fade-left delay-3">
            Отслеживайте эффективность всех филиалов в реальном времени. Аналитика, планы и факты в одном месте.
        </p>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3 mt-10 fade-left delay-4">
            <div class="stat-card" style="animation-delay:0s">
                <div class="text-2xl font-black text-white">3</div>
                <div class="text-emerald-300/80 text-xs mt-0.5">Филиала</div>
            </div>
            <div class="stat-card" style="animation-delay:1.5s">
                <div class="text-2xl font-black text-white">25</div>
                <div class="text-emerald-300/80 text-xs mt-0.5">KPI метрик</div>
            </div>
            <div class="stat-card" style="animation-delay:3s">
                <div class="text-2xl font-black text-emerald-300">Live</div>
                <div class="text-emerald-300/80 text-xs mt-0.5">Обновление</div>
            </div>
        </div>
    </div>

    {{-- Bottom branding --}}
    <div class="relative fade-left delay-5">
        <div class="flex items-center gap-2 text-emerald-300/60 text-xs">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
            <span>Разработано <a href="https://instagram.com/baprogram.kz" target="_blank" class="text-emerald-400 hover:text-emerald-300 transition-colors font-medium">baProger.kz</a></span>
        </div>
    </div>
</div>

{{-- ═══ Right panel (form) ════════════════════════════════════════════════════ --}}
<div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 bg-white">

    {{-- Mobile logo --}}
    <div class="lg:hidden mb-8 text-center fade-up">
        <div class="inline-flex items-center gap-3 mb-3">
            <img src="{{ asset('images/artlogo.png') }}" alt="Artgroups" class="h-12 w-auto object-contain">
        </div>
        <div class="text-2xl font-black text-gray-800">Artgroups ERP</div>
        <div class="text-gray-400 text-sm">Система управления KPI</div>
    </div>

    <div class="w-full max-w-sm">

        {{-- Header --}}
        <div class="mb-8 fade-up delay-1">
            <h2 class="text-3xl font-black text-gray-900">Добро пожаловать</h2>
            <p class="text-gray-400 text-sm mt-1.5">Войдите в свой аккаунт для продолжения</p>
        </div>

        {{-- Success message --}}
        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl fade-up">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-emerald-700 text-sm">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Error --}}
        @if($errors->any())
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-2xl fade-up">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-700 text-sm">{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div class="fade-up delay-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-4.5 h-4.5 text-gray-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="input-field {{ $errors->has('email') ? 'error' : '' }}"
                           placeholder="your@email.com"
                           autocomplete="email">
                </div>
            </div>

            {{-- Password --}}
            <div class="fade-up delay-3" x-data="{ show: false }">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Пароль</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg style="width:18px;height:18px" class="text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input :type="show ? 'text' : 'password'" name="password" required
                           class="input-field"
                           placeholder="••••••••"
                           autocomplete="current-password">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg x-show="!show" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" x-cloak style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Remember me --}}
            <div class="flex items-center justify-between fade-up delay-4">
                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <div class="relative">
                        <input id="remember" type="checkbox" name="remember" class="sr-only peer">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-all group-hover:border-emerald-400"></div>
                        <svg class="absolute inset-0 w-5 h-5 text-white opacity-0 peer-checked:opacity-100 transition-opacity p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Запомнить меня</span>
                </label>
            </div>

            {{-- Submit --}}
            <div class="fade-up delay-5 pt-1">
                <button type="submit" class="btn-primary">
                    Войти в систему
                </button>
            </div>
        </form>

        {{-- Divider + register --}}
        <div class="mt-8 fade-up delay-5">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-xs text-gray-400 font-medium">или</span>
                <div class="flex-1 h-px bg-gray-100"></div>
            </div>
            <p class="text-center text-sm text-gray-500">
                Нет аккаунта?
                <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold ml-1 transition-colors">
                    Зарегистрироваться →
                </a>
            </p>
        </div>

        {{-- Mobile branding --}}
        <div class="lg:hidden mt-10 text-center fade-up">
            <a href="https://instagram.com/baprogram.kz" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-pink-500 transition-colors">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                baProger.kz
            </a>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>[x-cloak]{display:none!important}</style>
</body>
</html>
