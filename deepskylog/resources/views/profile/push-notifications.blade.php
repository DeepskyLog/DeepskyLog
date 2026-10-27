{{-- Push notifications of the alerts in this browser (App\Services\PushNotifications, public/sw.js) --}}
<x-action-section>
    <x-slot name="title">
        {{ __("Push notifications") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Get the clear night alert, the reminder of sessions without observations and the notice that your PDF is ready as a notification on this phone or computer, as soon as they are sent.") }}
    </x-slot>

    <x-slot name="content">
        <script>
            function dslPushNotifications(config) {
                const texts = {
                    failed: @js(__('Push notifications could not be turned on. Please try again later.')),
                    testSent: @js(__('A test notification was sent.')),
                    testFailed: @js(__('The test notification could not be sent.')),
                };

                function keyBytes(base64) {
                    const padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');
                    return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
                }

                function sameKey(buffer, bytes) {
                    if (!buffer) {
                        return false;
                    }
                    const a = new Uint8Array(buffer);
                    return a.length === bytes.length && a.every((value, i) => value === bytes[i]);
                }

                function send(method, url, body) {
                    return fetch(url, {
                        method: method,
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(body),
                    });
                }

                return {
                    state: 'checking',
                    busy: false,
                    message: '',
                    registration: null,

                    async init() {
                        const ios = /iPad|iPhone|iPod/.test(navigator.userAgent)
                            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                        if (!config.key || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                            this.state = ios ? 'ios' : 'unsupported';
                            return;
                        }
                        if (Notification.permission === 'denied') {
                            this.state = 'denied';
                            return;
                        }
                        try {
                            this.registration = await navigator.serviceWorker.register('/sw.js');
                            await navigator.serviceWorker.ready;
                            let subscription = await this.registration.pushManager.getSubscription();
                            // Signed with other keys: the browser would refuse the notifications
                            if (subscription && !sameKey(subscription.options.applicationServerKey, keyBytes(config.key))) {
                                await subscription.unsubscribe();
                                subscription = null;
                            }
                            if (subscription && Notification.permission === 'granted') {
                                // Also makes it this observer's when another one used this browser before
                                await send('POST', config.store, subscription.toJSON());
                                this.state = 'on';
                            } else {
                                this.state = 'off';
                            }
                        } catch (e) {
                            this.state = 'unsupported';
                        }
                    },

                    async subscribe() {
                        this.busy = true;
                        this.message = '';
                        try {
                            const permission = await Notification.requestPermission();
                            if (permission !== 'granted') {
                                this.state = permission === 'denied' ? 'denied' : 'off';
                                return;
                            }
                            const subscription = await this.registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: keyBytes(config.key),
                            });
                            const response = await send('POST', config.store, subscription.toJSON());
                            if (!response.ok) {
                                throw new Error(String(response.status));
                            }
                            this.state = 'on';
                        } catch (e) {
                            this.message = texts.failed;
                        } finally {
                            this.busy = false;
                        }
                    },

                    async unsubscribe() {
                        this.busy = true;
                        this.message = '';
                        try {
                            const subscription = await this.registration.pushManager.getSubscription();
                            if (subscription) {
                                await send('DELETE', config.destroy, { endpoint: subscription.endpoint });
                                await subscription.unsubscribe();
                            }
                            this.state = 'off';
                        } finally {
                            this.busy = false;
                        }
                    },

                    async test() {
                        this.busy = true;
                        this.message = '';
                        try {
                            const subscription = await this.registration.pushManager.getSubscription();
                            const response = subscription ? await send('POST', config.test, { endpoint: subscription.endpoint }) : null;
                            this.message = response && response.ok ? texts.testSent : texts.testFailed;
                        } catch (e) {
                            this.message = texts.testFailed;
                        } finally {
                            this.busy = false;
                        }
                    },
                };
            }
        </script>
        <div
            x-data="dslPushNotifications(@js([
                'key' => \App\Services\PushNotifications::publicKey(),
                'store' => route('push-subscriptions.store'),
                'destroy' => route('push-subscriptions.destroy'),
                'test' => route('push-subscriptions.test'),
            ]))"
            class="space-y-3 text-sm"
        >
            <p x-show="state === 'checking'" x-cloak>{{ __("Checking this browser…") }}</p>
            <p x-show="state === 'unsupported'" x-cloak>{{ __("This browser cannot receive push notifications.") }}</p>
            <p x-show="state === 'ios'" x-cloak>
                {{ __("On an iPhone or iPad, add DeepskyLog to the home screen first (Share, Add to Home Screen), open it from there and turn the notifications on in its profile page.") }}
            </p>
            <p x-show="state === 'denied'" x-cloak>
                {{ __("Notifications are blocked for DeepskyLog in this browser. Allow them in the site settings of the browser, then reload this page.") }}
            </p>

            <div x-show="state === 'off'" x-cloak class="space-y-3">
                <p>{{ __("Push notifications are off on this device.") }}</p>
                <x-button secondary type="button" x-on:click="subscribe()" x-bind:disabled="busy" label="{{ __('Turn on push notifications on this device') }}" />
            </div>

            <div x-show="state === 'on'" x-cloak class="space-y-3">
                <p>{{ __("Push notifications are on for this device.") }}</p>
                <div class="flex flex-wrap gap-2">
                    <x-button secondary type="button" x-on:click="test()" x-bind:disabled="busy" label="{{ __('Send a test notification') }}" />
                    <x-button flat type="button" x-on:click="unsubscribe()" x-bind:disabled="busy" label="{{ __('Turn off on this device') }}" />
                </div>
            </div>

            <p x-show="message" x-text="message" x-cloak class="text-gray-400"></p>

            <p class="text-xs text-gray-400">
                {{ __("The settings above still decide which of these messages you get. Push notifications are set per device and end when you log out in that browser.") }}
            </p>
        </div>
    </x-slot>
</x-action-section>
