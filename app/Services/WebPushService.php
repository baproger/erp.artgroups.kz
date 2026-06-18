<?php

namespace App\Services;

use App\Models\PushSubscription as PushSubscriptionModel;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Отправляет уведомление на все подписки пользователя.
     * Недействительные подписки (404/410) удаляются.
     *
     * @param array $payload  ['title' => ..., 'body' => ..., 'url' => ..., 'icon' => ...]
     */
    public function sendToUser(\App\Models\User $user, array $payload): void
    {
        $subscriptions = $user->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        $json = json_encode($payload);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
            ]);

            $this->webPush->queueNotification($subscription, $json);
        }

        foreach ($this->webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                $statusCode = $report->getResponse()?->getStatusCode();
                // 404/410 — подписка больше не действительна, удаляем
                if (in_array($statusCode, [404, 410], true)) {
                    PushSubscriptionModel::where('endpoint', $report->getRequest()->getUri()->__toString())
                        ->delete();
                }
            }
        }
    }
}
