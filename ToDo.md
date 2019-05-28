# ToDos for DeepskyLog.laravel

## TESTS

## MESSAGING SYSTEM

+ [ ] Select good library for the messaging system.
+ [ ] Convert old messages to the new messaging system

## FILTERS

+ [ ] Add number of filters to users/view.blade.php

## EYEPIECES

+ [ ] Add number of eyepieces to users/view.blade.php

## LOCATIONS

+ [ ] Add number of locations to users/view.blade.php
+ [ ] Add the selection of locations to the user settings (users/settings.blade.php)
+ [ ] Add the standard location to the user details (users/view.blade.php)
+ [ ] Add the locations to subheader/location.blade.php

## INSTRUMENTS

+ [ ] Add number of instruments to users/view.blade.php
+ [ ] Add the selection of instruments to the user settings (settings.blade.php)
+ [ ] Add the standard instrument to the user details (users/view.blade.php)
+ [ ] Add the instruments to subheader/instrument.blade.php

## OBJECTS

+ [ ] Add new types for the objects
+ [ ] Insert all old objects in the new database

## OBSERVATIONS

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
  + [ ] Create the charts in users/view.blade.php
  + [ ] Add the deepskylog star page in users/view.blade.php

## SEEDERS

+ [ ] Observations
  + [ ] Add number of observations to the lenses.

## INSTALLATION

+ After making an empty database and doing a migration to create the tables:
  + Make a link from observers to /observer_pics
  + Set the correct database entries in .env: DB_DATABASE_OLD, DB_USERNAME_OLD, and
DB_PASSWORD_OLD
  + Run the seeders: php artisan db:seed
  + Remove the link to /observer_pics
