<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyObservationsSeeder extends Seeder
{
    public function run(): void
    {
        $legacy = DB::connection('mysqlOld');
        $app = DB::connection();

        DB::disableQueryLog();

        $userIdByUsername = DB::table('users')->pluck('id', 'username')->all();
        $cometNameById = DB::table('cometobjects')->pluck('name', 'id')->all();

        // Keep this seeder idempotent and deterministic.
        $app->statement('SET FOREIGN_KEY_CHECKS=0');
        $app->table('comet_observation_details')->truncate();
        $app->table('deepsky_observation_details')->truncate();
        $app->table('observations')->truncate();
        $app->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->seedDeepskyObservations($legacy, $app, $userIdByUsername);
        $this->seedCometObservations($legacy, $app, $userIdByUsername, $cometNameById);
    }

    private function seedDeepskyObservations($legacy, $app, array $userIdByUsername): void
    {
        $legacy->table('observations')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($app, $userIdByUsername): void {
                foreach ($rows as $row) {
                    $observer = html_entity_decode((string) ($row->observerid ?? ''));

                    $observationId = $app->table('observations')->insertGetId([
                        'legacy_source' => 'deepsky',
                        'legacy_id' => (int) $row->id,
                        'observer_username' => $observer,
                        'observer_user_id' => $userIdByUsername[$observer] ?? null,
                        'instrument_id' => $this->nullablePositiveInt($row->instrumentid ?? null),
                        'location_id' => $this->nullablePositiveInt($row->locationid ?? null),
                        'eyepiece_id' => $this->nullablePositiveInt($row->eyepieceid ?? null),
                        'filter_id' => $this->nullablePositiveInt($row->filterid ?? null),
                        'lens_id' => $this->nullablePositiveInt($row->lensid ?? null),
                        'observed_on' => $this->legacyDateToSqlDate($row->date ?? null),
                        'observed_time' => $this->legacyTimeToSqlTime($row->time ?? null),
                        'legacy_date_int' => $this->nullableInt($row->date ?? null),
                        'legacy_time_int' => $this->nullableInt($row->time ?? null),
                        'legacy_timestamp' => $this->nullableString($row->timestamp ?? null, 14),
                        'description' => $row->description,
                        'language' => $this->normalizeLanguage($row->language ?? null),
                        'has_drawing' => ((int) ($row->hasDrawing ?? 0)) === 1,
                        'target_type' => 'deepsky',
                        'target_table' => 'objects',
                        'target_key' => $this->nullableString($row->objectname ?? null, 191),
                        'target_name' => $this->nullableString($row->objectname ?? null, 255),
                        'seeing' => $this->nullableInt($row->seeing ?? null),
                        'limiting_magnitude' => $this->nullableFloat($row->limmag ?? null),
                        'sqm' => $this->nullableFloat($row->SQM ?? null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $app->table('deepsky_observation_details')->insert([
                        'observation_id' => $observationId,
                        'visibility' => $this->nullableInt($row->visibility ?? null),
                        'small_diameter' => $this->nullableFloat($row->smallDiameter ?? null),
                        'large_diameter' => $this->nullableFloat($row->largeDiameter ?? null),
                        'stellar' => $this->nullableInt($row->stellar ?? null),
                        'extended' => $this->nullableInt($row->extended ?? null),
                        'resolved' => $this->nullableInt($row->resolved ?? null),
                        'mottled' => $this->nullableInt($row->mottled ?? null),
                        'cluster_type' => $this->nullableString($row->clusterType ?? null, 1),
                        'unusual_shape' => $this->nullableInt($row->unusualShape ?? null),
                        'partly_unresolved' => $this->nullableInt($row->partlyUnresolved ?? null),
                        'color_contrasts' => $this->nullableInt($row->colorContrasts ?? null),
                        'magnification' => $this->nullableString($row->magnification ?? null, 16),
                        'equal_brightness' => $this->nullableInt($row->equalBrightness ?? null),
                        'nice_field' => $this->nullableInt($row->niceField ?? null),
                        'component1' => $this->nullableInt($row->component1 ?? null),
                        'component2' => $this->nullableInt($row->component2 ?? null),
                        'date_dec' => $this->nullableFloat($row->dateDec ?? null),
                    ]);
                }
            }, 'id');
    }

    private function seedCometObservations($legacy, $app, array $userIdByUsername, array $cometNameById): void
    {
        $legacy->table('cometobservations')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($app, $userIdByUsername, $cometNameById): void {
                foreach ($rows as $row) {
                    $observer = html_entity_decode((string) ($row->observerid ?? ''));
                    $cometObjectId = $this->nullablePositiveInt($row->objectid ?? null);

                    $observationId = $app->table('observations')->insertGetId([
                        'legacy_source' => 'comet',
                        'legacy_id' => (int) $row->id,
                        'observer_username' => $observer,
                        'observer_user_id' => $userIdByUsername[$observer] ?? null,
                        'instrument_id' => $this->nullablePositiveInt($row->instrumentid ?? null),
                        'location_id' => $this->nullablePositiveInt($row->locationid ?? null),
                        'observed_on' => $this->legacyDateToSqlDate($row->date ?? null),
                        'observed_time' => $this->legacyTimeToSqlTime($row->time ?? null),
                        'legacy_date_int' => $this->nullableInt($row->date ?? null),
                        'legacy_time_int' => $this->nullableInt($row->time ?? null),
                        'legacy_timestamp' => $this->nullableString($row->timestamp ?? null, 14),
                        'description' => $row->description,
                        'language' => 'en',
                        'has_drawing' => ((int) ($row->hasDrawing ?? 0)) === 1,
                        'target_type' => 'comet',
                        'target_table' => 'cometobjects',
                        'target_key' => $cometObjectId !== null ? (string) $cometObjectId : null,
                        'target_name' => $cometObjectId !== null ? ($cometNameById[$cometObjectId] ?? null) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $app->table('comet_observation_details')->insert([
                        'observation_id' => $observationId,
                        'comet_object_id' => $cometObjectId,
                        'method' => $this->nullableString($row->methode ?? null, 1),
                        'magnitude' => $this->nullableFloat($row->mag ?? null, [-99.9]),
                        'chart' => $this->nullableString($row->chart ?? null, 2),
                        'magnification' => $this->nullableString($row->magnification ?? null, 8),
                        'magnitude_uncertain' => ((int) ($row->maguncertain ?? 0)) === 1,
                        'less_magnitude' => ((int) ($row->lessmagnitude ?? 0)) === 1,
                        'dc' => $this->nullableString($row->dc ?? null, 1),
                        'coma' => $this->nullableInt($row->coma ?? null, [-99]),
                        'tail' => $this->nullableInt($row->tail ?? null, [-99]),
                        'position_angle' => $this->nullableInt($row->pa ?? null, [-99]),
                    ]);
                }
            }, 'id');
    }

    private function normalizeLanguage(?string $lang): string
    {
        $lang = strtolower(trim((string) $lang));

        return $lang !== '' ? substr($lang, 0, 10) : 'en';
    }

    private function legacyDateToSqlDate(mixed $value): ?string
    {
        $date = preg_replace('/\D+/', '', (string) $value);
        if (strlen($date) !== 8 || $date === '00000000') {
            return null;
        }

        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        // MySQL DATE requires month 01-12 and day 01-31; treat 00 as unknown → null
        if ($month === '00' || $day === '00') {
            return null;
        }

        return sprintf('%s-%s-%s', $year, $month, $day);
    }

    private function legacyTimeToSqlTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $time = preg_replace('/\D+/', '', (string) $value);
        if ($time === '' || $time === '0' || $time === '0000' || $time === '000000') {
            return null;
        }

        if (strlen($time) <= 4) {
            $time = str_pad($time, 4, '0', STR_PAD_LEFT) . '00';
        } else {
            $time = str_pad(substr($time, 0, 6), 6, '0', STR_PAD_RIGHT);
        }

        $hours = min(23, (int) substr($time, 0, 2));
        $minutes = min(59, (int) substr($time, 2, 2));
        $seconds = min(59, (int) substr($time, 4, 2));

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        $int = $this->nullableInt($value);

        return ($int !== null && $int > 0) ? $int : null;
    }

    private function nullableInt(mixed $value, array $nullSentinels = []): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        if (in_array($int, $nullSentinels, true)) {
            return null;
        }

        return $int;
    }

    private function nullableFloat(mixed $value, array $nullSentinels = []): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $float = (float) $value;

        if (in_array($float, $nullSentinels, true)) {
            return null;
        }

        return $float;
    }

    private function nullableString(mixed $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : mb_substr($text, 0, $maxLength);
    }
}
