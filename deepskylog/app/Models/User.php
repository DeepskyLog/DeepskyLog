<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use LevelUp\Experience\Concerns\HasAchievements;

class User extends Authenticatable implements MustVerifyEmail
{
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
        'name', 'email', 'password', 'username', 'sendMail', 'about',
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
            ? Attribute::get(fn () => $this->profile_photo_path)
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
        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Checks if the user's active team is the administrators team
     *
     * @return bool True if the user's active team is the administrators team
     */
    public function isAdministrator(): bool
    {
        return $this->isCurrentTeam(Team::where('name', 'Administrators')->firstOrFail());
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
        return $this->isCurrentTeam(Team::where('name', 'Database Experts')->firstOrFail());
    }

    /**
     * Checks if the user's active team is the team of Observers
     *
     * @return bool True if the user's active team is the team of Observers
     */
    public function isObserver(): bool
    {
        return $this->isCurrentTeam(Team::where('name', 'Observers')->firstOrFail());
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
        // TODO: Change to the selected language!
        $language = 'nl_NL';

        $firstObservation = ObservationsOld::where('observerid', $this->username)->min('date');
        if ($firstObservation == null) {
            return [null, null];
        }
        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');
        $id = ObservationsOld::where('observerid', $this->username)->where('date', $firstObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Returns the date and the id of the most recent observation
     *
     * @return array The date and the id of the last observation
     */
    public function lastObservationDate(): array
    {
        // TODO: Change to the selected language!
        $language = 'nl_NL';

        $firstObservation = ObservationsOld::where('observerid', $this->username)->max('date');
        if ($firstObservation == null) {
            return [null, null];
        }
        $date = Carbon::createFromFormat('Ymd', $firstObservation)->locale($language)->isoFormat('LL');
        $id = ObservationsOld::where('observerid', $this->username)->where('date', $firstObservation)->first()['id'];

        return [$date, $id];
    }

    /**
     * Retrieves the count of observed objects from the specified catalog.
     *
     * @param  string  $catalog  The catalog to retrieve the observed count from.
     * @return int The count of observed objects from the specified catalog.
     */
    public function getObservedCountFromCatalog(string $catalog): int
    {
        return DB::connection('mysqlOld')->table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.objectname')
            ->where('observations.observerid', $this->username)
            ->where('observations.visibility', '!=', 7)
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
        return DB::connection('mysqlOld')->table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.objectname')
            ->where('observations.observerid', $this->username)
            ->where('observations.visibility', '!=', 7)
            ->where('objectnames.catalog', $catalog)
            ->where('observations.hasDrawing', 1)
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
        $total = count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and objects.type = "OPNCL" and observations.observerid = "'.$this->username.'"'));

        return $total + count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and objects.type = "CLANB" and observations.observerid = "'.$this->username.'"'));
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
        $total = count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and objects.type = "OPNCL" and observations.observerid = "'.$this->username.'"'));

        return $total + count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and objects.type = "CLANB" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and objects.type = "GLOCL" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and objects.type = "GLOCL" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and objects.type = "PLNNB" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and objects.type = "PLNNB" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and objects.type = "GALXY" and observations.observerid = "'.$this->username.'"'));
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and objects.type = "GALXY" and observations.observerid = "'.$this->username.'"'));
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
            'EMINB', 'ENRNN', 'ENSTR', 'REFNB', 'RNHII', 'HII', 'SNREM', 'WRNEB',
        ];

        $total = 0;

        foreach ($objectTypes as $objectType) {
            $query = 'SELECT DISTINCT(objects.name) FROM objects,observations
                  WHERE objects.name = observations.objectname
                  AND objects.type = ?
                  AND observations.observerid = ?';
            $results = DB::connection('mysqlOld')->select($query, [$objectType, $this->username]);
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
            'EMINB', 'ENRNN', 'ENSTR', 'REFNB', 'RNHII', 'HII', 'SNREM', 'WRNEB',
        ];

        $total = 0;

        foreach ($objectTypes as $objectType) {
            $query = 'SELECT DISTINCT(objects.name) FROM objects,observations
                  WHERE objects.name = observations.objectname
                  AND objects.type = ?
                  AND observations.observerid = ?
                  AND hasDrawing = 1';
            $results = DB::connection('mysqlOld')->select($query, [$objectType, $this->username]);
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
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname
                                and observations.observerid = "'.$this->username.'"'));
    }

    /**
     * Retrieves the count of unique objects with drawings made by the user.
     *
     * @return int The count of unique objects with drawings.
     */
    public function getUniqueObjectsDrawings(): int
    {
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objects.name) from objects,observations
                              where objects.name = observations.objectname and hasDrawing = 1
                                and observations.observerid = "'.$this->username.'"'));
    }

    /**
     * Retrieves the count of comet observations made by the user.
     *
     * @return int The count of comet observations.
     */
    public function getCometObservations(): int
    {
        return count(DB::connection('mysqlOld')
            ->select('select * from cometobservations
                              where observerid = "'.$this->username.'"'));
    }

    /**
     * Retrieves the count of comet drawings made by the user.
     *
     * @return int The count of comet drawings.
     */
    public function getCometDrawings(): int
    {
        return count(DB::connection('mysqlOld')
            ->select('select * from cometobservations where hasDrawing = 1
                              and observerid = "'.$this->username.'"'));
    }

    /**
     * Retrieves the count of unique comet observations made by the user.
     *
     * @return int The count of unique comet observations.
     */
    public function getUniqueCometObservations(): int
    {
        return count(DB::connection('mysqlOld')
            ->select('select DISTINCT(objectid) from cometobservations
                              where observerid = "'.$this->username.'"'));
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
        return DB::connection('mysqlOld')->table('observations')
            ->where('observerid', $this->username)
            ->where('hasDrawing', 1)->get()->count();
    }

    /**
     * Get whether the user is in the top ten of observers based on the count of their observations.
     *
     * @return bool Whether the user is in the top ten of observers.
     */
    public function isInTopTenOfObservers(): bool
    {
        // Get all the count of all observations combined per user
        $allObservations = DB::connection('mysqlOld')->table('observations')
            ->select(DB::raw('count(*) as count, observerid'))
            ->groupBy('observerid')
            ->orderBy('count', 'desc')
            ->get();

        $userIndex = 0;
        foreach ($allObservations as $user) {
            if ($user->observerid == $this->username) {
                break;
            }
            $userIndex++;
        }

        return $userIndex < 10;
    }

    /**
     * Retrieves the list names of the user's observing lists.
     *
     * @return Collection The collection of distinct list names.
     */
    public function getObservingLists(): Collection
    {
        // TODO: Need a pivot table here
        // Get the distinct list names of the user's observing lists
        return ObserverListOld::where('observerid', $this->username)->distinct('listname')->get('listname');
    }

    /**
     * Retrieves the list names of the user's public observing lists.
     *
     * @return Collection The collection of distinct list names.
     */
    public function getPublicObservingLists(): Collection
    {
        // Get the distinct list names of the user's observing lists
        return ObserverListOld::where('observerid', $this->username)->where('public', 1)->distinct('listname')->get('listname');
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
        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        return ObservationsOld::where('date', '>=', $lastYear)->where('observerid', $this->username)->count();
    }

    /**
     * Retrieves the count of comet observations made by the user in the last year.
     *
     * This method calculates the total number of comet observations made by the user in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'cometobservations' table for entries where the 'date' column's year is greater than or equal to the last year
     * and the 'observerid' matches the current user's username.
     *
     * The count of these entries is returned, representing the total number of comet observations made by the user in the last year.
     *
     * @return int The total number of comet observations made by the user in the last year.
     */
    public function getCometObservationsLastYear(): int
    {
        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        return CometObservationsOld::where('date', '>=', $lastYear)->where('observerid', $this->username)->count();
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
        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        return ObservationsOld::where('date', '>=', $lastYear)->where('observerid', $this->username)->where('hasDrawing', 1)->count();
    }

    /**
     * Retrieves the count of comet drawings made by the user in the last year.
     *
     * This method calculates the total number of comet drawings made by the user in the last year.
     * It first retrieves the current year and subtracts one to get the previous year.
     * It then queries the 'cometobservations' table for entries where the 'date' column's year is greater than or equal to the last year,
     * the 'observerid' matches the current user's username, and the 'hasDrawing' column is set to 1 (indicating that the observation has a drawing).
     *
     * The count of these entries is returned, representing the total number of comet drawings made by the user in the last year.
     *
     * @return int The total number of comet drawings made by the user in the last year.
     */
    public function getCometDrawingsLastYear(): int
    {
        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        return CometObservationsOld::where('date', '>=', $lastYear)->where('observerid', $this->username)->where('hasDrawing', 1)->count();
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
        // Get all the count of all observations combined per user
        $allObservations = DB::connection('mysqlOld')->table('observations')
            ->select(DB::raw('count(*) as count, observerid'))
            ->groupBy('observerid')
            ->orderBy('count', 'desc')
            ->get();

        $userIndex = 0;
        foreach ($allObservations as $user) {
            if ($user->observerid == $this->username) {
                break;
            }
            $userIndex++;
        }

        return $userIndex;
    }

    /**
     * Establishes a relationship between the User model and the LocationsOld model.
     *
     * This method defines a one-to-one relationship between the User model and the LocationsOld model.
     * The relationship is established based on the 'stdlocation' attribute of the User model and the 'id' attribute of the LocationsOld model.
     *
     * @return HasOne The relationship between the User model and the LocationsOld model.
     */
    public function standardLocation(): HasOne
    {
        return $this->hasOne(related: LocationsOld::class, foreignKey: 'id', localKey: 'stdlocation');
    }

    /**
     * Establishes a relationship between the User model and the InstrumentsOld model.
     *
     * This method defines a one-to-one relationship between the User model and the InstrumentsOld model.
     * The relationship is established based on the 'stdtelescope' attribute of the User model and the 'id' attribute of the InstrumentsOld model.
     *
     * @return HasOne The relationship between the User model and the InstrumentsOld model.
     */
    public function standardInstrument(): HasOne
    {
        return $this->hasOne(related: InstrumentsOld::class, foreignKey: 'id', localKey: 'stdtelescope');
    }

    public function observationsCount(): float
    {
        return $this->observations()->count();
    }

    public function observations(): HasMany
    {
        return $this->hasMany(related: ObservationsOld::class, foreignKey: 'observerid', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the LocationsOld model.
     *
     * This method defines a one-to-many relationship between the User model and the LocationsOld model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the LocationsOld model.
     *
     * @return HasMany The relationship between the User model and the LocationsOld model.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(related: LocationsOld::class, foreignKey: 'observer', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the InstrumentsOld model.
     *
     * This method defines a one-to-many relationship between the User model and the InstrumentsOld model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the InstrumentsOld model.
     *
     * @return HasMany The relationship between the User model and the InstrumentsOld model.
     */
    public function instruments(): HasMany
    {
        return $this->hasMany(related: InstrumentsOld::class, foreignKey: 'observer', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the EyepiecesOld model.
     *
     * This method defines a one-to-many relationship between the User model and the EyepiecesOld model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the EyepiecesOld model.
     *
     * @return HasMany The relationship between the User model and the EyepiecesOld model.
     */
    public function eyepieces(): HasMany
    {
        return $this->hasMany(related: EyepiecesOld::class, foreignKey: 'observer', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the FiltersOld model.
     *
     * This method defines a one-to-many relationship between the User model and the FiltersOld model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the FiltersOld model.
     *
     * @return HasMany The relationship between the User model and the FiltersOld model.
     */
    public function filters(): HasMany
    {
        return $this->hasMany(related: FiltersOld::class, foreignKey: 'observer', localKey: 'username');
    }

    /**
     * Establishes a relationship between the User model and the LensesOld model.
     *
     * This method defines a one-to-many relationship between the User model and the LensesOld model.
     * The relationship is established based on the 'username' attribute of the User model and the 'observer' attribute of the LensesOld model.
     *
     * @return HasMany The relationship between the User model and the LensesOld model.
     */
    public function lenses(): HasMany
    {
        return $this->hasMany(related: LensesOld::class, foreignKey: 'observer', localKey: 'username');
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
}
