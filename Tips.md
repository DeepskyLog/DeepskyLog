# Tips and tricks

- [Tips and tricks](#tips-and-tricks)
  - [Installation on the server](#installation-on-the-server)
  - [Unit testing](#unit-testing)
  - [GUI components](#gui-components)
    - [Rich text editing](#rich-text-editing)
  - [User authentication](#user-authentication)
    - [Teams](#teams)

## Installation on the server

```bash
cd /var/www/test.deepskylog.org/DeepskyLog/deepskylog
git pull
module load php83
php artisan storage:link
composer update
npm update
npm run build
php artisan view:clear
php artisan migrate:fresh
# To add all the profile images
ln -s /var/www/www.deepskylog.be/common/observer_pics .
php artisan db:seed
chmod 777 storage/app/public/profile-photos
```

## New objects

```bash
php artisan search:reindex
```

## Translation

When new strings were added, the translation files need to be updated. This can be done with the following command:

```bash
php artisan localize nl 
php artisan localize fr 
php artisan localize de 
php artisan localize es 
php artisan localize sv
```

Other languages can be added by changing the language code (nl) to the correct language code.

## Unit testing

```bash
php artisan test
```

## Queues (workers and management)

DeepskyLog uses Laravel queued jobs for background processing (e.g. computing per-user metrics like Contrast Reserve).
Below are common commands and an example `systemd` service to run queue workers on the server.

1) Configure your queue driver in `.env` (examples):

```bash
# Use the database queue driver (simple, works without Redis)
QUEUE_CONNECTION=database
```

2) If using the database driver, create the queue tables and migrate:

```bash
cd /var/www/DeepskyLog/deepskylog
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

3) Start a foreground worker (good for debugging):

```bash
cd /var/www/DeepskyLog/deepskylog
php artisan queue:work --tries=3 --sleep=3 --timeout=60
```

4) Restart workers after deploy or code change (graceful restart):

```bash
cd /var/www/DeepskyLog/deepskylog
php artisan queue:restart
```

5) Run a single job dispatching command (example: enqueue per-object CR computation):

```bash
# dispatch jobs (queued mode)
php artisan metrics:compute-cr USER_ID --instrument_id=INSTRUMENT_ID --location_id=LOCATION_ID --queued

# or run synchronously for a quick smoke test (small chunk):
php artisan metrics:compute-cr USER_ID --instrument_id=INSTRUMENT_ID --location_id=LOCATION_ID --chunk=10
```

6) Manage failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry {id}
php artisan queue:retry all
php artisan queue:forget {id}
php artisan queue:flush
```

7) Example `systemd` service for a persistent worker (create `/etc/systemd/system/deepskylog-worker.service`):

```ini
[Unit]
Description=DeepskyLog queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/DeepskyLog/deepskylog/artisan queue:work --sleep=3 --tries=3 --timeout=60
WorkingDirectory=/var/www/DeepskyLog/deepskylog
StandardOutput=syslog
StandardError=syslog

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable deepskylog-worker.service
sudo systemctl start deepskylog-worker.service
sudo systemctl status deepskylog-worker.service
```

8) Useful debugging tips

- Tail the Laravel log for job errors:

```bash
tail -f /var/www/DeepskyLog/deepskylog/storage/logs/laravel.log
```

- If jobs are not processed, check `QUEUE_CONNECTION` and ensure the worker is running under the same environment and user.
- For high volume, use Redis + Laravel Horizon (optional).

## GUI components

- For all components, we use wireui. For the tables, we use livewire-powergrid

### Rich text editing

- TinyMCE is used for the rich text editing forms. An example on how to use this in the code can be found in
  resources/view/profile/update-profile-information-form.php

## User authentication

### Teams

- There are two teams in DeepskyLog. Extra teams can be added later (for example moderators, social media team, ...):

| Team             | Description                                               |
|------------------|-----------------------------------------------------------|
| Observers        | All observers and guest are part of this team             |
| Administrators   | Members of this team can do everything                    |
| Database Experts | Members of this team can do everything with the databases |

- Check if the user belongs to the correct team:

```php
// If the current user's active group is the Administrators group
return $user->isAdministrator();
// If the current user's active group is the Database Expert group
return $user->isDatabaseExpert();
// If the current user's active group is the Observers group
return $user->isObserver();
// If the current user belongs to the Administrators group (does not need to be active)
return $user->hasAdministratorPrivileges();
```

- Get the currently logged in user:

```php
Auth::user()
```
