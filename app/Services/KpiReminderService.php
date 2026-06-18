<?php

namespace App\Services;

use App\Models\Kpi;
use App\Models\User;
use Illuminate\Support\Carbon;

class KpiReminderService
{
    /**
     * Незаполненные за сегодня KPI для конкретного сотрудника.
     *
     * @return array{count:int, departments:array<int,array{department:string,kpis:array<int,string>}>}
     */
    public function unfilledForUser(User $user, ?string $date = null): array
    {
        $date = $date ?? today()->toDateString();

        $departments = [];
        $count = 0;

        foreach ($user->fillableDepartments() as $dept) {
            $missing = Kpi::where('department_id', $dept->id)
                ->where('is_active', true)
                ->whereDoesntHave('facts', fn($q) => $q->whereDate('fact_date', $date))
                ->orderBy('sort_order')
                ->pluck('name')
                ->all();

            if (! empty($missing)) {
                $label = $dept->branch
                    ? "{$dept->name} — {$dept->branch->name}"
                    : $dept->name;

                $departments[] = ['department' => $label, 'kpis' => $missing];
                $count += count($missing);
            }
        }

        return ['count' => $count, 'departments' => $departments];
    }

    /**
     * Короткий текст для уведомления: «Не заполнено N KPI: a, b, c …».
     */
    public function buildMessage(array $data): string
    {
        $names = [];
        foreach ($data['departments'] as $d) {
            foreach ($d['kpis'] as $k) {
                $names[] = $k;
            }
        }

        $preview = implode(', ', array_slice($names, 0, 4));
        $more = count($names) > 4 ? ' и ещё ' . (count($names) - 4) : '';

        return "Не заполнено {$data['count']} KPI за сегодня: {$preview}{$more}";
    }

    /**
     * Наступил ли час напоминания (Asia/Almaty).
     */
    public function isRemindTime(): bool
    {
        return Carbon::now()->hour >= (int) config('kpi.remind_hour', 17);
    }
}
