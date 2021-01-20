<?php
/**
 * User eloquent model.
 *
 * PHP Version 7
 *
 * @category UserManagement
 *
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Cmgmyr\Messenger\Traits\Messagable;
use Illuminate\Notifications\Notifiable;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User eloquent model.
 *
 * @category UserManagement
 *
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use Notifiable;
    use InteractsWithMedia;
    use Messagable;
    use HasFactory;
    use Sluggable;

    public const ADMIN_TYPE   = 'admin';
    public const DEFAULT_TYPE = 'default';

    /**
     * Check if this user is an admin.
     *
     * @return bool true if the user is an admin
     */
    public function isAdmin()
    {
        return $this->type === self::ADMIN_TYPE;
    }

    /**
     * Users can have one lens.
     *
     * @return HasOne The eloquent relationship
     */
    public function lens()
    {
        return $this->hasOne('App\Models\Lens', 'user_id');
    }

    /**
     * Users can have more lenses.
     *
     * @return HasMany The eloquent relationship
     */
    public function lenses()
    {
        return $this->hasMany('App\Models\Lens', 'user_id');
    }

    /**
     * Users can have one filter.
     *
     * @return HasOne The eloquent relationship
     */
    public function filter()
    {
        return $this->hasOne('App\Models\Filter', 'user_id');
    }

    /**
     * Users can have more filters.
     *
     * @return HasMany The eloquent relationship
     */
    public function filters()
    {
        return $this->hasMany('App\Models\Filter', 'user_id');
    }

    /**
     * Users can have one eyepiece.
     *
     * @return HasOne The eloquent relationship
     */
    public function eyepiece()
    {
        return $this->hasOne('App\Models\Eyepiece', 'user_id');
    }

    /**
     * Users can have more eyepieces.
     *
     * @return HasMany The eloquent relationship
     */
    public function eyepieces()
    {
        return $this->hasMany('App\Models\Eyepiece', 'user_id');
    }

    /**
     * Users can have more sets.
     *
     * @return HasMany The eloquent relationship
     */
    public function sets()
    {
        return $this->hasMany('App\Models\Set', 'user_id');
    }

    /**
     * Number of eyepieces.
     *
     * @return int The number of eyepieces
     */
    public function getNumberOfEyepiecesAttribute(): int
    {
        return count($this->eyepieces);
    }

    /**
     * Users can have one instrument.
     *
     * @return HasOne The eloquent relationship
     */
    public function instrument()
    {
        return $this->hasOne('App\Models\Instrument', 'user_id');
    }

    /**
     * Users can have more instruments.
     *
     * @return HasMany The eloquent relationship
     */
    public function instruments()
    {
        return $this->hasMany('App\Models\Instrument', 'user_id');
    }

    /**
     * Users can have one location.
     *
     * @return HasOne The eloquent relationship
     */
    public function location()
    {
        return $this->hasOne('App\Models\Location', 'user_id');
    }

    /**
     * Users can have more locations.
     *
     * @return HasMany The eloquent relationship
     */
    public function locations()
    {
        return $this->hasMany('App\Models\Location', 'user_id');
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
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.';
        } elseif (strcmp($text, 'Attribution-ShareAlike CC BY-SA') === 0) {
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
     * Sets the password attribute.
     *
     * @param mixed $password The password
     *
     * @return None
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * Sets the password attribute. This method should only be used
     * to copy the old md5 passwords to the new database.
     *
     * @param mixed $password The md5 encrypted password
     *
     * @return None
     */
    public function setMd5Password($password)
    {
        $this->attributes['password'] = $password;
    }

    /**
     * Sends the email verification mail.
     *
     * @return None
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\DeepskyLogVerificationNotification());
    }

    /**
     * Sends the password reset mail.
     *
     * @param mixed $token The token for the reset mail
     *
     * @return None
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\DeepskyLogResetPassword($token));
    }

    /**
     * Make sure to only have one picture for the observer.
     *
     * @return None
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('observer')
            ->singleFile();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'email', 'password', 'country', 'language',
        'observationlanguage', 'copyright', 'sendMail', 'fstOffset',
        'standardAtlasCode', 'showInches', 'overviewFoV', 'lookupFoV',
        'detailFoV', 'overviewdsos', 'lookupdsos',
        'detaildsos', 'overviewstars', 'lookupstars', 'stdtelescope',
        'detailstars', 'photosize1', 'photosize2', 'atlaspagefont',
        'stdlocation', 'stdlens', 'stdeyepiece', 'about', 'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
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
     * Also store a thumbnail of the image.
     *
     * @param $media the media
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'username',
            ],
        ];
    }
}
