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
+ [X] Create migration to add all needed information to the users table, create unit test
+ [X] Check if logging in using the old accounts works
+ [X] Make it possible to log in using the user id (and not using the mail address)
+ [X] Don't show the teams dropdown if the user only belongs to one team.
+ [X] When registering, add the userid
+ [X] Create seeder to move all the old users to the new database
+ [X] Start using wireui
+ [X] Show message when logged in and the country (or the about) is not set.
+ [X] In the observer page, add all the extra information
+ [X] Add sponsors page
+ [X] Create the all users administrator page
+ [X] Add team member should show a list of observers, not just an option to add a person using an email address.
+ [X] Move to slugs for the username and the team names
  + [ ] Make link with slugs for the teams pages
+ [ ] Update table with members of the team automatically when adding a new user
+ [ ] Create a new page to see the observer details (when someone clicks on the observer)
  + [ ] Use the slug to go to the observer page
  + [ ] Add link to the rows in the user administrator page to go directly to the observer
  + [ ] Add administrator only options
+ [ ] Test on Phone
+ [ ] Add translations for the new log in pages
  + [ ] Also adapt the code in api.php to translate the country names, and the language names.
  + [ ] Automatically cvhange the language of the UI when the language settings are changed
+ [ ] Check if the user belongs to the correct role / team
+ [ ] Use the old version of DeepskyLog alongside the new version
  + [ ] Add the code
  + [ ] Fix the code for php 8.2
  + [ ] Change the link to the user settings page.
+ [ ] Create menu for the new version
  + [ ] Add the sponsor page to the old version!
+ [ ] Check log in using the new version and try using one of the old pages as logged in user
  + [ ] Change links in the old version, also for the sponsor page (http://deepskylog.test/sponsors)
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

+ [ ] Remove code from the old version of DeepskyLog:
  + [ ] Log in
  + [ ] Log out
  + [ ] Reset password
+ [ ] Release new version of DeepskyLog with new registration and login pages.

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

## Observing lists

## Interesting websites

+ [ ] Timezones with php: Carbon: <https://carbon.nesbot.com/docs/#api-timezone>
+ [ ] <https://jamesmills.co.uk/2019/02/28/laravel-timezone/> for timezones / date
+ [ ] Difficult queries: <https://laravel-news.com/laravel-query-builder>
  + [ ] See https://laraveldaily.teachable.com/courses/393790/lectures/6329089
+ [ ] Feeds: https://laravel-news.com/learn-to-create-an-rss-feeds-from-scratch-in-laravel
+ [ ] Check https://laravel-comments.com/ for commenting on observations, observing lists, ...
