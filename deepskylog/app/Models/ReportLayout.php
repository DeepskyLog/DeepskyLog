<?php

namespace App\Models;

use App\Services\ReportLayoutCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The layout of an object report: page settings and, for every field of an
 * object, where it goes. A layout without a user is a DeepskyLog default.
 *
 * @property array<string, string> $settings
 * @property array<string, array{line: int, x: float, width: float, style: string, before: string, after: string, legend: string}> $fields
 */
class ReportLayout extends Model
{
    protected $fillable = ['user_id', 'name', 'settings', 'fields'];

    protected $casts = [
        'settings' => 'array',
        'fields' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefaults(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function isDefault(): bool
    {
        return $this->user_id === null;
    }

    /**
     * The settings and fields with every value the catalogue knows filled in,
     * so a layout saved before a field existed still renders.
     *
     * @return array{settings: array<string, string>, fields: array<string, array<string, mixed>>}
     */
    public function definition(): array
    {
        return ReportLayoutCatalog::normalize([
            'settings' => $this->settings ?? [],
            'fields' => $this->fields ?? [],
        ]);
    }
}
