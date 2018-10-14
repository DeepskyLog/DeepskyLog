<?php
/** 
 * Add a new instrument for the logged in user.
 * 
 * PHP Version 7
 * 
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!$loggedUser) {
    throw new Exception(
        _("You need to be logged in to change your locations or equipment.")
    );
} else {
    newInstrument();
}

/** 
 * Add a new instrument for the logged in user.
 * 
 * @return None
 */
function newInstrument() 
{
    global $baseURL, $loggedUserName, $objInstrument, $objPresentations, $objUtil;
    echo "<div id=\"main\">";
    $insts = $objInstrument->getSortedInstruments('name', "", true);
    echo "<form role=\"form\" action=\"" . $baseURL 
        . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\"" 
        . " value=\"validate_instrument\" />";
    $content1b = "<select class=\"form-control\" " 
        . "onchange=\"location = this.options[this.selectedIndex].value;\" " 
        . "name=\"catalog\">";
    $content1b .= "<option selected=\"selected\" value=\"" . $baseURL 
        . "index.php?indexAction=add_instrument\"> &nbsp; </option>";
    foreach ($insts as $key=>$value) {
        $content1b .= "<option value=\"" . $baseURL 
            . "index.php?indexAction=add_instrument&amp;instrumentid=" 
            . urlencode($value) . "\" " 
            . (($value == $objUtil->checkRequestKey('instrumentid')) 
            ? " selected=\"selected\" " : '') 
            . ">" . $objInstrument->getInstrumentPropertyFromId($value, 'name') 
            . "</option>";
    }
    $content1b .= "</select>";

    echo "<h4>" . _("Add new instrument") . "</h4>";
    echo "<hr />";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right tour2\" " 
        . "name=\"add\" value=\"" . _("Add instrument") . "\" />&nbsp;";

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Select an existing instrument") 
        . "</label>";
    echo "<div class=\"form-inline\">";
    echo $content1b;
    echo "</div></div>";

    echo _("or add instrument manually");
    echo "<br /><br />";

    $type = $objUtil->checkRequestKey('type');
    if ($instrumentid = $objUtil->checkRequestKey('instrumentid', 0)) {
        $type = $objInstrument->getInstrumentPropertyFromId($instrumentid, 'type');
    }

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Instrument name") . "</label>";
    echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" " 
        . "name=\"instrumentname\" size=\"30\"  value=\"" 
        . stripslashes($objUtil->checkRequestKey('instrumentname')) 
        . stripslashes(
            $objInstrument->getInstrumentPropertyFromId(
                $objUtil->checkRequestKey('instrumentid'), 'name'
            )
        ) . "\" />";
    echo "</div>";
    echo "</div>";

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Diameter") . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0.01\" step=\"0.01\" required " 
        . "class=\"form-control\" maxlength=\"64\" name=\"diameter\" " 
        . "id=\"diameter\" size=\"10\" value=\"" 
        . stripslashes($objUtil->checkRequestKey('diameter')) 
        . stripslashes(
            $objInstrument->getInstrumentPropertyFromId(
                $objUtil->checkRequestKey('instrumentid'), 'diameter'
            )
        ) . "\" />" 
        . "<select id=\"dunits\" name=\"diameterunits\" size=\"15\" " 
        . "class=\"form-control\"> " 
        . "<option value=\"inch\">inch</option>" 
        . "<option value=\"mm\" selected=\"selected\">mm</option> " 
        . "</select>";
    echo "</div>";
    echo "</div>";

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Type") . "</label>";
    echo "<div class=\"form-inline\">";
    echo $objInstrument->getInstrumentEchoListType($type);
    echo "</div>";
    echo "</div>";

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Focal Length") . "</label>";
    echo "<div class=\"form-inline\">";

    if ($objInstrument->getInstrumentPropertyFromId(
        $objUtil->checkRequestKey('instrumentid'), 'fd'
    ) != ''
    ) {
        $val = $objInstrument->getInstrumentPropertyFromId(
            $objUtil->checkRequestKey('instrumentid'), 'diameter'
        ) * $objInstrument->getInstrumentPropertyFromId(
            $objUtil->checkRequestKey('instrumentid'), 'fd'
        );
    } else {
        $val = '';
    }
    echo "<input type=\"number\" min=\"0.0\" step=\"0.0\" class=\"form-control\"" 
        . " maxlength=\"64\" id=\"focallength\" name=\"focallength\" size=\"10\"" 
        . " value=\"" 
        . stripslashes($objUtil->checkRequestKey('focallength')) 
        . stripslashes($val) . "\" />" 
        . "<select class=\"form-control\" size=\"10\" id=\"funits\" " 
        . "name=\"focallengthunits\">" 
        . "<option value=\"inch\">inch</option>" 
        . "<option value=\"mm\" selected=\"selected\">mm</option>" 
        . "</select>" 
        . "&nbsp;<span>" . _("or F/D") . "</span>&nbsp;" 
        . "<input type=\"number\" min=\"0.0\" step=\"0.01\" " 
        . "class=\"form-control\" maxlength=\"64\" name=\"fd\" " 
        . "id=\"fd\" size=\"10\" value=\"" 
        . stripslashes($objUtil->checkRequestKey('fd')) 
        . stripslashes(
            $objInstrument->getInstrumentPropertyFromId(
                $objUtil->checkRequestKey('instrumentid'), 'fd'
            )
        ) . "\" />";
    echo "</div>";
    echo "</div>";

    echo "<div class=\"form-group\">
           <label for=\"catalog\">" . _("Fixed magnification") . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0.0\" step=\"0.1\" class=\"form-control\"" 
        . " maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"" 
        . ($objUtil->checkRequestKey('fixedMagnification')) 
        . stripslashes(
            $objInstrument->getInstrumentPropertyFromId(
                $objUtil->checkRequestKey('instrumentid'), 'fixedMagnification'
            )
        ) . "\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" 
        . _("Only for binoculars, finder scopes, ...") . "</span>";
    echo "</div>";

    echo "<hr />";
    echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\"" 
        . _("Add instrument") . "\" />&nbsp;";
    echo "</div></form>";
    echo "</div>";

    echo '<script type="text/javascript">
        $(document).ready(function() {
            var dUnitChange = 1;
            var fUnitChange = 1;

            // Adapt the F/D whenever the focal length changes
            $("#focallength").on("keyup change", function(event) {
                focallength = event.target.value;
                diameter = $("#diameter").val();

                $("#fd").val(Math.round(focallength / diameter * 100) / 100);
            });

            // Adapt the focal length whenever the F/D changes
            $("#fd").on("keyup change", function(event) {
                fd = event.target.value;
                diameter = $("#diameter").val();

                $("#focallength").val(Math.round(fd * diameter));
            });

            // If the unit changes for the diameter, also change the unit for the 
            // focal length and vice versa
            $("#dunits").change(function(){
                diameterUnits = $(this).find("option:selected").attr("value");

                if (dUnitChange == 1) {
                    dUnitChange = 1;
                    fUnitChange = 0;
                    $("#funits").val(diameterUnits).change();

                    if (diameterUnits == "mm") {
                        $("#diameter").val(
                            Math.round(($("#diameter").val() * 25.4))
                        );
                        $("#focallength").val(
                            Math.round($("#focallength").val() * 25.4)
                        );
                    } else {
                        $("#diameter").val(
                            Math.round($("#diameter").val() * 100.0 / 25.4) / 100
                        );
                        $("#focallength").val(
                            Math.round($("#focallength").val() * 1.0 / 25.4)
                        );
                    }
                } else {
                    dUnitChange = 1;
                }
            });

            $("#funits").change(function(){
                focalLengthUnits = $(this).find("option:selected").attr("value");

                if (fUnitChange == 1) {
                    fUnitChange = 1;
                    dUnitChange = 0;
                    $("#dunits").val(focalLengthUnits).change();

                    if (diameterUnits == "mm") {
                        $("#diameter").val(Math.round($("#diameter").val() * 25.4));
                        $("#focallength").val(
                            Math.round($("#focallength").val() * 25.4)
                        );
                    } else {
                        $("#diameter").val(Math.round($("#diameter").val() / 25.4));
                        $("#focallength").val(
                            Math.round($("#focallength").val() / 25.4)
                        );
                    }
                } else {
                    fUnitChange = 1;
                }
            });
        });
        </script>';
}
?>
