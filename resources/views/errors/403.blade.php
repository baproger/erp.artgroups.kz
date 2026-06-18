<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Сюда нельзя 🙅</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }

        @keyframes floaty   { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-18px) rotate(-6deg); } }
        @keyframes shakeNo  { 0%,100% { transform: rotate(0deg); } 25% { transform: rotate(12deg); } 75% { transform: rotate(-12deg); } }
        @keyframes popIn    { from { opacity:0; transform: scale(.7); } to { opacity:1; transform: scale(1); } }
        @keyframes sweat    { 0% { opacity:0; transform: translateY(-6px) scale(.5); } 40% { opacity:1; } 100% { opacity:0; transform: translateY(26px) scale(1); } }
        @keyframes blob     { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-20px) scale(1.1); } 66% { transform: translate(-20px,20px) scale(.95); } }

        .floaty  { animation: floaty 3.5s ease-in-out infinite; }
        .shake-no{ animation: shakeNo 1.2s ease-in-out infinite; display:inline-block; }
        .pop-in  { animation: popIn .5s cubic-bezier(.18,.89,.32,1.28) forwards; }
        .blob    { animation: blob 14s ease-in-out infinite; }
        .delay-1 { animation-delay:.1s; opacity:0; }
        .delay-2 { animation-delay:.25s; opacity:0; }
        .delay-3 { animation-delay:.4s; opacity:0; }
        .sweat   { animation: sweat 2.2s ease-in infinite; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-700 overflow-hidden flex items-center justify-center p-6 relative">

    {{-- Фоновые пятна --}}
    <div class="absolute top-[-100px] left-[-100px] w-96 h-96 bg-emerald-400/20 rounded-full blob"></div>
    <div class="absolute bottom-[-120px] right-[-80px] w-80 h-80 bg-teal-400/20 rounded-full blob" style="animation-delay:5s"></div>

    <div class="relative text-center max-w-lg">

        {{-- Охранник --}}
        <div class="relative inline-block mb-2">
            <div class="text-[110px] leading-none floaty select-none">💂</div>
            <span class="absolute top-2 right-2 text-4xl shake-no select-none">🚫</span>
            {{-- капелька пота для драматизма --}}
            <span class="absolute top-8 left-10 text-xl sweat select-none">💧</span>
        </div>

        {{-- 403 --}}
        <h1 class="text-8xl sm:text-9xl font-black text-white tracking-tight pop-in">403</h1>

        <h2 class="text-2xl sm:text-3xl font-extrabold text-emerald-200 mt-2 pop-in delay-1">
            Стоп! Тут вход по пропускам 🪪
        </h2>

        <p class="text-emerald-100/80 text-base sm:text-lg mt-4 leading-relaxed pop-in delay-2">
            Охранник Серик проверил твою роль и вежливо, но твёрдо сказал:
            <span class="font-semibold text-white">«Сюда низя»</span>.
            Этот раздел не для твоих глаз 👀 — может, ты искал что-то другое?
        </p>

        @if(! empty($exception) && $exception->getMessage())
        <p class="inline-block mt-4 text-xs text-emerald-300/70 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 pop-in delay-2">
            🛡️ {{ $exception->getMessage() }}
        </p>
        @endif

        {{-- Кнопки --}}
        <div class="flex flex-wrap items-center justify-center gap-3 mt-8 pop-in delay-3">
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                🏠 На главную, от греха подальше
            </a>
            <button onclick="history.back()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all">
                ↩️ Назад
            </button>

            {{-- Кнопка-обманка: убегает от курсора, кликнуть нельзя 😈 --}}
            <button id="sneakyBtn" type="button"
                    style="transition: left .12s ease, top .12s ease; z-index:60;"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-amber-400 text-amber-950 font-bold rounded-xl shadow-lg">
                😏 Всё равно зайду
            </button>
        </div>

        {{-- Подпись-троллинг под бегающей кнопкой --}}
        <p id="sneakyMsg" class="text-amber-300/80 text-sm font-medium mt-5 h-5 transition-opacity duration-300"></p>

        <p class="text-emerald-300/50 text-xs mt-10 pop-in delay-3">
            Если ты уверен, что доступ нужен — попроси администратора повысить тебя в звании 😎
        </p>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('sneakyBtn');
            const msg = document.getElementById('sneakyMsg');
            if (!btn) return;

            const phrases = [
                'Не так быстро 😜',
                'Мимо! 🏃',
                'Поймай меня 😆',
                'Даже не мечтай 🙅',
                'Ха-ха, не выйдет 😂',
                'Серик начеку 💂',
                'Упорный! Но нет 🚫',
                'Сдавайся уже 🤣',
            ];
            let freed = false;
            let tries = 0;

            function moveAway() {
                const w = window.innerWidth, h = window.innerHeight;
                const bw = btn.offsetWidth, bh = btn.offsetHeight;

                if (!freed) {
                    btn.style.position = 'fixed';
                    freed = true;
                }

                // случайная точка в пределах экрана (с отступами)
                const x = Math.max(10, Math.floor(Math.random() * (w - bw - 20)));
                const y = Math.max(10, Math.floor(Math.random() * (h - bh - 20)));
                btn.style.left = x + 'px';
                btn.style.top  = y + 'px';

                if (msg) {
                    msg.textContent = phrases[tries % phrases.length];
                    msg.style.opacity = '1';
                }
                tries++;
            }

            // Убегает при наведении и при клике (вдруг успел)
            btn.addEventListener('mouseenter', moveAway);
            btn.addEventListener('click', function (e) { e.preventDefault(); moveAway(); });
            btn.addEventListener('touchstart', function (e) { e.preventDefault(); moveAway(); }, { passive: false });

            // Убегает, как только курсор подбирается близко
            document.addEventListener('mousemove', function (e) {
                const r = btn.getBoundingClientRect();
                const cx = r.left + r.width / 2;
                const cy = r.top + r.height / 2;
                const dist = Math.hypot(e.clientX - cx, e.clientY - cy);
                if (dist < 50) moveAway(); // отпрыгивает только когда курсор совсем близко
            });
        })();
    </script>

</body>
</html>
