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
+ [X] Send mail to verify mail account directly after registration
+ [X] Make a dark version of all the socialstream pages
+ [X] Don't show the teams dropdown if the user only belongs to one team.
+ [X] Show message when logged in and the country (or the about) is not set.
+ [X] In the observer page, add all the extra information
+ [X] Add sponsors page
+ [X] Create the all users administrator page
+ [X] Add team member should show a list of observers, not just an option to add a person using an email address.
+ [X] Move to slugs for the username and the team names
+ [X] Make code work again with new version of laravel, livewire, ...
+ [X] Refactor the accomplishment methods to make them more general.
+ [X] Update to wireui 2.0
+ [ ] Create menu for the new version
    + [X] Create all menus
    + [X] Create Download Magazines page
    + [ ] Menu for small screens
    + [ ] Test on Phone
    + [ ] Add observing list does not work...
    + [ ] Is observing lists selection needed?
+ [ ] Finish observer detail page
    + [X] Add number of unique objects seen and drawn
    + [ ] Add all DeepskyLog sketches of the Week / Month to the observer detail page
    + [ ] Speed up the observer detail page
    + [ ] Add https://laravel-comments.com/ (not free), https://github.com/ryangjchandler/laravel-comments,
      or https://github.com/anilkumarthakur60/Commentable/ for commenting on observer?
+ [ ] User administration page
    + [ ] Add link to the rows in the user administrator page to go directly to the observer
    + [ ] Make sure that users which have observations, ... can not be deleted in the user administrator page
    + [ ] Add administrator only options
+ [ ] Add translations for the new version
    + [ ] Also adapt the code in api.php and User.php to translate the country names, and the language names.
    + [ ] Automatically change the language of the UI when the language settings are changed
+ [ ] Use the old version of DeepskyLog alongside the new version
    + [ ] Add the code
    + [X] Fix the code for php 8.3
    + [ ] Change the link to the user settings page
    + [ ] Change the link to the user information page
    + [ ] Add the link to download the Deep-sky magazines
    + [ ] Check if the user belongs to the correct role / team
    + [ ] Change the link to the observer detail page
+ [ ] Check log in using the new version and try using one of the old pages as logged-in user
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
    + [ ] User information page
    + [ ] User profile update
+ [ ] Release new version of DeepskyLog with new registration and login pages.

### Next steps

+ Messages in DeepskyLog
    + Also messages for everyone // like a forum
+ Index page
+ Instruments, locations, eyepieces, filters, lenses, sets, ...
+ Sessions
    + Overview page with all images
    + Blog style?
+ Observing lists

## Eloquent

+ Check Target.php -> protected $with = ['type', 'constellation'];

## Small issues

+ [ ] In Team Settings page: Automatically update table if new user is added (only works when next user is added)

## Interesting websites

+ [ ] Timezones with php: Carbon: <https://carbon.nesbot.com/docs/#api-timezone>
+ [ ] <https://jamesmills.co.uk/2019/02/28/laravel-timezone/> for timezones / date
+ [ ] Difficult queries: <https://laravel-news.com/laravel-query-builder>
    + [ ] See https://laraveldaily.teachable.com/courses/393790/lectures/6329089
+ [ ] Feeds: https://laravel-news.com/learn-to-create-an-rss-feeds-from-scratch-in-laravel
+ [ ] Check https://laravel-comments.com/ for commenting on observations, observing lists, ...
