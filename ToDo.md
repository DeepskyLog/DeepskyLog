# ToDos for DeepskyLog.laravel

## TESTS

## Moon / sun

+ [ ] Timezones with javascript: https://moment.github.io/luxon/
+ [ ] Timezones with php: Carbon: https://carbon.nesbot.com/docs/#api-timezone

## CODE QUALITY

+ [ ] CODE CLIMATE: https://codeclimate.com/github/WimDeMeester/DeepskyLog.laravel/issues?category=duplication&engine_name%5B%5D=structure&engine_name%5B%5D=duplication

## INSTRUMENTS

+ [ ] Add the default instrument selection: What to do with the datatables as a service and the checkbox??
+ [ ] Select the default instrument from subheader/instrument.blade.php
+ [ ] What happens if delete instrument (or deactivate) and that instrument is selected as standard instrument?

## LOCATIONS

+ [ ] Add number of locations to users/view.blade.php
+ [ ] Add the selection of locations to the user settings (users/settings.blade.php)
+ [ ] Add the standard location to the user details (users/view.blade.php)
+ [ ] Add the locations to subheader/location.blade.php

## OBJECTS

+ [ ] Difficult queries: https://laravel-news.com/laravel-query-builder
+ [ ] Add new types for the objects
+ [ ] Insert all old objects in the new database

## OBSERVATION LISTS

+ [ ] Add planets, sun, ...
+ [ ] Rethink the observation lists
+ [ ] Only receive messages if you opt in for this
+ [ ] Likes / dislikes / ... : https://github.com/cybercog/laravel-love
+ [ ] Sort on highest number of likes, add extra likes when someone subscribes to the observation list. Add dislikes if someone describes from the observation list.

## OBSERVATIONS

+ [ ] https://github.com/VanOns/laraberg for editor
+ [ ] https://jamesmills.co.uk/2019/02/28/laravel-timezone/ for timezones / date
+ [ ] Lenses
  + [ ] Only show delete button if there are no observations
    + [ ] Also update the tests for lens
  + [ ] Recalculate number of observations for each lens of the observer whenever (needed for datatables?):
    + [ ] Add observation
    + [ ] Update observation
    + [ ] Delete observation
+ [ ] Users
  + [ ] Only show delete button if there are no observations
  + [ ] Show number of observations, instruments and lists in users/view.blade.php
  + [ ] Create the charts in users/view.blade.php, check if there is a better laravel integration with other charting libraries (in stead of HighCharts).
  + [ ] Add the deepskylog star page in users/view.blade.php
  + [ ] Likes? Comments? https://github.com/cybercog/laravel-love
  + [ ] Sort on highest number of likes


## SEEDERS

+ [ ] Observations
  + [ ] Add number of observations to the lenses and to the other instrument related things.

## INSTALLATION

+ After making an empty database and doing a migration to create the tables:
  + Make a link from observers to /observer_pics
  + Set the correct database entries in .env: DB_DATABASE_OLD, DB_USERNAME_OLD, and
DB_PASSWORD_OLD
  + Run the seeders: php artisan db:seed
  + Remove the link to /observer_pics
