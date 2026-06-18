<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID ключи для Web Push
    |--------------------------------------------------------------------------
    | Один и тот же набор ключей должен использоваться и на локальной машине,
    | и на сервере — иначе подписки браузеров станут недействительными.
    | Значения по умолчанию подходят для работы «из коробки»; при желании
    | можно перегенерировать (php artisan webpush:vapid) и положить в .env.
    */
    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', env('APP_URL', 'https://erp.artgroups.kz')),
        'public_key'  => env('VAPID_PUBLIC_KEY',  'BOfM8Fconp0nDwHUJI1lByGA4S_1Ad4Oi5wvtiklOguKZp2PiP2yT1AHotTtI4P3_KifhT3hILv4OpqGK-Ypn6k'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'FVO_i6n9auHILbv0Ahm2ykqgYsPycN65DqIS59qLXUg'),
    ],

    // Час (Asia/Almaty), с которого рассылается напоминание о незаполненных фактах.
    'remind_hour' => (int) env('KPI_REMIND_HOUR', 17),
];
