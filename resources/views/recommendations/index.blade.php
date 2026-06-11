@extends('layouts.app')
@section('title', 'Рекомендации')
@section('page-title', 'Рекомендации системы')

@section('content')
@php
    $typeLabels = ['all'=>'Все','critical_lag'=>'Критические','department_drop'=>'Падение отдела','missing_fact'=>'Пропуск факта'];
    $typeColors = [
        'critical_lag'    => ['bg'=>'bg-red-50',    'text'=>'text-red-800',   'border'=>'border-red-200',   'badge'=>'bg-red-100 text-red-700',   'icon'=>'🔴'],
        'department_drop' => ['bg'=>'bg-amber-50',  'text'=>'text-amber-800', 'border'=>'border-amber-200', 'badge'=>'bg-amber-100 text-amber-700','icon'=>'⚠️'],
        'missing_fact'    => ['bg'=>'bg-blue-50',   'text'=>'text-blue-800',  'border'=>'border-blue-200',  'badge'=>'bg-blue-100 text-blue-700',  'icon'=>'📋'],
    ];
@endphp

{{-- Stats strip --}}
<div class="mt-4 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 fade-in-up">
        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Активных</div>
        <div class="text-3xl font-black {{ $counts['active'] > 0 ? 'text-amber-500' : 'text-emerald-600' }}">{{ $counts['active'] }}</div>
    </div>
    <div class="bg-red-50 border border-red-100 rounded-2xl shadow-sm px-5 py-4 fade-in-up">
        <div class="text-xs text-red-400 uppercase tracking-wide mb-1">Критических</div>
        <div class="text-3xl font-black text-red-500">{{ $counts['critical'] }}</div>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-2xl shadow-sm px-5 py-4 fade-in-up">
        <div class="text-xs text-amber-400 uppercase tracking-wide mb-1">Падение отдела</div>
        <div class="text-3xl font-black text-amber-500">{{ $counts['drop'] }}</div>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-2xl shadow-sm px-5 py-4 fade-in-up">
        <div class="text-xs text-blue-400 uppercase tracking-wide mb-1">Пропуск факта</div>
        <div class="text-3xl font-black text-blue-500">{{ $counts['missing'] }}</div>
    </div>
</div>

{{-- Filters + Generate button --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2">
        {{-- Status filter --}}
        @foreach(['active'=>'Активные','dismissed'=>'Принятые','all'=>'Все'] as $s => $sl)
        <a href="{{ route('rec.index', array_merge(request()->query(), ['status' => $s])) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all border
                  {{ $status === $s ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}">
            {{ $sl }}
        </a>
        @endforeach

        <span class="text-gray-300">|</span>

        {{-- Type filter --}}
        @foreach($typeLabels as $t => $tl)
        <a href="{{ route('rec.index', array_merge(request()->query(), ['type' => $t])) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all border
                  {{ $filter === $t ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}">
            @if($t === 'critical_lag') 🔴 @elseif($t === 'department_drop') ⚠️ @elseif($t === 'missing_fact') 📋 @endif
            {{ $tl }}
        </a>
        @endforeach
    </div>

    <form action="{{ route('rec.generate') }}" method="POST">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Обновить рекомендации
        </button>
    </form>
</div>

{{-- Flash message --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif

{{-- Recommendations list --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up">
    @forelse($recommendations as $rec)
    @php
        $tc = $typeColors[$rec->type] ?? ['bg'=>'bg-gray-50','text'=>'text-gray-700','border'=>'border-gray-100','badge'=>'bg-gray-100 text-gray-600','icon'=>'ℹ️'];
        $deptUrl = $rec->department_id ? route('department.view', $rec->department_id) : null;
    @endphp
    <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0
                {{ $rec->is_dismissed ? 'opacity-50' : '' }} hover:bg-gray-50/60 transition-colors">

        {{-- Icon --}}
        <div class="w-9 h-9 rounded-xl {{ $tc['bg'] }} border {{ $tc['border'] }} flex items-center justify-center text-lg shrink-0 mt-0.5">
            {{ $tc['icon'] }}
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $tc['badge'] }}">
                    {{ $typeLabels[$rec->type] ?? $rec->type }}
                </span>
                @if($rec->department)
                <span class="text-xs text-gray-400">
                    {{ $rec->department->branch?->name }} / {{ $rec->department->name }}
                </span>
                @endif
                <span class="text-xs text-gray-300">{{ $rec->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $rec->message }}</p>
            @if($rec->is_dismissed && $rec->dismissedBy)
            <p class="mt-1 text-xs text-gray-400">Принято: {{ $rec->dismissedBy->name }} · {{ $rec->dismissed_at?->format('d.m.Y H:i') }}</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            @if($deptUrl)
            <a href="{{ $deptUrl }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-medium rounded-lg transition-colors">
                Перейти
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endif
            @if(!$rec->is_dismissed)
            <form action="{{ route('rec.dismiss', $rec) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-500 hover:bg-gray-200 text-xs font-medium rounded-lg transition-colors">
                    Принять ✓
                </button>
            </form>
            @else
            <span class="px-3 py-1.5 bg-gray-50 text-gray-400 text-xs rounded-lg">Принято</span>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="text-gray-500 font-medium mb-1">Нет рекомендаций</div>
        <div class="text-gray-400 text-sm">
            @if($status === 'active') Все рекомендации приняты или система не выявила проблем
            @elseif($status === 'dismissed') Ещё нет принятых рекомендаций
            @else Рекомендаций не найдено
            @endif
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($recommendations->hasPages())
<div class="mt-4">{{ $recommendations->links() }}</div>
@endif

@endsection
