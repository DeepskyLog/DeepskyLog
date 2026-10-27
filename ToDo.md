# ToDos

## Next steps

- [ ] Update to laravel 13.
  - [ ] Only needs PHP 8.4 -> Ubuntu 26.04 LTS.
- [ ] Translations
- [ ] Double stars to check by hand (`php artisan objects:fill-double-star-magnitudes -v` lists them):
  - [ ] HU 559: 41″ and primary 6.4 in DeepskyLog, 0.6″ and primary 9.6 in the WDS (HU 559 BC); probably another pair.
  - [ ] HJ 748: 4″ and primary 11.2 in DeepskyLog, 6.8″ and primary 8.37 in the WDS.
  - [ ] STF 1156: the WDS entry lies 31′ from DeepskyLog's position, primary 7.8 against 9.5.
  - [ ] Fox 256: the WDS entry lies 43′ from DeepskyLog's position, primary 10.9 against 8.52.
  - [ ] Names the command cannot resolve: Nu Boo, Zeta Sco, Omega Sco and Xi Cen (two numbered stars each), 42-45 Ori and 64-65 Gem (ranges), Star Abagail, Lorenzin 18, 19 and 22, and the entries without a separation, such as Procyon and 118 Tauri.
  - [ ] About 270 NGC/IC "double stars" are not in the WDS and mostly have no separation or magnitudes.
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
  - [ ] php artisan observing-lists:remove-spurious-items -v (dry run: check the lists it lists, and the lists it keeps M 1 in because their name says it belongs there), then php artisan observing-lists:remove-spurious-items --apply (removes the M 1 the import of April 2026 put first in lists it does not belong to, such as NSOG Apus, and deletes the lists that leaves empty unless they have comments, likes or subscribers; on the development database: 183 items, 12 empty lists, 16 kept for checking, among them "Arp galaxies in de Messier lijst", whose M 1 is probably spurious too)
  - [ ] php artisan objects:add-struve-sigma-names (dry run: 3650 new names on the development database), then php artisan objects:add-struve-sigma-names --apply (adds Σ 1, Σ I 1, Σ II 1, OΣ 1 and OΣΣ 1 as alternative names of the STF, STFA, STFB, STT and STTA double stars and updates their search index, #480; new objects and edited names get them automatically), then php artisan responsecache:clear (the cached object pages do not show the new names yet)
  - [ ] php artisan observations:repair-sky-values (dry run: check the observations and locations it lists and the counts), then php artisan observations:repair-sky-values --apply (moves NELM values stored as SQM to the limiting magnitude, drops SQMs below 10 and NELMs above 8, and stores the old site's placeholders, an SQM of -1 or 0 and a limiting magnitude of 0, as empty; on the development database: 14 moved, 381 dropped, 10 locations, about 120,000 SQM and 40,000 limiting magnitude placeholders)
  - [ ] php artisan messages:repair-usernames (dry run: check the names it renames and the names of deleted accounts it leaves alone), then php artisan messages:repair-usernames --apply (renames the HTML-encoded or garbled usernames of old messages and their read/deleted marks, e.g. `Torbj&ouml;rn Holmqvist`, to the username of the account, so these observers see their messages and no longer get every old broadcast as unread; on the development database: 5 names renamed (Torbjörn Holmqvist twice, Frédo, hajüem, René Rijken), 6 names of deleted accounts left alone)
  - [ ] php artisan messages:delete-unaddressed (dry run: check the senders and dates it lists), then php artisan messages:delete-unaddressed --apply (deletes the messages without a receiver, the old site's achievement notices of 2015–2016, which no inbox shows; on the development database: 2,767 messages, all from DeepskyLog)
  - [ ] php artisan objects:import-hickson-members (dry run: check the members it matches by position, and the members it lists to check by hand), then php artisan objects:import-hickson-members --apply (adds the member galaxies of the Hickson compact groups as alternative names `Hickson 11A` of their galaxy, or as new galaxies, linked to their group; on the development database: 177 members already there, 161 matched to a galaxy, 117 new galaxies, 17 part-of links added, 8 to check by hand: 17B, 26G, 28D, 50E, 54D, 59D, 75B, 84E)
  - [ ] php artisan objects:merge-types (dry run: check the new type of each object), then php artisan objects:merge-types --apply (removes the object types CLANB, ENRNN, ENSTR, RNHII and STNEB and gives their objects an H-II region, emission nebula, reflection nebula, open cluster or cluster in a galaxy type, and deletes the test object Hickson 752, #409; on the development database: 37 objects, 1 test object), then php artisan update:achievements and php artisan responsecache:clear (M 17, M 20 and IC 2944 now count as nebulae instead of open clusters)
  - [ ] php artisan migrate (adds `objects.mag_source`)
  - [ ] php artisan objects:repair-nebula-positions -v (dry run: check the positions it moves and the alternative names it removes), then php artisan objects:repair-nebula-positions --apply (moves the nebulae at a wrong position, checked against SIMBAD, and replaces the LBN names that belong to another nebula; on the development database: 8 moved (Ced 178, LBN 289, Sh 2-36, Sh 2-67, Sh 2-117, Sh 2-243, Sh 2-283, Sh 2-285), LBN 115 of Sh 2-115 replaced by LBN 357, LBN 601 of IC 353 removed), then php artisan responsecache:clear
  - [ ] php artisan objects:estimate-nebula-magnitudes -v (dry run: check the estimates and the names it still refuses), then php artisan objects:estimate-nebula-magnitudes --apply (gives the bright nebulae without a magnitude one estimated from the brightness class of their LBN or Sh 2 name, so they get a contrast reserve and filter advice; on the development database: 350 nebulae, among them IC 434 ≈ 6.7; only LBN 554 of Sh 2-164 is still refused, as Lynds' own declination is 3° off), then php artisan responsecache:clear
  - [ ] php artisan migrate (deletes the stored contrast reserves of double stars, which have none now)
  - [ ] php artisan objects:fill-double-star-magnitudes -v (dry run: check the pairs it lists to check by hand), then php artisan objects:fill-double-star-magnitudes --apply (fills in the magnitudes of the companions of the double stars from the Washington Double Star Catalog, replaces separations of another pair and wrong primary magnitudes, and moves the double stars at a wrong position; on the development database, in two runs: about 5130 companions, 22 primaries, 97 separations, 99 positions (sign errors in the declination, wrong hours), 4 to check, 290 not found, mostly NGC/IC pairs that are not in the WDS), then php artisan responsecache:clear
  - [ ] php artisan migrate (`2026_10_27_000000_forget_metrics_of_binoculars` and `2026_10_27_000010_forget_metrics_before_point_source_limit`: deletes all stored contrast reserves, as a contrast reserve is now never above that of a star of the object's magnitude; they are calculated again by the queue worker when needed, so the tables and Tonight's best show "…" for a while)
  - [ ] php artisan objects:repair-measurements -v (dry run: check the changes), then php artisan objects:repair-measurements --apply (multiplies by 60 the sizes of the galaxies stored in arcminutes as arcseconds, and corrects M 24 (magnitude 4.6 instead of NGC 6603's 11.1), M 43 (6′ × 3′ instead of 20′ × 20′) and six galaxies from the PGC and UGC; on the development database: 58 galaxies, M 24, M 43 and 6 galaxies), then php artisan responsecache:clear
  - [ ] php artisan queue:restart (the workers must calculate the contrast reserves with the new code)
  - [ ] php artisan objects:repair-identities (dry run: check the merges, the type and the deletion), then php artisan objects:repair-identities --apply (merges the duplicates IC 1108 into NGC 5882, MCG -03-28-004 into NGC 3450, MCG +08-01-018 into UGC 12889 and NGC 5906 into NGC 5907, with their observations and list items, their names becoming alternative names; makes MCG +01-48-001 a bright nebula; deletes KAZ 50, which does not exist; on the development database: 4 merges with 3 observations and 3 list items, 1 type, 1 deletion)
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
  - [ ] Ready to be released, make sure to check if everything still works with the PiFinder.

### Done

- [x] Seasonal planner per list: for each object in an observing list, the months it is well placed from the observer's location, plus a "what's up this month" filter. Most of the year-graph logic already exists.
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
- [x] Added planets, lunar features, sun, moon, asteroids
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
- [X] For every object, the user can show the field views: where to find the object with the naked eye, binoculars, and telescopes
- [X] Add a wizard to create an observing list.
- [X] Organize star parties.  Users can subscribe to them, and the organizer can send a mail to all subscribers.
- [X] New section on the home page
- [X] Personal blog page for every user. 
- [X] Weather forecasts
- [X] Personal observing goals: "200 Herschel objects this year", "every Messier in one season", with progress on the observer page and in the blog.
- [x] Year in review: a public page per observer and finished year (totals against the year before, per month, new objects, best night, faintest and rarest object, catalog progress, drawings, equipment, goals), in the blog and the RSS feed, with a DeepskyLog message in the first week of January.
- [x] Observation reminders after a session: notes a session has but no observation records yet. This nudges people to finish logging the night.
- [X] Year in review
- [X] Messier marathon planner: the best nights of March and April near the new Moon at the observer's location, and the order to see the Messier objects in, with the time window of each object (objects only high enough in the twilight marked), saved as an observing list.
- [X] Filter advice per object
- [X] Double star resolvability: whether the standard telescope splits a double star (Dawes limit, wider for a fainter companion, the companion bright enough) and at which magnification, on the object page, in the field views and as a filter of the object search. Double stars no longer get a contrast reserve; their companion magnitudes, separations and positions are completed and corrected from the Washington Double Star Catalog.
- [X] Variable stars and novae via AAVSO. Showing the latest AAVSO estimate on the object page for supernovae, novae and the variable stars in observing lists  makes the bright-supernova news items possible too.
- [X] Red night mode and an "at the eyepiece" view. There's no red-light theme today. Add a red-on-black toggle and a stripped-down page for an observing list that has times: the current object, the next one, the finder chart and a big "Log it" button that opens the observation form already filled in (session, time, equipment). The list times you just added would then be useful at the telescope, not only before the night.
- [X] Offline lists (PWA). Dark sites often have no signal. A service worker could cache the observing list, its charts and the at-the-eyepiece page, and keep observations entered offline until the connection return.
- [X] "What's new for me" after new equipment: the objects a telescope shows that none of the observer's other active instruments shows, or an eyepiece adds to the standard telescope, at one of their locations, not seen yet, the 200 most observed, with a button to make that a list. Linked from the instrument and eyepiece pages, highlighted right after adding them.
- [X] Binoculars: both eyes see fainter than one, so binoculars get a higher contrast reserve (+0.15) and limiting magnitude (+0.38) than a telescope of the same aperture, everywhere these are calculated (object page, stored metrics, Tonight's best, double stars, filter advice). Their field is estimated wider (60° apparent), the field views show them upright in place of the telescope, and the Messier marathon planner takes an instrument (by default the standard telescope): a faint object only counts from the altitude at which it can be seen with it, or is missed as too faint.
- [X] Sky quality history of a location: the SQM and NELM recorded with the observations at a location, a point per night (median of its observations) and a line through the median of each year, on the location page.

Community features

Observing challenges. Site-wide, time-limited challenges such as "Hickson month" or "Arp galaxies this spring", with a shared progress page and a leaderboard. They would reuse ObservingGoal::progress() and ObjectSources, and get their announcements from the news items.

Object-of-the-week discussion. One object a week chosen from Tonight's best, shown on the home page with everyone's observations and drawings of it from that week. It's the observation counterpart of the sketch of the week.

"Observed together" at starparties. After a starparty, a combined page of what all participants saw, ranked by how many people saw each object, made into a shareable report. StarPartyActivity already collects the data.

Smaller additions

Calendar feed of your planned list times. .ics export of an observing list that has times, or of the clear-night alerts, as starparties already have.

Android app to send GoTo commands to the Nexus DSC Pro.  

Android has no equivalent command, and DeepskyLog's web page can't drive the mount directly either. But your Nexus DSC Pro gives you a way that needs no phone at all.

Why the page can't send a GoTo itself

The Nexus DSC Pro talks to SkySafari over its own WiFi: IP 10.0.0.1, port 4060, using the LX200 Classic command set. It passes GoTo commands on to the ServoCAT over USB.
A web page in Chrome can only make web requests, not the raw network connection the Nexus expects. That's a browser rule, on Android as on iPhone.
The Nexus has no Bluetooth, and its other ports are RS232 serial and USB. So the earlier Bluetooth idea is out as well.
SkySafari for Android has no documented way for another app to send it an object. There's an open feature request from a user asking for exactly that, with no reply from the developer.
What does work: the list on the Nexus itself

The Nexus DSC Pro manual says it reads observing lists in SkySafari's .skylist format from the root folder of its microSD card. DeepskyLog already exports that format.

Export the list as .skylist, from the list page or the button at the bottom of the eyepiece page.
Copy the file to the root of the Nexus's microSD card.
At the telescope, choose Find → From Observation List on the Nexus, pick the list, and step through the objects with the arrow keys.
Press OK to open the telescope control panel, choose GOTO, and the ServoCAT slews.
Keep DeepskyLog's eyepiece page alongside it for the times, charts and "Log it". SkySafari isn't needed for the GoTo at all.

Caveat: the Nexus only shows objects that are also in its own databases. Planets, comets and obscure objects from a DeepskyLog list may be missing. Try it with a list and tell me which objects it skips; I can then adjust the names the .skylist export writes.

A true one-tap GoTo

That would need a small Android app of our own. It would open a DeepskyLog link such as dslgoto://… and send the object's coordinates to 10.0.0.1:4060 with the LX200 GoTo commands. It's feasible, but it's a separate project, and the manual doesn't say whether the Nexus accepts a second connection while SkySafari is connected. I'd only go that way if the microSD workflow turns out to be too clumsy.  Will this also work for other mounts?