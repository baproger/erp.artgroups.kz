<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\User;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Час, с которого напоминать о незаполненных фактах (Asia/Almaty).
     */
    private const REMIND_FROM_HOUR = 17;

    /**
     * Список KPI, по которым сотрудник ещё не ввёл факт за сегодня.
     * Используется фронтендом для push-уведомления с 17:00.
     */
    public function unfilledFacts()
    {
        /** @var User $user */
        $user  = auth()->user();
        $today = today()->toDateString();

        $departments = [];
        $count = 0;

        foreach ($user->fillableDepartments() as $dept) {
            $missing = Kpi::where('department_id', $dept->id)
                ->where('is_active', true)
                ->whereDoesntHave('facts', fn($q) => $q->whereDate('fact_date', $today))
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

        $now = Carbon::now(); // Asia/Almaty (config/app.php)

        return response()->json([
            'should_notify' => $count > 0 && $now->hour >= self::REMIND_FROM_HOUR,
            'count'         => $count,
            'departments'   => $departments,
            'today'         => $today,
            'server_hour'   => $now->hour,
        ]);
    }
}
