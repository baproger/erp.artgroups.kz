@extends('layouts.app')
@section('title', 'Мой профиль')
@section('page-title', 'Мой профиль')

@section('content')
@php
    $avatarUrl = $user->avatarUrl();
    $initials  = $user->initials();
@endphp

<div class="max-w-2xl mx-auto mt-6 space-y-6">

    {{-- ─── Аватар ─── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up"
         x-data="avatarUpload()">

        <div class="px-6 py-5 flex items-center gap-6">

            {{-- Avatar preview --}}
            <div class="relative shrink-0">
                <div class="w-20 h-20 rounded-full overflow-hidden ring-4 ring-emerald-100 shadow-md">
                    @if($avatarUrl)
                    <img id="avatarPreview" src="{{ $avatarUrl }}" alt="{{ $user->name }}"
                         class="w-full h-full object-cover">
                    @else
                    <div id="avatarInitials"
                         class="w-full h-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-2xl select-none">
                        {{ $initials }}
                    </div>
                    @endif
                </div>
                {{-- Camera badge --}}
                <label for="avatarInput"
                       class="absolute -bottom-1 -right-1 w-7 h-7 bg-emerald-600 hover:bg-emerald-700 rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors"
                       title="Загрузить фото">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </label>
            </div>

            {{-- Info + actions --}}
            <div class="flex-1 min-w-0">
                <div class="text-base font-semibold text-gray-800 truncate">{{ $user->name }}</div>
                <div class="text-sm text-gray-500">{{ $user->getRoleLabel() }}</div>
                @if($user->department)
                <div class="text-xs text-emerald-600 mt-0.5">{{ $user->department->name }}</div>
                @endif

                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <label for="avatarInput"
                           class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Загрузить фото
                    </label>

                    @if($avatarUrl)
                    <form method="POST" action="{{ route('profile.avatar.destroy') }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Удалить фото
                        </button>
                    </form>
                    @endif

                    <span class="text-xs text-gray-400">JPG, PNG, WebP · макс. 2 МБ</span>
                </div>
            </div>
        </div>

        {{-- Hidden upload form --}}
        <form id="avatarForm" method="POST" action="{{ route('profile.avatar') }}"
              enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" id="avatarInput" name="avatar"
                   accept="image/jpeg,image/png,image/webp,image/gif"
                   @change="previewAndSubmit($event)">
        </form>

        @error('avatar')
        <div class="px-6 pb-4 text-xs text-red-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- ─── Карточка: основные данные ─── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up" style="animation-delay:.05s">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Личные данные</h3>
                <p class="text-xs text-gray-400">Имя и email отображаются в системе</p>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Имя и фамилия</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500
                              @error('email') border-red-400 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
                    <div class="px-4 py-2.5 border border-gray-100 rounded-xl bg-gray-50 text-sm text-gray-500">
                        {{ $user->getRoleLabel() }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Отдел</label>
                    <div class="px-4 py-2.5 border border-gray-100 rounded-xl bg-gray-50 text-sm text-gray-500">
                        {{ $user->department?->name ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Сохранить изменения
                </button>
            </div>
        </form>
    </div>

    {{-- ─── Карточка: смена пароля ─── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up" style="animation-delay:.1s">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Смена пароля</h3>
                <p class="text-xs text-gray-400">Минимум 8 символов</p>
            </div>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Текущий пароль</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('current_password') border-red-400 @enderror"
                       placeholder="••••••••">
                @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Новый пароль</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('password') border-red-400 @enderror"
                       placeholder="••••••••">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Подтверждение нового пароля</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="••••••••">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Изменить пароль
                </button>
            </div>
        </form>
    </div>

    {{-- ─── Карточка: инфо об аккаунте ─── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5 fade-in-up" style="animation-delay:.15s">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Информация об аккаунте</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Статус</span>
                <span class="font-medium {{ $user->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $user->is_active ? 'Активен' : 'Заблокирован' }}
                </span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Регистрация</span>
                <span class="font-medium text-gray-700">{{ $user->created_at->format('d.m.Y') }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-500">Последнее обновление</span>
                <span class="font-medium text-gray-700">{{ $user->updated_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>
    </div>

</div>

<script>
function avatarUpload() {
    return {
        previewAndSubmit(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                // Swap initials div for image preview
                const container = document.querySelector('.w-20.h-20 > div, .w-20.h-20 > img');
                if (container) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-full object-cover';
                    container.parentNode.replaceChild(img, container);
                }
            };
            reader.readAsDataURL(file);

            // Submit after short delay to show preview
            setTimeout(() => document.getElementById('avatarForm').submit(), 300);
        }
    }
}
</script>
@endsection
