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

    const ADMIN_TYPE = 'admin';
    const DEFAULT_TYPE = 'default';

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
     * Sends the email verification mail.
     *
     * @return None
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new DeepskyLogVerificationNotification);
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
        'name', 'email', 'password', 'country', 'language',
        'observationlanguage', 'copyright'
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
