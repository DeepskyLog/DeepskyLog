# ToDos for DeepskyLog.laravel

## TESTS

+ [ ] Why do the test don't use the UserFactory and fail because country is not set?
+ [ ] Tests for a lens with a too short name, negative factor, ...
+ [ ] Test lens when not logged in
+ [ ] Test when the user is logged in, but not verified
+ [ ] Check if DeepskyLog can send a mail to the user to verify.
+ [ ] Extra tests for the user class.

## LENSES

+ [ ] Sort on number of observations sorts alphabetically
+ [ ] Fix edit lens
+ [ ] Write view one lens
+ [ ] Add flash_messages when lens is updated (see store)
+ [ ] Show all lenses? (as administrator)
+ [ ] Only show delete button if there are no observations
+ [ ] Show the correct number of observations with a certain lens and make the correct link.

## AUTHENTICATION

+ [ ] We only need: guest, verified and admin -> Do we need spatie/laravel-permissions for that?
+ [ ] Do we need maileclipse?
+ [ ] Write user settings page and table for the DeepskyLog information
+ [ ] Update admin page for the users, add extra information, move operations in two different colums, use icons for operations
+ [ ] Use authentication on the pages and in the layout.
+ [ ] Write script to convert old observers table of DeepskyLog to laravel
+ [ ] Write script to convert old lenses table of DeepskyLog to laravel
+ [ ] Page to change observer settings
+ [ ] Page to view observer
+ [ ] Clean up source code

## SEEDER
