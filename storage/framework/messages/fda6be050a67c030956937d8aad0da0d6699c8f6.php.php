<?php
/**
 * User authentication.
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

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Xinax\LaravelGettext\Facades\LaravelGettext;
use Illuminate\Http\Request;

/**
 * Logs in the user, sets the correct language and redirects to the home page.
 *
 * @category UserManagement
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Set the language from the database.
     *
     * @return None
     */
    public function authenticated()
    {
        LaravelGettext::setLocale(Auth::user()->language);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param Request $request The request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        $field = $this->field($request);

        return [
            $field => $request->get($this->username()),
            'password' => $request->get('password'),
        ];
    }

    /**
     * Determine if the request field is email or username.
     *
     * @param Request $request The request
     *
     * @return string
     */
    public function field(Request $request)
    {
        $email = $this->username();

        return filter_var(
            $request->get($email), FILTER_VALIDATE_EMAIL
        ) ? $email : 'username';
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request The request
     *
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $field = $this->field($request);

        $messages = [
            "{$this->username()}.exists" =>
            _i('The account you are trying to login is not registered or it has been disabled.')
        ];

        $this->validate(
            $request,
            [
                $this->username() => "required|exists:users,{$field}",
                'password' => 'required',
            ], $messages
        );
    }
}
