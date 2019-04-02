<?php
/**
 * User registration.
 *
 * PHP Version 7
 *
 * @category UserManagement
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers\Auth;

use App\User;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

/**
 * User registration.
 *
 * @category UserManagement
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data The data to validate
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 'string', 'email', 'max:255', 'unique:users'
                ],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'country' => ['required'],
                'observationlanguage' => ['required'],
                'language' => ['required'],
                'copyright',
                'g-recaptcha-response' => 'required|captcha'
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data The validated data
     *
     * @return \App\User
     */
    protected function create(array $data)
    {
        // Set the observer role
        $role_r = Role::where('name', '=', 'observer')->firstOrFail();

        $user = User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'country' => $data['country'],
                'observationlanguage' => $data['observationlanguage'],
                'language' => $data['language'],
                'copyright' => $data['copyright']
            ]
        );
        $user->assignRole($role_r); //Assigning role to user

        return $user;
    }

    /**
     * Validate the request, create the user and return to the correct page.
     *
     * @param Request $request The request with all information on the user
     *
     * @return redirect the page of the registered user
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // $this->guard()->login($user);

        return $this->registered($request, $user)
                    ?: redirect($this->redirectPath());
    }
}
