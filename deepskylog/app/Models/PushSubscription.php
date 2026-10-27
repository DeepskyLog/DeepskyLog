<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A browser in which the observer turned on push notifications
 * (App\Services\PushNotifications). Keyed on the SHA-256 of the endpoint:
 * a browser holds one subscription, of the observer who last turned it on.
 */
class PushSubscription extends Model
{
    protected $fillable = ['user_id', 'endpoint', 'endpoint_hash', 'public_key', 'auth_token', 'content_encoding', 'user_agent', 'last_used_at'];

    protected $hidden = ['endpoint', 'public_key', 'auth_token'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime'];
    }

    public static function hashOf(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }

    public static function forEndpoint(string $endpoint): ?self
    {
        return self::where('endpoint_hash', self::hashOf($endpoint))->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
