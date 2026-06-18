<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — {{ \App\Models\Setting::get('company_name', 'Artgroups') }} ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-700 flex items-center justify-center p-4">

@php
    $deptsJson = $departments->map(fn($d) => ['id'=>$d->id,'name'=>$d->name,'branch_id'=>$d->branch_id])->values()->toJson();
    $companyName = \App\Models\Setting::get('company_name', 'Artgroups');
    $companyLogo = \App\Models\Setting::get('company_logo');
    $logoSrc     = $companyLogo ? asset('storage/' . $companyLogo) : asset('images/artlogo.png');
@endphp

<div class="w-full max-w-lg" x-data="registerForm()">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center bg-white rounded-2xl shadow-lg mb-3 px-5 py-3">
            <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="h-16 w-auto object-contain">
        </div>
        <h1 class="text-2xl font-bold text-white">{{ $companyName }} ERP</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-7">
        <h2 class="text-lg font-semibold text-gray-800 mb-5">Регистрация</h2>

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Имя и фамилия *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Иван Иванов"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm @error('name') border-red-400 @enderror">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@artgroups.kz"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm @error('email') border-red-400 @enderror">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Роль *</label>
                <select name="role" required
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-white @error('role') border-red-400 @enderror">
                    <option value="">— Выберите роль —</option>
                    @foreach($roles as $key => $label)
                    <option value="{{ $key }}" @selected(old('role') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Филиал + Отдел --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Филиал</label>
                    <select name="branch_id" x-model="branchId" @change="onBranchChange()"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-white">
                        <option value="">— Не выбрано —</option>
                        @foreach($branches as $br)
                        <option value="{{ $br->id }}" @selected(old('branch_id') == $br->id)>{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Отдел
                        <span x-show="branchId && filteredDepts.length === 0" class="font-normal text-gray-400" x-cloak>(нет отделов)</span>
                    </label>
                    <select name="department_id" x-model="deptId"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-white">
                        <option value="">— Без отдела —</option>
                        <template x-for="dept in filteredDepts" :key="dept.id">
                            <option :value="dept.id" x-text="dept.name"></option>
                        </template>
                    </select>
                    <p x-show="!branchId" class="mt-1 text-xs text-gray-400" x-cloak>Сначала выберите филиал</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Пароль *</label>
                    <input type="password" name="password" required minlength="8" placeholder="минимум 8 символов"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm @error('password') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Подтверждение *</label>
                    <input type="password" name="password_confirmation" required placeholder="повторите пароль"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>
            </div>

            <p class="text-xs text-gray-500 bg-amber-50 px-3 py-2 rounded-xl border border-amber-200">
                После регистрации аккаунт будет активирован администратором.
            </p>

            <button type="submit"
                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors text-sm shadow-sm">
                Зарегистрироваться
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">← Уже есть аккаунт</a>
        </div>
    </div>
</div>

<script>
const _regDepts = {!! $deptsJson !!};
function registerForm() {
    return {
        branchId: '{{ old('branch_id', '') }}',
        deptId:   '{{ old('department_id', '') }}',
        get filteredDepts() {
            if (!this.branchId) return [];
            return _regDepts.filter(d => d.branch_id == this.branchId);
        },
        onBranchChange() {
            if (!this.filteredDepts.some(d => d.id == this.deptId)) this.deptId = '';
        }
    };
}
</script>
</body>
</html>
