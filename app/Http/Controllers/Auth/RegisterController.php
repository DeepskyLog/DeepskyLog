<?php
/**
 * User registration.
 *
 * PHP Version 7
 *
 * @category UserManagement
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

/**
 * User registration.
 *
 * @category UserManagement
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
    protected $redirectTo = '/login';

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
                'username' => [
                    'required', 'string', 'max:255', 'min:2', 'unique:users',
                ],
                'name'  => ['required', 'string', 'max:255', 'min:5'],
                'email' => [
                    'required', 'string', 'email', 'max:255', 'unique:users',
                ],
                'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&^]/', ],
                'country'             => ['required'],
                'type'                => User::DEFAULT_TYPE,
                'observationlanguage' => ['required'],
                'language'            => ['required'],
                'copyright',
                'g-recaptcha-response' => 'required|captcha',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data The validated data
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create(
            [
                'username'            => $data['username'],
                'name'                => $data['name'],
                'email'               => $data['email'],
                'password'            => $data['password'],
                'country'             => $data['country'],
                'observationlanguage' => $data['observationlanguage'],
                'language'            => $data['language'],
                'copyright'           => $data['copyright'],
                'type'                => User::DEFAULT_TYPE,
            ]
        );
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

        $request->session()->flash(
            str_random(4),
            ['type' => 'success', 'message' => _i('User "%s" successfully registered. You can now log in.', $user->name)]
        );

        if ($this->registered($request, $user)) {
            return $this->registered($request, $user);
        } else {
            return redirect($this->redirectPath());
        }
    }
}
