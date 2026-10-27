<?php

use App\Jobs\GenerateObjectSetsPdf;
use App\Jobs\NotifyQueuedPdfFinished;
use App\Models\ObservationSession;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\PushNotifications;
use App\Services\SessionObservationReminders;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;

/**
 * A push service that answers each endpoint with the status in $statuses
 * (201 when not given) and records what it was asked to send.
 */
function fakePushService(array $statuses = []): object
{
    config([
        'services.webpush.enabled' => true,
        'services.webpush.public_key' => 'BPReqeXP04JWTM-nIPnPhhG5TUjoJ0dWrYwJqq3RwSjlzOPBqvHItefan8qgQcZMSxmbm5gL84-RcXNir1a_c-c',
        'services.webpush.private_key' => 'Q8ryIfbj8sZacPOBGDWc5JhpBy2QUGxjGDNxowcCCCI',
    ]);

    $fake = new class extends PushNotifications {
        public array $statuses = [];

        public array $sent = [];

        protected function client(): WebPush
        {
            $client = Mockery::mock(WebPush::class);
            $client->shouldReceive('queueNotification')->andReturnUsing(function ($subscription, $payload, $options) {
                $this->sent[] = ['endpoint' => $subscription->getEndpoint(), 'payload' => json_decode($payload, true), 'options' => $options];
            });
            $client->shouldReceive('flush')->andReturnUsing(function () {
                foreach ($this->sent as $sent) {
                    $status = $this->statuses[$sent['endpoint']] ?? 201;
                    yield new MessageSentReport(new Request('POST', $sent['endpoint']), new Response($status), $status < 300, (string) $status);
                }
            });

            return $client;
        }
    };
    $fake->statuses = $statuses;
    app()->instance(PushNotifications::class, $fake);

    return $fake;
}

function pushSubscriptionData(string $endpoint = 'https://push.example.com/send/abc'): array
{
    return ['endpoint' => $endpoint, 'keys' => ['p256dh' => 'BKey' . md5($endpoint), 'auth' => 'auth' . md5($endpoint)], 'contentEncoding' => 'aes128gcm'];
}

it('stores, renews and removes the subscription of a browser', function () {
    $user = User::factory()->create();

    $this->postJson(route('push-subscriptions.store'), pushSubscriptionData())->assertUnauthorized();

    $this->actingAs($user)->postJson(route('push-subscriptions.store'), pushSubscriptionData())->assertOk();
    $this->actingAs($user)->postJson(route('push-subscriptions.store'), pushSubscriptionData())->assertOk();
    expect($user->pushSubscriptions()->count())->toBe(1);

    // Only https endpoints
    $this->actingAs($user)->postJson(route('push-subscriptions.store'), pushSubscriptionData('http://push.example.com/x'))->assertUnprocessable();

    // The service worker hands over a renewed subscription without login or CSRF token
    auth()->logout();
    $this->postJson(route('push-subscriptions.renew'), pushSubscriptionData('https://push.example.com/send/new') + ['old_endpoint' => 'https://push.example.com/send/unknown'])
        ->assertNotFound();
    $this->postJson(route('push-subscriptions.renew'), pushSubscriptionData('https://push.example.com/send/new') + ['old_endpoint' => 'https://push.example.com/send/abc'])
        ->assertOk();
    expect($user->pushSubscriptions()->pluck('endpoint')->all())->toBe(['https://push.example.com/send/new']);

    $this->actingAs($user)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => 'https://push.example.com/send/new'])->assertOk();
    expect($user->pushSubscriptions()->count())->toBe(0);
});

it('gives a browser to the observer who last turned it on, and keeps the newest browsers', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    PushNotifications::subscribe($first, pushSubscriptionData());
    PushNotifications::subscribe($second, pushSubscriptionData());
    expect($first->pushSubscriptions()->count())->toBe(0)
        ->and($second->pushSubscriptions()->count())->toBe(1);

    for ($i = 0; $i < PushNotifications::MAX_PER_USER + 2; $i++) {
        $this->travel(1)->minutes();
        PushNotifications::subscribe($first, pushSubscriptionData('https://push.example.com/send/' . $i));
    }
    expect($first->pushSubscriptions()->count())->toBe(PushNotifications::MAX_PER_USER)
        ->and(PushSubscription::forEndpoint('https://push.example.com/send/0'))->toBeNull()
        ->and(PushSubscription::forEndpoint('https://push.example.com/send/' . (PushNotifications::MAX_PER_USER + 1)))->not->toBeNull();
});

it('sends to all browsers of the observer and forgets the ones that are gone', function () {
    $user = User::factory()->create();
    PushNotifications::subscribe($user, pushSubscriptionData('https://push.example.com/send/phone'));
    PushNotifications::subscribe($user, pushSubscriptionData('https://push.example.com/send/old'));
    PushNotifications::subscribe(User::factory()->create(), pushSubscriptionData('https://push.example.com/send/other'));

    $fake = fakePushService(['https://push.example.com/send/old' => 410]);

    expect(PushNotifications::send($user, 'Title', 'Text', 'https://deepskylog.test/x', 'tag'))->toBe(1)
        ->and(collect($fake->sent)->pluck('endpoint')->sort()->values()->all())->toBe(['https://push.example.com/send/old', 'https://push.example.com/send/phone'])
        ->and($fake->sent[0]['payload'])->toMatchArray(['title' => 'Title', 'body' => 'Text', 'url' => 'https://deepskylog.test/x', 'tag' => 'tag'])
        ->and(PushSubscription::forEndpoint('https://push.example.com/send/old'))->toBeNull()
        ->and(PushSubscription::forEndpoint('https://push.example.com/send/phone')->last_used_at)->not->toBeNull();
});

it('sends nothing without VAPID keys', function () {
    $user = User::factory()->create();
    PushNotifications::subscribe($user, pushSubscriptionData());
    $fake = fakePushService();
    config(['services.webpush.private_key' => null]);

    expect(PushNotifications::send($user, 'Title', 'Text', '/'))->toBe(0)
        ->and($fake->sent)->toBe([]);
});

it('sends a test notification only to a browser of the observer', function () {
    $user = User::factory()->create();
    PushNotifications::subscribe($user, pushSubscriptionData());
    PushNotifications::subscribe(User::factory()->create(), pushSubscriptionData('https://push.example.com/send/other'));
    $fake = fakePushService();

    $this->actingAs($user)->postJson(route('push-subscriptions.test'), ['endpoint' => 'https://push.example.com/send/other'])->assertNotFound();
    $this->actingAs($user)->postJson(route('push-subscriptions.test'), ['endpoint' => 'https://push.example.com/send/abc'])->assertOk()->assertJson(['ok' => true]);
    expect($fake->sent)->toHaveCount(1);
});

it('pushes the session reminder and the notice of a ready file', function () {
    $user = User::factory()->create(['username' => 'pushed', 'language' => 'en', 'sendMail' => false]);
    PushNotifications::subscribe($user, pushSubscriptionData());
    $fake = fakePushService();

    $session = ObservationSession::query()->create([
        'name' => 'Empty night',
        'observerid' => $user->username,
        'begindate' => now()->subDays(3)->setTime(21, 0)->toDateTimeString(),
        'enddate' => now()->subDays(2)->setTime(2, 0)->toDateTimeString(),
        'locationid' => 0,
        'language' => 'en',
        'active' => 1,
    ]);
    expect(SessionObservationReminders::send($user, collect([$session])))->toBeTrue()
        ->and($fake->sent[0]['payload']['title'])->toBe('Your session has no observations yet')
        ->and($fake->sent[0]['payload']['url'])->toBe(SessionObservationReminders::addObservationUrl($session));

    $token = 'pushtoken';
    Cache::put(GenerateObjectSetsPdf::statusKey($token), ['state' => 'done', 'user_id' => $user->id, 'label' => 'M 31', 'completed_at' => now()->toIso8601String()], now()->addHour());
    (new NotifyQueuedPdfFinished(GenerateObjectSetsPdf::class, $token, $user->id, 'en', true))->handle();

    expect($fake->sent)->toHaveCount(2)
        ->and($fake->sent[1]['payload']['title'])->toContain('Your file is ready')
        ->and($fake->sent[1]['payload']['url'])->toBe(route('downloads.mine'));
});

it('offers push notifications on the profile page only with VAPID keys', function () {
    expect(PushNotifications::enabled())->toBeFalse();

    fakePushService();
    $this->actingAs(User::factory()->create());
    expect(PushNotifications::enabled())->toBeTrue();
    $this->view('profile.push-notifications')
        ->assertSee('Turn on push notifications on this device')
        ->assertSee(config('services.webpush.public_key'));
});
