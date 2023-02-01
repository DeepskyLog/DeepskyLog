# ToDos

+ [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Combined version of old DeepskyLog and laravel DeepskyLog

> /Users/wim/GitHub/DeepskyLog/laravel is http://laravel.test/, the old code is http://deepskylog.test/old/

+ deepskylog: uses laravel jetstream and socialstream for authentication

### First version

+ [X] Add privacy policy
+ [X] Fix showing (and adding) profile picture
+ [X] Fix sending mails from socialstream
+ [X] Use DeepskyLog logo in mails
+ [X] Use php 8.2 on https://test.deepskylog.org/
+ [X] Install on test.deepskylog.org
+ [X] Send mail to verify mail account directly after registration
+ [X] Fix logging in using Socialite
+ [X] Use DeepskyLog logo
+ [X] Make a dark version of all the socialstream pages
+ [X] Investigate groups
  + [X] Add new role 'Guest'
  + [X] Add a new team Observers, Database Experts and Administrators.
  + [X] Add all new users to the Observers team.  Remove the role admin.
  + [X] Don't create a new group for all new users
  + [X] Use group administrators for the admins
  + [X] Create isAdministrator() method
  + [X] Write unit tests
  + [X] Remove the possibility of creating a new team
  + [X] Don't show all users of the current team is the user is an Observer
+ [X] Create migration to add all needed information to the users table, create unit test
+ [ ] Create seeder to move all the old users to the new database, create unit test
+ [ ] Check if logging in using the old accounts works
+ [ ] Create the new user adminstrator page
+ [ ] Check if the user belongs to the correct role / team
+ [ ] Add translations for the new log in pages
+ [ ] Use the old version of DeepskyLog alongside the new version
+ [ ] Check log in using the new version and try using one of the old pages as logged in user
  + [ ] Change link in the old version
  + [ ] Update the log in code in the old DeepskyLog

```php
include $_SERVER['DOCUMENT_ROOT'].'/../../vendor/autoload.php';

$app = include $_SERVER['DOCUMENT_ROOT'].'/../../bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

$kernel->handle($request = Illuminate\Http\Request::capture());

$id = (isset($_COOKIE[$app['config']['session.cookie']]) ? $app['encrypter']->decrypt($_COOKIE[$app['config']['session.cookie']], false) : null);

if ($id) {
    $app['session']->driver()->setId(explode('|', $id)[1]);
    $app['session']->driver()->start();

    // Session::all()
    // $app['auth']->getSession() //  Illuminate\Session\Store
    // Auth::user()
    // $app['auth']->user()
} else {
    var_dump('NO SESSION ID');
}
```

+ Remove code from the old version of DeepskyLog:
  + Log in
  + Log out
  + Reset password
+ Update the layout to show a similar design as the old DeepskyLog.
+ Update the routes.php file to only include the register and log in and log out pages.
+ Look into https://laravel-news.com/laravel-livewire-form-wizard
+ Release new version of DeepskyLog with new registration and login pages.

### Next steps

+ Messages in DeepskyLog?
+ Index page
+ Instruments, locations, eyepieces, filters, lenses, ...
+ Sessions
  + Overview page with all images
  + Blog style?
+ Observing lists

## Move to Tailwind

+ Create new menu
+ Create new footer
+ Create new sidebar
+ Fix all the rest

## Eloquent

+ Check Target.php -> protected $with = ['type', 'constellation'];

## PHP 8

+ Use new php 8 features

## Observing lists

## Interesting websites

+ [ ] Timezones with php: Carbon: <https://carbon.nesbot.com/docs/#api-timezone>
+ [ ] <https://jamesmills.co.uk/2019/02/28/laravel-timezone/> for timezones / date
+ [ ] CODE CLIMATE: <https://codeclimate.com/github/WimDeMeester/DeepskyLog.laravel/issues?category=duplication&engine_name%5B%5D=structure&engine_name%5B%5D=duplication>
+ [ ] Difficult queries: <https://laravel-news.com/laravel-query-builder>
  + [ ] See https://laraveldaily.teachable.com/courses/393790/lectures/6329089
+ [ ] <https://github.com/Te7a-Houdini/laravel-trix> for editor
  + [ ] For observation lists and observations
+ [ ] Feeds: https://laravel-news.com/learn-to-create-an-rss-feeds-from-scratch-in-laravel
+ [ ] Check https://laravel-comments.com/ for commenting on observations, observing lists, ...
