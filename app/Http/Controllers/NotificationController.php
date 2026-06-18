<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\KpiReminderService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private KpiReminderService $reminder) {}

    /**
     * Публичный VAPID-ключ для подписки браузера.
     */
    public function vapidKey()
    {
        return response()->json([
            'key' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Сохранить подписку браузера на push-уведомления.
     */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'         => 'required|string|max:500',
            'keys.p256dh'      => 'required|string',
            'keys.auth'        => 'required|string',
            'content_encoding' => 'nullable|string|max:30',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id'          => auth()->id(),
                'public_key'       => $data['keys']['p256dh'],
                'auth_token'       => $data['keys']['auth'],
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Удалить подписку (отписка).
     */
    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('endpoint', $request->endpoint)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Список KPI, по которым сотрудник ещё не ввёл факт за сегодня.
     * Используется для напоминания пока вкладка открыта (мгновенно, без 17:00 ограничения сервером).
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
