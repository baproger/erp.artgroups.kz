@extends('layouts.app')
@section('title', 'История — ' . $kpi->name)
@section('page-title', 'История: ' . $kpi->name)

@section('content')
@php
    $months   = [1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',
                 7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'];
    $isCeo    = auth()->user()->canManageUsers();
@endphp

{{-- Delete confirm modal (CEO only) --}}
@if($isCeo)
<div x-data="deleteModal()" x-cloak>
    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm">
    </div>

    {{-- Modal panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm pointer-events-auto">
            {{-- Icon --}}
            <div class="flex flex-col items-center px-6 pt-8 pb-2">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Удалить запись?</h3>
                <p class="text-sm text-gray-500 text-center mb-1">Это действие нельзя отменить.</p>
                <p class="text-xs text-gray-400 text-center" x-text="factInfo"></p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 px-6 py-5">
                <button @click="close()"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Отмена
                </button>

                {{-- Hidden form that gets submitted --}}
                <form :action="formAction" method="POST" x-ref="deleteForm" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="mt-4 mb-4 flex items-center gap-3">
    <a href="{{ route('department.view', $kpi->department) }}"
       class="text-sm text-emerald-600 hover:underline flex items-center gap-1">
        ← Назад к {{ $kpi->department->name }}
    </a>
</div>

@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
     class="mb-4 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-800">{{ $kpi->name }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $kpi->department->name }} · {{ $months[$month] }} {{ $year }}</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="year" onchange="this.form.submit()" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
            <select name="month" onchange="this.form.submit()" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                @foreach($months as $num => $name)
                <option value="{{ $num }}" @selected($num == $month)>{{ $name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($facts->isEmpty())
    <div class="py-12 text-center">
        <div class="text-gray-400 text-sm">Нет записей за этот период</div>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-6 py-3 font-medium">Дата</th>
                    <th class="text-right px-6 py-3 font-medium">Значение</th>
                    <th class="text-left px-6 py-3 font-medium">Заметка</th>
                    <th class="text-left px-6 py-3 font-medium">Кто ввёл</th>
                    <th class="text-left px-6 py-3 font-medium">Когда</th>
                    @if($isCeo)
                    <th class="text-center px-6 py-3 font-medium">Действия</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($facts as $fact)
                <tr x-data="{ editing: false }" class="hover:bg-gray-50 transition-colors">

                    {{-- View mode --}}
                    <td class="px-6 py-3 font-medium text-gray-700" x-show="!editing">
                        {{ $fact->fact_date->format('d.m.Y') }}
                    </td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800" x-show="!editing">
                        {{ number_format($fact->value, 2, '.', ' ') }}
                        <span class="text-xs text-gray-400">{{ $kpi->unit }}</span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-xs" x-show="!editing">{{ $fact->note ?: '—' }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs" x-show="!editing">{{ $fact->author?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-400 text-xs" x-show="!editing">{{ $fact->created_at->format('d.m.Y H:i') }}</td>

                    @if($isCeo)
                    {{-- Edit mode --}}
                    <td colspan="5" x-show="editing" x-cloak>
                        <form method="POST" action="{{ route('kpi.fact.update', $fact) }}"
                              class="flex flex-wrap items-center gap-2 px-4 py-3">
                            @csrf
                            @method('PUT')
                            <input type="date" name="fact_date"
                                   value="{{ $fact->fact_date->format('Y-m-d') }}"
                                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                            <input type="number" name="value" step="0.01"
                                   value="{{ $fact->value }}"
                                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm w-36 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                            <input type="text" name="note" placeholder="Заметка"
                                   value="{{ $fact->note }}"
                                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm flex-1 min-w-32 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                            <button type="submit"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">
                                Сохранить
                            </button>
                            <button type="button" @click="editing = false"
                                    class="text-gray-500 hover:text-gray-700 text-xs px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                        </form>
                    </td>

                    {{-- Actions column --}}
                    <td class="px-6 py-3 text-center whitespace-nowrap" x-show="!editing">
                        <div class="flex items-center justify-center gap-1">
                            <button @click="editing = true"
                                    class="inline-flex items-center gap-1 text-xs text-emerald-700 font-medium px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Изменить
                            </button>
                            <button @click="$dispatch('open-delete', {
                                        action: '{{ route('kpi.fact.destroy', $fact) }}',
                                        info: '{{ $fact->fact_date->format('d.m.Y') }} · {{ number_format($fact->value, 2, '.', ' ') }} {{ $kpi->unit }}'
                                    })"
                                    class="inline-flex items-center gap-1 text-xs text-red-600 font-medium px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Удалить
                            </button>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-50">
        {{ $facts->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@if($isCeo)
<div class="mt-3 text-xs text-gray-400 flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Редактирование и удаление фактов доступно только администратору
</div>
@endif

@if($isCeo)
<script>
function deleteModal() {
    return {
        open: false,
        formAction: '',
        factInfo: '',
        init() {
            window.addEventListener('open-delete', (e) => {
                this.formAction = e.detail.action;
                this.factInfo   = e.detail.info;
                this.open       = true;
            });
        },
        close() {
            this.open = false;
        }
    }
}
</script>
@endif
@endsection
