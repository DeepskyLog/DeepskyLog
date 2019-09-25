# ToDos for DeepskyLog.laravel

## TESTS

## Moon / sun

+ [ ] Timezones with javascript: <https://moment.github.io/luxon/>
+ [ ] Timezones with php: Carbon: <https://carbon.nesbot.com/docs/#api-timezone>

## CODE QUALITY

+ [ ] CODE CLIMATE: <https://codeclimate.com/github/WimDeMeester/DeepskyLog.laravel/issues?category=duplication&engine_name%5B%5D=structure&engine_name%5B%5D=duplication>

## OBJECTS - Targets

+ [ ] Database
  + [x] Create target_types
  + [x] Create constellations
    + [ ] Tests constellations with tinker
  + [ ] Create atlases
  + [ ] Translations
  + [ ] Create targets
    + [ ] Add moon
      + [ ] Seas
      + [ ] Mountains
      + [ ] Valleys -> Also add to the target_types
      + [x] Craters
    + [x] Add planets
    + [x] Add sun
      + [ ] Test to get the observation type from the object name: 
  `App\Target::where('name', 'Venus')->first()->type()->first()->observation_type()->first()->name`
  `App\TargetType::where('id', 'PLANET')->first()->targets()->get()`
    + [ ] Add Comets
    + [ ] Add deepsky-objects
    + [ ] Remove the old objects migrations
    + [ ] Think about alternative names
    + [ ] Think how to search on localized names for sun and planets.
+ [ ] Difficult queries: <https://laravel-news.com/laravel-query-builder>
+ [ ] Add new types for the objects
  + [ ] Check OAL how to import and which information to store for the planets / sun / moon
  + [ ] Add Moon (list of craters / seas)
  + [ ] Add Sun
    + [ ] Add eclipses?
+ [ ] Comets
  + [ ] Let normal users add new comets
  + [ ] Download comets automatically from the internet
+ [ ] Insert all old objects in the new database
+ [ ] Menu items:
  + [ ] Quick pick object
  + [ ] Search for objects
  + [ ] Add object
  + [ ] ...
+ [ ] Search:
  + [ ] Make it possible to search for objects using extra parameters (like seen by observer, ...)
  + [ ] Objects seen with my 22cm, but not with my 45cm telescope?
  + [ ] Objects seen from a bad location, but not yet seen from a good location?

## HOME PAGE

+ [ ] Add icon to search objects
+ [ ] Add icon to create observing list
+ [ ] Add icon to add new observation
+ [ ] Add icon to search observations
+ [ ] Add icon to show latest observations
+ [ ] Add icon to download the atlases

## OBSERVATION LISTS

+ [ ] Add planets, sun, ...
+ [ ] Rethink the observation lists
+ [ ] Only receive messages if you opt in for this
+ [ ] Likes / dislikes / ... for public lists: <https://github.com/cybercog/laravel-love>
+ [ ] Sort on highest number of likes, add extra likes when someone subscribes to the observation list. Add dislikes if someone describes from the observation list.
+ [ ] Share observation list on twitter / facebook

## OBSERVATIONS

+ [ ] <https://github.com/VanOns/laraberg> for editor
+ [ ] <https://jamesmills.co.uk/2019/02/28/laravel-timezone/> for timezones / date
+ [ ] Share observation using twitter / facebook / instagram
+ [ ] Lenses / Filters / Eyepieces / Instruments / Locations
  + [ ] Only show delete button if there are no observations
    + [ ] Also update the tests for lens
  + [ ] Recalculate number of observations for each lens / filter / ... of the observer whenever (needed for datatables?):
    + [ ] Add observation
    + [ ] Update observation
    + [ ] Delete observation
+ [ ] Show total observations, comet observations, double star observations, planet observations, moon observations, ...
+ [ ] Add scale for transparancy
  + [ ] Add to openastronomylog?
+ [ ] Users
  + [ ] Only show delete button if there are no observations
  + [ ] Show number of observations and lists in users/view.blade.php
  + [ ] Create the charts in users/view.blade.php, check if there is a better laravel integration with other charting libraries (in stead of HighCharts).
  + [ ] Add the deepskylog star page in users/view.blade.php
+ [ ] Instruments page: instrument/show.blade.php:
  + [ ] Add first light date
  + [ ] Add the last date of the last use
  + [ ] Add the used eyepieces, filters and lenses
  + [ ] Add google maps with the locations where the telescope was used.
+ [ ] In graph of users, also show number of observations of planets, sun, moon, double stars, ...
+ [ ] Likes? Comments? <https://github.com/cybercog/laravel-love>
+ [ ] Sort on highest number of likes

## SEEDERS

+ [ ] Observations
  + [ ] Add number of observations to the lenses and to the other instrument related things.

## INSTALLATION

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

Require:
        "coderello/laraflash": "^2.0",
        "zerospam/laravel-gettext": "^7.1"
