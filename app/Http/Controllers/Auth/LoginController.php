<?php
/**
 * User authentication.
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
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use deepskylog\LaravelGettext\Facades\LaravelGettext;

/**
 * Logs in the user, sets the correct language and redirects to the home page.
 *
 * @category UserManagement
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
    protected $redirectTo = '/home';

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
        \Carbon\Carbon::setLocale(Auth::user()->language);
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
            $field     => $request->get($this->username()),
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
            $request->get($email),
            FILTER_VALIDATE_EMAIL
        ) ? $email : 'username';
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request The request
     *
     * @return None
     */
    protected function validateLogin(Request $request)
    {
        $field = $this->field($request);

        $messages = [
            "{$this->username()}.exists" => _i('The account you are trying to login is not registered or it has been disabled.'),
        ];

        $this->validate(
            $request,
            [
                $this->username() => "required|exists:users,{$field}",
                'password'        => 'required',
            ],
            $messages
        );
    }

    /**
     * Overwrite default login method to help migrate viewers to using
     * bcrypt encrypted passwords.
     *
     * @param Request $request The request
     *
     * @return Response The Login response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically
        // throttle the login attempts for this application. We'll key this by
        // the username and the IP address of the client making these requests
        // into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // check if account logging in for first time
        // check against old md5 password, if correct, create bcrypted updated pw
        $email = $this->username();

        $input = filter_var(
            $request->get($email),
            FILTER_VALIDATE_EMAIL
        ) ? $email : 'username';

        if ($input == 'username') {
            $user = \App\Models\User::where('username', $request->input('email'))->first();
        } else {
            $user = \App\Models\User::where('email', $request->input('email'))->first();
        }

        // Check if the old md5 password is still in the database.
        // If this is the case, we update the password before going on.
        if (md5($request->input('password')) === $user->password) {
            $user->password = $request->input('password');
            $user->save();
        }

        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of
        // attempts to login and redirect the user back to the login form. Of
        // course, when this user surpasses their maximum number of attempts they
        // will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        // dump($githubUser->getNickname());

        $user  = User::firstOrCreate(
            ['email' => $githubUser->getEmail()],
            [
                'name'                => $githubUser->getName(),
                'username'            => $githubUser->getNickname(),
                'language'            => LaravelGettext::getLocale(),
                'observationLanguage' => LaravelGettext::getLocaleLanguage(),
                'email_verified_at'   => now(),
            ]
        );
        // Adapt database
        // 1) Make password nullable
        // 2) Add $table->string('provider_id')->nullable();

        // Log user in
        auth()->login($user, true);

        // Redirect to dashboard
        return redirect($this->redirectTo);
    }
}
