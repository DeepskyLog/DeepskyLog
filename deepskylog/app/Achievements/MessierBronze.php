<?php

namespace App\Achievements;

use App\Models\AccomplishmentsOld;
use App\Models\User;

class MessierBronze extends AchievementType
{
    public function description(): string
    {
        // TODO: Implement description() method.
    }

    public function qualifier(User $user): bool
    {
        return AccomplishmentsOld::where('observer', $user->username)->first()['messierGold'];
    }
}
