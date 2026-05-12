# ToDos

- [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Next steps

- [ ] Update to laravel 13.
- [ ] Observations
  - [x] Make migrations and seeder
  - [x] Show detailed observation page, with all details, comments, ...
  - [ ] Add observations page
    - [ ] Try to add an observation for all types of objects, to see if all fields are working correctly.
      - [] Sun
      - [ ] Planet
      - [ ] Open cluster
      - [ ] Binary star
      - [ ] Lunar feature
      - [ ] Galaxy
      - [ ] Comet
    - [ ] Do the same, but first create a new object of all types.
      - [ ] Sun
      - [ ] Planet
      - [ ] Open cluster
      - [ ] Binary star
      - [ ] Lunar feature
      - [ ] Galaxy
      - [ ] Comet
    - [ ] If an observation was already entered, fill the date with the latest date that was entered in the field (when the user adds more than one observation in a row).
    - [ ] Fill the location with the latest location that was entered in the field. Do the same for the instrument, eyepiece, lens and filter fields.  If this was not the case, use the standard / default location, instrument, eyepiece, and lens.
    - [ ] X, facebook, ... links for the sketch of the week and the month should be adapted, as well as the share buttons for the observations.
    - [ ] Check for APP_OLD_URL
    - [ ] Share from the observation detail page.  Also share observing lists?
    - [ ] RSS Feeds: <https://laravel-news.com/learn-to-create-an-rss-feeds-from-scratch-in-laravel>
    - [ ] Import observations from a file, like the one used by SkySafari, AstroPlanner, Argo Navis, OAL, ...
- [ ] Search for observations
  - [ ] Quick search in the left aside
  - [ ] Detailed search page
  - [ ] Export search results in a file, like the one used by SkySafari, AstroPlanner, Argo Navis, OAL, pdf, csv, ...
- [ ] All missing pages
- [ ] Create star charts and atlases.
- [ ] Create observing guides
- [ ] Translations
- [ ] On real server:
  - [ ] php artisan db:seed --class=BackfillSessionObservationIdsSeeder
  - [ ] php artisan db:seed --class=BackfillSketchObservationIdsSeeder
  - [ ] php artisan db:seed --class=addAchievementsSeeder
  - [ ] php artisan migrate
- [ ] Release new version of pyDeepskyLog
  - [ ] Should add a method to get the locations of a user, but only if authentication is implemented.

### For later

- [ ] Add a wizard to create an observing list.

### Done

- [x] Add privacy policy
- [x] Use DeepskyLog logo in mails
- [x] Send mail to verify mail account directly after registration
- [x] Show message when logged in and the country (or the about) is not set.
- [x] Make new observer page
- [x] Add sponsors page
- [x] Create the all users administrator page
- [x] Add team member should show a list of observers, not just an option to add a person using an email address.
- [x] Move to slugs for the username and the team names
- [x] Refactor the accomplishment methods to make them more general.
- [x] Create menu for the new version, also for small screens
- [x] Add 'My Drawings' page
- [x] Add all drawings page
- [x] Add Sketch of the week and the month page
- [x] User administration page
- [x] Add share buttons for Facebook, X, link, mail, WhatsApp
- [x] Add administrator / database administrator function to add a sketch of the week and the month
- [x] Add translations for the new version
- [x] Add new index page, with latest sketch of the week and the month, 10 latest drawings, 10 latest observers, 10
      latest observations
- [x] Add object type and constellation to the list of 10 newest observations
- [x] Automatically translate the descriptions of observations on the home page.
- [x] Add locations, instruments, eyepieces, lenses and filters to the new version of DeepskyLog.
  - [x] Use /instrument/user-slug/instrument-slug as url
- [x] Add description and picture to locations, instruments, eyepieces, lenses and filters.
- [x] Add a plot with the length of the night for a whole year when showing a location.
- [x] Add instrument sets.
- [x] Like observations and sketches
- [x] Rework message system
- [x] Totally reworked the sessions. They are now more blog-like
- [x] New objects detail page
- [x] Added planets, lunar features, sun, moon
- [x] Add a page to edit objects. More user-friendly than before, with a button to fetch data from Simbad.
- [x] Add a page to add a new object, with the same user-friendly form and Simbad fetching.
- [x] Add a page to show the catalog of objects, with filters and search.
- [x] Quick search in the left aside. Use '/' to jump to the search field.
- [x] Detailed search on objects
- [x] Check objects page for administrators, with a button to fetch data from Simbad.
- [x] Reworked observing lists. Users can comment on them, like them, subscribe to them, ...
- [x] Objects can be imported in an observing list from a file. The file can be a simple text file, argo navis file, AstroPlanner file, CSV file, or SkySafari file.

## Small issues

- [ ] In Team Settings page: Automatically update table if new user is added (only works when next user is added)
