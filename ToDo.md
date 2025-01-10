# ToDos

+ [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Combined version of old DeepskyLog and laravel DeepskyLog

> /Users/wim/GitHub/DeepskyLog/deepskylog is http://deepskylog.localhost/, the old code
> is http://deepskylog.localhost/old/

+ deepskylog: uses laravel jetstream and socialstream for authentication

### Next steps

+ [ ] Create instruments tables in the new version of DeepskyLog.
    + [X] Add Make and flip options to the instruments (see PiFinder model)
    + [X] Write script to populate the tables from the old database.
    + [X] Use database from the new version in the old version of DeepskyLog.
    + [X] Update user table every 5 minutes with new users from www.deepskylog.org
    + [X] Make a table for the instrument makes and add some standard makes (like SkyWatcher, Celestron, ...)
    + [X] Update user model to use the instruments from the new database.
    + [ ] Add page to see the details of an instrument
    + [ ] Change API to get the instruments from the new table.
    + [ ] Add all pages for adding, viewing, editing, deleting instruments. Also update user overview page.
    + [ ] Add page for administrator (only for the instrument_makes table)
    + Add obstruction_perc:
        + Obsession 18'': 17.2% (3.1'' / 18'')
        + Obsession 15'': 17.3% (2.6'' / 15'') ?
        + 20cm Reise-Teleskop: 25% (5cm / 20cm)
        + Zelfbouw: 21,4% (75mm / 350mm)
        + 30cm Orion Skyquest XT12i IntelliScope: 23%
    + [ ] Install updates on the server (for the old DeepskyLog and the new one)
    + [ ] Release new version of pyDeepskyLog
    + [ ] Change requirement in pifinder code
+ [ ] Create eyepieces tables in the new version of DeepskyLog.
    + [ ] Write script to populate the tables from the old database.
    + [ ] Update user model to use the eyepieces from the new database.
    + [ ] Add Field stop: tfov = eyepiece.field_stop / telescope.focal_length_mm * 57.2958
        + 31mm Nagler: 42mm
        + 21mm Ethos: 36.2mm
        + 20mm Nagler: 27.4mm
        + 19mm Panoptic: 21.3mm
        + 13mm Ethos: 22.3mm
        + 13mm Nagler: 17.6mm
        + 8mm Ethos: 13.9mm
        + 8mm Radian: 8.3mm
        + 5mm Nagler: 7mm
        + Baader 3.5mm Hyperion: 3.9mm
        + Baader 36mm Aspheric: 44mm
    + [ ] Use database from the new version in the old version of DeepskyLog.
    + [ ] Change API to get the eyepieces from the new table.
    + [ ] Add all pages for adding, viewing, editing, deleting eyepieces. Also update user overview page. TO DO AFTER
      adding the contrast reserve to PiFinder
+ [ ] Create lenses tables in the new version of DeepskyLog.
    + [ ] Write script to populate the tables from the old database.
    + [ ] Update user model to use the lenses from the new database.
    + [ ] Use database from the new version in the old version of DeepskyLog.
    + [ ] Create API to get the lenses from a given user.
    + [ ] Add all pages for adding, viewing, editing, deleting lenses, filters, instrument sets. Also update user
      overview page. TO DO AFTER adding the contrast reserve to PiFinder
+ [ ] Create filters tables in the new version of DeepskyLog
+ [ ] Create instrument sets tables in the new version of DeepskyLog

### Distant future

+ [ ] Messages in DeepskyLog
    + [ ] Also messages for everyone // like a forum
+ [ ] Sessions
    + [ ] Overview page with all images
    + [ ] Blog style?
+ [ ] Targets
+ [ ] Observing lists

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
