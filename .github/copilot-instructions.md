## DeepskyLog — Copilot instructions (concise)

This file contains focused, actionable information to help an AI coding agent be productive in this Laravel-based repository.

### Big picture

- Laravel 12 application (PHP 8.3). Main app lives in `deepskylog/`.
- Frontend assets built with Vite + Tailwind; Livewire (v4) and WireUI are used for interactive UI components.
- Key domain model: observation sessions, instruments, eyepieces, sketches. Core models live in `app/Models`.
- Routing is classical server-side: see `deepskylog/routes/web.php` for the route surface (examples: `/session/{user}/{session}`, `/object/{slug}`).

### Major components & where to look

- Controllers: `deepskylog/app/Http/Controllers` — behaviour and HTTP endpoints.
- Livewire components: `deepskylog/app/Livewire` — stateful UI code (tables, create forms). Example: `CreateSession.php`, `InstrumentTable.php`.
- Views and Blade templates: `deepskylog/resources/views` (blade + wireui components). TinyMCE usage example in `resources/views/profile/update-profile-information-form.php`.
- Routes: `deepskylog/routes/web.php` — useful to map URLs to controllers/Livewire.
- Config and services: `deepskylog/config/*.php` (mail, geocoder, responsecache, etc.).
- Database seeds/fact data: `deepskylog/database/seeders`, and CSVs in `deepskylog/database/` (e.g. `conlines.csv`).
- log files are in deepskylog/storage/logs/laravel-YYYY-MM-DD.log

### Developer workflows & commands (concrete)

Run from the `deepskylog/` directory.

- Install/update PHP deps: `composer update` (requires PHP 8.3)
- Install/update node deps and dev server: `npm update` and `npm run dev` (or `npm run build` for production). Vite is used (`deepskylog/package.json`).
- Common server tasks from `Tips.md`:
  - `php artisan storage:link`
  - `php artisan view:clear`
  - `php artisan migrate:fresh` (used on dev/test only)
  - `php artisan db:seed`
  - `php artisan search:reindex` (rebuild site search/TNTSearch)
- Tests: `php artisan test` (Pest/phpunit). The repo includes `phpunit.xml` and Pest config.

### Formatting & linters

- PHP: `laravel/pint` and `friendsofphp/php-cs-fixer` are used (see `composer.json` require-dev).
- Frontend/templates: Prettier with blade plugin. Use `npx prettier --write resources/` (script in `deepskylog/package.json` is `format`).

### Project-specific conventions and patterns

- Authorization frequently uses policy/can guards with the `add_sketch` ability: e.g. `->can('add_sketch', User::class)` in routes.
- Some controllers/middleware use `doNotCacheResponse` to avoid response caching on pages that must always be fresh (search/individual pages).
- Team-based operations rely on Jetstream teams (see `routes/web.php` override for `current-team.update`). Use `Auth::user()` and `->isAdministrator()` helpers in codebase.
- Livewire-powergrid is used for tables — check `app/Livewire/*Table.php` for column definitions and filters.
- Translations are stored in `deepskylog/resources/lang/i18n` and updated via `php artisan localize <lang>` (see `Tips.md`).
- **Always preload data in controllers** before passing to views
- **Never query in Blade loops** - preload everything
- **Use caching** for expensive queries that don't change often
- **Profile pages regularly** with Laravel Debugbar or Telescope
- **Add database indexes** for frequently queried columns
- **Consider eager loading** with `->with()` for Eloquent relationships

### Integrations & external services

- TNTSearch for local search (`teamtnt/tntsearch`). Use `php artisan search:reindex`.
- Geocoding: `geocoder-php/nominatim-provider` (see config `config/geocoder.php`).
- Mail: Mailgun support is present (`symfony/mailgun-mailer` in composer). Check `config/mail.php`.
- Frontend: TinyMCE, Leaflet, and related libs in `deepskylog/package.json`.

### Quick examples for an AI agent

- To find where a route is handled: open `deepskylog/routes/web.php` and follow the controller or Livewire reference (`App\Http\Controllers\...` or `app/Livewire/...`).
- To add a new Livewire table, copy an existing `*Table.php` in `app/Livewire` (powergrid pattern) and the corresponding Blade snippet.
- When changing translations, update `resources/lang/i18n/*` and run `php artisan localize <lang>` to regenerate PO files.

### Files to reference while coding/reviewing

- `deepskylog/routes/web.php` — site routing map
- `deepskylog/app/Livewire/` — Livewire components and tables
- `deepskylog/app/Http/Controllers/` — main controller logic
- `deepskylog/resources/views/` — blade templates and WireUI usage
- `deepskylog/package.json` and `deepskylog/composer.json` — build and dependency manifest

