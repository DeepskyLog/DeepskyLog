# ToDos

- [ ] See GitHub: <https://github.com/DeepskyLog/DeepskyLog/issues>

## Next steps

- [ ] Test! 
  - [ ] Fix the speed problems found (measured on the local database, as a guest: `/statistics` 5 s, `/popular-objects` 2 s, `/observers` 1.8 s, `/object/m-51` 231 queries, `/drawings` 392 queries, `/` 266 queries).
    - [ ] Queries run on almost every page
      - [ ] `Message::getNumberOfUnreadMails()` (unread badge on every logged-in page) plucks all message ids and diffs them in PHP: use one `COUNT(*)` with `NOT EXISTS`, cache per user.
      - [ ] Card components (`observation-deepsky`, `observation-comet`, `sketch-deepsky`, `sketch-comet`) query per card: likes count and "liked by me", `Observation::find`, magnification accessor, `Target::long_type()` (`target_types`), user lookups with `LOWER(username)`. Preload them in one grouped query in the callers (also in `session/show.blade.php`, which passes no preloads at all).
      - [ ] The object type probe in `navigation-menu.blade.php` (up to 6 queries on every `/object/*` page): let the page pass a flag.
    - [ ] Missing indexes (one migration)
      - [ ] `observations(target_type, target_key)`: the `ofDeepskyObject`/`ofComet` scopes scan all rows (35–250 ms per count, several per object page, and two per star chart).
      - [ ] `observations(created_at)`: RSS feeds.
      - [ ] `observation_likes(observation_type, observation_id)`: the only index starts with `user_id`.
      - [ ] Drop `LOWER(col) = ?` / `LOWER(col) LIKE` (the columns are `utf8mb4_unicode_ci`, so already case-insensitive): object slug lookups in `ObjectController` (an unknown `/object/{slug}` costs 150 ms before the 404), about 10 places in `ObservationsController`, `ObserverController`, `StatisticsController`, the importers, `search_index` in the search.
      - [ ] `show.blade.php:1128` and `comet-page.blade.php:69` filter observations on `target_name` without `target_type`; `comet-page.blade.php:122` runs `SketchOfTheWeek::where()` in a loop.
      - [ ] `Observation::getLastDrawingDateForUser()` / `getLastObservationDateForUser()` (object page, about 200 ms): use `ofDeepskyObject()` with `observer_user_id` and `max('observed_on')`.
    - [ ] Crawlers and bots
      - [ ] No rate limiting on the web routes nor on `/livewire/update` (only the API has `throttle`). Add per-IP limiters, stricter for guests and for exports.
      - [ ] Public exports without limits: `object/{slug}/nearby-chart.pdf` (radius up to 60°, `star_mag` unbounded, can load millions of stars), `nearby-names.pdf` (radius unbounded), `search/*.pdf` (`SearchExportController::resolveRows()` has no cap), `observations/search/export.*` and the PowerGrid exports (up to all observations). Clamp the parameters, cap the rows, throttle, and require login for the PDFs or queue them.
      - [ ] The nearby radius in `NearbyObjectsTable` is set by the client (`updateRadius()`) and stored in the user settings: clamp it.
      - [ ] `?force_translate=1&force_lang=…` in `SessionController` (show and home page) lets guests trigger Google Translate calls, jobs and cache entries for any language: remove or restrict to administrators.
      - [ ] `/api/users.select.api` has no auth and runs `User::all()` with all equipment (and exposes all usernames).
      - [ ] `SESSION_DRIVER=database` inserts a `sessions` row for every request without cookies (also for cached pages), kept 10 days (`SESSION_LIFETIME=14400`): use `file`/`redis`, or do not store sessions for bots.
      - [ ] The response cache refuses every request with a session cookie, so only bots get cached pages; every model save flushes the whole cache; junk query parameters give new cache keys. Key on `auth()` only, whitelist query parameters in `TeamAwareHasher`, invalidate more selectively.
      - [ ] `robots.txt`: disallow the export paths (`*.pdf`, `*.apd`, `*.skylist`, `nearby-*.txt`, `export.*`) and add `rel="nofollow"` to the export links.
      - [ ] `/observations` copies all query parameters into the pagination links (`ObservationsController.php:329`) and has no page cap.
    - [ ] Heavy work on each request
      - [ ] Cache the aggregates of `/statistics` (about 35 COUNTs), `/popular-objects` (GROUP BY over all observations, twice, plus 50 slug lookups), `/observers` (0.5 s + 1 s queries) and `/catalogs`, e.g. one hour.
      - [ ] Cache the RSS feeds for 10–15 minutes and eager load `location` (46 timezone queries per feed).
      - [ ] `ObserverController` runs `ComputeObserverStats` with `dispatchSync` when the cache is cold (about 70 COUNTs, half of them site-wide totals that are the same for every observer): share the site-wide totals under one key and never run it synchronously for guests. `getRank()` runs twice on the observer page, catalog counts are computed twice.
      - [ ] `SearchResultsTable`: `mount()` loads the whole result set, runs one `exists()` per name and computes the contrast reserve inline (about 1,000 queries and up to 125 writes on the first load); `rendering()` runs the search again on every round trip; `fields()` calls `Instrument::find()` twice per row. Move the precompute to one queued job and resolve the instrument once.
      - [ ] `NearbyObjectsTable`: the pending count in `rendering()` has no decl prefilter (all 50k objects); `fields()` queries and inserts metrics per row.
      - [ ] `AdvancedObjectSearchTable` groups the whole `search_index` (`GROUP BY LOWER(COALESCE(...))`) on every sort or page click (2.3 s): store the canonical name in an indexed column or a table rebuilt by `search:reindex`.
      - [ ] Sorting/filtering on the correlated statistics columns of `ObjectObservationStatistics` (seen, last seen) takes up to 3.5 s: keep per-object statistics in a table, or disable sorting on them for large result sets.
      - [ ] `PopularSessionsTable` / `PopularObservationsTable`: several `find()` and User loads per row; join the needed columns into the aggregate query.
      - [ ] Astronomy library: `Time::deltaT()` runs `DeltaT::first()` (and a year lookup) on every call, 171–379 queries per object page. Memoize in a static array in ~/GitHub/laravel-astronomy-library and release.
      - [ ] `StarChartService::loadStars()` can only use the decl part of the `(decl_degrees, ra_degrees)` index (4–6 s per detail chart, the main cost of atlas PDFs) and hydrates `Star` models: add a declination zone column with an index `(zone, ra_degrees, vmag)`, use `toBase()`, put the `LIMIT` in SQL outside atlas mode.
      - [ ] Changing the standard instrument, location or lens queues 50k `ComputeContrastReserveForObject` jobs of about 9 queries each: dispatch per chunk of objects, load the equipment once, write with one `upsert()`, make the job unique per user/equipment.
    - [ ] Smaller items
      - [ ] Importers: memoize the resolved object names during an import.
      - [ ] `ObservationSearchTable` / `ObservationSearchQuery`: `target_name LIKE '%q%'`; match on `target_name_normalized` instead.
      - [ ] `CountriesChart`: `Country::where()->first()` per row.
      - [ ] Admin make/type tables and `RemoveUserTable`: one `count()`/`exists()` per row, use `withCount()`/`withExists()`.
      - [ ] `Schema::hasColumn()` on every render in `SearchResultsTable`, `AdvancedObjectSearchTable`, `NearbyObjectsTable`: cache it.
      - [ ] `wire:model.live` on text fields in the create forms and `DeleteObjectModal`: use `.blur` or `.live.debounce`.
      - [ ] `AdvancedObservationSearch::mount()` runs `SELECT DISTINCT language` over all observations: cache it.
      - [ ] `/sessions`: `otherObservers()` per session; `EphemerisAside` runs `Location::forEphemerides` twice per page.
      - [ ] `AppServiceProvider::boot()` walks `app/Livewire` with `File::allFiles()` on every request: cache the component map.
      - [ ] `sessions:backfill-slugs` runs every 5 minutes: hourly, or remove once all slugs exist.
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

### Left for the security review

- `User::switchTeam()` skips the membership check and `/switch-team/{team}` lets any user switch to Administrators.
- `AtlasController::download` checks neither ownership nor status.
