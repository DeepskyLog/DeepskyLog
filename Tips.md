# Tips and tricks

## Installation

```bash
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
return $user->isAdministrator();
return $user->isDatabaseExpert();
return $user->isObserver();
```
