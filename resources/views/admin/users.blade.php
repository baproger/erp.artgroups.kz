@extends('layouts.app')
@section('title', 'Пользователи')
@section('page-title', 'Управление пользователями')

@section('content')

{{-- ─── Branch filter tabs ──────────────────────────────────────── --}}
<div class="mt-4 mb-5">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.users') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                  {{ !$branchId || $branchId === 'all' ? 'bg-gray-800 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Все филиалы
            <span class="ml-0.5 px-1.5 py-0.5 rounded-full text-xs {{ !$branchId || $branchId === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ \App\Models\User::count() }}
            </span>
        </a>

        @foreach($branches as $br)
        @php
            $brCount = \App\Models\User::where('branch_id', $br->id)->count();
            $isActive = $branchId == $br->id;
            $tabColor = match($br->color) {
                'blue'   => $isActive ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600',
                'violet' => $isActive ? 'bg-violet-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:border-violet-300 hover:text-violet-600',
                default  => $isActive ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:border-emerald-300 hover:text-emerald-600',
            };
            $dotColor = match($br->color) {
                'blue'   => $isActive ? 'bg-white/80' : 'bg-blue-400',
                'violet' => $isActive ? 'bg-white/80' : 'bg-violet-400',
                default  => $isActive ? 'bg-white/80' : 'bg-emerald-400',
            };
            $countColor = $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500';
        @endphp
        <a href="{{ route('admin.users', ['branch' => $br->id]) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all {{ $tabColor }}">
            <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
            {{ $br->name }}
            <span class="ml-0.5 px-1.5 py-0.5 rounded-full text-xs {{ $countColor }}">{{ $brCount }}</span>
        </a>
        @endforeach
    </div>

    @if($currentBranch)
    <div class="mt-3 flex items-center gap-2 text-sm text-gray-500">
        <span class="w-2 h-2 rounded-full {{ match($currentBranch->color){'blue'=>'bg-blue-400','violet'=>'bg-violet-400',default=>'bg-emerald-400'} }}"></span>
        Показаны пользователи филиала <strong class="text-gray-700">{{ $currentBranch->name }}</strong>
    </div>
    @endif
</div>

<div x-data="deleteUserModal()" x-cloak>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="close()" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm pointer-events-auto">
            <div class="flex flex-col items-center px-6 pt-8 pb-2">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Удалить пользователя?</h3>
                <p class="text-sm text-gray-500 text-center mb-1">Это действие нельзя отменить.</p>
                <p class="text-sm font-medium text-gray-700 text-center" x-text="userName"></p>
            </div>
            <div class="flex gap-3 px-6 py-5">
                <button @click="close()" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Отмена
                </button>
                <form :action="formAction" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 mb-6 flex items-center justify-between">
    <p class="text-sm text-gray-500">Всего зарегистрировано: <strong>{{ $users->total() }}</strong>
        @if($users->where('is_active', false)->count() > 0)
        <span class="ml-2 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
            {{ $users->where('is_active', false)->count() }} ожидают активации
        </span>
        @endif
    </p>
    <button @click="$dispatch('open-create-user')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                   text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Создать пользователя
    </button>
</div>

{{-- ─── Модал создания пользователя ───────────────────────────────── --}}
<div x-data="{ open: false, ...userEditForm(null, null) }"
     @open-create-user.window="open = true; branchId = null; deptId = null"
     x-show="open" x-cloak
     class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto fade-in-up">
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
            <h3 class="text-lg font-bold text-gray-800">Создать пользователя</h3>
            <button @click="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Имя и фамилия *</label>
                    <input type="text" name="name" required placeholder="Иван Иванов"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Email *</label>
                    <input type="email" name="email" required placeholder="user@artgroups.kz"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Пароль *</label>
                    <input type="password" name="password" required minlength="8" placeholder="минимум 8 символов"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Роль *</label>
                    <select name="role" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($roles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Филиал</label>
                    <select name="branch_id"
                            x-model="branchId"
                            @change="onBranchChange()"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Без филиала —</option>
                        @foreach($branches as $br)
                        <option value="{{ $br->id }}">{{ $br->name }} ({{ $br->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">
                        Отдел
                        <span class="ml-1 font-normal text-gray-400" x-show="branchId && filteredDepts.length === 0">(нет отделов)</span>
                    </label>
                    <select name="department_id"
                            x-model="deptId"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Без отдела —</option>
                        <template x-for="dept in filteredDepts" :key="dept.id">
                            <option :value="dept.id" x-text="dept.name"></option>
                        </template>
                    </select>
                    <p x-show="!branchId" class="mt-1 text-xs text-amber-500">Сначала выберите филиал</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-semibold">Статус</label>
                    <select name="is_active"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="1" selected>✅ Активен</option>
                        <option value="0">🚫 Неактивен</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-600 mb-2 font-semibold">
                    Доступ к филиалам
                    <span class="ml-1 font-normal text-gray-400">(для просмотра дашбордов)</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach($branches as $br)
                    @php $dotCls = match($br->color){'blue'=>'bg-blue-400','violet'=>'bg-violet-400','purple'=>'bg-purple-400','amber'=>'bg-amber-400',default=>'bg-emerald-400'}; @endphp
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border bg-white border-gray-200 hover:border-gray-300 cursor-pointer transition-all">
                        <input type="checkbox" name="accessible_branches[]" value="{{ $br->id }}"
                               class="w-3.5 h-3.5 rounded accent-emerald-600">
                        <span class="w-2 h-2 rounded-full {{ $dotCls }}"></span>
                        <span class="text-xs font-medium text-gray-700">{{ $br->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="flex-1 sm:flex-none px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Создать пользователя
                </button>
                <button type="button" @click="open = false"
                        class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-6 py-3 font-medium">Пользователь</th>
                    <th class="text-left px-4 py-3 font-medium">Email</th>
                    <th class="text-left px-4 py-3 font-medium">Роль</th>
                    <th class="text-left px-4 py-3 font-medium">Филиал</th>
                    <th class="text-left px-4 py-3 font-medium">Отдел</th>
                    <th class="text-center px-4 py-3 font-medium">Статус</th>
                    <th class="text-center px-4 py-3 font-medium">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" x-data="{ editOpen: null }">
                @foreach($users as $u)
                <tr class="{{ !$u->is_active ? 'bg-amber-50/30' : '' }} hover:bg-gray-50/60 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full overflow-hidden ring-2 {{ $u->is_active ? 'ring-emerald-100' : 'ring-gray-200' }} shrink-0">
                                @if($u->avatarUrl())
                                    <img src="{{ $u->avatarUrl() }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold select-none {{ $u->is_active ? 'bg-gradient-to-br from-emerald-500 to-emerald-700' : 'bg-gradient-to-br from-gray-400 to-gray-500' }}">
                                        {{ $u->initials() }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ $u->name }}</div>
                                <div class="text-xs text-gray-400">с {{ $u->created_at->format('d.m.Y') }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-4 text-gray-600 text-xs">{{ $u->email }}</td>

                    <td class="px-4 py-4">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ in_array($u->role, ['ceo','commercial_director']) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $u->getRoleLabel() }}
                        </span>
                    </td>

                    <td class="px-4 py-4 text-xs">
                        @if($u->branch)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($u->branch->color){'blue'=>'bg-blue-100 text-blue-700','violet'=>'bg-violet-100 text-violet-700',default=>'bg-emerald-100 text-emerald-700'} }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ match($u->branch->color){'blue'=>'bg-blue-400','violet'=>'bg-violet-400',default=>'bg-emerald-400'} }}"></span>
                            {{ $u->branch->name }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-gray-500 text-xs">{{ $u->department?->name ?? '—' }}</td>

                    <td class="px-4 py-4 text-center">
                        @if($u->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Активен
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Ожидает
                        </span>
                        @endif
                    </td>

                    <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button @click="editOpen = (editOpen === {{ $u->id }}) ? null : {{ $u->id }}"
                                    :class="editOpen === {{ $u->id }} ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    class="text-xs px-3 py-1.5 rounded-lg transition-colors font-medium">
                                <span x-text="editOpen === {{ $u->id }} ? '✕ Закрыть' : '✏️ Изменить'"></span>
                            </button>
                            @if($u->id !== auth()->id())
                            <button @click="$dispatch('open-delete-user', { action: '{{ route('admin.users.destroy', $u) }}', name: '{{ addslashes($u->name) }}' })"
                                    class="text-xs px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                🗑️ Удалить
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>

                <tr x-show="editOpen === {{ $u->id }}" x-cloak>
                    <td colspan="7" class="px-0 py-0">
                        <div class="border-t border-emerald-100 bg-emerald-50/40 px-6 py-5"
                             x-data="userEditForm({{ $u->branch_id ?? 'null' }}, {{ $u->department_id ?? 'null' }})">
                            <form action="{{ route('admin.users.update', $u) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">Имя и фамилия</label>
                                        <input type="text" name="name" value="{{ $u->name }}" required
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">Email</label>
                                        <input type="email" name="email" value="{{ $u->email }}" required
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">
                                            Новый пароль <span class="text-gray-400 font-normal">(пусто — не меняется)</span>
                                        </label>
                                        <input type="password" name="password" minlength="8" placeholder="••••••••"
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">Роль</label>
                                        <select name="role" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" @selected($u->role === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Филиал — при смене сбрасывает отдел --}}
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">Филиал</label>
                                        <select name="branch_id"
                                                x-model="branchId"
                                                @change="onBranchChange()"
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <option value="">— Без филиала —</option>
                                            @foreach($branches as $br)
                                            <option value="{{ $br->id }}">{{ $br->name }} ({{ $br->city }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Отдел — фильтруется по выбранному филиалу --}}
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">
                                            Отдел
                                            <span class="ml-1 font-normal text-gray-400" x-show="branchId && filteredDepts.length === 0">(нет отделов в этом филиале)</span>
                                        </label>
                                        <select name="department_id"
                                                x-model="deptId"
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <option value="">— Без отдела —</option>
                                            <template x-for="dept in filteredDepts" :key="dept.id">
                                                <option :value="dept.id" x-text="dept.name" :selected="dept.id == deptId"></option>
                                            </template>
                                        </select>
                                        <p x-show="!branchId" class="mt-1 text-xs text-amber-500">Сначала выберите филиал</p>
                                        <p x-show="branchId && filteredDepts.length === 0" class="mt-1 text-xs text-gray-400">Добавьте отделы в этот филиал</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">Статус</label>
                                        <select name="is_active" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <option value="1" @selected($u->is_active)>✅ Активен</option>
                                            <option value="0" @selected(!$u->is_active)>🚫 Заблокирован</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Multi-branch access (не для CEO — у них и так всё) --}}
                                @if($u->role !== 'ceo')
                                @php $userBranchIds = $u->accessibleBranches->pluck('id')->toArray(); @endphp
                                <div class="mb-4">
                                    <label class="block text-xs text-gray-600 mb-2 font-semibold">
                                        Доступ к филиалам
                                        <span class="ml-1 font-normal text-gray-400">(для просмотра дашбордов)</span>
                                    </label>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($branches as $br)
                                        @php
                                            $dotCls = match($br->color){'blue'=>'bg-blue-400','violet'=>'bg-violet-400','purple'=>'bg-purple-400','amber'=>'bg-amber-400',default=>'bg-emerald-400'};
                                            $checked = in_array($br->id, $userBranchIds);
                                        @endphp
                                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer transition-all
                                                       {{ $checked ? 'bg-emerald-50 border-emerald-300' : 'bg-white border-gray-200 hover:border-gray-300' }}">
                                            <input type="checkbox"
                                                   name="accessible_branches[]"
                                                   value="{{ $br->id }}"
                                                   {{ $checked ? 'checked' : '' }}
                                                   class="w-3.5 h-3.5 rounded accent-emerald-600">
                                            <span class="w-2 h-2 rounded-full {{ $dotCls }}"></span>
                                            <span class="text-xs font-medium text-gray-700">{{ $br->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-400">
                                        Если выбрано несколько — пользователь видит сводный дашборд. Если ни одного — видит только свой филиал.
                                    </p>
                                </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                                        Сохранить
                                    </button>
                                    <button type="button" @click="editOpen = null" class="px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                                        Отмена
                                    </button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">{{ $users->links() }}</div>
    @endif
</div>

@php
    $deptsJson = $departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'branch_id' => $d->branch_id])->values()->toJson();
@endphp
<script>
const _allDepts = {!! $deptsJson !!};

function userEditForm(branchId, deptId) {
    return {
        branchId: branchId,
        deptId:   deptId,
        get filteredDepts() {
            if (!this.branchId) return [];
            return _allDepts.filter(d => d.branch_id == this.branchId);
        },
        onBranchChange() {
            const stillValid = this.filteredDepts.some(d => d.id == this.deptId);
            if (!stillValid) this.deptId = null;
        }
    };
}

function deleteUserModal() {
    return {
        open: false, formAction: '', userName: '',
        init() {
            window.addEventListener('open-delete-user', (e) => {
                this.formAction = e.detail.action;
                this.userName   = e.detail.name;
                this.open       = true;
            });
        },
        close() { this.open = false; }
    }
}
</script>
@endsection
