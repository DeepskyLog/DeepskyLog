# Deploying the new DeepskyLog and the old site on the new database

Server: `vvs-vps.vvs.be` (AlmaLinux 10, Apache + PHP-FPM 8.3, MariaDB 10.11), checked on 28 October 2026.

| | Directory | Code now | Database now |
|---|---|---|---|
| New site, test.deepskylog.org | `/mnt/bigdisk/var/www/test.deepskylog.org` (branch `Combination` of DeepskyLog/DeepskyLog) | `c9ad490e` (29 September) | `deepskylogLaravel`, 121 migrations, the last of 23 April |
| Old site, www.deepskylog.{be,de,fr,nl,org} | `/mnt/bigdisk/var/www/www.deepskylog.<tld>`, five separate git checkouts (branch `master`) | `04759486` | `deepskylog` (observations, observers, stars, …) and `deepskylogLaravel` |

There is also a sixth copy, `www.deepskylog.se`, without a vhost. The drawings of all copies, and `public/images/drawings` of the new site, are symlinks to `www.deepskylog.org/deepsky/drawings`. Both PHP-FPM pools run as `apache`. The MySQL user `deepskylog` has all rights on both databases, so the grant steps of ToDo.md are not needed. The config is not cached (`bootstrap/cache` has no `config.php`), so `config:clear` is enough. `RESPONSE_CACHE_DRIVER` is not set (good). The job queue is empty (14 failed jobs). The scheduler runs from wim's crontab, the workers are `deepskylog-worker.service` (running) and `deepskylog-pdf-worker.service` (failed).

## What was found

**The observations were not copied yet.** `deepskylogLaravel` has no `observations` table: the server runs an older version of the new site, which still reads the observations from the old database. The tables `observations`, `deepsky_observation_details`, `comet_observation_details`, `stars`, `object_contours`, `report_layouts`, `user_preferences`, `observation_comments` and about 40 more are missing. They are made by the 84 migrations that haven't run on the server and filled by the legacy seeders. The old database has 195,500 deep-sky observations (newest 17 October 2026) and 2,898 comet observations.

Already shared, nothing to copy:
- users
- instruments, locations, eyepieces, filters and lenses (same ids in both databases)
- sessions, observing lists, messages, likes, sketches of the week/month

**The order of the database steps is critical.** The observation seeder gives every observation a new id and keeps the old one in `legacy_id`. Several migrations translate old ids to new ones and **delete what they can't translate**:
- `2026_04_28_000001` deletes the `sessionObservations` rows without an observation: all 34,290 links if the observations aren't there yet.
- `2026_09_28_000002` deletes the likes and comments without an observation.

So the observations must be imported, and the session links translated, *between* the first observation migrations and the rest. A plain `php artisan migrate` first (as ToDo.md suggests) would lose all session links and likes. Phase 3 below has the right order.

**The stars.** `LegacyStarsSeeder` copies the old `stars*` tables, about 100 million rows, into `stars`. They never change, so it runs now, in Phase 0.4, while both sites keep running. It must finish before `move_tables_to_new_database.sql` (3.9) moves those tables away. The seeder was rewritten on 28 October: it used to page with OFFSET, which rereads every earlier row for each page (dev stopped in `stars1525` after 38 million rows). It now reads each table in one unbuffered pass and skips the tables that are already complete, so it can be started again after it stops.

**`LEGACY_SITE_URL`** now takes a comma-separated list, because the old site runs on five domains: an observer who registers from www.deepskylog.nl returns to .nl. This was changed today, with a test.

**The old database password.** During the check, `DB_PASSWORD_OLD` of the server's `.env` was printed into my session's output. It is the password of the MySQL user `deepskylog`, which also has the new database. Consider changing it: `ALTER USER 'deepskylog'@'localhost' IDENTIFIED BY '…'`, then `DB_PASSWORD`/`DB_PASSWORD_OLD` in `.env` and `$pass` in the six `databaseInfo.php` files, all in one go.

---

## Phase 0: Preparation (no downtime, days before)

### 0.1 Rehearse on copies of the server databases (strongly recommended)
The nightly dumps are on copernicus: `/STER/wim/databases/www.deepskylog.org.sql` and `www.deepskylogLaravel.org.sql`.
1. Load them locally in two scratch databases, for example `dsl_old` and `dsl_new`.
2. Point a separate `.env` (or the local one, temporarily) at them: `DB_DATABASE=dsl_new`, `DB_DATABASE_OLD=dsl_old`.
3. Run Phase 3 exactly as written.
4. Run every dry run of 3.7 and review the output there, so the window itself only needs `--apply`.
5. Note how long each step takes.
6. Point the local old site at `dsl_new` and test it as in Phase 5.

### 0.2 Commit and publish the code
New site:
1. Commit the work on `observations`.
2. Push it and merge it into `Combination` of DeepskyLog/DeepskyLog, the branch the server pulls.

Old site (`/var/www/DeepskyLog.old`, branch `master`, same GitHub repository):
1. Commit the 60 changed files and the new `lib/observationStore.php` and `sql/move_tables_to_new_database.sql`. Leave out `lib/setup/.databaseInfo.php.swp`; `CLAUDE.md` as you like.
2. Push to `master`.

Don't pull anything on the server yet.

### 0.3 Old site maintenance page
Prepare a `maintenance.html` ("DeepskyLog is being updated, back in an hour") and this block to put at the top of the `.htaccess` of each of the five copies during the window. Replace `1\.2\.3\.4` with your own IP address, so you can still test.

```apache
# MAINTENANCE START
RewriteEngine On
RewriteCond %{REMOTE_ADDR} !^1\.2\.3\.4$
RewriteCond %{REQUEST_URI} !^/maintenance\.html$
RewriteRule ^ - [R=503,L]
ErrorDocument 503 /maintenance.html
# MAINTENANCE END
```

### 0.4 Import the stars now (no downtime, takes many hours)
**Done on 28–29 October 2026** (finished at 03:22): 99,625,913 stars, 28.8 GB. The 35,932 rows fewer than the old tables are exact duplicates within those tables (checked with GROUP BY on the import key). `~/dsl-next` is removed. The `decl_zone` index of `stars` is still pending and is added by the migrate in Phase 3.

The live site runs older code that doesn't know the `stars` table, so it can be filled next to the live site from a separate checkout of the new code. It needs the code of 0.2 on GitHub.

1. Get the new code next to the live site:
   ```bash
   cd ~ && git clone --branch Combination git@github.com:DeepskyLog/DeepskyLog.git dsl-next
   cd ~/dsl-next/deepskylog
   cp /mnt/bigdisk/var/www/test.deepskylog.org/deepskylog/.env .
   composer install --no-dev --optimize-autoloader
   ```
2. Create only the `stars` table. The migration is recorded, so the `migrate` of 3.4 skips it later. The `decl_zone` index is added by 3.4, after the import, which keeps the inserts faster:
   ```bash
   php artisan migrate --force --path=database/migrations/2026_05_28_100500_create_stars_table.php
   ```
3. Start the import in the background, preferably in the evening because it loads the database for hours:
   ```bash
   nohup php artisan db:seed --force --class=LegacyStarsSeeder > ~/stars-import.log 2>&1 &
   ```
   Follow it with `tail -f ~/stars-import.log`. It prints a line per table and per million stars. If it stops, run the same command again: complete tables are skipped, and the unfinished one is read again without making duplicates.
4. Check that it is complete. The old tables are about 100M rows; dev took about 2,500 rows a second, which would make roughly 10–12 hours. Every table must have the count of its old table (`stars8` misses 3 duplicate stars):
   ```sql
   SELECT source_table, COUNT(*) FROM deepskylogLaravel.stars GROUP BY source_table;
   ```
   Compare with `SELECT COUNT(*) FROM deepskylog.stars1600` and so on.
5. Remove `~/dsl-next` afterwards.

**Disk:** dev's 38M stars take 13.3 GB, so 100M will be about 35 GB on `/` (`/var/lib/mysql`), which has 81 GB free. Add `--ignore-table=deepskylogLaravel.stars` to the second `mysqldump` of `~/scripts/dumpDatabase`, or the nightly dump and copy to copernicus grow by tens of GB. The stars can be imported again from the old tables (after 3.9, from the `legacy_stars*` tables, which do stay in the dump).

Each old table is a magnitude band: `stars8` up to 8.9, `stars9` 9.0–9.9, …, `stars1500` 15.0–15.24, `stars1525` 15.25–15.49, … `stars1775` up to 17.99. The charts have no limit of their own: they draw every star down to the observer's limit (default 10, 12 and 15 for the overview, lookup and detail charts, 15 for object sets; observers may set up to 20, object sets up to 18). Stopping the import after `stars1500` is enough for the default limits only; a fainter limit then silently misses the stars beyond it, so import all tables.

### 0.5 The day before
Check that the nightly dump of 02:00 ran, and that there is room: `/mnt/bigdisk` has 1.5 TB free; `/` had 81 GB free on 28 October and 49 GB on 3 November, after the 29 GB of stars. The backups of 1.4 (about 4.5 GB) go to `~/databases` on `/`.

**State on 3 November:** the server still runs `c9ad490e`, the six old copies `04759486`; the old database still has 195,500 deep-sky observations (max id 203,264) and 2,898 comet observations; the nightly dumps ran; `deepskylog-pdf-worker` is still failed. Since the merge of 0.2 (`33073a79`), 48 more commits are on `observations` (16 migrations, all new tables and columns: the order of Phase 3 stays valid). The old site's commit `2d54700f` is not pushed yet.

---

## Phase 1: Freeze (start of the window)

```bash
ssh www.deepskylog.be
N=/mnt/bigdisk/var/www/test.deepskylog.org/deepskylog
O=/mnt/bigdisk/var/www/www.deepskylog
umask 002                                   # files stay writable for the apache group, as the cron does
```

1. Old sites in maintenance: add the block of 0.3 to `$O.be/.htaccess`, `$O.de/…`, `$O.fr/…`, `$O.nl/…` and `$O.org/.htaccess`, and copy `maintenance.html` into each. Check from another network (or your phone without wifi) that `https://www.deepskylog.be/` shows the page.
2. New site down, with a secret to look at it yourself:
   ```bash
   cd $N && php artisan down --secret="dsl-deploy-2026"   # then https://test.deepskylog.org/dsl-deploy-2026 lets your browser in
   sudo systemctl stop deepskylog-worker deepskylog-pdf-worker
   ```
   In maintenance mode the scheduler doesn't run its tasks.
3. Wait a minute, then check that nothing writes anymore: no PHP processes on the old site, and no new rows in `SELECT MAX(id) FROM deepskylog.observations`.
4. Back up both databases (the old one is about 2.7 GB, the new one about 3 GB):
   ```bash
   mkdir -p ~/databases/before-switch
   mysqldump -u deepskylog -p --single-transaction deepskylog        > ~/databases/before-switch/deepskylog.sql
   mysqldump -u deepskylog -p --single-transaction deepskylogLaravel > ~/databases/before-switch/deepskylogLaravel.sql
   ```
5. Note the current commits for a rollback: `git -C $N/.. rev-parse HEAD` (c9ad490e) and `git -C $O.be rev-parse HEAD` (04759486).

## Phase 2: New code on test.deepskylog.org

```bash
cd $N/..
git checkout -- deepskylog/config/livewire-powergrid.php deepskylog/package-lock.json   # the server's PowerGrid hotfix; the new code replaces it
git pull                                     # branch Combination
cd $N
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:clear && php artisan view:clear && php artisan route:clear
```

Then edit `$N/.env`:
- `LEGACY_SITE_URL=https://www.deepskylog.be/,https://www.deepskylog.de/,https://www.deepskylog.fr/,https://www.deepskylog.nl/,https://www.deepskylog.org/`
- Run `php artisan webpush:vapid` **once** and add the `VAPID_PUBLIC_KEY=` and `VAPID_PRIVATE_KEY=` lines it prints.
- `DB_DATABASE_OLD=deepskylog` is already there; the seeders need it.

Then `php artisan config:clear`.

## Phase 3: The database, in this order

Production asks for confirmation, so every `migrate` and `db:seed` needs `--force`.

**3.1 Only the tables the import needs** (`--path` runs just these, out of order; `2026_04_29_000001` adds `drawing_filename`, which the seeder fills):
```bash
php artisan migrate --force \
  --path=database/migrations/2026_04_27_000100_create_observations_table.php \
  --path=database/migrations/2026_04_27_000101_create_observation_detail_tables.php \
  --path=database/migrations/2026_04_29_000001_add_drawing_filename_to_observations.php
```

**3.2 Import the observations** (a few minutes; it empties and refills the three tables, so it can be repeated):
```bash
php artisan db:seed --force --class=LegacyObservationsSeeder
```
Check: `SELECT legacy_source, COUNT(*) FROM deepskylogLaravel.observations GROUP BY 1` must give deepsky 195,500 and comet 2,898, the counts of `deepskylog.observations` and `deepskylog.cometobservations`.

**3.3 Translate the session links to the new ids (before the rest of the migrations!):**
```bash
php artisan db:seed --force --class=BackfillSessionObservationIdsSeeder
```
It prints how many it converted. Nearly all 34,290 rows should be converted, and comet observations are added to their sessions.

Then translate the sketches of the week and month to the new ids:
```bash
php artisan db:seed --force --class=BackfillSketchObservationIdsSeeder
```
Nearly every old id is also the new id of another observation, so the seeder gives each sketch the observation that is a drawing of the sketch's `user_id` (the old deep-sky id, minus the old comet id, or the id as it is). All rows should be "updated" (about 250 + 20); "not resolved" lists the rows to look at by hand. Running it again changes nothing. (The migration `2026_04_28_122058_fix_sketch_observation_id_mapping` used to do this in 3.4 and linked about one in eight sketches to another observer's drawing; it is now empty.)

**3.4 All other migrations:**
```bash
php artisan migrate --force
```
This also translates the likes and comments, normalises the observation targets, adds the indexes and generated columns, creates the `stars` table, the views for the old site (`2026_10_28_000000`, `…000010`) and `legacy_observation_changes`, deletes the stored contrast reserves, and runs level-up 3. Check with `php artisan migrate:status | grep -c Pending`, which must say 0.

**3.5 Check the sketches of the week and month** (translated in 3.3). Every sketch must point at a drawing of its own observer. This must give 0 rows:
```sql
SELECT s.id, s.observation_id, u.username, o.observer_username FROM deepskylogLaravel.sketch_of_the_week s
LEFT JOIN deepskylogLaravel.observations o ON o.id = s.observation_id
LEFT JOIN deepskylogLaravel.users u ON u.id = s.user_id
WHERE o.id IS NULL OR o.has_drawing = 0 OR u.username <> o.observer_username;
```
Run the same query for `sketch_of_the_month`.

**3.6 Data from the old database** (it still exists; nothing has been moved yet):
```bash
php artisan db:seed --force --class=addAchievementsSeeder
php artisan db:seed --force --class=LegacyObjectContoursSeeder
php artisan db:seed --force --class=LegacyReportLayoutsSeeder
php artisan db:seed --force --class=LegacyObserverSettingsSeeder
```
The stars were imported in 0.4. If the import is not complete yet, run `php artisan db:seed --force --class=LegacyStarsSeeder` now and wait for it to finish: 3.9 moves its source tables away.

**3.7 Repairs and completions.** Each one is a dry run first. If you reviewed the output in the rehearsal, go straight to `--apply`:
```bash
php artisan objects:fix-html-entities --apply
php artisan sessions:fix-html-entities --apply
php artisan observing-lists:normalize-items --apply
php artisan observing-lists:remove-spurious-items --apply
php artisan objects:add-struve-sigma-names --apply
php artisan observations:repair-sky-values --apply
php artisan messages:repair-usernames --apply
php artisan messages:delete-unaddressed --apply
php artisan objects:import-hickson-members --apply
php artisan objects:merge-types --apply
php artisan objects:repair-nebula-positions --apply
php artisan objects:estimate-nebula-magnitudes --apply
php artisan objects:fill-double-star-magnitudes --apply
php artisan objects:repair-measurements --apply
php artisan objects:repair-identities --apply
php artisan lunar-features:fill-from-gazetteer --apply
php artisan users:fill-country --apply
php artisan astroforum:objects-of-the-month --apply
```

Tom Polakis's drawings (pbase, with his permission; downloads about 190 drawings, about 3 minutes). Look at the dry run first; `--create` would also add the 174 drawings of nights his log has no observation for:
```bash
php artisan observations:import-polakis-drawings -v
php artisan observations:import-polakis-drawings --apply
```

**3.8 Derived data:**
```bash
php artisan observations:rebuild-object-stats
php artisan update:achievements
php artisan astronomy:updateOrbitalElements && php artisan astronomy:updateCometPhotometry
php artisan observations:check-suspect --all          # about 40 s
php artisan observers:fit-detection-thresholds -v     # a few seconds
php artisan objects:rebuild-also-seen                 # about 30 s, up to 1 GB
php artisan weather:fill-nights                       # dry run only: the scheduler fetches 250 nights an hour
rm -f storage/logs/horizons_wrapper_*.json
mkdir -p storage/app/atlas && chmod 2775 storage/app/atlas
```

**3.9 Move the old site's own tables into the new database** (an instant rename; after this the old database is no longer used):
```bash
mysql -u deepskylog -p < /mnt/bigdisk/var/www/www.deepskylog.org/sql/move_tables_to_new_database.sql
```
This works after the old site's `git pull` (Phase 4); or copy the file from your machine with scp first. The database names in it are `deepskylog` and `deepskylogLaravel`, the same as on the server.

## Phase 4: Old site code (all five copies)

For each `tld` in `be de fr nl org` (and `se`, to keep it the same):
```bash
cd /mnt/bigdisk/var/www/www.deepskylog.$tld
git status --short | grep -v cometdrawings      # only .htaccess (and robots.txt in .org) may be changed
git pull                                        # branch master
```
Then edit `lib/setup/databaseInfo.php` in each copy:
1. Add `$baseURLNew,$newStorageDir` to the `global` line, as in `databaseInfo.php.dist`.
2. Set:
   ```php
   $baseURLNew    = "https://test.deepskylog.org/";
   $newStorageDir = "/mnt/bigdisk/var/www/test.deepskylog.org/deepskylog/storage/app/public/";
   ```

`$dbnameNew = "deepskylogLaravel"` is already set. The old database settings may stay; they are no longer used.

Check that the old site can write the drawings (both pools run as `apache`, and the directory is `apache:apache 2770`): `ls -ld $N/storage/app/public`.

## Phase 5: Start and test

1. New site:
   ```bash
   cd $N
   php artisan up
   sudo systemctl daemon-reload
   sudo systemctl reset-failed deepskylog-pdf-worker
   sudo systemctl start deepskylog-worker deepskylog-pdf-worker
   php artisan queue:restart && php artisan responsecache:clear
   php artisan schedule:list | grep apply-legacy-changes      # every minute
   systemctl status deepskylog-worker deepskylog-pdf-worker   # both "active (running)"
   ```
   If the PDF worker doesn't start, see ToDo.md lines 51–59.
2. Test the new site (through the secret URL or directly, now that it is up):
   - the home page
   - `/observations`, an old observation with a drawing, a comet observation
   - a session with its observations
   - an observation with likes
   - an observer page with its badges
   - an object page (seen/last seen)
   - adding and deleting an observation
   - the push notification test on the profile page
   - an atlas page and an object set PDF, which must finish
3. Test the old site with your own IP, before removing the maintenance block:
   - log in (an old password still works)
   - an observation page, the M search, the statistics, the observer ranking, a comet observation
   - add an observation with a drawing, change it (clear the lens), and delete it. Within a minute `SELECT COUNT(*) FROM deepskylogLaravel.legacy_observation_changes` must be back at 0, and the observation must show up on and disappear from test.deepskylog.org.
   - the account page (save it), the badges
   - an atlas page, the report layouts (save and delete one), a saved search
   - "Register" must go to test.deepskylog.org and come back to the domain you started from
4. Remove the maintenance block from the five `.htaccess` files. Everyone who was logged in on the old site has to log in again.
5. Look at the logs for the first hour: `$N/storage/logs/laravel-$(date +%F).log` and `/var/log/httpd/www.deepskylog.*-error_log`.

## Rollback (if something is badly wrong in Phase 3–5)

1. Old sites back in maintenance, then `php artisan down` and stop both workers.
2. Restore the databases:
   ```bash
   mysql -u deepskylog -p deepskylog < ~/databases/before-switch/deepskylog.sql
   mysql -u deepskylog -p deepskylogLaravel < ~/databases/before-switch/deepskylogLaravel.sql
   ```
   First drop the tables the new code created, or recreate the database if you have the right. Restoring both brings back the `stars*` tables moved in 3.9.
3. Restore the code: `git checkout 04759486` in the five old copies (the `databaseInfo.php` additions do no harm), and `git checkout c9ad490e` plus `composer install`, `npm ci && npm run build` and `config:clear` in the new site.
4. Bring everything back up.

## Afterwards
- The nightly `dumpDatabase` keeps dumping `deepskylog`. After a few weeks without problems, drop the old database and remove that line.
- `observing_list_items_bak_20260923` in `deepskylogLaravel` can probably go.
- Tick off ToDo.md's "On real server" list. All of it is in this plan except the push-notification and PowerGrid tests, which are covered by Phase 5.
