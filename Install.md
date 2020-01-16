# INSTALLATION

## Warning


+ After making an empty database and doing a migration to create the tables:
  + Make a link from observers to /observer_pics
  
```bash
ln -s /srv/www/www.deepskylog.org/common/observer_pics/ .
```

+ Set the correct database entries in .env: DB_DATABASE_OLD, DB_USERNAME_OLD, and
DB_PASSWORD_OLD, LIGHTPOLLUTION_KEY, GOOGLEMAPS_KEY
+ Run the seeders:

```bash
php73 /usr/local/bin/composer install
php73 /usr/local/bin/composer dump-autoload
php73 artisan db:seed
```

+ Remove the link to /observer_pics
+ Don't allow the use of google api from everywhere (also test if the timezone is set correctly then): <https://console.developers.google.com/apis/credentials/key/211?project=deepskylog-1528998866034>
+ Make sure to put `post_max_size = 10M` and `upload_max_filesize = 10M` in /etc/opt/remi/php73/php.ini
