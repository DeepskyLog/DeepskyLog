# Install on CentOS 8

## Prepare the database

php artisan migrate

### Fill the tables with the orbital elements for the comets and the asteroids

```bash
php artisan astronomy:updateOrbitalElements
```

### Update the table for delta t to the latest version

```bash
php artisan astronomy:updateDeltat
```

### laravel-love

The like and dislike actions should be defined:

```bash
php artisan love:reaction-type-add --default
php artisan love:reaction-type-add --name=Subscribe --mass=5
php artisan love:reaction-type-add --name=Tag --mass=2
php artisan love:reaction-type-add --name=Tags --mass=5
php artisan love:reaction-type-add --name=Description --mass=5
```

### Seed the database

```bash
php artisan db:seed
```

## Set up the crontab to download the orbital elements automatically

Enter the following line in your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
