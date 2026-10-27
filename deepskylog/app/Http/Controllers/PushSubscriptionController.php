<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Push notifications of a browser (App\Services\PushNotifications): the
 * profile page turns them on (store) and off (destroy) and sends a test
 * notification; the service worker hands over a renewed subscription
 * (renew), which a browser may make by itself.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateSubscription($request);
        PushNotifications::subscribe($request->user(), $data, $request->userAgent());

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        PushSubscription::where('endpoint_hash', PushSubscription::hashOf($data['endpoint']))
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * A test notification to the browser that asked for it.
     */
    public function test(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        $subscription = PushSubscription::where('endpoint_hash', PushSubscription::hashOf($data['endpoint']))
            ->where('user_id', $request->user()->id)
            ->first();
        if ($subscription === null) {
            return response()->json(['ok' => false], 404);
        }

        $delivered = app(PushNotifications::class)->deliver(
            [$subscription],
            'DeepskyLog',
            __('Push notifications work on this device.'),
            route('profile.show'),
            'test',
            300,
        );

        return response()->json(['ok' => $delivered > 0]);
    }

    /**
     * The browser replaced its subscription (the service worker's
     * pushsubscriptionchange). Without a CSRF token or login, as the service
     * worker has neither: knowing the old endpoint, which only the browser
     * and DeepskyLog know, proves the subscription is the browser's.
     */
    public function renew(Request $request): JsonResponse
    {
        $request->validate(['old_endpoint' => ['required', 'string', 'max:2048']]);
        $old = PushSubscription::forEndpoint((string) $request->input('old_endpoint'));
        if ($old === null) {
            return response()->json(['ok' => false], 404);
        }

        $data = $this->validateSubscription($request);
        $user = $old->user;
        if ($old->endpoint_hash !== PushSubscription::hashOf($data['endpoint'])) {
            $old->delete();
        }
        PushNotifications::subscribe($user, $data, $request->userAgent());

        return response()->json(['ok' => true]);
    }

    /**
     * @return array{endpoint: string, keys: array{p256dh: string, auth: string}, contentEncoding?: string|null}
     */
    private function validateSubscription(Request $request): array
    {
        return $request->validate([
            'endpoint'        => ['required', 'string', 'max:2048', 'url:https'],
            'keys.p256dh'     => ['required', 'string', 'max:255'],
            'keys.auth'       => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:16'],
        ]);
    }
}
