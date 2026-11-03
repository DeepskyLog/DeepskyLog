<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationZipBuilder;
use App\Support\MemoryLimit;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * "Download all my data" (GenerateAccountExport): one zip with everything
 * the account holds, as the privacy policy promises.
 *
 * - account.json: the users row, without the secrets of SECRET_COLUMNS, and
 *   the teams;
 * - data/{table}.json: the rows of each table of tables() that belong to
 *   the observer, as stored (their equipment, locations, sessions, lists,
 *   messages, goals, settings, comments, likes, follows, ...);
 * - observations/: observations.xml (OAL) with the drawings and
 *   drawings.csv, as the observations zip (ObservationZipBuilder::addTo());
 * - files/: the profile photo and the pictures of the sessions, equipment
 *   and locations.
 *
 * Derived data the site computes again (contrast reserves, statistics) is
 * left out. A table added for the observer's own rows belongs in tables(),
 * as it belongs in DeleteUser.
 */
class AccountDataExport
{
    /**
     * Columns never written: passwords, keys and the tokens of the secret
     * addresses, which would let whoever finds the zip in.
     *
     * @var array<string, array<int, string>>
     */
    public const SECRET_COLUMNS = [
        'users' => ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'follow_feed_token', 'calendar_feed_token'],
        'connected_accounts' => ['token', 'secret', 'refresh_token'],
        'personal_access_tokens' => ['token'],
        'push_subscriptions' => ['public_key', 'auth_token'],
        'sessions' => ['payload'],
        'star_party_invitations' => ['token'],
    ];

    /** Tables with the pictures of their rows on the public disk. */
    private const PICTURE_TABLES = ['instruments', 'eyepieces', 'filters', 'lenses', 'locations', 'instrument_sets', 'observation_sessions'];

    /**
     * Write the zip of the observer's data to $path.
     *
     * @param  callable(array<string, mixed>): void  $progress  merges its argument into the status
     * @return array{tables: int, observations: int, drawings: int, files: int}
     */
    public function write(string $path, User $user, callable $progress): array
    {
        // The OAL of 25,000 observations takes about 250 MB.
        MemoryLimit::atLeast('1024M');

        // ZipArchive reads added files only when it closes, so the JSON
        // files stay here until then.
        $work = $path . '.d';
        File::ensureDirectoryExists($work);

        try {
            $zip = new ZipArchive;
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not create the zip file.');
            }

            $progress(['phase' => 'data']);
            $zip->addFromString('README.txt', $this->readme($user));
            $zip->addFromString('account.json', $this->json($this->account($user)));

            $tables = $this->tables($user);
            foreach ($tables as $name => $query) {
                $file = $work . '/' . $name . '.json';
                $this->writeRows($file, $query, self::SECRET_COLUMNS[$name] ?? []);
                $zip->addFile($file, 'data/' . $name . '.json');
            }

            $observations = Observation::query()
                ->where(fn ($query) => $query->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username))
                ->orderBy('observed_on')
                ->orderBy('id')
                ->get();
            $counts = app(ObservationZipBuilder::class)->addTo($zip, $observations, 'observations/', dirname($path), $progress);
            unset($observations);

            $files = $this->addFiles($zip, $user);

            $zip->registerProgressCallback(0.02, fn (float $done) => $progress(['phase' => 'writing', 'percent' => (int) round($done * 100)]));

            if (! $zip->close()) {
                throw new \RuntimeException('Could not write the zip file.');
            }
        } finally {
            File::deleteDirectory($work);
        }

        return ['tables' => count($tables), 'observations' => $counts['observations'], 'drawings' => $counts['drawings'], 'files' => $files];
    }

    /**
     * The rows of the observer, per file name in data/.
     *
     * @return array<string, Builder>
     */
    public function tables(User $user): array
    {
        $id = $user->id;
        $username = $user->username;

        $observationIds = DB::table('observations')
            ->where(fn ($query) => $query->where('observer_user_id', $id)->orWhere('observer_username', $username))
            ->select('id');
        $sessionIds = DB::table('observation_sessions')->where('observerid', $username)->select('id');
        $listIds = DB::table('observing_lists')->where('owner_user_id', $id)->select('id');
        $setIds = DB::table('instrument_sets')->where('user_id', $id)->select('id');

        $tables = [
            'observations' => DB::table('observations')->whereIn('id', $observationIds)->orderBy('id'),
            'deepsky_observation_details' => DB::table('deepsky_observation_details')->whereIn('observation_id', $observationIds),
            'comet_observation_details' => DB::table('comet_observation_details')->whereIn('observation_id', $observationIds),
            'variable_star_estimates' => DB::table('variable_star_estimates')->whereIn('observation_id', $observationIds),
            // Only the descriptions the observer wrote in other languages, not Google's translations
            'observation_translations' => DB::table('observation_translations')->whereIn('observation_id', $observationIds)->where('by_observer', true),

            'observation_sessions' => DB::table('observation_sessions')->where('observerid', $username)->orderBy('id'),
            'session_observations' => DB::table('sessionObservations')->whereIn('sessionid', $sessionIds),
            'session_observers' => DB::table('sessionObservers')->where(fn ($query) => $query->whereIn('sessionid', $sessionIds)->orWhere('observer', $username)),

            'instruments' => DB::table('instruments')->where('user_id', $id)->orderBy('id'),
            'eyepieces' => DB::table('eyepieces')->where('user_id', $id)->orderBy('id'),
            'lenses' => DB::table('lenses')->where('user_id', $id)->orderBy('id'),
            'filters' => DB::table('filters')->where('user_id', $id)->orderBy('id'),
            'locations' => DB::table('locations')->where('user_id', $id)->orderBy('id'),
            'instrument_sets' => DB::table('instrument_sets')->where('user_id', $id)->orderBy('id'),

            'observing_lists' => DB::table('observing_lists')->where('owner_user_id', $id)->orderBy('id'),
            'observing_list_items' => DB::table('observing_list_items')->where(fn ($query) => $query->whereIn('observing_list_id', $listIds)->orWhere('added_by_user_id', $id))->orderBy('id'),
            'observing_list_comments' => DB::table('observing_list_comments')->where('user_id', $id)->orderBy('id'),
            'observing_list_likes' => DB::table('observing_list_likes')->where('user_id', $id),
            'observing_list_subscriptions' => DB::table('observing_list_subscriptions')->where('user_id', $id),

            'messages' => DB::table('messages')->where(fn ($query) => $query->where('receiver', $username)->orWhere('sender', $username))->orderBy('id'),
            'messages_read' => DB::table('messages_read')->where('receiver', $username),
            'messages_deleted' => DB::table('messages_deleted')->where('receiver', $username),
            'message_blocks' => DB::table('message_blocks')->where('user_id', $id),

            'observation_comments' => DB::table('observation_comments')->where('user_id', $id)->orderBy('id'),
            'observation_likes' => DB::table('observation_likes')->where('user_id', $id),
            'object_finding_notes' => DB::table('object_finding_notes')->where('user_id', $id)->orderBy('id'),
            'object_finding_note_likes' => DB::table('object_finding_note_likes')->where('user_id', $id),
            'user_follows' => DB::table('user_follows')->where(fn ($query) => $query->where('follower_id', $id)->orWhere('followed_id', $id)),
            'object_follows' => DB::table('object_follows')->where('user_id', $id),
            'observing_requests' => DB::table('observing_requests')->where('user_id', $id)->orderBy('id'),
            'observing_request_answers' => DB::table('observing_request_answers')->whereIn('observing_request_id', DB::table('observing_requests')->where('user_id', $id)->select('id'))->orderBy('id'),
            'sky_measurements' => DB::table('sky_measurements')->where('user_id', $id)->orderBy('id'),

            'observing_goals' => DB::table('observing_goals')->where('user_id', $id)->orderBy('id'),
            'clubs' => DB::table('clubs')->where('user_id', $id)->orderBy('id'),
            'club_members' => DB::table('club_members')->where('user_id', $id),
            'club_observing_list' => DB::table('club_observing_list')->where('user_id', $id),
            'star_parties' => DB::table('star_parties')->where('user_id', $id)->orderBy('id'),
            'star_party_participants' => DB::table('star_party_participants')->where('user_id', $id),
            'star_party_invitations' => DB::table('star_party_invitations')->where(fn ($query) => $query->where('invited_by_user_id', $id)->orWhere('accepted_by_user_id', $id)),
            'sketch_of_the_week' => DB::table('sketch_of_the_week')->where('user_id', $id),
            'sketch_of_the_month' => DB::table('sketch_of_the_month')->where('user_id', $id),
            'achievement_user' => DB::table('achievement_user')->where('user_id', $id),
            'experiences' => DB::table('experiences')->where('user_id', $id),
            'streaks' => DB::table('streaks')->where('user_id', $id),

            'saved_observation_searches' => DB::table('saved_observation_searches')->where('user_id', $id),
            'saved_object_searches' => DB::table('saved_object_searches')->where('user_id', $id),
            'report_layouts' => DB::table('report_layouts')->where('user_id', $id),
            'user_table_settings' => DB::table('user_table_settings')->where('user_id', $id),
            'user_preferences' => DB::table('user_preferences')->where('user_id', $id),
            'connected_accounts' => DB::table('connected_accounts')->where('user_id', $id),
            'personal_access_tokens' => DB::table('personal_access_tokens')->where('tokenable_type', User::class)->where('tokenable_id', $id),
            'push_subscriptions' => DB::table('push_subscriptions')->where('user_id', $id),
            'sessions' => DB::table('sessions')->where('user_id', $id),
        ];

        foreach (['instrument_set_instrument', 'instrument_set_eyepiece', 'instrument_set_lens', 'instrument_set_filter', 'instrument_set_location'] as $pivot) {
            $tables[$pivot] = DB::table($pivot)->whereIn('instrument_set_id', $setIds);
        }

        // Tables of the old DeepskyLog or of packages that a database may not have
        return array_filter($tables, fn (Builder $query) => Schema::hasTable($query->from));
    }

    /**
     * @return array<string, mixed>
     */
    private function account(User $user): array
    {
        $row = (array) DB::table('users')->where('id', $user->id)->first();

        return array_diff_key($row, array_flip(self::SECRET_COLUMNS['users']))
            + ['teams' => $user->teams()->pluck('name')->all()];
    }

    /**
     * Write the rows as a JSON array, one at a time.
     *
     * @param  array<int, string>  $secret  columns left out
     */
    private function writeRows(string $file, Builder $query, array $secret): void
    {
        $handle = fopen($file, 'w');
        if ($handle === false) {
            throw new \RuntimeException('Could not write ' . basename($file) . '.');
        }

        fwrite($handle, '[');
        $first = true;
        foreach ($query->cursor() as $row) {
            fwrite($handle, ($first ? "\n" : ",\n") . $this->json(array_diff_key((array) $row, array_flip($secret)), false));
            $first = false;
        }
        fwrite($handle, $first ? "]\n" : "\n]\n");
        fclose($handle);
    }

    private function json(mixed $value, bool $pretty = true): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR | ($pretty ? JSON_PRETTY_PRINT : 0)
        );
    }

    /**
     * Add the profile photo and the pictures of the observer's sessions,
     * equipment and locations under files/.
     */
    private function addFiles(ZipArchive $zip, User $user): int
    {
        $public = Storage::disk('public');
        $added = 0;

        $add = function (?string $stored, string $entry) use ($zip, $public, &$added) {
            $stored = ltrim(trim((string) $stored), '/');
            if ($stored === '' || str_contains($stored, '..') || ! $public->exists($stored)) {
                return;
            }

            $extension = strtolower(pathinfo($stored, PATHINFO_EXTENSION)) ?: 'jpg';
            $zip->addFile($public->path($stored), $entry . '.' . $extension);
            $added++;
        };

        $add($user->profile_photo_path, 'files/profile-photo');

        foreach (self::PICTURE_TABLES as $table) {
            $column = $table === 'observation_sessions' ? 'observerid' : 'user_id';
            $value = $table === 'observation_sessions' ? $user->username : $user->id;
            $folder = $table === 'observation_sessions' ? 'sessions' : str_replace('_', '-', $table);

            foreach (DB::table($table)->where($column, $value)->whereNotNull('picture')->get(['id', 'picture']) as $row) {
                $add($row->picture, 'files/' . $folder . '/' . $row->id);
            }
        }

        // The session pictures of the old site
        foreach (DB::table('observation_sessions')->where('observerid', $user->username)->pluck('id') as $sessionId) {
            foreach (glob(public_path('images/sessions/' . $sessionId . '.*')) ?: [] as $image) {
                $zip->addFile($image, 'files/sessions/old-site-' . basename($image));
                $added++;
            }
        }

        return $added;
    }

    private function readme(User $user): string
    {
        $lines = [
            __('All data of your DeepskyLog account :name, made on :date.', [
                'name' => $user->username,
                'date' => now()->toDateTimeString() . ' UTC',
            ]),
            '',
            __('account.json: your account and settings.'),
            __('data/: the rows of each table that belong to your account, as DeepskyLog stores them, in JSON (equipment, locations, sessions, observing lists, messages, goals, comments, likes, follows and more).'),
            __('observations/observations.xml: your observations in the OAL format, which other observing programs can import, with your drawings in observations/drawings/ and a list of them in observations/drawings.csv.'),
            __('files/: your profile photo and the pictures of your sessions, equipment and locations.'),
            '',
            __('Passwords, keys and the secret addresses of your calendar and feed are left out.'),
        ];

        return implode("\r\n", $lines) . "\r\n";
    }
}
