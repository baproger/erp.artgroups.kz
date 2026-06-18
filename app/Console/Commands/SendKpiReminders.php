<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\KpiReminderService;
use App\Services\WebPushService;
use Illuminate\Console\Command;

class SendKpiReminders extends Command
{
    protected $signature = 'kpi:remind';

    protected $description = 'Рассылает push-уведомления сотрудникам о незаполненных за сегодня KPI';

    public function handle(KpiReminderService $reminder, WebPushService $push): int
    {
        // Только активные сотрудники с подпиской на push
        $users = User::where('is_active', true)
            ->whereHas('pushSubscriptions')
            ->with('department', 'accessibleBranches')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            $data = $reminder->unfilledForUser($user);
            if ($data['count'] === 0) {
                continue;
            }

            $push->sendToUser($user, [
                'title' => '📋 Заполните KPI за сегодня',
                'body'  => $reminder->buildMessage($data),
                'url'   => $user->department ? url('/departments/' . $user->department_id) : url('/dashboard'),
                'icon'  => url('/images/artlogo.png'),
                'tag'   => 'kpi-reminder',
            ]);

            $sent++;
        }

        $this->info("Напоминания отправлены: {$sent} сотрудникам.");

        return self::SUCCESS;
    }
}
