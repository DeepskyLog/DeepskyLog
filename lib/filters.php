<?php
// filters.php
// The filters class collects all functions needed to enter, retrieve and adapt filters data from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex)) {
    include "../../redirect.php";
}
class Filters {
	public function addFilter($name, $type, $color, $wratten, $schott) // addFilter adds a new filter to the database. The name, type, color, wratten and schott should be given as parameters.
{
		global $objDatabase;
		$objDatabase->execSQL ( "INSERT INTO filters (name, type, color, wratten, schott) VALUES (\"" . $name . "\", \"" . $type . "\", \"" . $color . "\", \"" . $wratten . "\", \"" . $schott . "\")" );
		return $objDatabase->selectSingleValue ( "SELECT id FROM filters ORDER BY id DESC LIMIT 1", 'id', '' );
	}
	public function getAllFiltersIds($id) // returns a list with all id's which have the same name as the name of the given id
{
		global $objDatabase;
		return $objDatabase->selectSinleArray ( "SELECT id FROM filters WHERE name = \"" . $objDatabase->selectSingleValue ( "SELECT name FROM filters WHERE id = \"" . $id . "\"" ) . "\"" );
	}
	public function getEchoColor($color) {
		if ($color == FILTERCOLORLIGHTRED)
			return _("Light red");
		if ($color == FILTERCOLORRED)
			return _("Red");
		if ($color == FILTERCOLORDEEPRED)
			return _("Deep red");
		if ($color == FILTERCOLORORANGE)
			return _("Orange");
		if ($color == FILTERCOLORLIGHTYELLOW)
			return _("Light yellow");
		if ($color == FILTERCOLORDEEPYELLOW)
			return _("Deep yellow");
		if ($color == FILTERCOLORYELLOW)
			return _("Yellow");
		if ($color == FILTERCOLORYELLOWGREEN)
			return _("Yellow-Green");
		if ($color == FILTERCOLORLIGHTGREEN)
			return _("Light green");
		if ($color == FILTERCOLORGREEN)
			return _("Green");
		if ($color == FILTERCOLORMEDIUMBLUE)
			return _("Medium blue");
		if ($color == FILTERCOLORPALEBLUE)
			return _("Pale blue");
		if ($color == FILTERCOLORBLUE)
			return _("Blue");
		if ($color == FILTERCOLORDEEPBLUE)
			return _("Deep blue");
		if ($color == FILTERCOLORDEEPVIOLET)
			return _("Violet");
		return "-";
	}
	public function getEchoListColor($color, $disabled = "") {
		$tempColorList = "<select name=\"color\" class=\"form-control inputfield\" " . $disabled . " >";
		$tempColorList .= "<option value=\"\">&nbsp;</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTRED . "\">" . _("Light red") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORRED . "\">" . _("Red") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPRED . "\">" . _("Deep red") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORORANGE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORORANGE . "\">" . _("Orange") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTYELLOW . "\">" . _("Light yellow") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPYELLOW . "\">" . _("Deep yellow") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORYELLOW . "\">" . _("Yellow") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORYELLOWGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORYELLOWGREEN . "\">" . _("Yellow-Green") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTGREEN . "\">" . _("Light green") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORGREEN . "\">" . _("Green") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORMEDIUMBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORMEDIUMBLUE . "\">" . _("Medium blue") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORPALEBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORPALEBLUE . "\">" . _("Pale blue") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORBLUE . "\">" . _("Blue") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPBLUE . "\">" . _("Deep blue") . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPVIOLET) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPVIOLET . "\">" . _("Violet") . "</option>";
		$tempColorList .= "</select>";
		return $tempColorList;
	}
	public function getEchoListType($type, $disabled = "") {
		$tempTypeList = "<select name=\"type\" class=\"form-control inputfield\" " . $disabled . " >";
		$tempTypeList .= "<option " . (($type == FILTEROTHER) ? " selected=\"selected\" " : "") . " value=\"" . FILTEROTHER . "\">" . _("Other filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERBROADBAND) ? " selected=\"selected\" " : "") . " value=\"" . FILTERBROADBAND . "\">" . _("Broadband filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERNARROWBAND) ? " selected=\"selected\" " : "") . " value=\"" . FILTERNARROWBAND . "\">" . _("Narrowband filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTEROIII) ? " selected=\"selected\" " : "") . " value=\"" . FILTEROIII . "\">" . _("O-III filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERHBETA) ? " selected=\"selected\" " : "") . " value=\"" . FILTERHBETA . "\">" . _("H beta filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERHALPHA) ? " selected=\"selected\" " : "") . " value=\"" . FILTERHALPHA . "\">" . _("H alpha filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERCOLOR) ? " selected=\"selected\" " : "") . " value=\"" . FILTERCOLOR . "\">" . _("Color filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERNEUTRAL) ? " selected=\"selected\" " : "") . " value=\"" . FILTERNEUTRAL . "\">" . _("Neutral filter") . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERCORRECTIVE) ? " selected=\"selected\" " : "") . " value=\"" . FILTERCORRECTIVE . "\">" . _("Corrective filter") . "</option>";
		$tempTypeList .= "</select>";
		return $tempTypeList;
	}
	public function getEchoType($type) {
		if ($type == FILTEROTHER)
			return _("Other filter");
		if ($type == FILTERBROADBAND)
			return _("Broadband filter");
		if ($type == FILTERNARROWBAND)
			return _("Narrowband filter");
		if ($type == FILTEROIII)
			return _("O-III filter");
		if ($type == FILTERHBETA)
			return _("H beta filter");
		if ($type == FILTERHALPHA)
			return _("H alpha filter");
		if ($type == FILTERCOLOR)
			return _("Color filter");
		if ($type == FILTERNEUTRAL)
			return _("Neutral filter");
		if ($type == FILTERCORRECTIVE)
			return _("Corrective filter");
		return "-";
	}
	public function getFilterId($name, $observer) // returns the id for this instrument
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT id FROM filters where name=\"" . $name . "\" and observer=\"" . $observer . "\"", 'id', - 1 );
	}
	public function getFilterObserverPropertyFromName($name, $observer, $property) // returns the property for the filter of the observer
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM filters where name=\"" . $name . "\" and observer=\"" . $observer . "\"", $property );
	}
	public function getFilterPropertiesFromId($id) // returns the properties of the filters with id
{
		global $objDatabase;
		return $objDatabase->selectRecordArray ( "SELECT * FROM filters WHERE id=\"" . $id . "\"" );
	}
	public function getFilterPropertyFromId($id, $property, $defaultValue = '') // returns the property of the given filter
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM filters WHERE id = \"" . $id . "\"", $property, $defaultValue );
	}
	public function getFilterUsedFromId($id) // returns the number of times the eyepiece is used in observations
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT count(id) as ObsCnt FROM observations WHERE filterid=\"" . $id . "\"", 'ObsCnt', 0 );
	}
	public function getSortedFilters($sort, $observer = "", $active = '') // returns an array with the ids of all filters, sorted by the column specified in $sort
{
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT id, name FROM filters " . ($observer ? "WHERE observer LIKE \"" . $observer . "\" " . ($active ? " AND filteractive=" . $active : '') : " GROUP BY name") . " ORDER BY " . $sort . ", name", 'id' );
	}
	public function setFilterProperty($id, $property, $propertyValue) // sets the property to the specified value for the given filter
{
		global $objDatabase;
		return $objDatabase->execSQL ( "UPDATE filters SET " . $property . " = \"" . $propertyValue . "\" WHERE id = \"" . $id . "\"" );
	}
	public function showFiltersObserver() {
		global $baseURL, $loggedUser, $objUtil, $objFilter, $objPresentations, $loggedUserName;
		$filts = $objFilter->getSortedFilters ( 'id', $loggedUser );
		if (count ( $filts ) > 0) { // Add the button to select which columns to show
			$objUtil->addTableColumSelector ();

			echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
			echo "<thead><tr>";
			echo "<th>" . _("Active") . "</th>";
			echo "<th data-priority=\"critical\">" . _("Name") . "</th>";
			echo "<th>" . _("Type") . "</th>";
			echo "<th>" . _("Color") . "</th>";
			echo "<th>" . _("Wratten number") . "</th>";
			echo "<th>" . _("Schott number") . "</th>";
			echo "<th>" . _("Delete") . "</th>";
			echo "<th>" . _("Number of observations") . "</th>";
			echo "</tr></thead>";
			$count=0;
			while ( list ( $key, $value ) = each ( $filts ) ) {
				$filterProperties = $objFilter->getFilterPropertiesFromId ( $value );
				echo "<tr>";
						
				echo "<td>" . "<span class=\"hidden\">" . $filterProperties ['filteractive'] . "</span><input id=\"filteractive" . $value . "\" type=\"checkbox\" " . ($filterProperties ['filteractive'] ? " checked=\"checked\" " : "") . " onclick=\"setactivation('filter'," . $value . ");var order = this.checked ? '1' : '0'; $(this).prev().html(order);$(this).parents('table').trigger('update');\" />" . "</td>";
				echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_filter&amp;filter=" . urlencode ( $value ) . "\">" . stripslashes ( $filterProperties ['name'] ) . "</a></td>";
				echo "<td>" . $objFilter->getEchoType ( $filterProperties ['type'] ) . "</td>";
				echo "<td>" . $objFilter->getEchoColor ( $filterProperties ['color'] ) . "</td>";
				echo "<td>" . ($filterProperties ['wratten'] ? $filterProperties ['wratten'] : "-") . "</td>";
				echo "<td>" . ($filterProperties ['schott'] ? $filterProperties ['schott'] : "-") . "</td>";
				// Make it possible to delete the lenses
				echo "<td>";
				if (! ($obsCnt = $objFilter->getFilterUsedFromId ( $value ))) {
					echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode ( $value ) . "\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></a>";
				}
				echo "</td>";
				// Show the number of observations for this lens.
				echo "<td>";
				echo "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . $loggedUser . "&amp;filter=" . $value . "&amp;exactinstrumentlocation=true\">";
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
	public function validateDeleteFilter() // validates and deletes a filter
{
		global $objUtil, $objDatabase;
		if (($filterid = $objUtil->checkGetKey ( 'filterid' )) && $objUtil->checkAdminOrUserID ( $this->getFilterPropertyFromId ( $filterid, 'observer' ) ) && (! ($this->getFilterUsedFromId ( $filterid )))) {
			$objDatabase->execSQL("DELETE FROM filters WHERE id=\"" . $filterid . "\"");
			return _("The filter is removed from your equipment list");
		}
	}
	public function validateSaveFilter() // validates and saves a filter and returns a message
{
		global $objUtil, $loggedUser;
		if ($objUtil->checkPostKey ( 'add' ) && $objUtil->checkSessionKey ( 'deepskylog_id' ) && $objUtil->checkPostKey ( 'filtername' )) {
			$id = $this->addFilter ( $objUtil->checkPostKey ( 'filtername' ), $objUtil->checkPostKey ( 'type' ), $objUtil->checkPostKey ( 'color', 0 ), $objUtil->checkPostKey ( 'wratten' ), $objUtil->checkPostKey ( 'schott' ) );
			$this->setFilterProperty ( $id, 'observer', $loggedUser );
			return _("The filter is added to your equipment list");
		} elseif ($objUtil->checkPostKey ( 'change' ) && $objUtil->checkPostKey ( 'id' ) && $objUtil->checkPostKey ( 'filtername' ) && $objUtil->checkAdminOrUserID ( $this->getFilterPropertyFromId ( $_POST ['id'], 'observer' ) )) {
			$this->setFilterProperty ( $_POST ['id'], 'name', $objUtil->checkPostKey ( 'filtername' ) );
			$this->setFilterProperty ( $_POST ['id'], 'type', $objUtil->checkPostKey ( 'type' ) );
			$this->setFilterProperty ( $_POST ['id'], 'color', $objUtil->checkPostKey ( 'color', 0 ) );
			$this->setFilterProperty ( $_POST ['id'], 'wratten', $objUtil->checkPostKey ( 'wratten' ) );
			$this->setFilterProperty ( $_POST ['id'], 'schott', $objUtil->checkPostKey ( 'schott' ) );
			// $this->setFilterProperty($_POST['id'], 'observer', $loggedUser);
			return _("The filter is changed in your equipment list");
		} else
			return _("All required fields must be filled in!");
	}
}
?>
