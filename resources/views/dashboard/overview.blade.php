@extends('layouts.app')
@section('title', 'Обзор компании')
@section('page-title', 'Обзор компании')

@section('content')
@php
    $months = [1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',
               7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'];
    $effPct     = $companyStats['eff_pct'] ?? 0;
    $totalProb  = collect($branchesStats)->sum('problematic');
    $totalKpis  = collect($branchesStats)->sum('kpi_count');
    $totalDepts = collect($branchesStats)->sum('dept_count');

    $topGrad = match(true) {
        $effPct >= 95  => 'from-emerald-400 via-emerald-500 to-teal-500',
        $effPct >= 80  => 'from-amber-400 via-orange-400 to-orange-500',
        default        => 'from-red-400 via-rose-400 to-pink-500',
    };
@endphp

{{-- Branch switcher --}}
<div class="mt-4 mb-5">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                     bg-gradient-to-r from-gray-700 to-gray-800 text-white shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            Все филиалы
        </span>

        @foreach($branchesStats as $bs)
        @php
            $br    = $bs['branch'];
            $bep   = $bs['eff_pct'] ?? 0;
            $tGrad = $br->getBgClass();
        @endphp
        <a href="{{ route('branch.view', $br) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                  bg-white border border-gray-100 text-gray-600 hover:shadow-md hover:-translate-y-0.5
                  transition-all duration-200 shadow-sm">
            <span class="w-2 h-2 rounded-full {{ $br->getDotClass() }}"></span>
            {{ $br->name }}
            <span class="text-xs font-bold {{ $bep >= 95 ? 'text-emerald-600' : ($bep >= 80 ? 'text-amber-500' : 'text-red-500') }}">
                {{ $bep }}%
            </span>
        </a>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
            </span>
            <span class="text-sm text-gray-500" id="lastUpdated">обновлено {{ now()->format('H:i:s') }}</span>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="month" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                @foreach($months as $n => $lbl)
                <option value="{{ $n }}" @selected($n == $month)>{{ $lbl }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                @foreach(range(now()->year - 1, now()->year) as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Top stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Main eff card --}}
    <div class="col-span-2 lg:col-span-1 bg-gradient-to-br {{ $topGrad }} rounded-2xl p-5 text-white shadow-lg fade-in-up relative overflow-hidden">
        <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/5 rounded-full"></div>
        <div class="relative">
            <div class="text-xs font-semibold uppercase tracking-wider text-white/70 mb-2">Эффективность компании</div>
            <div class="text-5xl font-black leading-none" id="companyEff">{{ $effPct }}%</div>
            <div class="mt-3 text-white/60 text-xs">{{ $months[$month] }} {{ $year }}</div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100 shadow-sm fade-in-up flex flex-col justify-between" style="animation-delay:.05s">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-semibold text-blue-400 uppercase tracking-wider">Филиалы</div>
            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div class="text-4xl font-black text-blue-600">{{ count($branchesStats) }}</div>
        <div class="text-xs text-blue-400 mt-1">активных</div>
    </div>

    <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-5 border border-violet-100 shadow-sm fade-in-up flex flex-col justify-between" style="animation-delay:.08s">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-semibold text-violet-400 uppercase tracking-wider">Отделов</div>
            <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="text-4xl font-black text-violet-600">{{ $totalDepts }}</div>
        <div class="text-xs text-violet-400 mt-1">{{ $totalKpis }} KPI</div>
    </div>

    <div class="bg-gradient-to-br {{ $totalProb > 0 ? 'from-red-50 to-rose-50 border-red-100' : 'from-emerald-50 to-teal-50 border-emerald-100' }} rounded-2xl p-5 border shadow-sm fade-in-up flex flex-col justify-between" style="animation-delay:.1s">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-semibold {{ $totalProb > 0 ? 'text-red-400' : 'text-emerald-400' }} uppercase tracking-wider">Критических</div>
            <div class="w-8 h-8 rounded-xl {{ $totalProb > 0 ? 'bg-red-100' : 'bg-emerald-100' }} flex items-center justify-center">
                <svg class="w-4 h-4 {{ $totalProb > 0 ? 'text-red-500' : 'text-emerald-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="text-4xl font-black {{ $totalProb > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $totalProb }}</div>
        <div class="text-xs {{ $totalProb > 0 ? 'text-red-400' : 'text-emerald-400' }} mt-1">{{ $totalProb > 0 ? 'требуют внимания' : 'всё в норме' }}</div>
    </div>
</div>

{{-- Branch cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
@foreach($branchesStats as $bs)
@php
    $ep      = $bs['eff_pct'] ?? 0;
    $br      = $bs['branch'];
    $bGrad   = $br->getBgClass();
    $bChart  = $br->getChartColor();

    $effClr = match(true) {
        $ep >= 95  => ['text' => 'text-emerald-600', 'bar' => 'from-emerald-400 to-teal-500',    'bg' => 'bg-emerald-50',  'chart' => '#10b981'],
        $ep >= 80  => ['text' => 'text-amber-600',   'bar' => 'from-amber-400 to-orange-400',    'bg' => 'bg-amber-50',    'chart' => '#f59e0b'],
        default    => ['text' => 'text-red-500',     'bar' => 'from-red-400 to-rose-400',        'bg' => 'bg-red-50',      'chart' => '#f87171'],
    };
    $chartId = 'bd_'.$br->id;
@endphp
<a href="{{ route('branch.view', $br) }}"
   class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden block fade-in-up">

    {{-- Gradient header --}}
    <div class="bg-gradient-to-r {{ $bGrad }} px-5 py-4 relative overflow-hidden">
        <div class="absolute -top-3 -right-3 w-16 h-16 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-white/20"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <div class="text-white font-bold text-lg leading-tight">{{ $br->name }}</div>
                <div class="text-white/70 text-xs">{{ $br->city }}</div>
            </div>
            <svg class="w-5 h-5 text-white/50 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>

    <div class="px-5 py-4 flex items-center gap-4">
        <div class="relative shrink-0 w-20 h-20">
            <canvas id="{{ $chartId }}" width="80" height="80"></canvas>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-sm font-black {{ $effClr['text'] }}">{{ $ep }}%</span>
            </div>
        </div>
        <div class="flex-1">
            <div class="text-3xl font-black text-gray-800 leading-none">{{ $ep }}%</div>
            <div class="text-xs text-gray-400 mb-3">эффективность</div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-0.5 text-xs">
                <span class="text-gray-400">Отделов</span>
                <span class="font-semibold text-gray-700">{{ $bs['dept_count'] }}</span>
                <span class="text-gray-400">Критичных</span>
                <span class="font-semibold {{ $bs['problematic'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $bs['problematic'] }}</span>
            </div>
        </div>
    </div>

    @if($bs['departments']->count())
    <div class="px-5 pb-4 space-y-2 border-t border-gray-50 pt-3">
        @foreach($bs['departments']->take(4) as $ds)
        @php $dp = $ds['eff_pct'] ?? 0; @endphp
        <div class="flex items-center justify-between text-xs gap-2">
            <span class="text-gray-500 truncate flex-1">{{ $ds['department']->name }}</span>
            <div class="flex items-center gap-1.5 shrink-0">
                <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full progress-bar bg-gradient-to-r
                        {{ $dp >= 95 ? 'from-emerald-400 to-teal-400' : ($dp >= 80 ? 'from-amber-400 to-orange-400' : 'from-red-400 to-rose-400') }}"
                         style="width:{{ min($dp,100) }}%"></div>
                </div>
                <span class="font-semibold text-gray-700 w-8 text-right">{{ $dp }}%</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</a>
<script>
(function(){
    const ctx=document.getElementById('{{ $chartId }}');
    if(!ctx)return;
    new Chart(ctx,{type:'doughnut',data:{datasets:[{data:[{{ $ep }},{{ max(0,100-$ep) }}],backgroundColor:['{{ $effClr['chart'] }}','#f3f4f6'],borderWidth:0,borderRadius:4}]},options:{cutout:'72%',plugins:{legend:{display:false},tooltip:{enabled:false}},animation:{duration:1200,easing:'easeInOutQuart'}}});
})();
</script>
@endforeach
</div>

{{-- Trend + Recommendations --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 fade-in-up">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Динамика эффективности</h3>
            <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">{{ $months[$month] }} {{ $year }}</span>
        </div>
        <div style="height:200px"><canvas id="trendChart"></canvas></div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 fade-in-up flex flex-col" style="max-height:420px">
        <div class="flex items-center justify-between mb-3 shrink-0">
            <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                <span class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Рекомендации
                @if($recommendations->count() > 0)
                <span class="inline-block px-1.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $recommendations->count() }}</span>
                @endif
            </h3>
            <a href="{{ route('rec.index') }}"
               class="text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1 transition-colors">
                Все
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="overflow-y-auto flex-1 pr-1 space-y-0" style="scrollbar-width:thin;scrollbar-color:#d1fae5 transparent">
        @forelse($recommendations as $rec)
        @php
            $recColors = match($rec->type) {
                'critical_lag'    => ['bg'=>'bg-red-50',    'text'=>'text-red-800',   'border'=>'border-red-100',   'hover'=>'hover:bg-red-100',   'arrow'=>'text-red-400'],
                'department_drop' => ['bg'=>'bg-amber-50',  'text'=>'text-amber-800', 'border'=>'border-amber-100', 'hover'=>'hover:bg-amber-100', 'arrow'=>'text-amber-400'],
                default           => ['bg'=>'bg-blue-50',   'text'=>'text-blue-800',  'border'=>'border-blue-100',  'hover'=>'hover:bg-blue-100',  'arrow'=>'text-blue-400'],
            };
            $recIcon = match($rec->type) {
                'critical_lag'    => '🔴',
                'department_drop' => '⚠️',
                default           => '📋',
            };
            $deptUrl = $rec->department_id
                ? route('department.view', $rec->department_id)
                : null;
        @endphp
        <div x-data="{show:true}" x-show="show" class="mb-2">
            <div class="flex items-start gap-1 rounded-xl border {{ $recColors['bg'] }} {{ $recColors['border'] }} overflow-hidden">
                {{-- Кликабельная часть с ссылкой --}}
                @if($deptUrl)
                <a href="{{ $deptUrl }}"
                   class="flex items-start gap-2 p-2.5 flex-1 min-w-0 {{ $recColors['hover'] }} transition-colors group">
                    <span class="shrink-0 mt-0.5">{{ $recIcon }}</span>
                    <span class="flex-1 text-xs {{ $recColors['text'] }} leading-relaxed">{{ $rec->message }}</span>
                    <svg class="w-3 h-3 shrink-0 mt-0.5 {{ $recColors['arrow'] }} opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @else
                <div class="flex items-start gap-2 p-2.5 flex-1">
                    <span class="shrink-0">{{ $recIcon }}</span>
                    <span class="flex-1 text-xs {{ $recColors['text'] }} leading-relaxed">{{ $rec->message }}</span>
                </div>
                @endif
                {{-- Кнопка закрытия --}}
                <form action="{{ route('rec.dismiss',$rec) }}" method="POST" class="shrink-0 p-1.5 self-start mt-1">
                    @csrf
                    <button type="submit" @click="show=false"
                            class="w-5 h-5 flex items-center justify-center rounded-md text-gray-400
                                   hover:text-gray-600 hover:bg-white/60 transition-colors text-xs">
                        ✕
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="text-xs text-gray-400">Нет активных рекомендаций</div>
        </div>
        @endforelse
        </div>{{-- /scroll --}}
    </div>
</div>

<script>
(function(){
    const ctx=document.getElementById('trendChart');
    if(!ctx)return;
    const labels=@json(array_column($trend,'date'));
    const data=@json(array_column($trend,'eff'));
    const gradient=ctx.getContext('2d').createLinearGradient(0,0,0,200);
    gradient.addColorStop(0,'rgba(16,185,129,0.25)');
    gradient.addColorStop(1,'rgba(16,185,129,0)');
    new Chart(ctx,{type:'line',data:{labels,datasets:[{data,borderColor:'#10b981',backgroundColor:gradient,borderWidth:2.5,tension:0.4,fill:true,pointRadius:4,pointBackgroundColor:'#fff',pointBorderColor:'#10b981',pointBorderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.parsed.y+'%'},backgroundColor:'#1f2937',titleColor:'#f9fafb',bodyColor:'#d1fae5',cornerRadius:8,padding:10}},scales:{y:{min:0,max:120,grid:{color:'#f3f4f6'},ticks:{callback:v=>v+'%',font:{size:11},color:'#9ca3af'}},x:{grid:{display:false},ticks:{font:{size:11},color:'#9ca3af'}}},animation:{duration:1000}}});
})();
setInterval(()=>{
    fetch('{{ route('dashboard.live') }}?year={{ $year }}&month={{ $month }}')
        .then(r=>r.json()).then(d=>{
            const el=document.getElementById('companyEff');
            if(el)el.textContent=d.eff_pct+'%';
            const lu=document.getElementById('lastUpdated');
            if(lu)lu.textContent='обновлено '+d.updated_at;
        });
},30000);
</script>
@endsection
