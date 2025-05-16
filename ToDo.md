# ToDos

+ [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

### Next steps

+ [ ] Update to laravel 12
    + [ ] Update resources/views/livewire/settings/linked-account.blade.php and update-avatar.blade.php
    + [ ] Test logging in using Google / ... and update the code if necessary
    + [X] Update marineusde/larapex-charts to the newest version (with laravel 12 support)
    + [ ] Add laravel-12 compatible version of PeterColes/multilingual-language-list
        + [ ] Uncomment everything in language_selection.blade.php
+ [ ] Update Tailwindcss to version 4.0
+ [X] Create instruments tables in the new version of DeepskyLog.
    + [X] Add Make and flip options to the instruments (see PiFinder model)
    + [X] Write script to populate the tables from the old database.
    + [X] Use database from the new version in the old version of DeepskyLog.
    + [X] Make a table for the instrument makes and add some standard makes (like SkyWatcher, Celestron, ...)
    + [X] Update user model to use the instruments from the new database.
    + [X] Add page to see the details of an instrument
    + [X] Add page to view all instruments of an observer
    + [X] Add page to add / edit instruments.
    + [X] Move to a better url: instrument/wim-de-meester/coronado-solarmax-40mm
    + [X] Check if the create instrument and the list instruments page can only be seen if logged in.
    + [X] Add page for administrator (only for the instrument_makes table)
    + [X] Translate the new strings
    + [X] Test on the old server and install updates on the server (for the old DeepskyLog and the new one)
    + [X] Release new version of pyDeepskyLog
    + [X] Change pifinder code
+ [ ] Create eyepieces tables in the new version of DeepskyLog.
    + [X] Create eyepiece makes table
    + [X] Create eyepiece type table
    + [X] Create eyepiece table
    + [X] Write script to populate the eyepiece table from the old database.
    + [X] Update script to add the number of observations done by each eyepiece.
    + [X] Update user model to use the eyepieces from the new database.
    + [X] Use database from the new version in the old version of DeepskyLog.
    + [X] Add page to view all eyepieces of an observer
    + [X] Add page to see the details of an eyepiece
    + [X] Add table with all instruments and calculate the magnification, field of view, exit pupil, ...
    + [X] Do the same in the detail page for the instrument.
    + [X] Adapt instrument edit -> fixed magnification is set to 0, should not be filled.
    + [X] Add page to add / edit eyepieces.
    + [X] Check if the create eyepiece and the list eyepieces page can only be seen if logged in.
    + [X] Add page for administrator (only for the eyepiece_makes and eyepiece_types table)
    + [X] Translate the new strings
    + [X] Test on the old server and install updates on the server (for the old DeepskyLog and the new one)
    + [X] Release new version of DeepskyLog
    + [X] Release new version of DeepskyLog.old
    + [ ] Release new version of pyDeepskyLog
    + [ ] Change pifinder code
+ [ ] Create lenses tables in the new version of DeepskyLog.
    + [ ] Write script to populate the tables from the old database.
    + [ ] Update user model to use the lenses from the new database.
    + [ ] Use database from the new version in the old version of DeepskyLog.
    + [ ] Create API to get the lenses from a given user.
    + [ ] Add all pages for adding, viewing, editing, deleting lenses, filters, instrument sets. Also update user
      overview page. TO DO AFTER adding the contrast reserve to PiFinder
    + [ ] Adapt instrument and eyepiece detail page to also use the lenses (dropdown to select lens?).
+ [ ] Create filters tables in the new version of DeepskyLog
+ [ ] Create instrument sets tables in the new version of DeepskyLog
    + [ ] Also show immediately a table with all eyepieces, magnifications, fields of view, ... (also taking into
      account the Barlow and other lenses)
+ [ ] Create locations tables in the new version of DeepskyLog

### Distant future

+ [ ] Messages in DeepskyLog
    + [ ] Also messages for everyone // like a forum
+ [ ] Sessions
    + [ ] Overview page with all images
    + [ ] Blog style?
+ [ ] Targets
+ [ ] Observing lists
+ [ ] Friends? Add friends and see their observations / sessions / ... Different lists for Murzim, Capella, ...

### Combination of old and new DeepskyLog

+ [ ] Use the old version of DeepskyLog alongside the new version
+ [ ] Use the new database tables in the old version of DeepskyLog
+ [ ] On the server, set up the apache / nginx configuration to use the old version of DeepskyLog
+ [ ] On the server, make sure to run the queue workers: https://laravel.com/docs/11.x/queues#running-the-queue-worker
+ [ ] Release new version of DeepskyLog with new registration and login pages.

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
+ [X] Add instruments to the new version of DeepskyLog.
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
