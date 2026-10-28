# Developer Notes — DeepskyLog (concise)

A short, always-available reference for common locations, tools, and commands.

- **Laravel log:** storage/logs/laravel-YYYY-MM-DD.log (example: `laravel-2025-12-11.log`) — daily log files in `storage/logs` (relative to `deepskylog/`).
- **Livewire:** v4 — server-driven components live in `app/Livewire`.
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
  - `php artisan objects:fix-html-entities` (dry run; `--apply` to write) — decodes HTML entities stored in catalogue names, e.g. `T&ouml;pler 2`. Renaming an object rewrites every table that refers to it by name, so follow it with `search:reindex`.
  - `php artisan test`
- **Frontend:** Tailwind is used for styling. Prefer Livewire server-driven UI over custom JavaScript — use JavaScript only when strictly necessary. Node tooling (if present) and scripts are in `package.json`: `npm run dev`, `npm run build`, `npx prettier --write resources/`.
- **Formatting / linters:** `laravel/pint`, `friendsofphp/php-cs-fixer`, Prettier (Blade plugin).
- **Atlas front matter:** a generated atlas opens with a title page (`StarChartService::buildTitlePage`) and then the index maps (`AtlasIndexPageService`) — polar maps of each hemisphere carrying the page grid, its numbers and the constellations, so a reader can find a chart from the sky. Two maps per hemisphere, more when the page grid is fine enough that the numbers would stop being readable.
- **Atlas back matter:** it closes with the object index (`AtlasObjectIndexService`) — every object the atlas plots, against its page, under the name the charts print beside it. That is `objects.name` and never an `objectnames` alias, because a chart label only ever carries the former; indexing the aliases sent readers looking for names that are on no page. Same magnitude test as `StarChartService::loadObjects`, so the index never names a page an object is not on. Sorted with `SORT_NATURAL`, which is what puts ASCC 9 before ASCC 100 and ASCC 130 before Abell 85, and set in Courier so the dot leaders can be padded by character count instead of measured — every line the same width, which right-aligns the page numbers without padding them with zeroes. The column count comes from the longest line in the index (three across a portrait page, four across a landscape one), so an unusually long name costs a column rather than getting truncated.
- **Constellation data:** the boundaries are in `constellation_boundaries` (from `database/conlines.csv`); the figures — the stick figures joining the bright stars, drawn on every chart and index map — are in `constellation_lines` (from `database/constellation_figures.csv`), and where to write a constellation's name is in `constellations.ra_degrees` / `decl_degrees` (from `database/constellation_centers.csv`). The figure and label coordinates come from [d3-celestial](https://github.com/ofrohn/d3-celestial) (BSD 3-Clause, Olaf Frohn); the CSV headers record that.
- **Useful folders:**
  - `public/` for built assets and public files
  - `storage/` for uploads, logs, and TNTSearch indexes (`storage/tnt`)
  - `database/` for seeds/migrations and CSV fixtures
- **Quick dev setup (from `deepskylog/`):**
  - `composer install`
  - `npm install`
  - `cp .env.example .env` + set DB
  - `php artisan key:generate`
