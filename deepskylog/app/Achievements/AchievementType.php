<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Str;

abstract class AchievementType
{
    const string BEGINNER = 'beginner';

    /**
     * Get the level for the achievement type.
     */
    public function level(): string
    {
        return self::BEGINNER;
    }

    /**
     * Get the icon for the achievement type.
     */
    public function icon(): string
    {
        return Str::lcfirst(class_basename($this)).'blade.php';
    }

    abstract public function description(): string;

    abstract public function qualifier(User $user): bool;
}
