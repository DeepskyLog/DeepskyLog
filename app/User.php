<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\DeepskyLogVerificationNotification;
use App\Notifications\DeepskyLogResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use HasRoles;

    public function lenses()
    {
        return $this->hasMany(Lens::class, 'observer_id');
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    // Override this function to use our own notification
    public function sendEmailVerificationNotification()
    {
        $this->notify(new DeepskyLogVerificationNotification);
    }

    // Override this function to use our own notification
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new DeepskyLogResetPassword($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'country', 'language', 'observationlanguage', 'copyright'
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
