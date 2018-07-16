<?php
// eyepieces.php
// The eyepieces class collects all functions needed to enter, retrieve and adapt eyepiece data from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Eyepieces {
	public function addEyepiece($name, $focalLength, $apparentFOV) // addEyepiece adds a new eyepiece to the database. The name, focalLength and apparentFOV should be given as parameters.
{
		global $objDatabase;
		$objDatabase->execSQL ( "INSERT INTO eyepieces (name, focalLength, apparentFOV) VALUES (\"" . $name . "\", \"" . $focalLength . "\", \"" . $apparentFOV . "\")" );
		return $objDatabase->selectSingleValue ( "SELECT id FROM eyepieces ORDER BY id DESC LIMIT 1", 'id', '' );
	}
	public function getAllEyepiecesIds($id) // getAllIds returns a list with all id's which have the same name as the name of the given id
{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT id FROM eyepieces WHERE name=" . $objDatabase->selectSingleValue ( "SELECT name FROM eyepieces WHERE id = \"" . $id . "\"", 'name' ), 'id' );
	}
	public function getEyepieceId($name, $observer) // returns the id for this eyepiece
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT id FROM eyepieces where name=\"" . $name . "\" and observer=\"" . $observer . "\"", 'id', - 1 );
	}
	public function getEyepieceObserverPropertyFromName($name, $observer, $property) // returns the property for the eyepiece of the observer
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM eyepieces where name=\"" . $name . "\" and observer=\"" . $observer . "\"", $property );
	}
	public function getEyepiecePropertiesFromId($id) // returns the properties of the eyepiece with id
{
		global $objDatabase;
		return $objDatabase->selectRecordArray ( "SELECT * FROM eyepieces WHERE id=\"" . $id . "\"" );
	}
	public function getEyepiecePropertyFromId($id, $property, $defaultValue = '') // returns the property of the given eyepiece
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM eyepieces WHERE id = \"" . $id . "\"", $property, $defaultValue );
	}
	public function getEyepieceUsedFromId($id) // returns the number of times the eyepiece is used in observations
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT count(id) as ObsCnt FROM observations WHERE eyepieceid=\"" . $id . "\"", 'ObsCnt', 0 );
	}
	public function getSortedEyepieces($sort, $observer = "", $active = "") // returns an array with the ids of all eyepieces, sorted by the column specified in $sort
{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT id, name FROM eyepieces " . ($observer ? "WHERE observer LIKE \"" . $observer . "\"" . ($active ? " AND eyepieceactive = " . $active : "") : " GROUP BY name") . " ORDER BY " . $sort . ", name", 'id' );
	}
	public function setEyepieceProperty($id, $property, $propertyValue) // sets the property to the specified value for the given eyepiece
{
		global $objDatabase;
		$sql = "UPDATE eyepieces SET " . $property . " = \"" . $propertyValue . "\" WHERE id = \"" . $id . "\"";
		return $objDatabase->execSQL ( $sql );
	}
	public function showEyepiecesObserver() {
		global $baseURL, $loggedUser, $objUtil, $objEyepiece, $objPresentations, $loggedUserName;
		$eyeps = $objEyepiece->getSortedEyepieces ( 'id', $loggedUser );
		if ($eyeps != null) { // Add the button to select which columns to show
			$objUtil->addTableColumSelector ();

			echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
			echo "<thead><tr>";
			echo "<th>" . LangViewActive . "</td>";
			echo "<th data-priority=\"critical\">" . LangViewEyepieceName . "</th>";
			echo "<th>" . LangViewEyepieceFocalLength . "</th>";
			echo "<th>" . LangViewEyepieceMaxFocalLength . "</th>";
			echo "<th>" . LangViewEyepieceApparentFieldOfView . "</th>";
			echo "<th>" . _("Delete") . "</th>";
			echo "<th>" . LangTopObserversHeader3 . "</th>";
			echo "</tr></thead>";
			$count = 0;
			while ( list ( $key, $value ) = each ( $eyeps ) ) {
				$eyepiece = $objEyepiece->getEyepiecePropertiesFromId ( $value );
				echo "<tr>";
				echo "<td>" . "<span class=\"hidden\">" . $eyepiece ['eyepieceactive'] . "</span><input id=\"eyepieceactive" . $value . "\" type=\"checkbox\" " . ($eyepiece ['eyepieceactive'] ? " checked=\"checked\" " : "") . " onclick=\"setactivation('eyepiece'," . $value . ");var order = this.checked ? '1' : '0'; $(this).prev().html(order);$(this).parents('table').trigger('update');\" />" . "</td>";
				echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_eyepiece&amp;eyepiece=" . urlencode ( $value ) . "\">" . stripslashes ( $eyepiece ['name'] ) . "</a></td>";
				echo "<td>" . $eyepiece ['focalLength'] . "</td>";
				echo "<td>" . (($eyepiece ['maxFocalLength'] != - 1) ? $eyepiece ['maxFocalLength'] : "-") . "</td>";
				echo "<td>" . $eyepiece ['apparentFOV'] . "</td>";
				// Make it possible to delete the lenses
				echo "<td>";
				if (! ($obsCnt = $objEyepiece->getEyepieceUsedFromId ( $value ))) {
					echo ("<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=" . urlencode ( $value ) . "\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></a>");
				}
				echo "</td>";
				// Show the number of observations for this lens.
				echo "<td>";
				echo "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . $loggedUser . "&amp;eyepiece=" . $value . "&amp;exactinstrumentlocation=true\">";
				if ($obsCnt != 1) {
					echo $obsCnt . ' ' . LangGeneralObservations . "</a>";
				} else {
					echo $obsCnt . ' ' . LangGeneralObservation . "</a>";
				}
				echo "</td></tr>";
				$count++;
			}
			echo "</table>";
			$objUtil->addPager ( "", $count );
			echo "<hr />";
		}
	}
	public function validateDeleteEyepiece() // validates and deletes an eyepiece
{
		global $objUtil, $objDatabase;
		if (($eyepieceid = $objUtil->checkGetKey ( 'eyepieceid' )) && $objUtil->checkAdminOrUserID ( $this->getEyepiecePropertyFromId ( $eyepieceid, 'observer' ) ) && (! ($this->getEyepieceUsedFromId ( $eyepieceid )))) {
			$objDatabase->execSQL ( "DELETE FROM eyepieces WHERE id=\"" . $eyepieceid . "\"" );
			return LangValidateEyepieceMessage6;
		}
	}
	public function validateSaveEyepiece() // validates and saves an eyepiece and returns a message
{
		global $objUtil, $loggedUser;
		if ($objUtil->checkPostKey ( 'eyepiecename' ) && $objUtil->checkSessionKey ( 'deepskylog_id' ) && $objUtil->checkPostKey ( 'focalLength' ) && $objUtil->checkPostKey ( 'apparentFOV' ) && $objUtil->checkPostKey ( 'add' )) {
			$id = $this->addEyepiece ( $_POST ['eyepiecename'], $_POST ['focalLength'], $_POST ['apparentFOV'] );
			$this->setEyepieceProperty ( $id, 'observer', $loggedUser );
			$this->setEyepieceProperty ( $id, 'maxFocalLength', $objUtil->checkPostKey ( 'maxFocalLength', - 1 ) );
			return LangValidateEyepieceMessage2;
		} elseif ($objUtil->checkPostKey ( 'id' ) && $objUtil->checkPostKey ( 'eyepiecename' ) && $objUtil->checkPostKey ( 'focalLength' ) && $objUtil->checkPostKey ( 'apparentFOV' ) && $objUtil->checkPostKey ( 'change' ) && $objUtil->checkAdminOrUserID ( $this->getEyepiecePropertyFromId ( $_POST ['id'], 'observer' ) )) {
			$this->setEyepieceProperty ( $_POST ['id'], 'name', $_POST ['eyepiecename'] );
			$this->setEyepieceProperty ( $_POST ['id'], 'focalLength', $_POST ['focalLength'] );
			$this->setEyepieceProperty ( $_POST ['id'], 'apparentFOV', $_POST ['apparentFOV'] );
			// $this->setEyepieceProperty($_POST['id'],'observer', $loggedUser);
			$this->setEyepieceProperty ( $_POST ['id'], 'maxFocalLength', $objUtil->checkPostKey ( 'maxFocalLength', - 1 ) );
			return LangValidateEyepieceMessage5 . ' ' . LangValidateEyepieceMessage4;
		} else
			return LangValidateMessage1;
	}
}
?>
