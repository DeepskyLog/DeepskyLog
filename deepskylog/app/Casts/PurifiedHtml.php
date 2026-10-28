<?php

namespace App\Casts;

use App\Support\Html;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Purifies a rich-text attribute when it is saved. The views purify it again
 * on output, which also covers rows written before this cast or by SQL.
 */
class PurifiedHtml implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Html::purify((string) $value);
    }
}
