<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Services\Weather\CoolDownAdvice;
use App\Services\Weather\NightForecast;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The cloud forecast of the coming nights at one of the observer\'s locations (Open-Meteo, CC BY 4.0, and the seeing of 7Timer or AstrArome): per night the dark hours, the Moon, the clear stretches, a verdict (clear_dark, clear_twilight, clear_moon, partly, cloudy, no_dark), the seeing, dew, and when to put the standard telescope outside to cool down. Optionally hour by hour for one night.')]
#[IsReadOnly]
class GetNightForecast extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'location_id'      => ['nullable', 'integer'],
            'hourly_for_night' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $user     = $request->user();
        $location = McpData::location($user, $validated['location_id'] ?? null);
        if ($location === null) {
            return Response::error(($validated['location_id'] ?? null)
                ? 'That location is not one of the observer\'s. get-my-profile lists their locations.'
                : 'The observer has no standard location; pass location_id.');
        }
        if (! NightForecast::enabled()) {
            return Response::error('The weather forecast is turned off on this DeepskyLog server.');
        }

        $nights = NightForecast::forLocation($location);
        if ($nights === null) {
            return Response::error('No forecast available right now (the location has no coordinates, or the weather service did not answer). Try again later.');
        }
        $instrument = McpData::instrument($user);
        $hourlyFor  = $validated['hourly_for_night'] ?? null;

        return Response::structured([
            'location' => McpData::plain($location->name),
            'timezone' => $location->timezone,
            'credit'   => 'Weather data by Open-Meteo.com (CC BY 4.0)',
            'nights'   => array_map(function (array $night) use ($instrument, $hourlyFor) {
                $coolDown = $instrument ? CoolDownAdvice::forNight($night, $instrument) : null;

                return array_filter([
                    'night'                      => $night['night'],
                    'verdict'                    => $night['verdict'],
                    'verdict_label'              => NightForecast::verdictLabel($night['verdict'], 'en'),
                    'summary'                    => NightForecast::summary($night, 'en'),
                    'dark_hours'                 => $night['dark'],
                    'moon'                       => $night['moon'] ? ['illuminated_percent' => $night['moon']['fraction'], 'up_from' => $night['moon']['from'], 'up_until' => $night['moon']['until']] : null,
                    'average_cloud_percent'      => $night['cloud'],
                    'clear_minutes'              => $night['clear_minutes'],
                    'clear_dark_minutes'         => $night['clear_dark_minutes'],
                    'longest_clear_dark_stretch' => $night['clear_dark'] ? array_intersect_key($night['clear_dark'], array_flip(['from', 'until', 'minutes'])) : null,
                    'seeing'                     => $night['seeing'],
                    'dew'                        => NightForecast::dewLabel($night, 'en'),
                    'cool_down'                  => $coolDown ? CoolDownAdvice::label($coolDown, $instrument, 'en') : null,
                    'hours'                      => $hourlyFor === $night['night'] ? array_map(fn (array $hour) => array_filter([
                        'time'             => $hour['time'],
                        'cloud_percent'    => $hour['cloud'],
                        'temperature_c'    => $hour['temperature'],
                        'dew_point_c'      => $hour['dew_point'],
                        'humidity_percent' => $hour['humidity'],
                        'wind_kmh'         => $hour['wind'],
                        'seeing_arcsec'    => $hour['seeing_arcsec'] ?? null,
                        'moon_up'          => $hour['moon']          ?? null,
                        'twilight'         => $hour['twilight']      ?? null,
                    ], fn ($value) => $value !== null), $night['hours']) : null,
                ], fn ($value) => $value !== null);
            }, $nights),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location_id'      => $schema->integer()->description('One of the observer\'s locations (default: the standard location).'),
            'hourly_for_night' => $schema->string()->format('date')->description('Also give the hours of this night (its evening, Y-m-d).'),
        ];
    }
}
