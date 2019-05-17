# ToDos for DeepskyLog.laravel

## TESTS

## AUTHENTICATION

+ [ ] Write script to convert old observers table of DeepskyLog to laravel
+ [ ] Write script to convert old lenses table of DeepskyLog to laravel
+ [ ] Clean up source code: php artisan insights

## MESSAGING SYSTEM

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
+ [ ] Add the insturments to subheader/instrument.blade.php

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

## SEEDER
