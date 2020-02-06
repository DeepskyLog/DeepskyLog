# ToDos for DeepskyLog

## Write tests!!!

## GitHub

+ Bug triaging
+ Test defined GitHub actions

## Interesting websites

+ [ ] Timezones with javascript: <https://moment.github.io/luxon/>
+ [ ] Timezones with php: Carbon: <https://carbon.nesbot.com/docs/#api-timezone>

## CODE QUALITY

+ [ ] CODE CLIMATE: <https://codeclimate.com/github/WimDeMeester/DeepskyLog.laravel/issues?category=duplication&engine_name%5B%5D=structure&engine_name%5B%5D=duplication>

## Authentication

+ [ ] Issue-421: Check possibility to log in using openID

## Targets

+ [ ] In the catalogs page, search for comets, planets, ...
+ [ ] Details for one object
  + [x] Add contrast information
  + [x] Add rising, setting, transit
  + [x] Add ephemerides
  + [ ] Add objects near by
    + [ ] DataTable -> add information on highest from , around, ...
  + [ ] Add administrator functions to adapt objects
+ [ ] Quickpick
+ [ ] Difficult queries: <https://laravel-news.com/laravel-query-builder>
+ [ ] Check permissions on the page
+ [ ] Adding objects
  + [ ] Comets
    + [ ] Let normal users add new comets
    + [ ] Download comets automatically from the internet
  + [ ] Asteroids
  + [ ] Satellites
  + [ ] Deepsky
+ [ ] Menu items:
  + [ ] Quick pick object
  + [ ] Search for objects
  + [ ] Add object
  + [ ] ...
+ [ ] Search:
  + [ ] Make it possible to search for objects using extra parameters (like seen by observer, ...)
  + [ ] Objects seen with my 22cm, but not with my 45cm telescope?
  + [ ] Objects seen from a bad location, but not yet seen from a good location?
+ [ ] Atlas / interactive atlases
  + [ ] Poll to see if we still need these options
  + [ ] Remove the old objects migrations for objectoutlines
+ [ ] OpenAstronomyLog for importing and exporting targets?

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
+ [ ] In target/show.blade.php
  + [ ] Make it possible to change / view the description of the target
  + [ ] Add the private / public lists the object belongs to
+ [ ] Likes / dislikes / ... for public lists: <https://github.com/cybercog/laravel-love>
+ [ ] Sort on highest number of likes, add extra likes when someone subscribes to the observation list. Add dislikes if someone describes from the observation list.
+ [ ] Share observation list on twitter / facebook
+ [ ] Search for object that don't belong to a certain type.

## OBSERVATIONS

+ [ ] <https://github.com/Te7a-Houdini/laravel-trix> for editor
  + [ ] Also use for observation lists
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
+ [ ] Targets
  + [ ] sub/detail.blade.php: Add information if the object was already seen
  + [ ] Make the buttons work to add a new observation
+ [ ] In graph of users, also show number of observations of planets, sun, moon, double stars, ...
+ [ ] Likes? Comments? <https://github.com/cybercog/laravel-love>
+ [ ] Sort on highest number of likes

## SEEDERS

+ [ ] Observations
  + [ ] Add number of observations to the lenses and to the other instrument related things.
