<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\KpiReminderService;

class NotificationController extends Controller
{
    public function __construct(private KpiReminderService $reminder) {}

    /**
     * Список KPI, по которым сотрудник ещё не ввёл факт за сегодня.
     * Используется колокольчиком в шапке.
     */
    public function unfilledFacts()
    {
        /** @var User $user */
        $user = auth()->user();
        $data = $this->reminder->unfilledForUser($user);

        return response()->json([
            'should_notify' => $data['count'] > 0 && $this->reminder->isRemindTime(),
            'count'         => $data['count'],
            'departments'   => $data['departments'],
        ]);
    }
}
