<?php
/** 
 * Shows the privacy page
 * 
 * PHP Version 7
 * 
 * @category Deepsky
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "redirect.php";
} else {
    privacy();
}

/** 
 * Shows the privacy page.
 * 
 * @return None
 */
function privacy()
{
    echo "<h1>" . _("DeepskyLog Privacy Policy") . "</h1>";
    echo _(
        "The majority of information on this site can be accessed without providing any personal information."
    ) . " " . 
    _(
        "In case users want to record observations and get acces to a variety of useful tools, the user is asked to register and provide personal information including name, first name and email address."
    ) . " " . 
    _(
        "This information will be used only for user management and to keep you informed about our activities."
    ) . " " . 
    _(
        "The user has the right at any time, at no cost and upon request, to prohibit the use of his information for the purpose of direct communication."
    ) . " " . 
    _(
        "Your personal information is never passed on to third parties."
    ) . "<br /><br />" . 
    _(
        "In case the registered user has not recorded any information in DeepskyLog within 24 months after registration, his account will be made obsolete and personal information deleted from the database."
    ) . "<br /><br />" . 
    sprintf(
        _(  
            "In case of questions or concerns regarding your personal data, do not hesitate to contact us at %sdevelopers@deepskylog.be%s."
        ), "<a href='mailto:developers@deepskylog.be'>", "</a>"
    );
}
?>