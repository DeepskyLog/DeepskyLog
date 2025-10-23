# ToDos

+ [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

### Next steps

+ [ ] Problem with Mockery in Pest
    + [ ] XDEBUG_MODE=off ./vendor/bin/pest --filter DeepskyObjectTest -v
+ [ ] Targets
    + [X] Create tables for deepsky, comets, planets, moons, lunar features, and other object types.
    + [ ] Show information about an object, like type, constellation, other names, description, ...
        + [X] Add Aladin preview:
        + [X] Add date, rise, transit, set, best time and maximum altitude.
        + [X] Add graph with elevation of the object for a location and a certain date -> method does exist in laravel-astronomy-library
        + [X] Add moon and astronomical twilight information for that date and location.
        + [ ] Update laravel-astronomy-library to show a line instead of points for the altitude graph.
            + [ ] Don't show < 0 degrees altitude.
            + [ ] Also add moon elevation to that graph.
        + [ ] Add visibility information (best months to observe, ...).
        + [ ] Seen : Y(858/8) 20080929
        + [ ] Add a list of observations of this object, with a link to the observation.
        + [ ] Add a list of drawings of this object, with a link to the drawing.  At least the sketches of the week / month should be shown.
        + [ ] Add nearby objects
        + [ ] Add export to CSV, Excel, PDF (names PDF, details PDF), Argo Navis, SkySafari, Report, Maps
        + [ ] Add star map created from DeepskyLog or from external source (stellarium web, ...)?
        + [ ] Add lists which have this object
    + [ ] Administrator / Database expert pages to add / edit / delete objects
    + [ ] Search targets, first by name, then by type, constellation, ...
    + [ ] Install sqlite3 on the server to be able to use TNTSearch
+ [ ] Release new version of pyDeepskyLog
    + [ ] Should add a method to get the locations of a user, but only if authentication is implemented.

### Distant future

+ [ ] Observing lists
+ [ ] Observations
+ [ ] All missing pages
+ [ ] Friends? Add friends and see their observations / sessions / ... Different lists for Murzim, Capella, ...

### Done

+ [X] Add privacy policy
+ [X] Use DeepskyLog logo in mails
+ [X] Send mail to verify mail account directly after registration
+ [X] Show message when logged in and the country (or the about) is not set.
+ [X] Make new observer page
+ [X] Add sponsors page
+ [X] Create the all users administrator page
+ [X] Add team member should show a list of observers, not just an option to add a person using an email address.
+ [X] Move to slugs for the username and the team names
+ [X] Refactor the accomplishment methods to make them more general.
+ [X] Create menu for the new version, also for small screens
+ [X] Add 'My Drawings' page
+ [X] Add all drawings page
+ [X] Add Sketch of the week and the month page
+ [X] User administration page
+ [X] Add share buttons for Facebook, X, link, mail, WhatsApp
+ [X] Add administrator / database administrator function to add a sketch of the week and the month
+ [X] Add translations for the new version
+ [X] Add new index page, with latest sketch of the week and the month, 10 latest drawings, 10 latest observers, 10
  latest observations
+ [X] Add object type and constellation to the list of 10 newest observations
+ [X] Automatically translate the descriptions of observations on the home page.
+ [X] Add locations, instruments, eyepieces, lenses and filters to the new version of DeepskyLog.
    + [X] Use /instrument/user-slug/instrument-slug as url
+ [X] Add description and picture to locations, instruments, eyepieces, lenses and filters.
+ [X] Add a plot with the length of the night for a whole year when showing a location.
+ [X] Add instrument sets.
+ [X] Like observations and sketches
+ [X] Rework message system
+ [X] Totally reworked the sessions.  They are now more blog-like

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
