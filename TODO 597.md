### Issue #597: Offer observers creative commons options when setting a copyright notice.

#### The settings page:
(common/content/change_account.php)

+ Select the correct license from the settings.
+ Let the user also choose to use an own copyright message.
  + Make the current field active only if this option is selected
+ Let the user choose not to use a license. Mention that this is not the most safe option.

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

#### The observer details page
+ Show the license, with the image from the Creative Commons page and a link to the license at the page with the picture.

#### The detail observation page
+ When there is a picture, show the license (image + link)

Also in the new datatables page.

#### When not logged in
+ Message on the cookies?
