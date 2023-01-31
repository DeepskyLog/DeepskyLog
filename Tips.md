# Tips and tricks

## Installation on the server

```bash
module load php8
composer update
npm update
npm run build
php artisan migrate:fresh
php artisan db:seed
```

## Unit testing

```bash
php artisan test
```

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
