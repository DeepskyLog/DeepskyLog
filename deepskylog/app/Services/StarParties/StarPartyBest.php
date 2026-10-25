<?php

namespace App\Services\StarParties;

use App\Models\StarParty;
use App\Models\User;
use App\Services\TonightsBest;

/**
 * The objects worth observing at the site of a starparty (#315) in one of its
 * nights: TonightsBest at the site, leaving out the deep-sky objects of the
 * lists of the earlier nights, so each night has its own objects. The
 * planets and comets stay, as they belong to each night.
 */
class StarPartyBest
{
    /**
     * @return array<string, mixed>|null a result of TonightsBest::for()
     */
    public static function for(StarParty $party, ?User $user, string $night): ?array
    {
        $site    = $party->site();
        $exclude = [];
        foreach ($party->nights() as $earlier) {
            $result = TonightsBest::for($user, ['night' => $earlier, 'exclude' => $exclude], $site);
            if ($earlier === $night || $result === null) {
                return $result;
            }
            foreach ($result['objects'] as $object) {
                if ($object['kind'] === 'deepsky') {
                    $exclude[] = $object['name'];
                }
            }
        }

        return null;
    }
}
