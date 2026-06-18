<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Дашборд') — Artgroups ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50:  '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .count-up { transition: all 0.8s ease-out; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
        @keyframes progressFill {
            from { width: 0%; }
        }
        .progress-bar { animation: progressFill 1.2s ease-out forwards; }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
@php
    $authUser       = auth()->user();
    $authAvatarUrl  = $authUser->avatarUrl();
    $authInitials   = $authUser->initials();
    $companyName    = \App\Models\Setting::get('company_name', 'Artgroups');
    $companyLogo    = \App\Models\Setting::get('company_logo');
    $logoSrc        = $companyLogo ? asset('storage/' . $companyLogo) : asset('images/artlogo.png');
@endphp

<div class="min-h-full" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar overlay -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo -->
        <div class="flex items-center h-16 px-5 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-500 relative overflow-hidden">
            <div class="absolute -top-3 -right-3 w-14 h-14 bg-white/10 rounded-full"></div>
            <a href="{{ route('dashboard') }}" class="relative flex items-center gap-3">
                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="h-11 w-auto object-contain drop-shadow-sm">
                <div>
                    <div class="text-white font-bold text-sm leading-tight">{{ $companyName }}</div>
                    <div class="text-white/70 text-xs">ERP Dashboard</div>
                </div>
            </a>
        </div>

        <!-- Nav -->
        <nav class="flex flex-col h-[calc(100%-4rem)] justify-between py-4">
            <div class="px-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Дашборд
                </a>

                @if($authUser->canSeeAllBranches())
                    {{-- CEO: all branches --}}
                    <div class="pt-3 pb-1 px-3 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Филиалы</span>
                        <button @click="$dispatch('open-branch-modal')"
                                class="w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors"
                                title="Добавить филиал">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                    @foreach(\App\Models\Branch::where('is_active',true)->orderBy('sort_order')->get() as $br)
                    @php
                        $brActive = request()->routeIs('branch.view') && request()->route('branch')?->id === $br->id;
                        $brDot    = match($br->color){'blue'=>'bg-blue-400','violet'=>'bg-violet-400','purple'=>'bg-purple-400','amber'=>'bg-amber-400',default=>'bg-emerald-400'};
                    @endphp
                    <div class="flex items-center rounded-lg transition-colors {{ $brActive ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }} group/br">
                        <a href="{{ route('branch.view', $br) }}" class="flex items-center gap-3 px-3 py-2 flex-1 text-sm min-w-0">
                            <div class="w-2 h-2 rounded-full {{ $brDot }} shrink-0"></div>
                            <span class="truncate">{{ $br->name }}</span>
                        </a>
                        <div class="flex items-center gap-0.5 pr-1.5 opacity-0 group-hover/br:opacity-100 transition-opacity shrink-0">
                            <button @click="$dispatch('open-edit-branch', { id: {{ $br->id }}, name: '{{ addslashes($br->name) }}', city: '{{ addslashes($br->city) }}', color: '{{ $br->color }}' })"
                                    class="w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Редактировать">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="$dispatch('open-delete-branch', { id: {{ $br->id }}, name: '{{ addslashes($br->name) }}' })"
                                    class="w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Удалить">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                @elseif($authUser->canSeeAllDepartments())
                    {{-- Commercial director / multi-branch user --}}
                    @php
                        $sidebarBranches = \App\Models\Branch::whereIn('id', $authUser->accessibleBranchIds())
                            ->where('is_active', true)->orderBy('sort_order')->get();
                    @endphp
                    @if($sidebarBranches->count() > 1)
                        {{-- Multi-branch: show branch links --}}
                        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Мои филиалы</div>
                        @foreach($sidebarBranches as $br)
                        <a href="{{ route('branch.view', $br) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                                  {{ request()->routeIs('branch.view') && request()->route('branch')?->id === $br->id ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <div class="w-2 h-2 rounded-full {{ $br->getDotClass() }}"></div>
                            {{ $br->name }}
                        </a>
                        @endforeach
                    @elseif($sidebarBranches->count() === 1)
                        {{-- Single branch: show departments --}}
                        @php $singleBranch = $sidebarBranches->first(); @endphp
                        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $singleBranch->name }}</div>
                        @foreach($singleBranch->activeDepartments as $dept)
                        <a href="{{ route('department.view', $dept) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                                  {{ request()->routeIs('department.view') && request()->route('department')?->id === $dept->id ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <div class="w-2 h-2 rounded-full {{ $singleBranch->getDotClass() }}"></div>
                            {{ $dept->name }}
                        </a>
                        @endforeach
                    @endif
                @elseif($authUser->department)
                    @php
                        $accessIds    = $authUser->accessibleBranchIds();
                        $myDeptName   = $authUser->department->name;
                        $crossDepts   = \App\Models\Department::whereIn('branch_id', $accessIds)
                            ->where('name', $myDeptName)
                            ->where('is_active', true)
                            ->with('branch')
                            ->get();
                    @endphp

                    @if($crossDepts->count() > 1)
                        {{-- Multi-branch staff: show same-type dept for each branch --}}
                        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $myDeptName }}</div>
                        @foreach($crossDepts as $cd)
                        <a href="{{ route('department.view', $cd) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                                  {{ request()->routeIs('department.view') && request()->route('department')?->id === $cd->id ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <div class="w-2 h-2 rounded-full {{ $cd->branch?->getDotClass() ?? 'bg-gray-400' }}"></div>
                            {{ $cd->branch?->name ?? '—' }}
                        </a>
                        @endforeach
                    @else
                        {{-- Single dept staff --}}
                        <a href="{{ route('department.view', $authUser->department) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Мои KPI
                        </a>
                    @endif
                @endif

                @if($authUser->canSeeRecommendations())
                <a href="{{ route('rec.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('rec.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Рекомендации
                    @php $recCount = \App\Models\Recommendation::where('is_dismissed',false)->count(); @endphp
                    @if($recCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $recCount }}</span>
                    @endif
                </a>
                @endif

                @if($authUser->canManageUsers())
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Администрирование</div>
                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Пользователи
                </a>
                <a href="{{ route('settings') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('settings*') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Настройки
                </a>
                @endif

                @if($authUser->canExportExcel())
                <div class="pt-2">
                <a href="{{ route('export.download', ['year' => now()->year, 'month' => now()->month]) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Скачать отчёт
                </a>
                </div>
                @endif
            </div>

            <!-- User profile -->
            <div class="px-3 border-t border-gray-100 pt-4">
                <a href="{{ route('profile') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-50 hover:bg-emerald-50 transition-colors group">
                    <div class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-emerald-200 shrink-0">
                        @if($authAvatarUrl)
                            <img src="{{ $authAvatarUrl }}" alt="{{ $authUser->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-sm select-none">
                                {{ $authInitials }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate group-hover:text-emerald-700">{{ $authUser->name }}</div>
                        <div class="text-xs text-gray-500">{{ $authUser->getRoleLabel() }}</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-500 bg-gradient-to-r from-red-50/80 to-rose-50/60 hover:from-red-100 hover:to-rose-100 hover:text-red-600 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Выйти
                    </button>
                </form>

                {{-- Branding --}}
                <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                    <a href="https://instagram.com/baproger.kz" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-pink-500 transition-colors group">
                        <svg class="w-3.5 h-3.5 group-hover:text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        <span class="font-medium">baProger.kz</span>
                    </a>
                    <div class="text-xs text-gray-300 mt-0.5">система разработана</div>
                </div>
            </div>
        </nav>
    </aside>

    {{-- Add Branch Modal (CEO only) --}}
    @if(auth()->user()->canSeeAllBranches())
    <div x-data="{ open: false }"
         @open-branch-modal.window="open = true"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 fade-in-up">
            <button @click="open = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-5">Добавить филиал</h3>
            <form action="{{ route('admin.branches.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Название *</label>
                    <input type="text" name="name" required placeholder="напр. Актау"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Город *</label>
                    <input type="text" name="city" required placeholder="напр. Актау"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Цвет</label>
                    <select name="color" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="emerald">Зелёный (Emerald)</option>
                        <option value="blue">Синий (Blue)</option>
                        <option value="violet">Фиолетовый (Violet)</option>
                        <option value="purple">Пурпурный (Purple)</option>
                        <option value="amber">Оранжевый (Amber)</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">
                        Создать
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Edit Branch Modal --}}
    <div x-data="{ open: false, branchId: null, name: '', city: '', color: 'emerald' }"
         @open-edit-branch.window="open = true; branchId = $event.detail.id; name = $event.detail.name; city = $event.detail.city; color = $event.detail.color"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 fade-in-up">
            <button @click="open = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 class="text-lg font-bold text-gray-800 mb-5">Редактировать филиал</h3>
            <form :action="'/admin/branches/' + branchId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Название *</label>
                    <input type="text" name="name" x-model="name" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Город *</label>
                    <input type="text" name="city" x-model="city" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Цвет</label>
                    <select name="color" x-model="color" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="emerald">Зелёный (Emerald)</option>
                        <option value="blue">Синий (Blue)</option>
                        <option value="violet">Фиолетовый (Violet)</option>
                        <option value="purple">Пурпурный (Purple)</option>
                        <option value="amber">Оранжевый (Amber)</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Отмена
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Branch Confirm Modal (CEO only) --}}
    <div x-data="{ open: false, branchId: null, branchName: '' }"
         @open-delete-branch.window="open = true; branchId = $event.detail.id; branchName = $event.detail.name"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 fade-in-up text-center">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Удалить филиал?</h3>
            <p class="text-sm text-gray-500 mb-1">Это действие нельзя отменить.</p>
            <p class="text-sm font-semibold text-gray-700 mb-1" x-text="branchName"></p>
            <p class="text-xs text-red-500 mb-5">Все отделы и KPI этого филиала будут удалены.</p>
            <div class="flex gap-3">
                <button @click="open = false"
                        class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    Отмена
                </button>
                <form :action="'/admin/branches/' + branchId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-semibold transition-colors">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Main content -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Дашборд')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500"
                         x-data="{
                             time: '',
                             date: '',
                             months: ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'],
                             tick() {
                                 const now = new Date();
                                 const h = String(now.getHours()).padStart(2,'0');
                                 const m = String(now.getMinutes()).padStart(2,'0');
                                 const s = String(now.getSeconds()).padStart(2,'0');
                                 this.time = h+':'+m+':'+s;
                                 this.date = now.getDate()+' '+this.months[now.getMonth()]+' '+now.getFullYear();
                             }
                         }"
                         x-init="tick(); setInterval(() => tick(), 1000)">
                        <span x-text="date"></span>
                        <span class="text-gray-300">·</span>
                        <span x-text="time" class="font-mono tabular-nums"></span>
                    </div>
                    <a href="{{ route('profile') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-gray-200 group-hover:ring-emerald-400 transition-all shrink-0">
                            @if($authAvatarUrl)
                                <img src="{{ $authAvatarUrl }}" alt="{{ $authUser->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-xs select-none">
                                    {{ $authInitials }}
                                </div>
                            @endif
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700 group-hover:text-emerald-700 transition-colors">
                            {{ $authUser->name }}
                        </span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-4 sm:px-6 pt-4">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm fade-in-up">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm fade-in-up">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif
        </div>

        <!-- Page content -->
        <main class="flex-1 px-4 sm:px-6 pb-8">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

{{-- ═══════════════════════════════════════════════════════════════
     PUSH-НАПОМИНАНИЕ О НЕЗАПОЛНЕННЫХ ФАКТАХ (с 17:00, для сотрудников)
═══════════════════════════════════════════════════════════════ --}}
@if(! $authUser->canSeeAllBranches() && ! $authUser->isCommercialDirector() && $authUser->department)
<script>
(function () {
    const ENDPOINT = "{{ route('notifications.unfilled') }}";
    const ICON     = "{{ $logoSrc }}";
    const POLL_MS  = 5 * 60 * 1000; // проверять каждые 5 минут

    function dayKey() {
        return 'kpiReminderShown_' + new Date().toISOString().slice(0, 10);
    }

    function buildBody(data) {
        const names = [];
        data.departments.forEach(d => d.kpis.forEach(k => names.push(k)));
        const preview = names.slice(0, 4).join(', ');
        const more = names.length > 4 ? ' и ещё ' + (names.length - 4) : '';
        return 'Не заполнено ' + data.count + ' KPI за сегодня: ' + preview + more;
    }

    function showBanner(text) {
        if (document.getElementById('kpiReminderBanner')) return;
        const el = document.createElement('div');
        el.id = 'kpiReminderBanner';
        el.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;max-width:360px;' +
            'background:#fff;border:1px solid #fcd34d;border-left:4px solid #f59e0b;border-radius:14px;' +
            'box-shadow:0 10px 30px rgba(0,0,0,.12);padding:14px 16px;font-size:13px;color:#374151;' +
            'display:flex;gap:10px;align-items:flex-start;animation:fadeInUp .4s ease-out;';
        el.innerHTML =
            '<span style="font-size:20px;line-height:1">📋</span>' +
            '<div style="flex:1"><div style="font-weight:600;color:#b45309;margin-bottom:2px">Заполните KPI за сегодня</div>' +
            '<div>' + text + '</div></div>' +
            '<button style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:16px;line-height:1" ' +
            'onclick="this.parentElement.remove()">&times;</button>';
        document.body.appendChild(el);
        setTimeout(() => el && el.remove(), 12000);
    }

    function notify(data) {
        const body = buildBody(data);
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const n = new Notification('📋 Заполните KPI за сегодня', {
                    body: body, icon: ICON, tag: 'kpi-reminder', renotify: true,
                });
                n.onclick = () => { window.focus(); n.close(); };
            } catch (e) {
                showBanner(body);
            }
        } else {
            showBanner(body); // запасной вариант, если разрешение не выдано
        }
    }

    async function check() {
        try {
            const res = await fetch(ENDPOINT, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.should_notify && localStorage.getItem(dayKey()) !== '1') {
                notify(data);
                localStorage.setItem(dayKey(), '1'); // одно напоминание в день
            }
        } catch (e) { /* тихо игнорируем */ }
    }

    // Запросить разрешение на уведомления (на загрузке и по первому клику)
    function askPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }
    }
    askPermission();
    document.addEventListener('click', askPermission, { once: true });

    check();
    setInterval(check, POLL_MS);
})();
</script>
@endif
</body>
</html>
