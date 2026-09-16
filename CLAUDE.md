# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository layout

This is a monorepo. The Laravel application lives entirely in `deepskylog/` — run all commands below from that directory. The repo root also contains `3d-print/` (unrelated OpenSCAD/3D-print assets for physical hardware) and top-level docs (`README.md`, `Tips.md`, `CONTRIBUTING.md`).

## Commands

All commands run from `deepskylog/`.

- Install PHP deps: `composer install` (PHP 8.3 required); update with `composer update`.
- Install/build frontend: `npm install`, then `npm run dev` (Vite dev server) or `npm run build` (production).
- Format frontend/Blade: `npm run format` (Prettier with the Blade plugin, writes `resources/`).
- Format PHP: `laravel/pint` or `friendsofphp/php-cs-fixer` (config in `.php-cs-fixer.dist.php`, PSR-12 + short array syntax + alphabetized imports).
- Run tests: `php artisan test` (Pest, with `pestphp/pest-plugin-laravel`). Run a single test file with `php artisan test tests/Feature/SomeTest.php` or filter by name with `php artisan test --filter=testName`. Test env uses in-memory SQLite, sync queue, array cache/session (see `phpunit.xml`) — no external services needed.
- Rebuild search index after object data changes: `php artisan search:reindex` (TNTSearch).
- Regenerate translation files after adding new strings: `php artisan localize <lang>` (e.g. `nl`, `fr`, `de`, `es`, `sv`); language files live in `resources/lang/i18n`.
- Queue workers (background jobs like contrast-reserve/metrics computation and atlas PDF generation): `php artisan queue:work --tries=3 --sleep=3 --timeout=60`; restart after deploys with `php artisan queue:restart`. `QUEUE_CONNECTION=database` by default — inspect failures with `php artisan queue:failed` / `queue:retry`.
- Common dev housekeeping: `php artisan storage:link`, `php artisan view:clear`, `php artisan migrate:fresh` (dev/test only, destroys data), `php artisan db:seed`.

## Architecture

- Laravel 12 / PHP 8.3, server-rendered with classical routing (`routes/web.php`, ~880 lines — the authoritative map from URL to Controller or Livewire component).
- **Livewire v4** (`app/Livewire/`) drives almost all interactive UI — data tables (`*Table.php`, built on `power-components/livewire-powergrid`), create/edit forms, search. Reusable form objects live in `app/Livewire/Forms`. Prefer Livewire server-driven UI over hand-written JS; JS is only used where Livewire genuinely can't do the job (e.g. Leaflet maps, TinyMCE, Flatpickr).
- **WireUI** supplies UI components/modals used throughout Blade views (`resources/views`).
- **Auth/teams**: Laravel Jetstream + `joelbutcher/socialstream` (vendored as a local path package in `packages/socialstream`, not on Packagist — edit it in place if needed). Every user belongs to a team: `Observers` (default), `Administrators`, `Database Experts`. Check roles via `$user->isAdministrator()`, `->isDatabaseExpert()`, `->isObserver()`, `->hasAdministratorPrivileges()`, and gate routes with policies (`app/Policies`) using ability strings like `->can('add_sketch', User::class)`.
- **Domain model** (`app/Models`): observation-centric — `Observation`, `ObservationSession`, `DeepskyObject` (+ `CometObject`, `Asteroid`, `Planet`, `Moon`, `Star` for non-deep-sky targets), `Instrument`/`Eyepiece`/`Lens`/`Filter` (equipment, each with a `*Make`/`*Type` lookup model), `ObservingList` (+ items/comments/likes/subscriptions), `Location`, `Atlas`, `SketchOfTheMonth`/`Week`. Object naming/translation is split across `ObjectName`, `ObjectNames`, `ObjectNameTranslation`.
- **Astronomy calculations** (positions, magnitudes, ephemerides, coordinate transforms) come from the external `deepskylog/laravel-astronomy-library` package (in `vendor/`), not reimplemented in-app. `app/Services/EphemeridesBuilder.php` and the `ComputeEphemerides`/`ComputeContrastReserveFor*` jobs build on it.
- **Star charts & atlas PDFs**: `app/Services/StarChartService.php` (large — renders SVG star charts, polar grids, star/DSO labeling) is the core rendering engine, driven by `app/Http/Controllers/AtlasController.php` and `StarChartController`/`NearbyExportController`. Full-atlas PDFs are generated asynchronously: the controller queues `App\Jobs\GenerateAtlasPdf`, which re-invokes the controller's `generate()` action internally, captures the PDF response, and writes status/results to cache (key `atlas_pdf:{token}`) and `storage/app/atlas/{token}.pdf` for polling/download. PDF rendering uses `barryvdh/laravel-dompdf`; Blade templates for this live in `resources/views/pdf/`.
- **Search**: TNTSearch (`teamtnt/tntsearch`) indexes objects/observations; reindex via `php artisan search:reindex` whenever object data changes.
- **Caching**: `spatie/laravel-responsecache` caches full page responses; controllers/middleware that must always be fresh (e.g. search, per-user pages) opt out via `doNotCacheResponse`.
- **Background jobs** (`app/Jobs`): per-user/per-object metrics (contrast reserve, observer stats), session-invitation email, description translation, and atlas PDF generation. These are dispatched onto the `database` queue and require a running `queue:work` process to actually execute — commands that "queue" work will silently do nothing without a worker.
- **Ad-hoc/debug scripts**: `deepskylog/scripts/` holds standalone PHP scripts (run with `php scripts/xxx.php`, bootstrapping Laravel themselves) used for manual testing of chart/PDF rendering, cache table inspection, etc. — not part of the app runtime, safe to add to but not to treat as tested code paths.
- **Logs**: `storage/logs/laravel-YYYY-MM-DD.log` (daily rotation).
