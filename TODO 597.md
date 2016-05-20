### Issue #597: Offer observers creative commons options when setting a copyright notice.

#### The settings page:
(common/content/change_account.php)

We can just put one of the 6 Creative Commons licenses in the database field. This way, we don't need to update the database.

Done:
+ Add link to choose a license http://creativecommons.org/choose/
+ Let the user choose one of the 6 licenses:
  + Attribution CC BY
  + Attribution-ShareAlike CC BY-SA
  + Attribution-NoDerivs CC BY-ND
  + Attribution-NonCommercial CC BY-NC
  + Attribution-NonCommercial-ShareAlike CC BY-NC-SA
  + Attribution-NonCommercial-NoDerivs CC BY-NC-ND
+ Select the correct license from the settings: When there is no text, select the 'No license option'.
+ Let the user also choose to use an own copyright message.
+ Let the user choose not to use a license. Mention that this is not the most safe option.
+ Make the current field active only if the own license option is selected
+ Show the correct option from the database. Make also sure that the copyright field is correctly enabled/disabled in the beginning.

#### The observer class
(lib/observers.php)

Done:
+ Save the correct text to the database.
  + after registration
  + when setting the copyright message

#### The register page
(common/content/register.php)

Done:
+ Page with copyright

#### The observer details page
(common/content/view_observer.php)

Done:
+ Show the license, with the image from the Creative Commons page and a link to the license at the page with the picture.

#### The detail observation page
(lib/observations.php)

+ When there is a picture, show the license (image + link)

Also in the new datatables page.

#### When not logged in
+ Message on the cookies?
