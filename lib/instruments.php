<?php
// instruments.php
// The instruments class collects all functions needed to enter, retrieve and adapt instrument data from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Instruments {
	// adds a new instrument to the database. The name, diameter, fd and type should be given as parameters.
	public function addInstrument($name, $diameter, $fd, $type, $fixedMagnification, $observer) {
		global $objDatabase;
		$objDatabase->execSQL ( "INSERT INTO instruments (name, diameter, fd, type, fixedMagnification, observer) VALUES (\"$name\", \"$diameter\", \"$fd\", \"$type\", \"$fixedMagnification\", \"$observer\")" );
		return $objDatabase->selectSingleValue ( "SELECT id FROM instruments ORDER BY id DESC LIMIT 1", 'id' );
	}
	// returns a list with all id's which have the same name as the name of the given id
	public function getAllInstrumentsIds($id) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT id FROM instruments WHERE name = \"" . ($objDatabase->selectSingleValue ( "SELECT name FROM instruments WHERE id = \"" . addslashes ( $id ) . "\"", 'name' )) . "\"", 'id' );
	}
	public function getInstrumentEchoType($instrumentType) {
		if ($instrumentType == INSTRUMENTNAKEDEYE)
			return _("Naked Eye");
		if ($instrumentType == INSTRUMENTBINOCULARS)
			return _("Binoculars");
		if ($instrumentType == INSTRUMENTFINDERSCOPE)
			return _("Finderscope");
		if ($instrumentType == INSTRUMENTREFLECTOR)
			return _("Reflector");
		if ($instrumentType == INSTRUMENTREFRACTOR)
			return _("Refractor");
		if ($instrumentType == INSTRUMENTREST)
			return _("Other");
		if ($instrumentType == INSTRUMENTCASSEGRAIN)
			return _("Cassegrain");
		if ($instrumentType == INSTRUMENTSCHMIDTCASSEGRAIN)
			return _("Schmidt Cassegrain");
		if ($instrumentType == INSTRUMENTKUTTER)
			return _("Kutter");
		if ($instrumentType == INSTRUMENTMAKSUTOV)
			return _("Maksutov");
		return "unkown instrument type";
	}
	public function getInstrumentEchoListType($type, $disabled = "") {
		$tempTypeList = "<select name=\"type\" class=\"form-control\" " . $disabled . " >";
		$tempTypeList .= "<option " . (($type == INSTRUMENTREFLECTOR) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTREFLECTOR . "\">" . _("Reflector") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTREFRACTOR) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTREFRACTOR . "\">" . _("Refractor") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTCASSEGRAIN) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTCASSEGRAIN . "\">" . _("Cassegrain") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTSCHMIDTCASSEGRAIN) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTSCHMIDTCASSEGRAIN . "\">" . _("Schmidt Cassegrain") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTKUTTER) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTKUTTER . "\">" . _("Kutter") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTMAKSUTOV) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTMAKSUTOV . "\">" . _("Maksutov") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTBINOCULARS) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTBINOCULARS . "\">" . _("Binoculars") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTFINDERSCOPE) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTFINDERSCOPE . "\">" . _("Finderscope") . "</option>";
		$tempTypeList .= "<option " . (($type == INSTRUMENTOTHER) ? "selected=\"selected\" " : "") . "value=\"" . INSTRUMENTREST . "\">" . _("Other") . "</option>";
		$tempTypeList .= "</select>";
		return $tempTypeList;
	}
	public function getInstrumentId($name, $observer) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT id FROM instruments where name=\"" . $name . "\" and observer=\"" . $observer . "\"", 'id', - 1 );
	}
	public function getInstrumentPropertyFromId($id, $property, $defaultValue = '') {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM instruments WHERE id = \"" . $id . "\"", $property, $defaultValue );
	}
	public function getInstrumentUsedFromId($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT count(id) as ObsCnt FROM observations WHERE instrumentid=\"" . $id . "\"", 'ObsCnt', 0 ) + $objDatabase->selectSingleValue ( "SELECT count(id) as ObsCnt FROM cometobservations WHERE instrumentid=\"" . $id . "\"", 'ObsCnt', 0 );
	}
	public function getObserverFromInstrument($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT observer FROM instruments WHERE id = \"" . $id . "\"", 'observer' );
	}
	public function getSortedInstruments($sort, $observer = "", $active = '') {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT id, name FROM instruments " . ($observer ? "WHERE observer LIKE \"" . $observer . "\" " . ($active ? " AND instrumentactive=" . $active : "") : " GROUP BY name") . " ORDER BY " . $sort . ", name", 'id' );
	}
	public function getSortedInstrumentsList($sort, $observer = "", $active = '') {
		global $objDatabase;
		return $objDatabase->selectKeyValueArray ( "SELECT id, name FROM instruments " . ($observer ? "WHERE observer LIKE \"" . $observer . "\" " . ($active ? " AND instrumentactive=" . $active : "") : " GROUP BY name") . " ORDER BY " . $sort . ", name", 'id', 'name' );
	}
	public function setInstrumentProperty($id, $property, $propertyValue) {
		global $objDatabase;
		return $objDatabase->execSQL ( "UPDATE instruments SET " . $property . " = \"" . $propertyValue . "\" WHERE id = \"" . $id . "\"" );
	}
	public function showInstrumentsObserver() {
		global $baseURL, $loggedUser, $objUtil, $objObserver, $objInstrument, $objPresentations, $loggedUserName;
		$insts = $objInstrument->getSortedInstruments ( 'id', $loggedUser );
		if (count ( $insts ) > 0) {
			echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
			echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
			echo "<input type=\"hidden\" name=\"adaption\" value=\"1\" />";
			// Add the button to select which columns to show
			$objUtil->addTableColumSelector ();

			echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
			echo "<thead>";
			echo "<th>" . _("Active") . "</td>";

			echo "<th data-priority=\"critical\">" . _("Name") . "</th>";
			echo "<th>" . _("Diameter (mm)") . "</th>";
			echo "<th>" . _("F/D") . "</th>";
			echo "<th>" . _("Fixed magnification") . "</th>";
			echo "<th>" . _("Type") . "</th>";
			echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">" . _("Default instrument") . "</th>";
			echo "<th>" . _("Delete") . "</th>";
			echo "<th>" . _("Number of observations") . "</th>";
			echo "</thead>";
			$count=0;
			while ( list ( $key, $value ) = each ( $insts ) ) {
				$name = $objInstrument->getInstrumentPropertyFromId ( $value, 'name' );
				$diameter = round ( $objInstrument->getInstrumentPropertyFromId ( $value, 'diameter' ), 0 );
				$fd = round ( $objInstrument->getInstrumentPropertyFromId ( $value, 'fd' ), 1 );
				if ($fd == "0")
					$fd = "-";
				$type = $objInstrument->getInstrumentPropertyFromId ( $value, 'type' );
				$fixedMagnification = $objInstrument->getInstrumentPropertyFromId ( $value, 'fixedMagnification' );
				echo "<tr>";
				
				echo "<td>" . "<span class=\"hidden\">" .  $objInstrument->getInstrumentPropertyFromId ( $value, 'instrumentactive' ) . "</span><input id=\"instrumentactive" . $value . "\" type=\"checkbox\" " . ($objInstrument->getInstrumentPropertyFromId ( $value, 'instrumentactive' ) ? " checked=\"checked\" " : "") . " onclick=\"setactivation('instrument'," . $value . ");var order = this.checked ? '1' : '0'; $(this).prev().html(order);$(this).parents('table').trigger('update');\" />" . "</td>";
				if ($name == "Naked eye")
					echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $value ) . "\">" . _("Naked Eye") . "</a></td>";
				else
					echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_instrument&amp;instrument=" . urlencode ( $value ) . "\">" . $name . "</a></td>";
				echo "<td>$diameter</td>";
				echo "<td>$fd</td>";
				echo "<td>";
				if ($fixedMagnification > 0)
					echo ($fixedMagnification);
				else
					echo ("-");
				echo "</td>";
				echo "<td>";
				echo $objInstrument->getInstrumentEchoType ( $type );
				echo "</td>";
				echo "<td>";

				// Radio button for the standard instrument
				if ($value == $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ))
					echo ("<input type=\"radio\" name=\"stdtelescope\" value=\"" . $value . "\" checked=\"checked\" onclick=\"submit();\" />&nbsp;<br />");
				else
					echo ("<input type=\"radio\" name=\"stdtelescope\" value=\"" . $value . "\" onclick=\"submit();\"/>&nbsp;<br />");
				echo "</td>";

				// Make it possible to delete the instrument.
				echo "<td>";
				if (! ($obsCnt = $objInstrument->getInstrumentUsedFromId ( $value ))) {
					echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_instrument&amp;instrumentid=" . urlencode ( $value ) . "\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></a>";
				}
				echo "</td>";
				// Show the number of observations for this instrument.
				echo "<td>";
				echo "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . $loggedUser . "&amp;instrument=" . $value . "&amp;exactinstrumentlocation=true\">";
				if ($obsCnt != 1) {
					echo $obsCnt . ' ' . _("observations") . "</a>";
				} else {
					echo $obsCnt . ' ' . _("observation") . "</a>";
				}
				echo "</td>";
				echo "</tr>";
				$count++;
			}
			echo "</table>";
			$objUtil->addPager ( "", $count );

			echo "</div></form>";
			echo "<hr />";
		}
	}
	public function validateDeleteInstrument() {
		global $objUtil, $objDatabase;
		if (($instrumentid = $objUtil->checkGetKey ( 'instrumentid' )) && $objUtil->checkAdminOrUserID ( $this->getObserverFromInstrument ( $instrumentid ) ) && (! ($this->getInstrumentUsedFromId ( $instrumentid )))) {
			$objDatabase->execSQL ( "DELETE FROM instruments WHERE id=\"" . $instrumentid . "\"" );
			return _("The instrument is removed from your equipment list!");
		}
	}
	public function validateSaveInstrument() {
		global $objUtil, $objDatabase, $objObserver, $loggedUser;
		if (($objUtil->checkPostKey ( 'adaption' ) == 1) && $objUtil->checkPostKey ( 'stdtelescope' ) && $objUtil->checkUserID ( $this->getObserverFromInstrument ( $objUtil->checkPostKey ( 'stdtelescope' ) ) )) {
			$objObserver->setObserverProperty ( $loggedUser, 'stdtelescope', $_POST ['stdtelescope'] );
			return;
		}
		if ($objUtil->checkPostKey ( 'instrumentname' ) && $objUtil->checkPostKey ( 'diameter' ) && $objUtil->checkPostKey ( 'type' )) {
			$instrumentname = htmlspecialchars ( $_POST ['instrumentname'] );
			$instrumentname = htmlspecialchars_decode ( $instrumentname, ENT_QUOTES );
			$type = htmlspecialchars ( $_POST ['type'] );
			$diameter = $_POST ['diameter'];
			if ($objUtil->checkPostKey ( 'fd' ) || $objUtil->checkPostKey ( 'focallength' ) || ($objUtil->checkPostKey ( 'type' ) == INSTRUMENTBINOCULARS || $objUtil->checkPostKey ( 'type' ) == INSTRUMENTFINDERSCOPE)) {
				$fd = 0;
				$fixedMagnification = $objUtil->checkPostKey ( 'fixedMagnification' );
				if ($objUtil->checkPostKey ( 'diameterunits' ) == "inch")
					$diameter *= 25.4;
				if ($_POST ['focallength'] && ($_POST ['type'] != INSTRUMENTBINOCULARS)) {
					$focallength = $_POST ['focallength'];
					if (array_key_exists ( 'focallengthunits', $_POST ) && $_POST ['focallengthunits'] == "inch" && ! array_key_exists ( 'fd', $_POST ))
						$focallength = $focallength * 25.4;
					if ($diameter > 0)
						$fd = $focallength / $diameter;
				} elseif (array_key_exists ( 'fd', $_POST ) && $_POST ['fd'] && array_key_exists ( 'type', $_POST ) && ($_POST ['type'] != INSTRUMENTBINOCULARS))
					$fd = $objUtil->checkPostKey ( 'fd', 1.0 );
			}
			if ($objUtil->checkPostKey ( 'add' )) {
				if (! isset ( $fd )) {
					$fd = 0.0;
				}
				if ($fd > 1.0)
					$fixedMag = 0;
				else {
					$fixedMag = $objUtil->checkPostKey ( 'fixedMagnification', 0 );
					$fd = 0.0;
				}
				$this->addInstrument ( $instrumentname, $diameter, $fd, $type, $fixedMag, $loggedUser );
				return _("The instrument is added to your equipment list!");
			}
			if ($objUtil->checkPostKey ( 'change' ) && $objUtil->checkAdminOrUserID ( $this->getObserverFromInstrument ( $objUtil->checkPostKey ( 'id' ) ) )) {
				$id = $_POST ['id'];
				$this->setInstrumentProperty ( $_POST ['id'], 'type', $type );
				$this->setInstrumentProperty ( $_POST ['id'], 'name', $instrumentname );
				$this->setInstrumentProperty ( $_POST ['id'], 'diameter', $diameter );
				if ($fd > 1.0) {
					$this->setInstrumentProperty ( $_POST ['id'], 'fd', $fd );
					$this->setInstrumentProperty ( $_POST ['id'], 'fixedMagnification', 0 );
				} else {
					$this->setInstrumentProperty ( $_POST ['id'], 'fd', 0 );
					$this->setInstrumentProperty ( $_POST ['id'], 'fixedMagnification', $objUtil->checkPostKey ( 'fixedMagnification' ) );
				}
				return _("The instrument details have been changed in your equipment list!");
			}
		} else
			return _("All required fields must be filled in!");
	}
}
?>
