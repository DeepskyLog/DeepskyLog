<?php
// lenses.php
// The lenses class collects all functions needed to enter, retrieve and adapt lenses data from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Lenses {
	public function addLens($name, $factor) // adds a new lens to the database. The name and the factor should be given as parameters.
{
		global $objDatabase;
		$objDatabase->execSQL ( "INSERT INTO lenses (name, factor) VALUES (\"" . $name . "\", \"" . $factor . "\")" );
		return $objDatabase->selectSingleValue ( "SELECT id FROM lenses ORDER BY id DESC LIMIT 1", 'id' );
	}
	public function getAllFiltersIds($id) // returns a list with all id's which have the same name as the name of the given id
{
		global $objDatabase;
		return $objDatabase->selectSinleArray ( "SELECT id FROM lenses WHERE name = \"" . $objDatabase->selectSingleValue ( "SELECT name FROM lenses WHERE id = \"" . $id . "\"" ) . "\"" );
	}
	public function getLensId($name, $observer) // returns the id for this lens
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT id FROM lenses where name=\"" . $name . "\" and observer=\"" . $observer . "\"", 'id', - 1 );
	}
	public function getLensObserverPropertyFromName($name, $observer, $property) // returns the property for the eyepiece of the observer
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM lenses where name=\"" . $name . "\" and observer=\"" . $observer . "\"", $property );
	}
	public function getLensPropertyFromId($id, $property, $defaultValue = '') // returns the property of the given lens
	{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM lenses WHERE id = \"" . $id . "\"", $property, $defaultValue );
	}
	public function getSortedLenses($sort, $observer = "", $active = '') // returns an array with the ids of all lenses, sorted by the column specified in $sort
	{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT " . ($observer ? "" : "MAX(id)") . " id, name FROM lenses " . ($observer ? "WHERE observer LIKE \"" . $observer . "\" " . ($active ? " AND lensactive=" . $active : "") : " GROUP BY name") . " ORDER BY " . $sort . ", name", 'id' );
	}
	public function setLensProperty($id, $property, $propertyValue) // sets the property to the specified value for the given lens
{
		global $objDatabase;
		return $objDatabase->execSQL ( "UPDATE lenses SET " . $property . " = \"" . $propertyValue . "\" WHERE id = \"" . $id . "\"" );
	}
	public function getLensUsedFromId($id) // returns the number of times the lens is used in observations
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT count(id) as ObsCnt FROM observations WHERE lensid=\"" . $id . "\"", 'ObsCnt', 0 );
	}
	public function showLensesObserver() {
		global $baseURL, $loggedUser, $objUtil, $objLens, $objPresentations, $loggedUserName;
		$lns = $objLens->getSortedLenses ( 'id', $loggedUser );
		if ($lns != null) { // Add the button to select which columns to show
			$objUtil->addTableColumSelector ();

			echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
			echo "<thead><tr>";
			echo "<th>" . _("Active") . "</td>";
			echo "<th data-priority=\"critical\">" . _("Name") . "</th>";
			echo "<th>" . _("Factor") . "</th>";
			echo "<th>" . _("Delete") . "</th>";
			echo "<th>" . _("Number of observations") . "</th>";
			echo "</tr></thead>";
			$count = 0;
			foreach ($lns as $key => $value) {
				$name = stripslashes ( $objLens->getLensPropertyFromId ( $value, 'name' ) );
				$factor = $objLens->getLensPropertyFromId ( $value, 'factor' );
				echo "<tr>";
				
				echo "<td>" . "<span class=\"hidden\">" . $objLens->getLensPropertyFromId ( $value, 'lensactive' ) . "</span><input id=\"lensactive" . $value . "\" type=\"checkbox\" " . ($objLens->getLensPropertyFromId ( $value, 'lensactive' ) ? " checked=\"checked\" " : "") . " onclick=\"setactivation('lens'," . $value . "); var order = this.checked ? '1' : '0'; $(this).prev().html(order);$(this).parents('table').trigger('update');\" />" . "</td>";
				echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_lens&amp;lens=" . urlencode ( $value ) . "\">" . $name . "</a></td>";
				echo "<td>" . $factor . "</td>";
				// Make it possible to delete the lenses
				echo "<td>";
				if (! ($obsCnt = $objLens->getLensUsedFromId ( $value ))) {
					echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_lens&amp;lensid=" . urlencode ( $value ) . "\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></a>";
				}
				echo "</td>";
				// Show the number of observations for this lens.
				echo "<td>";
				echo "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . $loggedUser . "&amp;lens=" . $value . "&amp;exactinstrumentlocation=true\">";
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
			echo "<hr />";
		}
	}
	public function validateDeleteLens() // validates and removes the lens with id
{
		global $objUtil, $objDatabase;
		if (($lensid = $objUtil->checkGetKey ( 'lensid' )) && $objUtil->checkAdminOrUserID ( $this->getLensPropertyFromId ( $lensid, 'observer' ) ) && (! ($this->getLensUsedFromId ( $lensid )))) {
			$objDatabase->execSQL ( "DELETE FROM lenses WHERE id=\"" . $lensid . "\"" );
			return _("The lens is removed from your equipment list");
		}
	}
	public function validateSaveLens() // validates and saves a lens and returns a message
{
		global $objUtil, $loggedUser;
		if ($objUtil->checkPostKey ( 'add' ) && $objUtil->checkPostKey ( 'lensname' ) && $objUtil->checkPostKey ( 'factor' ) && $loggedUser) {
			$id = $this->addLens ( $_POST ['lensname'], $_POST ['factor'] );
			$this->setLensProperty ( $id, 'observer', $loggedUser );
			return _("The lens is added to your equipment list");
		} elseif ($objUtil->checkPostKey ( 'change' ) && $objUtil->checkAdminOrUserID ( $this->getLensPropertyFromId ( $objUtil->checkPostKey ( 'id' ), 'observer' ) ) && $objUtil->checkPostKey ( 'lensname' ) && $objUtil->checkPostKey ( 'factor' )) {
			$this->setLensProperty ( $_POST ['id'], 'name', $_POST ['lensname'] );
			$this->setLensProperty ( $_POST ['id'], 'factor', $_POST ['factor'] );
			// $this->setLensProperty($_POST['id'], 'observer', $loggedUser);
			return _("The lens is changed in your equipment list");
		} else
			return _("All required fields must be filled in!");
	}
}
?>
