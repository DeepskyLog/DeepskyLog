<?php

namespace App\Console\Commands;

use App\Services\PushNotifications;
use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

/**
 * Makes the VAPID keys that sign the push notifications
 * (App\Services\PushNotifications), to put in .env. Make them once: new keys
 * end every browser's subscription, as the browsers only accept
 * notifications signed with the key they subscribed with.
 */
class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';

    protected $description = 'Make the VAPID keys for the push notifications';

    public function handle(): int
    {
        if (PushNotifications::enabled()) {
            $this->warn('VAPID keys are already set: replacing them ends all push subscriptions.');
        }

        $keys = VAPID::createVapidKeys();
        $this->line('Add these lines to .env, then run php artisan config:clear:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);

        return self::SUCCESS;
    }
}
