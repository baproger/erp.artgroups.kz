<?php

return [
    'fact_input_days' => env('KPI_FACT_INPUT_DAYS', 7),

    // Час (Asia/Almaty), с которого колокольчик подсвечивает незаполненные факты.
    'remind_hour' => (int) env('KPI_REMIND_HOUR', 17),
];
