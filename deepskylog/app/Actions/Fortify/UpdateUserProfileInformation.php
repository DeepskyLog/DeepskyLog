<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'country' => ['string'],
            'about' => ['string:max:1000', 'nullable'],
            'fstOffset' => ['numeric', 'min:-5.0', 'max:5.0'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        } else {
            if ($input['copyrightSelection'] === 'Enter your own copyright text') {
                $copyright = $input['copyright'];
            } else {
                $copyright = $input['copyrightSelection'];
            }
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'country' => $input['country'],
                'about' => Str::limit($input['about'], 1000),
                'fstOffset' => $input['fstOffset'],
                'copyright' => $copyright,
                'copyrightSelection' => $input['copyrightSelection'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
