# ToDos

+ [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

### Next steps

+ [ ] Create filters tables in the new version of DeepskyLog
    + [X] Write script to populate the tables from the old database.
    + [ ] Use database from the new version in the old version of DeepskyLog.
    + [X] Make a table for the filter makes and add some standard makes (like Tele Vue, ...)
    + [X] Update user model to use the filters from the new database.
    + [X] Add page to see the details of a filter
    + [X] Add page to view all filters of an observer
    + [X] Add page to add / edit filters.
    + [X] Move to a better url: wim-de-meester/filter/lens-name
    + [X] Check if the create filter and the list filter page can only be seen if logged in.
    + [X] Add page for administrator (only for the filter_makes table)
    + [X] Translate the new strings
    + [ ] Test on the old server and install updates on the server (for the old DeepskyLog and the new one)
    + [ ] Release new version of pyDeepskyLog -> prepared
+ [ ] Fix logging in
+ [ ] Create locations tables in the new version of DeepskyLogf
+ [ ] Create instrument sets tables in the new version of DeepskyLog
    + [ ] Also show immediately a table with all eyepieces, magnifications, fields of view, ... (also taking into
      account the Barlow and other lenses)

### Distant future

+ [ ] Messages in DeepskyLog
    + [ ] Also messages for everyone // like a forum
+ [ ] Sessions
    + [ ] Overview page with all images
    + [ ] Blog style?
+ [ ] Targets
+ [ ] Observing lists
+ [ ] Friends? Add friends and see their observations / sessions / ... Different lists for Murzim, Capella, ...
+ [ ] Like observations / sketches / sessions / user profiles

### Combination of old and new DeepskyLog

+ [ ] Use the old version of DeepskyLog alongside the new version
+ [ ] Use the new database tables in the old version of DeepskyLog
+ [ ] On the server, set up the apache / nginx configuration to use the old version of DeepskyLog
+ [ ] On the server, make sure to run the queue workers: https://laravel.com/docs/11.x/queues#running-the-queue-worker

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
+ [X] Automatically translate the descriptions.
+ [X] Add instruments, eyepieces, lenses and filters to the new version of DeepskyLog.
    + [X] Use /instrument/user-slug/instrument-slug as url

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
