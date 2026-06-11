@extends('layouts.app')
@section('title', $branchStats['branch']->name . ' — Филиал')
@section('page-title', 'Филиал: ' . $branchStats['branch']->name)

@section('content')
@php
    $branch  = $branchStats['branch'];
    $ep      = $branchStats['eff_pct'] ?? 0;
    $months  = [1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',
                7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'];

    $bGrad  = $branch->getBgClass();
    $bChart = $branch->getChartColor();

    $effClr = match(true) {
        $ep >= 95  => ['chart' => '#10b981', 'badge' => 'bg-emerald-100 text-emerald-700', 'bar' => 'from-emerald-400 to-teal-500'],
        $ep >= 80  => ['chart' => '#f59e0b', 'badge' => 'bg-amber-100 text-amber-700',    'bar' => 'from-amber-400 to-orange-400'],
        default    => ['chart' => '#f87171', 'badge' => 'bg-red-100 text-red-700',         'bar' => 'from-red-400 to-rose-400'],
    };
@endphp

{{-- Back + filter --}}
<div class="mt-4 mb-5 flex flex-wrap items-center justify-between gap-3">
    @if(auth()->user()->canSeeAllBranches())
    <a href="{{ route('dashboard') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors group">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Все филиалы
    </a>
    @else
    <div></div>
    @endif
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

{{-- Branch hero --}}
<div class="bg-gradient-to-r {{ $bGrad }} rounded-2xl p-6 mb-6 shadow-lg fade-in-up relative overflow-hidden">
    {{-- Decorative circles --}}
    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
    <div class="absolute top-4 right-20 w-16 h-16 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white/5 rounded-full"></div>

    <div class="relative flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 text-white/70 text-xs mb-2 bg-white/10 px-3 py-1 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $branch->city }}
            </div>
            <div class="text-white text-3xl font-black">{{ $branch->name }}</div>
            <div class="text-white/60 text-sm mt-1">{{ $months[$month] }} {{ $year }}</div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-center">
                <div class="text-white text-5xl font-black leading-none">{{ $ep }}%</div>
                <div class="text-white/60 text-xs mt-1">эффективность</div>
            </div>
            <div class="relative w-24 h-24">
                <canvas id="heroDonut" width="96" height="96"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-white text-base font-black">{{ $ep }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="relative grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-white/20">
        <div class="text-center">
            <div class="text-white text-2xl font-black">{{ $branchStats['dept_count'] }}</div>
            <div class="text-white/60 text-xs mt-0.5">отделов</div>
        </div>
        <div class="text-center">
            <div class="text-white text-2xl font-black">{{ $branchStats['kpi_count'] }}</div>
            <div class="text-white/60 text-xs mt-0.5">KPI показателей</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-black {{ $branchStats['problematic'] > 0 ? 'text-yellow-200' : 'text-white' }}">
                {{ $branchStats['problematic'] }}
            </div>
            <div class="text-white/60 text-xs mt-0.5">критичных</div>
        </div>
    </div>
</div>

{{-- Departments grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @forelse($branchStats['departments'] as $ds)
    @php
        $dep  = $ds['eff_pct'] ?? 0;
        $dcol = match(true) {
            $dep >= 95  => ['grad'=>'from-emerald-400 to-teal-500',  'badge'=>'bg-emerald-100 text-emerald-700', 'dot'=>'bg-emerald-400', 'text'=>'text-emerald-600'],
            $dep >= 80  => ['grad'=>'from-amber-400 to-orange-400',  'badge'=>'bg-amber-100 text-amber-700',    'dot'=>'bg-amber-400',   'text'=>'text-amber-600'],
            default     => ['grad'=>'from-red-400 to-rose-400',      'badge'=>'bg-red-100 text-red-600',        'dot'=>'bg-red-400',     'text'=>'text-red-500'],
        };
    @endphp
    <a href="{{ route('department.view', $ds['department']) }}"
       class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden block fade-in-up">

        {{-- Color top accent --}}
        <div class="h-1 bg-gradient-to-r {{ $dcol['grad'] }}"></div>

        <div class="p-5">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full {{ $dcol['dot'] }}"></div>
                    <div>
                        <div class="font-bold text-gray-800 group-hover:text-gray-900 transition-colors">{{ $ds['department']->name }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $ds['kpi_stats']->count() }} показателей</div>
                    </div>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $dcol['badge'] }}">{{ $dep }}%</span>
            </div>

            {{-- Progress bar --}}
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-400 mb-1.5">
                    <span>Эффективность</span><span class="font-medium {{ $dcol['text'] }}">{{ $dep }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full progress-bar bg-gradient-to-r {{ $dcol['grad'] }}" style="width:{{ min($dep,100) }}%"></div>
                </div>
            </div>

            {{-- Top 3 KPIs --}}
            @if($ds['kpi_stats']->count())
            <div class="space-y-1.5">
                @foreach($ds['kpi_stats']->take(3) as $ks)
                @php $kp = $ks['pace_pct'] ?? 0; @endphp
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 truncate flex-1 mr-2">{{ $ks['kpi']->name }}</span>
                    <span class="font-semibold shrink-0
                        {{ $ks['status']==='critical'?'text-red-500':($ks['status']==='lag'?'text-amber-600':($ks['status']==='ahead'?'text-blue-600':'text-emerald-600')) }}">
                        {{ $kp !== null ? $kp.'%' : '—' }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                <span>Перейти к отделу</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
    </a>
    @empty
    <div class="col-span-3 py-16 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="text-sm">Нет активных отделов в этом филиале</div>
    </div>
    @endforelse
</div>

{{-- Trend chart --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 fade-in-up">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full animate-pulse" style="background:{{ $bChart }}"></div>
            <h3 class="font-bold text-gray-800">Динамика — {{ $branch->name }}</h3>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">{{ $months[$month] }} {{ $year }}</span>
    </div>
    <div style="height:200px"><canvas id="branchTrend"></canvas></div>
</div>

<script>
(function(){
    // Hero donut
    const hd = document.getElementById('heroDonut');
    if(hd) new Chart(hd,{type:'doughnut',data:{datasets:[{data:[{{ $ep }},{{ max(0,100-$ep) }}],backgroundColor:['rgba(255,255,255,0.9)','rgba(255,255,255,0.2)'],borderWidth:0,borderRadius:4}]},options:{cutout:'72%',plugins:{legend:{display:false},tooltip:{enabled:false}},animation:{duration:1200,easing:'easeInOutQuart'}}});

    // Trend
    const tc = document.getElementById('branchTrend');
    if(!tc) return;
    const ctx2 = tc.getContext('2d');
    const grad = ctx2.createLinearGradient(0,0,0,200);
    grad.addColorStop(0,'{{ $bChart }}40');
    grad.addColorStop(1,'{{ $bChart }}00');
    const labels=@json(array_column($trend,'date'));
    const data=@json(array_column($trend,'eff'));
    new Chart(tc,{type:'line',data:{labels,datasets:[{data,borderColor:'{{ $bChart }}',backgroundColor:grad,borderWidth:2.5,tension:0.4,fill:true,pointRadius:4,pointBackgroundColor:'#fff',pointBorderColor:'{{ $bChart }}',pointBorderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.parsed.y+'%'},backgroundColor:'#1f2937',titleColor:'#f9fafb',cornerRadius:8,padding:10}},scales:{y:{min:0,max:120,grid:{color:'#f3f4f6'},ticks:{callback:v=>v+'%',font:{size:11},color:'#9ca3af'}},x:{grid:{display:false},ticks:{font:{size:11},color:'#9ca3af'}}},animation:{duration:1000}}});
})();
</script>
@endsection
