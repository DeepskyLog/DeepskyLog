<?php
/**
 * Allows the instrument owner or an administrator to change an instrument
 * or another user to view the instrument details
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!($instrumentid = $objUtil->checkGetKey('instrument'))) {
    throw new Exception(_("You wanted to change an instrument, but none is specified. Please contact the developers with this message."));
} elseif (!($objInstrument->getInstrumentPropertyFromId($instrumentid, 'name'))) {
    throw new Exception(
        _(
            "Instrument not found in change_instrument.php, please contact the developers with this message:"
            . $eyepieceid
        )
    );
} else {
    changeInstrument();
}

/**
 * Change an instrument for the logged in user or administrator
 * or show the instrument for other users.
 *
 * @return None
 */
function changeInstrument()
{
    global $baseURL, $instrumentid, $loggedUser, $objInstrument, $objPresentations;
    global $objUtil;
    $disabled = " disabled=\"disabled\"";
    if (($loggedUser)
        && ($objUtil->checkAdminOrUserID(
            $objInstrument->getInstrumentPropertyFromId(
                $instrumentid, 'observer', ''
            )
        ))
    ) {
        $disabled = "";
    }
    $content = ($disabled
        ? "" : "<input type=\"submit\" class=\"btn btn-primary pull-right\" "
        . "name=\"change\" value=\"" . _("Change instrument")
        . "\" />&nbsp;");
    $name = $objInstrument->getInstrumentPropertyFromId($instrumentid, 'name');
    echo "<div id=\"main\">";
    echo "<form role=\"form\" action=\"" . $baseURL
        . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" "
        . "value=\"validate_instrument\" />";
    echo "<input type=\"hidden\" name=\"id\" value=\"" . $instrumentid . "\" />";
    echo "<h4>" . (($name == "Naked eye") ? _("Naked Eye") : $name) . "</h4>";
    echo "<hr />";
    echo $content;

    echo "<div class=\"form-group\">
            <label for=\"filtername\">" . _("Instrument name") . "</label>";
    echo "<input value=\"" . $name
        . "\" type=\"text\" required class=\"form-control\" maxlength=\"64\" "
        . "name=\"instrumentname\" size=\"30\" " . $disabled . " />";
    echo "</div>";

    $diameter = round(
        $objInstrument->getInstrumentPropertyFromId(
            $instrumentid, 'diameter'
        ), 0
    );
    $content = "<input value=\"" . $diameter . "\" type=\"number\" min=\"0.01\" "
        . "step=\"0.01\" "
        . "class=\"form-control\" required maxlength=\"64\" name=\"diameter\" "
        . "id=\"diameter\" size=\"10\" " . $disabled . " />";
    $content .= "<select name=\"diameterunits\" id=\"dunits\" size=\"10\" "
        . "class=\"form-control\""
        . $disabled . " >";
    $content .= "<option value=\"inch\">inch</option>";
    $content .= "<option value=\"mm\" selected=\"selected\">mm</option>";
    $content .= "</select>";

    echo "<div class=\"form-group\">
            <label for=\"filtername\">" . _("Diameter") . "</label>";
    echo "<div class=\"form-inline\">";
    echo $content;
    echo "</div>";
    echo "</div>";


    echo "<div class=\"form-group\">
            <label for=\"filtername\">" . _("Type") . "</label>";
    echo "<div class=\"form-inline\">";
    echo $objInstrument->getInstrumentEchoListType(
        $objInstrument->getInstrumentPropertyFromId(
            $instrumentid, 'type'
        ), $disabled
    );
    echo "</div></div>";

    $fl = round(
        $objInstrument->getInstrumentPropertyFromId($instrumentid, 'fd')
        * $objInstrument->getInstrumentPropertyFromId(
            $instrumentid, 'diameter'
        ), 0
    );

    if ($fl) {
        $fd = round($fl / $diameter * 10) / 10;
    } else {
        $fd = 0;
    }
    $content = "<input value=\""
        . ($fl ? $fl : "")
        . "\" type=\"number\" min=\"0.01\" step=\"0.01\" class=\"form-control\" "
        . "maxlength=\"64\" name=\"focallength\" id=\"focallength\" size=\"10\" "
        . $disabled . " />";
    $content .= "<select class=\"form-control\" size=\"10\" "
        . "name=\"focallengthunits\" id=\"funits\" "
        . $disabled . " >";
    $content .= "<option value=\"inch\">inch</option>";
    $content .= "<option value=\"mm\" selected=\"selected\">mm</option>";
    $content .= "</select>";
    $content .= ' ' . _("or F/D") . ' ';
    $content .= "<input type=\"number\" min=\"0.01\" step=\"0.01\" size=\"10\" "
        . "class=\"form-control\" maxlength=\"64\" id=\"fd\" name=\"fd\" "
        . "value=\"" . ($fd ? $fd : "") . "\" "
        . $disabled . " />";

    echo "<div class=\"form-group\">
            <label for=\"filtername\">" . _("Focal Length") . "</label>";
    echo "<div class=\"form-inline\">";
    echo $content;
    echo "</div></div>";

    echo "<div class=\"form-group\">
            <label for=\"filtername\">" . _("Fixed magnification") . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input value=\""
        . (($fm = $objInstrument->getInstrumentPropertyFromId(
            $instrumentid, 'fixedMagnification'
        ))
        ? $fm : "")
        . "\" type=\"number\" min=\"0.1\" step=\"0.1\" "
        . "class=\"form-control\" maxlength=\"10\" "
        . "name=\"fixedMagnification\" size=\"5\" " . $disabled . " />";
    echo "</div></div>";

    echo "<hr />";
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
