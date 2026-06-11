@extends('layouts.app')
@section('title', $deptStats['department']->name)
@section('page-title', $deptStats['department']->name)

@section('content')
@php
    use App\Services\KpiService;
    $kpiService = app(KpiService::class);
    $months = [1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',
               7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'];
    $colorMap = [
        'critical' => ['badge'=>'bg-red-100 text-red-700',      'bar'=>'bg-red-500'],
        'lag'      => ['badge'=>'bg-yellow-100 text-yellow-700', 'bar'=>'bg-yellow-400'],
        'on_track' => ['badge'=>'bg-emerald-100 text-emerald-700','bar'=>'bg-emerald-500'],
        'ahead'    => ['badge'=>'bg-blue-100 text-blue-700',     'bar'=>'bg-blue-500'],
        'no_data'  => ['badge'=>'bg-gray-100 text-gray-500',     'bar'=>'bg-gray-200'],
    ];
    $maxDays   = (int) config('kpi.fact_input_days', 7);
    $minDate   = now()->subDays($maxDays)->toDateString();
    $canManage = auth()->user()->canManagePlans();

    // Форматирование значений по единице измерения
    $fmt = function (float $v, string $unit, bool $exact = false): string {
        if ($unit === '%') {
            // Сохраняем до 2 знаков, убираем лишние нули: 32.50 → 32.5, 32.00 → 32
            return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
        }
        if ($unit === 'тнг') {
            return number_format((int) round($v), 0, '.', ' ');
        }
        // шт. и прочие: если exact=true (факт введён сотрудником) — сохраняем дробь
        if ($exact && $v != (int) $v) {
            return rtrim(rtrim(number_format($v, 2, '.', ' '), '0'), '.');
        }
        return number_format((int) round($v), 0, '.', ' ');
    };
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     СКРЫТАЯ ФОРМА ПЛАНОВ — вне таблицы, чтобы не конфликтовать
     с формами ввода факта. Поля заполняются через Alpine.js.
     Видна только CEO и коммерческому директору.
═══════════════════════════════════════════════════════════════ --}}
@if($canManage)
<form id="plansForm"
      action="{{ route('department.plans.update', $deptStats['department']) }}"
      method="POST" class="hidden">
    @csrf
    <input type="hidden" name="year"  value="{{ $year }}">
    <input type="hidden" name="month" value="{{ $month }}">
    @foreach($deptStats['kpi_stats'] as $s)
    <input type="hidden"
           id="planInput_{{ $s['kpi']->id }}"
           name="plans[{{ $s['kpi']->id }}]"
           value="{{ $s['plan_month'] }}">
    @endforeach
</form>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     ОСНОВНОЙ БЛОК
═══════════════════════════════════════════════════════════════ --}}
<div x-data="{ planMode: false }">

{{-- Шапка --}}
<div class="flex flex-wrap items-center gap-3 mt-4 mb-5">

    {{-- Выбор периода --}}
    <form method="GET" class="flex items-center gap-2">
        <select name="year" onchange="this.form.submit()"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @foreach(range(now()->year - 1, now()->year + 1) as $y)
            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>
        <select name="month" onchange="this.form.submit()"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @foreach($months as $num => $name)
            <option value="{{ $num }}" @selected($num == $month)>{{ $name }}</option>
            @endforeach
        </select>
    </form>

    {{-- Эффективность --}}
    @if($deptStats['effectiveness'] !== null)
    <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 border border-gray-100 shadow-sm">
        <span class="text-sm text-gray-500">Эффективность:</span>
        <span class="text-lg font-bold
            {{ $deptStats['eff_pct'] >= 100 ? 'text-emerald-600'
             : ($deptStats['eff_pct'] >= 80  ? 'text-yellow-600' : 'text-red-600') }}">
            {{ $deptStats['eff_pct'] }}%
        </span>
    </div>
    @endif

    {{-- Кнопки управления планами — ТОЛЬКО CEO и коммерческий директор --}}
    @if($canManage)
    <div class="ml-auto flex items-center gap-2">
        <button x-show="!planMode" @click="planMode = true"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700
                       text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                         m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Редактировать планы
        </button>
        <button x-show="planMode" x-cloak @click="planMode = false"
                class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200
                       text-gray-600 text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Отменить
        </button>
    </div>
    @endif
</div>

{{-- Баннер режима редактирования планов --}}
@if($canManage)
<div x-show="planMode" x-cloak
     class="mb-4 flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-sm">
    <svg class="w-5 h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Режим редактирования планов на
    <strong class="mx-1">{{ $months[$month] }} {{ $year }}</strong>.
    Введите значения и нажмите «Сохранить все планы».
</div>
@endif

{{-- Подсказка для рядовых сотрудников --}}
@if(!$canManage)
<div class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm">
    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Нажмите <strong class="mx-1">+ Факт</strong> рядом с нужным показателем, чтобы ввести данные за день.
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     ТАБЛИЦА KPI
═══════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in-up">

    {{-- Заголовок таблицы --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">KPI — {{ $deptStats['department']->name }}</h3>

        @if($canManage)
        <button type="submit" form="plansForm" x-show="planMode" x-cloak
                class="flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700
                       text-white text-sm font-semibold rounded-lg transition-colors shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Сохранить все планы
        </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="text-left px-6 py-3 font-medium">Показатель</th>
                    <th class="text-right px-4 py-3 font-medium">
                        @if($canManage)
                        <span x-show="!planMode">План (месяц)</span>
                        <span x-show="planMode" x-cloak class="text-blue-600 font-semibold">✎ Новый план</span>
                        @else
                        План (месяц)
                        @endif
                    </th>
                    <th class="text-right px-4 py-3 font-medium">План (сегодня)</th>
                    <th class="text-right px-4 py-3 font-medium">Факт</th>
                    <th class="text-right px-4 py-3 font-medium">Темп</th>
                    <th class="text-center px-4 py-3 font-medium">Статус</th>
                    <th class="text-center px-4 py-3 font-medium">Ввод факта</th>
                </tr>
            </thead>
            {{-- openFact хранит id KPI с открытой формой (null = все закрыты) --}}
            <tbody x-data="{ openFact: null }" class="divide-y divide-gray-50">

                @foreach($deptStats['kpi_stats'] as $s)
                @php
                    $colors = $colorMap[$s['status']] ?? $colorMap['no_data'];
                    $unit   = $s['kpi']->unit;
                    $kpiId  = $s['kpi']->id;
                @endphp

                {{-- ── Строка KPI ── --}}
                <tr :class="planMode ? 'bg-blue-50/30' : (openFact === {{ $kpiId }} ? 'bg-emerald-50/40' : 'hover:bg-gray-50')"
                    class="transition-colors">

                    {{-- Название --}}
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">{{ $s['kpi']->name }}</div>
                        @if($unit)
                        <div class="text-xs text-gray-400 mt-0.5">{{ $unit }} · вес {{ $s['kpi']->weight }}</div>
                        @endif
                    </td>

                    {{-- План месяц --}}
                    <td class="px-4 py-4 text-right">

                        {{-- Просмотр --}}
                        @if($canManage)
                        <span x-show="!planMode" class="font-medium text-gray-700">
                            {{ $s['plan_month'] > 0 ? $fmt($s['plan_month'], $unit) : '—' }}
                            @if($s['plan_month'] > 0)<span class="text-xs text-gray-400 ml-0.5">{{ $unit }}</span>@endif
                        </span>

                        {{-- Редактирование (только CEO / комдир) --}}
                        <div x-show="planMode" x-cloak>
                            <input type="number"
                                   step="any" min="0"
                                   value="{{ $s['plan_month'] }}"
                                   @input="document.getElementById('planInput_{{ $s['kpi']->id }}').value = $event.target.value"
                                   class="w-36 px-3 py-1.5 text-sm text-right border-2 border-blue-300
                                          rounded-lg focus:outline-none focus:border-blue-500 bg-white
                                          font-medium transition-colors">
                        </div>
                        @else
                        {{-- Обычный сотрудник — только просмотр --}}
                        <span class="font-medium text-gray-700">
                            {{ $s['plan_month'] > 0 ? $fmt($s['plan_month'], $unit) : '—' }}
                            @if($s['plan_month'] > 0)<span class="text-xs text-gray-400 ml-0.5">{{ $unit }}</span>@endif
                        </span>
                        @endif
                    </td>

                    {{-- План на сегодня --}}
                    <td class="px-4 py-4 text-right text-gray-600">
                        @if($s['plan_to_date'] > 0)
                            {{ $fmt($s['plan_to_date'], $unit) }}
                            <span class="text-xs text-gray-400 ml-0.5">{{ $unit }}</span>
                        @else
                            —
                        @endif
                    </td>

                    {{-- Факт --}}
                    <td class="px-4 py-4 text-right font-semibold text-gray-800">
                        {{ $fmt($s['fact'], $unit, true) }}
                        @if($s['fact'] > 0)<span class="text-xs text-gray-400 ml-0.5 font-normal">{{ $unit }}</span>@endif
                    </td>

                    {{-- Темп --}}
                    <td class="px-4 py-4 text-right">
                        @if($s['pace_pct'] !== null)
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full progress-bar {{ $colors['bar'] }}"
                                     style="width: {{ min($s['pace_pct'], 150) / 1.5 }}%"></div>
                            </div>
                            <span class="font-bold text-xs
                                {{ $s['pace_pct'] < 80  ? 'text-red-600'
                                 : ($s['pace_pct'] < 95  ? 'text-yellow-600'
                                 : ($s['pace_pct'] > 105 ? 'text-blue-600' : 'text-emerald-600')) }}">
                                {{ $s['pace_pct'] }}%
                            </span>
                        </div>
                        @else
                        <span class="text-gray-400 text-xs">Нет данных</span>
                        @endif
                    </td>

                    {{-- Статус --}}
                    <td class="px-4 py-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $colors['badge'] }}">
                            {{ $kpiService->getStatusLabel($s['status']) }}
                        </span>
                    </td>

                    {{-- Кнопки действий --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">

                            {{-- + Факт — доступно ВСЕМ, отключается только в режиме редактирования плана --}}
                            <button type="button"
                                    @click="openFact = (openFact === {{ $kpiId }}) ? null : {{ $kpiId }}"
                                    @if($canManage) :disabled="planMode"
                                    :class="planMode ? 'opacity-40 cursor-not-allowed' : (openFact === {{ $kpiId }} ? 'bg-emerald-200 text-emerald-800' : 'hover:bg-emerald-100 cursor-pointer')"
                                    @else
                                    :class="openFact === {{ $kpiId }} ? 'bg-emerald-200 text-emerald-800' : 'hover:bg-emerald-100 cursor-pointer'"
                                    @endif
                                    class="text-xs px-3 py-1.5 bg-emerald-50 text-emerald-700
                                           rounded-lg transition-colors font-medium">
                                <span x-text="openFact === {{ $kpiId }} ? '✕ Закрыть' : '+ Факт'">+ Факт</span>
                            </button>

                            <a href="{{ route('kpi.history', ['kpi' => $s['kpi'], 'year' => $year, 'month' => $month]) }}"
                               class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600
                                      hover:bg-gray-100 rounded-lg transition-colors">
                                История
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- ── Форма ввода факта ── --}}
                <tr x-show="openFact === {{ $kpiId }}" x-cloak class="bg-emerald-50/60">
                    <td colspan="7" class="px-6 py-4">
                        <form action="{{ route('kpi.fact.store', $s['kpi']) }}"
                              method="POST"
                              class="flex flex-wrap items-end gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-gray-600 mb-1 font-medium">Дата</label>
                                <input type="date" name="fact_date"
                                       value="{{ today()->toDateString() }}"
                                       min="{{ $minDate }}"
                                       max="{{ today()->toDateString() }}"
                                       required
                                       class="px-3 py-2 text-sm border border-emerald-200 rounded-lg
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1 font-medium">
                                    Значение {{ $unit ? "($unit)" : '' }}
                                </label>
                                <input type="number" name="value" step="any" required
                                       placeholder="0"
                                       class="w-40 px-3 py-2 text-sm border border-emerald-200 rounded-lg
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            </div>
                            <div class="flex-1 min-w-32">
                                <label class="block text-xs text-gray-600 mb-1 font-medium">Заметка</label>
                                <input type="text" name="note" maxlength="500"
                                       placeholder="Комментарий..."
                                       class="w-full px-3 py-2 text-sm border border-emerald-200 rounded-lg
                                              focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                                           text-white text-sm font-medium rounded-lg transition-colors">
                                Сохранить
                            </button>
                            <button type="button" @click="openFact = null"
                                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600
                                           text-sm rounded-lg hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                        </form>
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Нижняя панель сохранения планов --}}
    @if($canManage)
    <div x-show="planMode" x-cloak
         class="px-6 py-4 border-t border-blue-100 bg-blue-50/50 flex items-center justify-between">
        <span class="text-sm text-blue-700 font-medium">
            Планы на <strong>{{ $months[$month] }} {{ $year }}</strong>
        </span>
        <div class="flex items-center gap-3">
            <button type="button" @click="planMode = false"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600
                           text-sm rounded-lg hover:bg-gray-50 transition-colors">
                Отмена
            </button>
            <button type="submit" form="plansForm"
                    class="flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700
                           text-white text-sm font-semibold rounded-lg transition-colors shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить все планы
            </button>
        </div>
    </div>
    @endif

</div>{{-- /table card --}}
</div>{{-- /x-data --}}
@endsection
