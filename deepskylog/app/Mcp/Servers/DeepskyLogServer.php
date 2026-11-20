<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateObservingList;
use App\Mcp\Tools\GetCatalogProgress;
use App\Mcp\Tools\GetMyProfile;
use App\Mcp\Tools\GetMyStatistics;
use App\Mcp\Tools\GetNightForecast;
use App\Mcp\Tools\GetObject;
use App\Mcp\Tools\GetObservation;
use App\Mcp\Tools\GetObservingList;
use App\Mcp\Tools\GetTonightsBest;
use App\Mcp\Tools\ListMyGoals;
use App\Mcp\Tools\ListMyInstrumentSets;
use App\Mcp\Tools\ListMyObservations;
use App\Mcp\Tools\ListMyObservingLists;
use App\Mcp\Tools\SearchObjects;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

/**
 * The MCP server of DeepskyLog (/mcp, routes/ai.php): an AI assistant the
 * observer connects with a personal access token reads their own data and
 * DeepskyLog's calculations through these tools. The tools read; only
 * CreateObservingList writes, and only with a token that has the 'write'
 * permission.
 */
#[Name('DeepskyLog')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
DeepskyLog is a logbook and planning site for visual amateur astronomers (deep-sky objects, comets, planets, the Moon, double stars). These tools act for one observer, the owner of the access token: their observations, equipment, locations, observing lists and goals, and DeepskyLog's calculations for them.

- Start with get-my-profile to learn the observer's standard telescope and location and the ids of their equipment and locations; the other tools default to the standard ones.
- Object names use DeepskyLog's spelling with a space ("M 31", "NGC 7331", "Hickson 92"); search-objects and get-object also accept other spellings and common names.
- The contrast reserve says how well an object stands out against the sky with a telescope: below -0.2 not visible, -0.2 to 0.1 questionable, 0.1 to 0.35 difficult, 0.35 to 0.5 quite difficult, 0.5 to 1 easy, above 1 very easy.
- Dates are the evening of the night (Y-m-d); times are local at the location.
- Link to the pages in the "url" fields when the observer wants to read more.
MARKDOWN)]
class DeepskyLogServer extends Server
{
    protected array $tools = [
        GetMyProfile::class,
        ListMyInstrumentSets::class,
        SearchObjects::class,
        GetObject::class,
        ListMyObservations::class,
        GetObservation::class,
        GetMyStatistics::class,
        GetTonightsBest::class,
        GetNightForecast::class,
        ListMyObservingLists::class,
        GetObservingList::class,
        GetCatalogProgress::class,
        ListMyGoals::class,
        CreateObservingList::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
