<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Страница потерялась 🧭</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }

        @keyframes floaty   { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-18px) rotate(6deg); } }
        @keyframes lookAround { 0%,100% { transform: rotate(-15deg); } 50% { transform: rotate(15deg); } }
        @keyframes popIn    { from { opacity:0; transform: scale(.7); } to { opacity:1; transform: scale(1); } }
        @keyframes blob     { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.1); } 66% { transform: translate(-20px,20px) scale(.95); } }

        .floaty   { animation: floaty 3.5s ease-in-out infinite; }
        .look     { animation: lookAround 2.5s ease-in-out infinite; display:inline-block; }
        .pop-in   { animation: popIn .5s cubic-bezier(.18,.89,.32,1.28) forwards; }
        .blob     { animation: blob 14s ease-in-out infinite; }
        .delay-1  { animation-delay:.1s; opacity:0; }
        .delay-2  { animation-delay:.25s; opacity:0; }
        .delay-3  { animation-delay:.4s; opacity:0; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-700 overflow-hidden flex items-center justify-center p-6 relative">

    {{-- Фоновые пятна --}}
    <div class="absolute top-[-100px] left-[-100px] w-96 h-96 bg-emerald-400/20 rounded-full blob"></div>
    <div class="absolute bottom-[-120px] right-[-80px] w-80 h-80 bg-teal-400/20 rounded-full blob" style="animation-delay:5s"></div>

    <div class="relative text-center max-w-lg">

        {{-- Детектив с лупой --}}
        <div class="relative inline-block mb-2">
            <div class="text-[110px] leading-none floaty select-none">🕵️</div>
            <span class="absolute bottom-3 right-0 text-4xl look select-none">🔎</span>
        </div>

        {{-- 404 --}}
        <h1 class="text-8xl sm:text-9xl font-black text-white tracking-tight pop-in">404</h1>

        <h2 class="text-2xl sm:text-3xl font-extrabold text-emerald-200 mt-2 pop-in delay-1">
            Такой страницы нет 🧐
        </h2>

        <p class="text-emerald-100/80 text-base sm:text-lg mt-4 leading-relaxed pop-in delay-2">
            Серик-детектив обыскал весь офис, заглянул под стол и в холодильник —
            но <span class="font-semibold text-white">этой страницы не существует</span>.
            Возможно, ссылка устарела, или тут опечатка в адресе 🔗
        </p>

        @if(! empty($exception) && $exception->getMessage())
        <p class="inline-block mt-4 text-xs text-emerald-300/70 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 pop-in delay-2">
            🔍 {{ $exception->getMessage() }}
        </p>
        @endif

        {{-- Кнопки --}}
        <div class="flex flex-wrap items-center justify-center gap-3 mt-8 pop-in delay-3">
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                🏠 Вернуться на главную
            </a>
            <button onclick="history.back()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all">
                ↩️ Назад
            </button>
        </div>

        <p class="text-emerald-300/50 text-xs mt-10 pop-in delay-3">
            Если уверен, что страница должна быть — проверь ссылку или сообщи администратору 🛠️
        </p>
    </div>

</body>
</html>
