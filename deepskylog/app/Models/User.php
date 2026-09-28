<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Observation;
use App\Jobs\EnqueueComputeCRForUser;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use LevelUp\Experience\Concerns\GiveExperience;
use LevelUp\Experience\Concerns\HasAchievements;

class User extends Authenticatable implements MustVerifyEmail
{
    use GiveExperience;
    use HasAchievements;
    use HasApiTokens;
    use HasConnectedAccounts;
    use HasFactory;
    use HasProfilePhoto {
        profilePhotoUrl as getPhotoUrl;
    }
    use HasTeams;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use SetsProfilePhotoFromUrl;
    use Sluggable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'sendMail',
        'about',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected $with = ['locations', 'instruments', 'eyepieces', 'filters', 'lenses'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array The sluggable configuration array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return Attribute The URL to the user's profile photo
     */
    public function profilePhotoUrl(): Attribute
    {
        return filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)
            ? Attribute::get(fn() => $this->profile_photo_path)
            : $this->getPhotoUrl();
    }

    /**
     * Overrides the methods from HasTeams.Switch the user's context to the given team.
     *
     * @param  mixed  $team  The team to switch to
     * @return bool True if the team was switched
     */
    public function switchTeam($team): bool
    {
        // Jetstream's own switchTeam() bails out via belongsToTeam(); this
        // override deliberately skips that check, but still must not be
        // handed a null team -- currentTeam() calls switchTeam(personalTeam()).
        if (is_null($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Checks whether the user's active team is the team with the given name.
     *
     * Returns false -- rather than throwing -- when the team does not exist or
     * the user has no active team, so that a missing group never turns into a
     * 404 on every page that renders the navigation menu.
     *
     * @param  string  $teamName  The name of the team to check against
     * @return bool True if the user's active team is that team
     */
    protected function currentTeamIsNamed(string $teamName): bool
    {
        $team = Team::where('name', $teamName)->first();

        return $team !== null
            && $this->currentTeam !== null
            && $this->isCurrentTeam($team);
    }

    /**
     * Checks if the user's active team is the administrators team
     *
     * @return bool True if the user's active team is the administrators team
     */
    public function isAdministrator(): bool
    {
        return $this->currentTeamIsNamed('Administrators');
    }

    /**
     * Checks if the user's active team is the administrators team
     *
     * @return bool True if the user's active team is the administrators team
     */
    public function hasAdministratorPrivileges(): bool
    {
        if ($this->teams()->where('name', 'Administrators')->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the user's active team is the team of Database experts
     *
     * @return bool True if the user's active team is the team of Database experts
     */
    public function isDatabaseExpert(): bool
    {
        return $this->currentTeamIsNamed('Database Experts');
    }

    /**
     * Checks if the user's active team is the team of Observers
     *
     * @return bool True if the user's active team is the team of Observers
     */
    public function isObserver(): bool
    {
        return $this->currentTeamIsNamed('Observers');
    }

    /**
     * Returns the copyright information (including the image and the link).
     *
     * @return string The copyright information
     */
    public function getCopyright(): string
    {
        $text = $this->copyright;

        if (strcmp($text, 'Attribution-NoDerivs CC BY-ND') === 0) {
            $copyright = '<a rel="license" href="https://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution CC BY') === 0) {
            $copyright = '<a rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial CC BY-NC') === 0) {
            $copyright = '<a rel="license" href="https://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial-ShareAlike CC BY-NC-SA') === 0) {
            $copyright = '<a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial-NoDerivs CC BY-NC-ND') === 0) {
            $copyright = '<a rel="license" href="https://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc-nd/4.0/">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.';
        } else {
            $copyright = $text;
        }

        return $copyright;
    }

    /**
     * Checks if the user is one of the first DeepskyLog users (registered in 2004 or 2005)
     *
     * @return bool True if the user is one of the first DeepskyLog users
     */
    public function isEarlyAdopter(): bool
    {
        $earlyAdopterDate = Carbon::createFromFormat('d/m/Y', '31/12/2005');
        $registrationDate = $this->created_at;

        return $registrationDate->lt($earlyAdopterDate);
    }

    /**
     * Returns the date and the id of the first observation
     *
     * @return array The date and the id of the first observation
     */
    public function firstObservationDate(): array
    {
        $language = app()->getLocale();

        $first = Observation::where('observer_user_id', $this->id)->min('observed_on');

        if ($first === null) {
            return [null, null];
        }

        $date = Carbon::parse($first)->locale($language)->isoFormat('LL');
        $row = Observation::where('observer_user_id', $this->id)
            ->where('observed_on', $first)
            ->orderBy('id')
            ->first();

        return [$date, $row];
    }

    /**
     * Returns the date and the id of the most recent observation
     *
     * @return array The date and the id of the last observation
     */
    public function lastObservationDate(): array
    {
        $language = app()->getLocale();

        $last = Observation::where('observer_user_id', $this->id)->max('observed_on');

        if ($last === null) {
            return [null, null];
        }

        $date = Carbon::parse($last)->locale($language)->isoFormat('LL');
        $row = Observation::where('observer_user_id', $this->id)
            ->where('observed_on', $last)
            ->orderByDesc('id')
            ->first();

        return [$date, $row];
    }

    /**
     * Retrieves the count of observed objects from the specified catalog.
     *
     * @param  string  $catalog  The catalog to retrieve the observed count from.
     * @return int The count of observed objects from the specified catalog.
     */
    public function getObservedCountFromCatalog(string $catalog): int
    {
        return DB::table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.target_name')
            ->leftJoin('deepsky_observation_details', 'observations.id', '=', 'deepsky_observation_details.observation_id')
            ->where(function ($sub) {
                $sub->whereNull('deepsky_observation_details.visibility')
                    ->orWhere('deepsky_observation_details.visibility', '!=', 7);
            })
            ->where('observations.observer_username', $this->username)
            ->where('objectnames.catalog', $catalog)
            ->addSelect('objectnames.catindex')->distinct()->get()->count();
    }

    /**
     * Retrieves the count of drawings from the specified catalog.
     *
     * @param  string  $catalog  The catalog to retrieve the drawing count from.
     * @return int The count of drawings from the specified catalog.
     */
    public function getDrawingCountFromCatalog(string $catalog): int
    {
        return DB::table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.target_name')
            ->leftJoin('deepsky_observation_details', 'observations.id', '=', 'deepsky_observation_details.observation_id')
            ->where(function ($sub) {
                $sub->whereNull('deepsky_observation_details.visibility')
                    ->orWhere('deepsky_observation_details.visibility', '!=', 7);
            })
            ->where('observations.observer_username', $this->username)
            ->where('objectnames.catalog', $catalog)
            ->where('observations.has_drawing', 1)
            ->addSelect('objectnames.catindex')->distinct()->get()->count();
    }

    /**
     * Retrieves the count of open cluster observations for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, is of type "OPNCL" or "CLANB",
     * and belongs to the current user. The count of these distinct object names is
     * returned as the total number of open cluster observations for the current user.
     *
     * @return int The total number of open cluster observations for the current user.
     */
    public function getOpenClusterObservations(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name
                                                                and objects.type = ? and observations.observer_username = ?';

        $total = count(DB::select($query, ['OPNCL', $this->username]));
        return $total + count(DB::select($query, ['CLANB', $this->username]));
    }

    /**
     * Retrieves the count of open cluster drawings for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, has a drawing, is of type "OPNCL" or
     * "CLANB", and belongs to the current user. The count of these distinct object
     * names is returned as the total number of open cluster drawings for the current
     * user.
     *
     * @return int The total number of open cluster drawings for the current user.
     */
    public function getOpenClusterDrawings(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name and observations.has_drawing = 1
                                                                and objects.type = ? and observations.observer_username = ?';

        $total = count(DB::select($query, ['OPNCL', $this->username]));
        return $total + count(DB::select($query, ['CLANB', $this->username]));
    }

    /**
     * Retrieves the count of globular cluster observations for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, is of type "GLOCL", and belongs to the
     * current user. The count of these distinct object names is returned as the total
     * number of globular cluster observations for the current user.
     *
     * @return int The total number of globular cluster observations for the current user.
     */
    public function getGlobularClusterObservations(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['GLOCL', $this->username]));
    }

    /**
     * Retrieves the count of globular cluster drawings for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, has a drawing, is of type "GLOCL",
     * and belongs to the current user. The count of these distinct object names is
     * returned as the total number of globular cluster drawings for the current user.
     *
     * @return int The total number of globular cluster drawings for the current user.
     */
    public function getGlobularClusterDrawings(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name and observations.has_drawing = 1
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['GLOCL', $this->username]));
    }

    /**
     * Retrieves the count of planetary nebula observations for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, is of type "PLNNB", and belongs to
     * the current user. The count of these distinct object names is returned as the
     * total number of planetary nebula observations for the current user.
     *
     * @return int The total number of planetary nebula observations for the current user.
     */
    public function getPlanetaryNebulaObservations(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['PLNNB', $this->username]));
    }

    /**
     * Retrieves the count of planetary nebula drawings for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, has a drawing, is of type "PLNNB",
     * and belongs to the current user. The count of these distinct object names is
     * returned as the total number of planetary nebula drawings for the current user.
     *
     * @return int The total number of planetary nebula drawings for the current user.
     */
    public function getPlanetaryNebulaDrawings(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name and observations.has_drawing = 1
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['PLNNB', $this->username]));
    }

    /**
     * Retrieves the count of galaxy observations for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, is of type "GALXY", and belongs to
     * the current user. The count of these distinct object names is returned as
     * the total number of galaxy observations for the current user.
     *
     * @return int The total number of galaxy observations for the current user.
     */
    public function getGalaxyObservations(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['GALXY', $this->username]));
    }

    /**
     * Retrieves the count of galaxy drawings for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, has a drawing, is of type "GALXY",
     * and belongs to the current user. The count of these distinct object names is
     * returned as the total number of galaxy drawings for the current user.
     *
     * @return int The total number of galaxy drawings for the current user.
     */
    public function getGalaxyDrawings(): int
    {
        $query = 'select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name and observations.has_drawing = 1
                                                                and objects.type = ? and observations.observer_username = ?';
        return count(DB::select($query, ['GALXY', $this->username]));
    }

    /**
     * Retrieves the count of nebula observations for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name and belongs to one of the following
     * object types: "EMINB", "ENRNN", "ENSTR", "REFNB", "RNHII", "HII", "SNREM",
     * or "WRNEB". The count of these distinct object names is returned as the total
     * number of nebula observations for the current user.
     *
     * @return int The total number of nebula observations for the current user.
     */
    public function getNebulaObservations(): int
    {
        $objectTypes = [
            'EMINB',
            'ENRNN',
            'ENSTR',
            'REFNB',
            'RNHII',
            'HII',
            'SNREM',
            'WRNEB',
        ];

        $total = 0;

        foreach ($objectTypes as $objectType) {
            $query = 'SELECT DISTINCT(objects.name) FROM objects,observations
                  WHERE objects.name = observations.target_name
                  AND objects.type = ?
                  AND observations.observer_username = ?';
            $results = DB::select($query, [$objectType, $this->username]);
            $total += count($results);
        }

        return $total;
    }

    /**
     * Retrieves the count of nebula drawings for the current user.
     *
     * This function queries the 'mysqlOld' database connection and selects distinct
     * object names from the 'objects' and 'observations' tables where the object
     * name matches the observation object name, has a drawing, and belongs to one of
     * the following object types: "EMINB", "ENRNN", "ENSTR", "REFNB", "RNHII",
     * "HII", or "SNREM". The count of these distinct object names is returned as
     * the total count of nebula drawings for the current user.
     *
     * @return int The count of nebula drawings for the current user.
     */
    public function getNebulaDrawings(): int
    {
        $objectTypes = [
            'EMINB',
            'ENRNN',
            'ENSTR',
            'REFNB',
            'RNHII',
            'HII',
            'SNREM',
            'WRNEB',
        ];

        $total = 0;

        foreach ($objectTypes as $objectType) {
            $query = 'SELECT DISTINCT(objects.name) FROM objects,observations
                  WHERE objects.name = observations.target_name
                  AND objects.type = ?
                  AND observations.observer_username = ?
                  AND observations.has_drawing = 1';
            $results = DB::select($query, [$objectType, $this->username]);
            $total += count($results);
        }

        return $total;
    }

    /**
     * Retrieves the count of unique objects with observations made by the user.
     *
     * @return int The count of unique objects with observations.
     */
    public function getUniqueObjectsObservations(): int
    {
        return count(DB::select('select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name
                                                                and observations.observer_username = "' . $this->username . '"'));
    }

    /**
     * Retrieves the count of unique objects with drawings made by the user.
     *
     * @return int The count of unique objects with drawings.
     */
    public function getUniqueObjectsDrawings(): int
    {
        return count(DB::select('select DISTINCT(objects.name) from objects,observations
                                                            where objects.name = observations.target_name and observations.has_drawing = 1
                                                                and observations.observer_username = "' . $this->username . '"'));
    }

    /**
     * Retrieves the count of comet observations made by the user.
     *
     * @return int The count of comet observations.
     */
    public function getCometObservations(): int
    {
        return Observation::where('target_type', 'comet')
            ->where('observer_username', $this->username)
            ->count();
    }

    /**
     * Retrieves the count of comet drawings made by the user.
     *
     * @return int The count of comet drawings.
     */
    public function getCometDrawings(): int
    {
        return Observation::where('target_type', 'comet')
            ->where('observer_username', $this->username)
            ->where('has_drawing', 1)
            ->count();
    }

    /**
     * Retrieves the count of unique comet observations made by the user.
     *
     * @return int The count of unique comet observations.
     */
    public function getUniqueCometObservations(): int
    {
        return Observation::where('target_type', 'comet')
            ->where('observer_username', $this->username)
            ->whereNotNull('target_key')
            ->distinct()
            ->count('target_key');
    }

    /**
     * Retrieves the total number of drawings for the current user.
     *
     * This function queries the 'observations' table in the 'mysqlOld' database
     * to count the number of rows where the 'observerid' column matches the
     * current user's username and the 'hasDrawing' column is 1.
     *
     * @return int The total number of drawings for the current user.
     */
    public function getTotalNumberOfDrawings(): int
    {
        return DB::table('observations')
            ->where('observer_username', $this->username)
            ->where('has_drawing', 1)->get()->count();
    }

    /**
     * Get whether the user is in the top ten of observers based on the count of their observations.
     *
     * @return bool Whether the user is in the top ten of observers.
     */
    public function isInTopTenOfObservers(): bool
    {
        // Get all the count of all observations combined per user
        $allObservations = DB::table('observations')
            ->select(DB::raw('count(*) as count, observer_username'))
            ->groupBy('observer_username')
            ->orderBy('count', 'desc')
            ->get();

        $userIndex = 0;
        foreach ($allObservations as $user) {
            if ($user->observer_username == $this->username) {
                break;
            }
            $userIndex++;
        }

        return $userIndex < 10;
    }

    /**
     * Retrieves the owned observing lists of this user.
     *
     * @return Collection The collection of observing lists owned by this user.
     */
    public function getObservingLists(): Collection
    {
        return $this->observingLists()->get();
    }

    /**
     * Retrieves the public observing lists owned by this user.
     *
     * @return Collection The collection of public observing lists owned by this user.
     */
    public function getPublicObservingLists(): Collection
    {
        return $this->observingLists()->where('public', 1)->get();
    }

    /**
     * Retrieves the count of deep sky observations made by the user in the last year.
     *
     * This method calculates the total number of deep sky observations made by the user in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'observations' table for entries where the 'date' column's year is greater than or equal to the last year
     * and the 'observerid' matches the current user's username.
     *
     * The count of these entries is returned, representing the total number of deep sky observations made by the user in the last year.
     *
     * @return int The total number of deep sky observations made by the user in the last year.
     */
    public function getDeepskyObservationsLastYear(): int
    {
        return Observation::where('observed_on', '>=', now()->subYear())->where('observer_user_id', $this->id)->where('target_type', 'deepsky')->count();
    }

    public function getCometObservationsLastYear(): int
    {
        return Observation::where('observed_on', '>=', now()->subYear())->where('observer_user_id', $this->id)->where('target_type', 'comet')->count();
    }

    /**
     * Retrieves the count of deep sky drawings made by the user in the last year.
     *
     * This method calculates the total number of deep sky drawings made by the user in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'observations' table for entries where the 'date' column's year is greater than or equal to the last year,
     * the 'observerid' matches the current user's username, and the 'hasDrawing' column is set to 1 (indicating that the observation has a drawing).
     *
     * The count of these entries is returned, representing the total number of deep sky drawings made by the user in the last year.
     *
     * @return int The total number of deep sky drawings made by the user in the last year.
     */
    public function getDeepskyDrawingsLastYear(): int
    {
        return Observation::where('observed_on', '>=', now()->subYear())->where('observer_user_id', $this->id)->where('target_type', 'deepsky')->where('has_drawing', 1)->count();
    }

    public function getCometDrawingsLastYear(): int
    {
        return Observation::where('observed_on', '>=', now()->subYear())->where('observer_user_id', $this->id)->where('target_type', 'comet')->where('has_drawing', 1)->count();
    }

    /**
     * Retrieves the rank of the user based on the count of their observations.
     *
     * This method calculates the rank of the user among all users based on the count of their observations.
     * It first retrieves the count of all observations made by each user from the 'observations' table,
     * groups them by 'observerid', and orders them in descending order of count.
     *
     * It then iterates over these grouped observations, incrementing a user index until it finds the current user's username.
     * The user index at this point represents the rank of the user among all users based on the count of their observations.
     *
     * The rank is returned as an integer. Note that the rank is zero-based, so a rank of 0 means the user has the highest count of observations.
     *
     * @return int The rank of the user based on the count of their observations.
     */
    public function getRank(): int
    {
        $allObservations = Observation::selectRaw('count(*) as count, observer_user_id')
            ->groupBy('observer_user_id')
            ->orderByDesc('count')
            ->get();

        $userIndex = 0;
        foreach ($allObservations as $user) {
            if ($user->observer_user_id == $this->id) {
                break;
            }
            $userIndex++;
        }

        return $userIndex + 1;
    }

    /**
     * Establishes a relationship between the User model and the Location model.
     *
     * This method defines a one-to-one relationship between the User model and the Location model.
     * The relationship is established based on the 'stdlocation' attribute of the User model and the 'id' attribute of the Location model.
     *
     * @return HasOne The relationship between the User model and the Location model.
     */
    public function standardLocation(): HasOne
    {
        return $this->hasOne(related: Location::class, foreignKey: 'id', localKey: 'stdlocation');
    }

    /**
     * Establishes a relationship between the User model and the Instruments model.
     *
     * This method defines a one-to-one relationship between the User model and the Instruments model.
     * The relationship is established based on the 'stdtelescope' attribute of the User model and the 'id' attribute of the Instruments model.
     *
     * @return HasOne The relationship between the User model and the Instruments model.
     */
    public function standardInstrument(): HasOne
    {
        return $this->hasOne(related: Instrument::class, foreignKey: 'id', localKey: 'stdtelescope');
    }

    public function standardInstrumentSet(): HasOne
    {
        return $this->hasOne(related: \App\Models\InstrumentSet::class, foreignKey: 'id', localKey: 'stdinstrumentset');
    }

    public function observationsCount(): float
    {
        return Observation::getObservationsCountForUserByName($this);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(related: Observation::class, foreignKey: 'observer_username', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the Location model.
     *
     * This method defines a one-to-many relationship between the User model and the Location model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the Location model.
     *
     * @return HasMany The relationship between the User model and the Location model.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(related: Location::class);
    }

    /**
     * Establishes a relationship between the User model and the Instruments model.
     *
     * This method defines a one-to-many relationship between the User model and the Instruments model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the Instruments model.
     *
     * @return HasMany The relationship between the User model and the Instruments model.
     */
    public function instruments(): HasMany
    {
        return $this->hasMany(related: Instrument::class);
    }

    /**
     * Establishes a relationship between the User model and the Eyepiece model.
     *
     * This method defines a one-to-many relationship between the User model and the Eyepiece model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the Eyepiece model.
     *
     * @return HasMany The relationship between the User model and the Eyepiece model.
     */
    public function eyepieces(): HasMany
    {
        return $this->hasMany(related: Eyepiece::class)->orderBy('focal_length_mm', 'desc')->orderBy('name');
    }

    /**
     * Establishes a relationship between the User model and the Filters model.
     *
     * This method defines a one-to-many relationship between the User model and the Filters model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the Filters model.
     *
     * @return HasMany The relationship between the User model and the Filters model.
     */
    public function filters(): HasMany
    {
        return $this->hasMany(related: Filter::class);
    }

    /**
     * Establishes a relationship between the User model and the Lenses model.
     *
     * This method defines a one-to-many relationship between the User model and the Lenses model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the Lenses model.
     *
     * @return HasMany The relationship between the User model and the Lenses model.
     */
    public function lenses(): HasMany
    {
        return $this->hasMany(related: Lens::class);
    }

    /**
     * Establishes a relationship between the User model and the SketchOfTheWeek model.
     *
     * This method defines a one-to-many relationship between the User model and the SketchOfTheWeek model.
     * The relationship is established based on the 'id' attribute of the User model and the 'user_id' attribute of the SketchOfTheWeek model.
     *
     * @return HasMany The relationship between the User model and the SketchOfTheWeek model.
     */
    public function sketchOfTheWeek(): HasMany
    {
        return $this->hasMany(related: SketchOfTheWeek::class, foreignKey: 'user_id', localKey: 'id');
    }

    /**
     * Establishes a relationship between the User model and the SketchOfTheMonth model.
     *
     * This method defines a one-to-many relationship between the User model and the SketchOfTheMonth model.
     * The relationship is established based on the 'id' attribute of the User model and the 'user_id' attribute of the SketchOfTheMonth model.
     *
     * @return HasMany The relationship between the User model and the SketchOfTheMonth model.
     */
    public function sketchOfTheMonth(): HasMany
    {
        return $this->hasMany(related: SketchOfTheMonth::class, foreignKey: 'user_id', localKey: 'id');
    }

    public function instrumentSets(): HasMany
    {
        return $this->hasMany(related: InstrumentSet::class);
    }

    /**
     * Get the observing lists owned by this user.
     */
    public function observingLists(): HasMany
    {
        return $this->hasMany(related: ObservingList::class, foreignKey: 'owner_user_id', localKey: 'id');
    }

    /**
     * Get the active observing list for this user.
     */
    public function activeObservingList(): HasOne
    {
        return $this->hasOne(related: ObservingList::class, foreignKey: 'id', localKey: 'active_observing_list_id');
    }

    /**
     * Get the observing lists subscribed to by this user.
     */
    public function subscribedObservingLists()
    {
        return $this->belongsToMany(
            ObservingList::class,
            'observing_list_subscriptions',
            'user_id',
            'observing_list_id'
        )->withTimestamps();
    }

    /**
     * Get all observing lists accessible to this user (owned + subscribed).
     */
    public function getAccessibleObservingLists()
    {
        return $this->observingLists()->union(
            $this->subscribedObservingLists()
        )->orderBy('created_at', 'desc');
    }

    /**
     * Register model event hooks to react when the user's standard instrument or
     * standard location changes. We enqueue a background job to recompute
     * contrast-reserve metrics for the user so the cached per-user metrics stay
     * in sync with profile changes.
     */
    protected static function booted()
    {
        static::updated(function (self $user) {
            try {
                // Field names used in the database: stdtelescope (instrument) and stdlocation
                $instrumentDirty = $user->wasChanged('stdtelescope');
                $locationDirty = $user->wasChanged('stdlocation');

                if ($instrumentDirty || $locationDirty) {
                    $instrId = $user->stdtelescope ?? null;
                    $locId = $user->stdlocation ?? null;
                    Log::info('User: standard instrument/location changed - enqueuing metrics recompute', ['user_id' => $user->id, 'stdtelescope' => $instrId, 'stdlocation' => $locId]);

                    // Enqueue the job so it runs asynchronously (and itself will dispatch per-object jobs)
                    EnqueueComputeCRForUser::dispatch($user->id, $instrId, $locId);
                }
            } catch (\Throwable $ex) {
                Log::error('User: failed to enqueue metrics recompute', ['error' => $ex->getMessage(), 'user_id' => $user->id]);
            }
        });
    }
}
