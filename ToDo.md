# ToDos

- [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Next steps

- [ ] Test! 
  - [ ] Test for functionality / inconsistency problems (code review done, see "Functionality / inconsistency review" at the end of this file)
  - [ ] Check code for use of the legacy tables.  
  - [ ] Test for speed problems in the database queries, also think about crawlers and bots that can generate a lot of queries.
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
  - [ ] php artisan migrate
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


## Functionality / inconsistency review (2026-09-28)

Code review of the whole application, without the security aspects. Paths are relative to `deepskylog/`. Items marked (checked) were confirmed by hand; the others come from a review that read the code and migrations but did not reproduce every case. The test suite passed (259 passed, 7 skipped), so none of this is covered by tests yet.

### Low

Equipment, users, statistics
- [ ] Filter Wratten/Schott validated `max:25`, columns are 5 characters (`CreateFilter.php:94-95`).
- [ ] Eyepiece `apparentFOV` validated nullable, column NOT NULL (`CreateEyepiece.php:181`).
- [ ] New make with an existing name gives a 500 (`XMake::create`, use `firstOrCreate`).
- [ ] Admin "delete make" has no validation; the eyepiece version resets types to 1 and deletes types instead of moving them (`EyepieceController.php:74-81` and the Filter/Lens/Instrument versions).
- [x] Object-types pie chart uses the leftover loop variable in `match ($row->type)` (`StatisticsController.php:200`).
- [ ] Ranks and "different objects" are computed differently on /observers, /statistics and the profile (`ObserverController.php:54,67`, `User::getRank()`).
- [ ] Sketch of the month text uses `subMonth()` with overflow and `createFromFormat('Y-m-d')` (`SketchOfTheMonthController.php:48`).
- [ ] `Eyepiece::get_used_instruments()` returns the viewer's default instrument; `get_used_instruments_as_string` (Eyepiece, Location) has no null check.
- [ ] Equipment/location/set `show`/`edit` use `->first()->id` without a null check (500 instead of 404; `edit` renders the create form).
- [ ] Equipment slugs are not unique per user (`'unique' => false`).
- [ ] Lens `factor` allows 0 (division by zero in `Instrument::exit_pupil()`) (`CreateLens.php:77`).
- [ ] `Instrument::field_of_view()` can show "0° 60'".
- [ ] `UpdateUserObservingInformation` saves fields one by one, so `EnqueueComputeCRForUser` can run twice; changing `stdlens` doesn't recompute (plausible); eyepieces and lenses have no "Make default" button.
- [ ] `app/Policies/testPolicy.php` refers to a non-existent `App\Models\test`.

Ephemerides, jobs, commands
- [ ] `calculateApparentEquatorialCoordinates($date)` fallbacks don't pass `$date->copy()` (harmless with v6.8.3) (`ObjectEphemerides.php:520,528,534`, `AladinPreviewInfo.php:587,595`, `ObjectController.php:1803`).
- [ ] `metrics:backfill-best-mag --limit` is ignored by `chunk()`, and chunking skips rows because it updates the filtered column (`BackfillBestMagForMetrics.php:48`). Use `chunkById`.
- [ ] `ComputeEphemerides` cache keys don't match between the job, `ObjectEphemerides` and `pollCache`; `whereNull('user_id')` on locations never matches, so guests get an arbitrary location; `wire:poll.5s` runs forever when the ephemerides are empty.
- [ ] One date change triggers three recomputes of `ObjectEphemerides` and two of `MoonDetails`.
- [ ] `AladinPreviewInfo` 1 s debounce drops the second of two quick changes (`AladinPreviewInfo.php:286-300`).
- [ ] `AladinPreviewInfo` computes planet coordinates but uses the null `$obj->ra` / `$obj->decl` (970-995).
- [ ] (plausible) `sessions:backfill-slugs` (every 5 min) loops forever on names where `Str::slug()` is empty.
- [ ] `ComputeObserverStats` uses MySQL-only `MONTH()` / `YEAR()` and swallows all errors.
- [ ] Near-parabolic comets are geocentric, elliptic ones topocentric (library `NearParabolic.php:97`).
- [ ] Object-set overview charts always show unknown magnitudes, unlike the atlas (`ObjectSetPdfService.php:212`).
- [ ] Report layouts without `showelements` get '' instead of 'hetp' (`ReportLayoutCatalog.php:243`).
- [ ] `/downloads/dsl-atlas/generate` has no auth and falls back to user 1 (`AtlasController.php:120`).
- [ ] `NearbyExportController.php:21-44, ~415` checks `=== null`, so `ra=` (empty) gives a centre at 0/0. Use `is_numeric`.

Cross-cutting
- [ ] `routes/api.php` select endpoints call `->first()->id` / `->fullName()` without null checks (lines 262-267, 553, 721-911); `eyepiece.select.api` returns `name` instead of `fullName()` (957, 997, 1027); `eyepiece_types.api` without `make` returns only a blank entry (829-833).
- [ ] Mobile admin menu links to `/admin/eyepiece-type` instead of `/admin/eyepiece-types` (`components/menu/admin-responsive-dropdown.blade.php:50`).
- [ ] `env()` used outside config (`CreateLocation.php:210,225,378`, `TrustProxies.php:27`): null after `config:cache`. `config('app.domain')` in `ResolveSocialiteUser.php:48` is not defined.

### Left for the security review

- `User::switchTeam()` skips the membership check and `/switch-team/{team}` lets any user switch to Administrators.
- `AtlasController::download` checks neither ownership nor status.
