# Install on CentOS 8

## Prepare the database

### laravel-love

The like and dislike actions should be defined:

```bash
php artisan love:reaction-type-add --default
php artisan love:reaction-type-add --name=Subscribe --mass=5
php artisan love:reaction-type-add --name=Tag --mass=2
php artisan love:reaction-type-add --name=Tags --mass=5
php artisan love:reaction-type-add --name=Description --mass=5
```

### Update database

Update the database if the users are already seeded:

```bash
php artisan love:register-reacters --model="App\Models\User"
php artisan love:register-reactants --model="App\Models\ObservationList"
```
