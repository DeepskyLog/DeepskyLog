<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Web push: a notification on the phones and computers in which the observer
 * turned on push notifications on the profile page (push_subscriptions), next
 * to the DeepskyLog message of the clear night alert, the session reminder
 * and a ready or failed PDF. The service worker (public/sw.js) shows it and
 * opens its url when it is tapped.
 *
 * The notification is encrypted for each browser and signed with the VAPID
 * keys of services.webpush (php artisan webpush:vapid makes them); without
 * keys, or with WEB_PUSH=false (phpunit.xml), nothing is sent. A browser
 * whose push service answers that the subscription is gone (404/410) is
 * removed. Sending never throws: the message is what counts.
 */
class PushNotifications
{
    /** Seconds the push service keeps a notification for a browser that is offline. */
    public const DEFAULT_TTL = 12 * 3600;

    /** An observer keeps at most this many browsers; the least recently used go. */
    public const MAX_PER_USER = 20;

    /** Seconds to wait for a push service. */
    public const TIMEOUT = 10;

    public static function enabled(): bool
    {
        return (bool) config('services.webpush.enabled')
            && filled(config('services.webpush.public_key'))
            && filled(config('services.webpush.private_key'));
    }

    public static function publicKey(): ?string
    {
        return self::enabled() ? (string) config('services.webpush.public_key') : null;
    }

    /**
     * Send a notification to all browsers of the observer.
     *
     * @param  string  $title  plain text, in the observer's language
     * @param  string  $body  plain text, in the observer's language
     * @param  string  $url  the page that opens when the notification is tapped
     * @param  string|null  $tag  a notification with the same tag replaces the one shown
     * @return int the number of browsers that accepted it
     */
    public static function send(User $user, string $title, string $body, string $url, ?string $tag = null, int $ttl = self::DEFAULT_TTL): int
    {
        return app(self::class)->deliver($user->pushSubscriptions()->get()->all(), $title, $body, $url, $tag, $ttl);
    }

    /**
     * @param  list<PushSubscription>  $subscriptions
     */
    public function deliver(array $subscriptions, string $title, string $body, string $url, ?string $tag = null, int $ttl = self::DEFAULT_TTL): int
    {
        if ($subscriptions === [] || ! self::enabled()) {
            return 0;
        }

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'tag'   => $tag,
            'icon'  => asset('images/icons/icon-192.png'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $client = $this->client();
            $byHash = [];
            foreach ($subscriptions as $subscription) {
                $byHash[$subscription->endpoint_hash] = $subscription;
                $client->queueNotification(new Subscription(
                    $subscription->endpoint,
                    $subscription->public_key,
                    $subscription->auth_token,
                    $subscription->content_encoding ?: 'aes128gcm',
                ), $payload, ['TTL' => $ttl, 'urgency' => 'normal']);
            }

            $delivered = 0;
            foreach ($client->flush() as $report) {
                $subscription = $byHash[PushSubscription::hashOf($report->getEndpoint())] ?? null;
                if ($report->isSuccess()) {
                    $delivered++;
                    $subscription?->forceFill(['last_used_at' => now()])->save();
                } elseif ($report->isSubscriptionExpired()) {
                    $subscription?->delete();
                } else {
                    Log::info('Push notification refused', ['user_id' => $subscription?->user_id, 'reason' => $report->getReason()]);
                }
            }

            return $delivered;
        } catch (\Throwable $e) {
            Log::warning('Failed to send push notifications', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * Store the browser's subscription for the observer. A browser holds one
     * subscription, so one that belonged to another observer (who logged in
     * in the same browser before) moves to this one.
     *
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}, contentEncoding?: string|null}  $data
     */
    public static function subscribe(User $user, array $data, ?string $userAgent = null): PushSubscription
    {
        $subscription = PushSubscription::updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashOf($data['endpoint'])],
            [
                'user_id'          => $user->id,
                'endpoint'         => $data['endpoint'],
                'public_key'       => $data['keys']['p256dh'],
                'auth_token'       => $data['keys']['auth'],
                'content_encoding' => in_array($data['contentEncoding'] ?? null, ['aes128gcm', 'aesgcm'], true) ? $data['contentEncoding'] : 'aes128gcm',
                'user_agent'       => $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
            ],
        );

        $user->pushSubscriptions()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->skip(self::MAX_PER_USER)
            ->take(PHP_INT_MAX)
            ->get()
            ->each
            ->delete();

        return $subscription;
    }

    protected function client(): WebPush
    {
        return new WebPush(
            ['VAPID' => [
                'subject'    => (string) config('services.webpush.subject'),
                'publicKey'  => (string) config('services.webpush.public_key'),
                'privateKey' => (string) config('services.webpush.private_key'),
            ]],
            [],
            new Client(['timeout' => self::TIMEOUT, 'connect_timeout' => self::TIMEOUT]),
            logger: new class extends AbstractLogger {
                // Instead of trigger_error(), which Laravel turns into an
                // exception; the notice that GMP or BCMath would be faster is
                // left out (a notification takes milliseconds without them)
                public function log($level, \Stringable|string $message, array $context = []): void
                {
                    if (! in_array($level, [LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG], true)) {
                        Log::log($level, 'Web push: '.$message, $context);
                    }
                }
            },
        );
    }
}
