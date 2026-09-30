# ToDos

- [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Next steps

- [ ] Test! 
  - [ ] Test for security problems
  - [ ] Test manually when logged in and logged out, as database expert and as administrator
- [ ] Update to laravel 13.
  - [ ] Only needs PHP 8.4
- [ ] Translations
- [ ] On real server:
  - [ ] php artisan db:seed --class=BackfillSessionObservationIdsSeeder
  - [ ] php artisan db:seed --class=BackfillSketchObservationIdsSeeder
  - [ ] php artisan db:seed --class=addAchievementsSeeder
  - [ ] php artisan db:seed --class=LegacyObjectContoursSeeder
  - [ ] php artisan migrate (the `stars` index takes about a minute on 38M rows)
  - [ ] php artisan observations:rebuild-object-stats (after the migration; until then every object shows as unseen), then php artisan responsecache:clear
  - [ ] php artisan astronomy:updateOrbitalElements (after the migration: the orbital elements were stored as single precision floats), then php artisan astronomy:updateCometPhotometry (the first command empties the table, including the photometry)
  - [ ] Remove the old storage/logs/horizons_wrapper_*.json files
  - [ ] php artisan objects:fix-html-entities --apply   
  - [ ] php artisan migrate also runs 2026_09_28_000001_normalize_observation_targets (stores observations made with the web form under the canonical object name / comet id)
  - [ ] php artisan observing-lists:normalize-items (dry run: check the renames and merges), then php artisan observing-lists:normalize-items --apply (renames list items to the canonical object names so they show up in the list tables and exports; items that match no object are only reported)
  - [ ] Atlas pagina's worden gezet in storage/app/atlas -> kijk of die directory 755 als permissies heeft
  - [ ] Second queue worker for the PDF jobs (atlas, object sets, object reports). They now go to the `database-long` connection (queue `long`), so without this worker they stay "waiting for a worker". See also Tips.md.
    - [ ] Update the code only when no PDF is queued or being made: a PDF job that was queued before the update cannot be run by the new code (check with `SELECT id, queue, payload FROM jobs WHERE payload LIKE '%Pdf%';`).
    - [ ] After the update, in `/mnt/bigdisk/var/www/test.deepskylog.org/deepskylog`: `grep -n database-long config/queue.php` must show the new connection.
    - [ ] `php artisan config:clear` (or `php artisan config:cache` if the server uses a cached config): otherwise the worker does not know `database-long` and stops at once with exit code 1.
    - [ ] Test by hand, as the user of `deepskylog-worker.service`: `sudo -u <user> php artisan queue:work database-long --sleep=3` must print that it is processing jobs, not an error. Stop it with Ctrl+C.
    - [ ] Check `/etc/systemd/system/deepskylog-pdf-worker.service`: the same `User=`, `Group=` and `WorkingDirectory=` as `deepskylog-worker.service`, `Description=DeepskyLog PDF queue worker`, and `ExecStart=/usr/bin/php /mnt/bigdisk/var/www/test.deepskylog.org/deepskylog/artisan queue:work database-long --sleep=3`.
    - [ ] `systemctl daemon-reload`, `systemctl reset-failed deepskylog-pdf-worker.service` (it gave up after five failed starts), `systemctl enable --now deepskylog-pdf-worker.service`, then `systemctl status deepskylog-pdf-worker.service` must say "active (running)".
    - [ ] `php artisan queue:restart`, so the existing worker loads the new code and config as well.
    - [ ] Make an atlas page and an object set and check that both finish and can be downloaded. If not: `journalctl -u deepskylog-pdf-worker.service -n 50` and `storage/logs/laravel-<date>.log`.
  - [ ] Separate cache store for the response cache (`responsecache`, in `config/cache.php`): clearing the response cache empties its whole store, and it is now cleared much more often.
    - [ ] Check `grep -n RESPONSE_CACHE .env` in `/mnt/bigdisk/var/www/test.deepskylog.org/deepskylog`.
    - [ ] If it says `RESPONSE_CACHE_DRIVER=file`: remove that line. This can be done before the update, as the old code also falls back to `file`. Do not set it to `responsecache` before the update: the old code does not know that store and the site stops working.
    - [ ] After changing `.env`: `php artisan config:clear` (or `php artisan config:cache` if the server uses a cached config).
    - [ ] After the update: `php artisan responsecache:clear`, so no page cached without a locale is still served.
- [ ] Release new version of pyDeepskyLog
  - [ ] Should add a method to get the locations of a user, but only if authentication is implemented.

### Security review (2026-09-30)

Paths are relative to `deepskylog/`. The Critical and High items were checked in the code and the server config; no exploit was tried on a running site.

#### Medium

- [ ] PDF jobs can be queued without limit: `POST /downloads/dsl-atlas/generate` (`routes/web.php:218`) renders the whole atlas in the web request (`set_time_limit(0)`, no throttle); the atlas queue and `ObjectSets::generate()` (`$rows` not locked or capped) can fill the long-running worker. Only allow `scope=single` synchronously, throttle, allow 1–2 open jobs per user, and cap rows × FOVs × photos.
- [ ] Formula injection in CSV/XLSX exports (`ObservationSearchTable.php:836-888` and the PowerGrid exports): text starting with `=` becomes a real formula. Prefix cells starting with `= + - @ \t \r` with `'`.
- [ ] `POST /messages` sends email, needs only `auth` (not `verified`) and has no throttle. Add `verified` and a `throttle:messages` limiter; also limiters for comments and registration.
- [ ] No security headers (CSP, X-Frame-Options, HSTS, X-Content-Type-Options, Referrer-Policy).
- [ ] Deleting user 1 deletes the three system teams (`app/Actions/Jetstream/DeleteUser.php:60-62`; user 1 owns them in `GroupSeeder`). Skip the system teams in `deleteTeams()`.
- [ ] Other users' equipment can be referenced: `CreateInstrumentSet` (`instruments.*`, `locations.*`), `ObservationsController::store` and `Profile/UpdateUserObservingInformation` (`stdlocation`, `stdtelescope`, …) accept ids without checking the owner. Use `Rule::exists(...)->where('user_id', auth()->id())`.

#### Low / hardening

- [ ] `AtlasController.php:463-468` sets `isPhpEnabled` and `isRemoteEnabled` in dompdf (and `isRemoteEnabled` at line 268): not exploitable now, but turn both off and narrow the chroot.
- [ ] `AtlasController::download` checks neither ownership nor status (the token is an unguessable UUID). Add `auth` and the `user_id` check, as in `ObjectSetController` and `ObjectReportController`.
- [ ] Hidden locations: the clearoutside link and image in `location/show.blade.php:181-188` still show the coordinates to about 1 km.
- [ ] `users.select.api` (`routes/api.php:670`) returns the account for a given email, so it confirms which emails are registered.
- [ ] Internal exception text is shown in the PDF job status (`QueuedPdfJob`). Log it and show a generic message.
- [ ] OAuth tokens in `connected_accounts` are not encrypted: add `encrypted` casts.
- [ ] Set `SESSION_SECURE_COOKIE=true` in production.
- [ ] Slug-based photo filenames can collide, so one user can overwrite another's photo (fixed by the random names above).
- [ ] `Profile/UpdateUserLanguageInformation` never calls `validate()`: `language` and `observationlanguage` are stored as sent.
- [ ] `EyepieceTable.php:146-150` still has a debug `js('alert(...)')` listener.
- [ ] `UpdateUserProfileInformation.php:25`: the rule `'string:max:1000'` is malformed.
- [ ] `observations/create.blade.php:334`: use `@js(old(...))` in `x-data` instead of `'{{ }}'`.
- [ ] Open the uploaded AstroPlanner file read-only (`ObservingListFileImportService::parseAstroPlanner`).

#### Checked and found fine

No SQL or command injection, no `unserialize`/`eval` on user data, no open redirects, no XXE in the XML imports, no secrets in git (`.env` was never tracked), `composer audit` and `npm audit` clean, no CSRF exclusions, the response cache does not serve per-user pages, observing lists / observations / sessions / messages / object editing are authorized, object-set and report downloads check the owner, exports are throttled and capped.

### For later

- [ ] When printing a map for an object, create three pages, one with the naked eye, one with the finderscope and one with the telescope.  Magnitudes of the stars should be calculated for the given location.
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
- [X] Observations can be imported from CSV and OAL
- [X] Observations can be exported to CSV, OAL, AstroPlanner, SkySafari, ...
- [X] DeepskyLog atlases can be created.  They are updated to have a nicer layout.
- [X] Object sets can be created.  They are updated to have a nicer layout.  This was broken in the old version of DeepskyLog but is working again in the new version.
- [X] Reports of lists of objects can be created with your own layout.  The layouts of the old version of DeepskyLog are imported.  This was broken in the old version of DeepskyLog but is working again in the new version.