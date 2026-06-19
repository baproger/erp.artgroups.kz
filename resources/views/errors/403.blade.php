@php
    // Универсальная страница ошибок: код берём из переданного $code или из исключения
    $code = $code
        ?? (isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 403);

    // эмодзи · покачивающийся значок · заголовок · текст
    $map = [
        403 => ['💂', '🚫', 'Сюда нельзя',        'Охранник Серик проверил твою роль и вежливо, но твёрдо сказал: «Сюда низя». Этот раздел не для твоих глаз 👀.'],
        404 => ['🕵️', '🔎', 'Такой страницы нет', 'Серик-детектив обыскал весь офис, заглянул под стол и в холодильник — но этой страницы не существует. Возможно, ссылка устарела 🔗.'],
        419 => ['⏳', '🔄', 'Сессия истекла',     'Страница «протухла», пока ты отдыхал. Обнови и попробуй ещё раз.'],
        429 => ['🐢', '✋', 'Слишком быстро',      'Эй, не так шустро! Слишком много запросов подряд. Передохни пару секунд.'],
        500 => ['🔧', '💥', 'Что-то сломалось',   'Серик уже бежит с гаечным ключом чинить. Попробуй чуть позже.'],
        503 => ['🛠️', '😴', 'Идём на ТО',         'Сайт ненадолго прилёг на техобслуживание. Скоро вернёмся!'],
    ];
    [$face, $badge, $title, $text] = $map[$code] ?? ['🤷', '❓', 'Упс', 'Что-то пошло не так. Попробуй вернуться на главную.'];
@endphp
<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} — {{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }

        @keyframes floaty { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-18px) rotate(-6deg); } }
        @keyframes wobble { 0%,100% { transform: rotate(0deg); } 25% { transform: rotate(12deg); } 75% { transform: rotate(-12deg); } }
        @keyframes popIn  { from { opacity:0; transform: scale(.7); } to { opacity:1; transform: scale(1); } }
        @keyframes blob   { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.1); } 66% { transform: translate(-20px,20px) scale(.95); } }

        .floaty  { animation: floaty 3.5s ease-in-out infinite; }
        .wobble  { animation: wobble 1.6s ease-in-out infinite; display:inline-block; }
        .pop-in  { animation: popIn .5s cubic-bezier(.18,.89,.32,1.28) forwards; }
        .blob    { animation: blob 14s ease-in-out infinite; }
        .delay-1 { animation-delay:.1s; opacity:0; }
        .delay-2 { animation-delay:.25s; opacity:0; }
        .delay-3 { animation-delay:.4s; opacity:0; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-700 overflow-hidden flex items-center justify-center p-6 relative">

    {{-- Фоновые пятна --}}
    <div class="absolute top-[-100px] left-[-100px] w-96 h-96 bg-emerald-400/20 rounded-full blob"></div>
    <div class="absolute bottom-[-120px] right-[-80px] w-80 h-80 bg-teal-400/20 rounded-full blob" style="animation-delay:5s"></div>

    <div class="relative text-center max-w-lg">

        {{-- Персонаж --}}
        <div class="relative inline-block mb-2">
            <div class="text-[110px] leading-none floaty select-none">{{ $face }}</div>
            <span class="absolute top-2 right-1 text-4xl wobble select-none">{{ $badge }}</span>
        </div>

        {{-- Код --}}
        <h1 class="text-8xl sm:text-9xl font-black text-white tracking-tight pop-in">{{ $code }}</h1>

        <h2 class="text-2xl sm:text-3xl font-extrabold text-emerald-200 mt-2 pop-in delay-1">
            {{ $title }}
        </h2>

        <p class="text-emerald-100/80 text-base sm:text-lg mt-4 leading-relaxed pop-in delay-2">
            {{ $text }}
        </p>

        {{-- Кнопки --}}
        <div class="flex flex-wrap items-center justify-center gap-3 mt-8 pop-in delay-3">
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                🏠 На главную
            </a>
            <button onclick="history.back()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all">
                ↩️ Назад
            </button>
        </div>

        <p class="text-emerald-300/50 text-xs mt-10 pop-in delay-3">
            Если думаешь, что это ошибка — напиши администратору 🛠️
        </p>
    </div>

</body>
</html>
