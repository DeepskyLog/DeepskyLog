<?php
/**
 * User eloquent model.
 *
 * PHP Version 7
 *
 * @category UserManagement
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\DeepskyLogVerificationNotification;
use App\Notifications\DeepskyLogResetPassword;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Cmgmyr\Messenger\Traits\Messagable;

/**
 * User eloquent model.
 *
 * @category UserManagement
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use Notifiable;
    use HasMediaTrait;
    use Messagable;

    public const ADMIN_TYPE = 'admin';
    public const DEFAULT_TYPE = 'default';

    /**
     * Check if this user is an admin.
     *
     * @return bool True if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->type === self::ADMIN_TYPE;
    }

    /**
     * Users can have one or more lenses.
     *
     * @return HasMany The eloquent relationship
     */
    public function lenses()
    {
        return $this->hasMany('App\Lens', 'observer_id');
    }

    /**
     * Users can have one or more filters.
     *
     * @return HasMany The eloquent relationship
     */
    public function filters()
    {
        return $this->hasMany('App\Filter', 'observer_id');
    }

    /**
     * Returns the copyright information (including the image and the link).
     *
     * @return string The copyright information
     */
    public function getCopyright()
    {
        $text = $this->copyright;

        if (strcmp($text, "Attribution-NoDerivs CC BY-ND") === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0 International License</a>.';
        } elseif (strcmp($text, "Attribution CC BY") === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.';
        } elseif (strcmp($text, "Attribution-ShareAlike CC BY-SA") === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, "Attribution-NonCommercial CC BY-NC") === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0 International License</a>.';
        } elseif (strcmp($text, "Attribution-NonCommercial-ShareAlike CC BY-NC-SA") === 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.';
        } elseif (strcmp($text, "Attribution-NonCommercial-NoDerivs CC BY-NC-ND") === 0) {
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
        $this->notify(new DeepskyLogVerificationNotification());
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
        $this->notify(new DeepskyLogResetPassword($token));
    }

    /**
     * Make sure to only have one picture for the observer.
     *
     * @return None
     */
    public function registerMediaCollections()
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
        'detaildsos', 'overviewstars', 'lookupstars',
        'detailstars', 'photosize1', 'photosize2', 'atlaspagefont',
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
}
