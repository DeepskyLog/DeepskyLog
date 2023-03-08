# Tips and tricks

## Installation on the server

```bash
cd /var/www/test.deepskylog.org/DeepskyLog/deepskylog
git pull
module load php82
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

## Unit testing

```bash
php artisan test
```

## GUI components

- For all components, we use wireui.  For the tables, we use livewire-powergrid

## User authentication

### Teams

+ There are two teams in DeepskyLog.  Extra teams can be added later (for example moderators, social media team, ...):

|Team             |Description                                    |
|-----------------|-----------------------------------------------|
|Observers        | All observers and guest are part of this team |
|Administrators   | Members of this team can do everything        |
|Database Experts | Members of this team can do everything with the databases |

+ Check if the user belongs to the correct team:

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
