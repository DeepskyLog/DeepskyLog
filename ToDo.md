# ToDos

- [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Next steps

- [ ] Update to laravel 13.
- [ ] Observations
  - [x] Make migrations and seeder
    - [x] The sessions can now also have comet observations. Loop over the session (once) and check if comets should be added.
    - [x] The sketches of the week / month have negative ids for comets. This is the negative id of the old cometobservations table. As all observations (also from comets) are now in the same table, we can just remove the negative id and use the normal id of the observation. The ids should be adapted. In the add sketch of the week / add sketch of the month page, we can just use the normal id of the observation, and not the negative id. This should be adapted.
  - [ ] Show detailed observation page, with all details, comments, ...
    - [ ] If the user is an administrator of database expert, it should also be possible to add a sketch of the week / month for a comet observation directly from the observation detail page
    - [x] Difference between short observation and detailed page.
    - [ ] Name of the drawing should be in the table
    - [ ] Remove the two lists at the index. No more deep-sky and comets observations and drawings, show then all (20 latest). Maybe an option to only show deep-sky / moon / comets / ?
    - [ ] Comment on observations
    - [ ] Like observations -> already ok?
  - [ ] Overview page, when clicking on 'More details' on the home page, use a link to the new observation page, with all details, comments, ...
  - [ ] Add observations
    - [ ] other objects: planets, comets, lunar features, ...
      - [ ] Need to work on the database tables for these observations.
    - [ ] Quick add from the left aside
    - [ ] Import / export observations in a file, like the one used by SkySafari, AstroPlanner, Argo Navis, OAL, ...
  - [ ] Check code for ObservationOld and remove it, as all observations are now in the same table.
  - [ ] Check if any of the old models is still used. Maybe remove all the old models.
  - [ ] Feeds: <https://laravel-news.com/learn-to-create-an-rss-feeds-from-scratch-in-laravel>
  - [ ] Search for observations
    - [ ] Quick search in the left aside
    - [ ] Detailed search page
  - [ ] On real server:
    - [ ] php artisan db:seed --class=BackfillSessionObservationIdsSeeder
    - [ ] php artisan db:seed --class=BackfillSketchObservationIdsSeeder
    - [ ] php artisan migrate
- [ ] All missing pages
- [ ] Release new version of pyDeepskyLog
  - [ ] Should add a method to get the locations of a user, but only if authentication is implemented.

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
- [x] Added planets, lunar features, ...
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
