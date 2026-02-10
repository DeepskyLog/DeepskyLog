# Developer Notes — DeepskyLog (concise)

A short, always-available reference for common locations, tools, and commands.

- **Laravel log:** storage/logs/laravel-YYYY-MM-DD.log (example: `laravel-2025-12-11.log`) — daily log files in `storage/logs` (relative to `deepskylog/`).
- **Livewire:** v3 — server-driven components live in `app/Livewire`.
- **PowerGrid:** used for tables (look for `*Table.php` in `app/Livewire`). Config: `config/livewire-powergrid.php`.
- **Routes:** `routes/web.php` maps main site endpoints.
- **Views / Blade:** `resources/views` (Blade + WireUI components).
- **Main app code:** `app/` (Models, Controllers, Livewire, Policies, Helpers).
- **Common artisan commands:**
  - `php artisan storage:link`
  - `php artisan view:clear`
  - `php artisan migrate:fresh` (dev only)
  - `php artisan db:seed`
  - `php artisan search:reindex`
  - `php artisan test`
- **Frontend:** Tailwind is used for styling. Prefer Livewire server-driven UI over custom JavaScript — use JavaScript only when strictly necessary. Node tooling (if present) and scripts are in `package.json`: `npm run dev`, `npm run build`, `npx prettier --write resources/`.
- **Formatting / linters:** `laravel/pint`, `friendsofphp/php-cs-fixer`, Prettier (Blade plugin).
- **Useful folders:**
  - `public/` for built assets and public files
  - `storage/` for uploads, logs, and TNTSearch indexes (`storage/tnt`)
  - `database/` for seeds/migrations and CSV fixtures
- **Quick dev setup (from `deepskylog/`):**
  - `composer install`
  - `npm install`
  - `cp .env.example .env` + set DB
  - `php artisan key:generate`
