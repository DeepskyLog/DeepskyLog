<?php

namespace App\Models;

use App\Models\ObservationsOld;
use Carbon\Carbon;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Cviebrock\EloquentSluggable\Sluggable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use SetsProfilePhotoFromUrl;
    use HasProfilePhoto {
        profilePhotoUrl as getPhotoUrl;
    }
    use HasTeams;
    use HasConnectedAccounts;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'sendMail', 'about'
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

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the URL to the user's profile photo.
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
     * @param  mixed  $team
     * @return bool
     */
    public function switchTeam($team)
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
     * @return string
     */
    public function isAdministrator(): bool
    {
        return $this->isCurrentTeam(Team::where('name', 'Administrators')->firstOrFail());
    }

    /**
     * Checks if the user's active team is the administrators team
     *
     * @return bool
     */
    public function hasAdministratorPrivileges(): bool
    {
        if ($this->teams()->where("name", "Administrators")->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the user's active team is the team of Database experts
     *
     * @return bool
     */
    public function isDatabaseExpert(): bool
    {
        return $this->isCurrentTeam(Team::where('name', 'Database Experts')->firstOrFail());
    }

    /**
     * Checks if the user's active team is the team of Observers
     *
     * @return bool
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
    public function getCopyright()
    {
        $text = $this->copyright;

        if (strcmp($text, 'Attribution-NoDerivs CC BY-ND') === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution CC BY') === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial CC BY-NC') === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial-ShareAlike CC BY-NC-SA') === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-NonCommercial-NoDerivs CC BY-NC-ND') === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.';
        } else {
            $copyright = $text;
        }

        return $copyright;
    }

    /**
     * Checks if the user is one of the first DeepskyLog users (registered in 2004 or 2005)
     *
     * @return bool
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
     * @return array
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
     * @return array
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
     * Checks if the user has observed all 110 messier objects
     *
     * @return bool
     */
    public function hasMessierGold(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['messierGold'];
    }

    /**
     * Checks if the user has observed 50 different messier objects
     *
     * @return bool
     */
    public function hasMessierSilver(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['messierSilver'];
    }
    /**
     * Checks if the user has observed 25 different messier objects
     *
     * @return bool
     */
    public function hasMessierBronze(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['messierBronze'];
    }

    /**
     * Checks if the user has drawn all 110 messier objects
     *
     * @return bool
     */
    public function hasMessierGoldDrawing(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['messierDrawingsGold'];
    }

    /**
     * Checks if the user has drawn 50 different messier objects
     *
     * @return bool
     */
    public function hasMessierSilverDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['messierDrawingsSilver'];
    }
    /**
     * Checks if the user has drawn 25 different messier objects
     *
     * @return bool
     */
    public function hasMessierBronzeDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['messierDrawingsBronze'];
    }

    /**
     * Checks if the user has observed all 110 caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellGold(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwellGold'];
    }

    /**
     * Checks if the user has observed 50 different caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellSilver(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwellSilver'];
    }
    /**
     * Checks if the user has observed 25 different caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellBronze(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwellBronze'];
    }

    /**
     * Checks if the user has drawn all 110 caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellGoldDrawing(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwelldrawingsGold'];
    }

    /**
     * Checks if the user has drawn 50 different caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellSilverDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwellDrawingsSilver'];
    }
    /**
     * Checks if the user has drawn 25 different caldwell objects
     *
     * @return bool
     */
    public function hasCaldwellBronzeDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['caldwellDrawingsBronze'];
    }

    /**
     * Checks if the user has observed all 400 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400Platinum(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelPlatina'];
    }

    /**
     * Checks if the user has observed 200 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400Diamond(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDiamond'];
    }

    /**
     * Checks if the user has observed 100 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400Gold(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelGold'];
    }

    /**
     * Checks if the user has observed 50 different herschel objects
     *
     * @return bool
     */
    public function hasHerschel400Silver(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelSilver'];
    }
    /**
     * Checks if the user has observed 25 different herschel objects
     *
     * @return bool
     */
    public function hasHerschel400Bronze(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelBronze'];
    }

    /**
     * Checks if the user has drawn all 400 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400PlatinumDrawing(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDrawingsPlatina'];
    }

    /**
     * Checks if the user has drawn 200 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400DiamondDrawing(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDrawingsDiamond'];
    }

    /**
     * Checks if the user has drawn 100 herschel objects
     *
     * @return bool
     */
    public function hasHerschel400GoldDrawing(): bool
    {

        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDrawingsGold'];
    }

    /**
     * Checks if the user has drawn 50 different herschel objects
     *
     * @return bool
     */
    public function hasHerschel400SilverDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDrawingsSilver'];
    }
    /**
     * Checks if the user has drawn 25 different herschel objects
     *
     * @return bool
     */
    public function hasHerschel400BronzeDrawing(): bool
    {
        return AccomplishmentsOld::where('observer', $this->username)->first()['herschelDrawingsBronze'];
    }
}
