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
			return FiltersColorLightRed;
		if ($color == FILTERCOLORRED)
			return FiltersColorRed;
		if ($color == FILTERCOLORDEEPRED)
			return FiltersColorDeepRed;
		if ($color == FILTERCOLORORANGE)
			return FiltersColorOrange;
		if ($color == FILTERCOLORLIGHTYELLOW)
			return FiltersColorLightYellow;
		if ($color == FILTERCOLORDEEPYELLOW)
			return FiltersColorDeepYellow;
		if ($color == FILTERCOLORYELLOW)
			return FiltersColorYellow;
		if ($color == FILTERCOLORYELLOWGREEN)
			return FiltersColorYellowGreen;
		if ($color == FILTERCOLORLIGHTGREEN)
			return FiltersColorLightGreen;
		if ($color == FILTERCOLORGREEN)
			return FiltersColorGreen;
		if ($color == FILTERCOLORMEDIUMBLUE)
			return FiltersColorMediumBlue;
		if ($color == FILTERCOLORPALEBLUE)
			return FiltersColorPaleBlue;
		if ($color == FILTERCOLORBLUE)
			return FiltersColorBlue;
		if ($color == FILTERCOLORDEEPBLUE)
			return FiltersColorDeepBlue;
		if ($color == FILTERCOLORDEEPVIOLET)
			return FiltersColorDeepViolet;
		return "-";
	}
	public function getEchoListColor($color, $disabled = "") {
		$tempColorList = "<select name=\"color\" class=\"form-control inputfield\" " . $disabled . " >";
		$tempColorList .= "<option value=\"\">&nbsp;</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTRED . "\">" . FiltersColorLightRed . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORRED . "\">" . FiltersColorRed . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPRED) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPRED . "\">" . FiltersColorDeepRed . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORORANGE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORORANGE . "\">" . FiltersColorOrange . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTYELLOW . "\">" . FiltersColorLightYellow . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPYELLOW . "\">" . FiltersColorDeepYellow . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORYELLOW) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORYELLOW . "\">" . FiltersColorYellow . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORYELLOWGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORYELLOWGREEN . "\">" . FiltersColorYellowGreen . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORLIGHTGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORLIGHTGREEN . "\">" . FiltersColorLightGreen . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORGREEN) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORGREEN . "\">" . FiltersColorGreen . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORMEDIUMBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORMEDIUMBLUE . "\">" . FiltersColorMediumBlue . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORPALEBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORPALEBLUE . "\">" . FiltersColorPaleBlue . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORBLUE . "\">" . FiltersColorBlue . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPBLUE) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPBLUE . "\">" . FiltersColorDeepBlue . "</option>";
		$tempColorList .= "<option " . (($color == FILTERCOLORDEEPVIOLET) ? "selected=\"selected\" " : "") . "value=\"" . FILTERCOLORDEEPVIOLET . "\">" . FiltersColorDeepViolet . "</option>";
		$tempColorList .= "</select>";
		return $tempColorList;
	}
	public function getEchoListType($type, $disabled = "") {
		$tempTypeList = "<select name=\"type\" class=\"form-control inputfield\" " . $disabled . " >";
		$tempTypeList .= "<option " . (($type == FILTEROTHER) ? " selected=\"selected\" " : "") . " value=\"" . FILTEROTHER . "\">" . FiltersOther . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERBROADBAND) ? " selected=\"selected\" " : "") . " value=\"" . FILTERBROADBAND . "\">" . FiltersBroadBand . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERNARROWBAND) ? " selected=\"selected\" " : "") . " value=\"" . FILTERNARROWBAND . "\">" . FiltersNarrowBand . "</option>";
		$tempTypeList .= "<option " . (($type == FILTEROIII) ? " selected=\"selected\" " : "") . " value=\"" . FILTEROIII . "\">" . FiltersOIII . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERHALPHA) ? " selected=\"selected\" " : "") . " value=\"" . FILTERHALPHA . "\">" . FiltersHAlpha . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERCOLOR) ? " selected=\"selected\" " : "") . " value=\"" . FILTERCOLOR . "\">" . FiltersColor . "</option>";
		$tempTypeList .= "<option " . (($type == FILTERCORRECTIVE) ? " selected=\"selected\" " : "") . " value=\"" . FILTERCORRECTIVE . "\">" . FiltersCorrective . "</option>";
		$tempTypeList .= "</select>";
		return $tempTypeList;
	}
	public function getEchoType($type) {
		if ($type == FILTEROTHER)
			return FiltersOther;
		if ($type == FILTERBROADBAND)
			return FiltersBroadBand;
		if ($type == FILTERNARROWBAND)
			return FiltersNarrowBand;
		if ($type == FILTEROIII)
			return FiltersOIII;
		if ($type == FILTERHBETA)
			return FiltersHBeta;
		if ($type == FILTERHALPHA)
			return FiltersHAlpha;
		if ($type == FILTERCOLOR)
			return FiltersColor;
		if ($type == FILTERNEUTRAL)
			return FiltersNeutral;
		if ($type == FILTERCORRECTIVE)
			return FiltersCorrective;
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
			echo "<th data-priority=\"critical\">" . LangViewFilterName . "</th>";
			echo "<th>" . LangViewFilterType . "</th>";
			echo "<th>" . LangViewFilterColor . "</th>";
			echo "<th>" . LangViewFilterWratten . "</th>";
			echo "<th>" . LangViewFilterSchott . "</th>";
			echo "<th>" . _("Delete") . "</th>";
			echo "<th>" . LangTopObserversHeader3 . "</th>";
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
					echo $obsCnt . ' ' . LangGeneralObservations . "</a>";
				} else {
					echo $obsCnt . ' ' . LangGeneralObservation . "</a>";
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
			$objDatabase->execSQL ( "DELETE FROM filters WHERE id=\"" . $filterid . "\"" );
			return LangValidateFilterMessage6;
		}
	}
	public function validateSaveFilter() // validates and saves a filter and returns a message
{
		global $objUtil, $loggedUser;
		if ($objUtil->checkPostKey ( 'add' ) && $objUtil->checkSessionKey ( 'deepskylog_id' ) && $objUtil->checkPostKey ( 'filtername' )) {
			$id = $this->addFilter ( $objUtil->checkPostKey ( 'filtername' ), $objUtil->checkPostKey ( 'type' ), $objUtil->checkPostKey ( 'color', 0 ), $objUtil->checkPostKey ( 'wratten' ), $objUtil->checkPostKey ( 'schott' ) );
			$this->setFilterProperty ( $id, 'observer', $loggedUser );
			return LangValidateFilterMessage2;
		} elseif ($objUtil->checkPostKey ( 'change' ) && $objUtil->checkPostKey ( 'id' ) && $objUtil->checkPostKey ( 'filtername' ) && $objUtil->checkAdminOrUserID ( $this->getFilterPropertyFromId ( $_POST ['id'], 'observer' ) )) {
			$this->setFilterProperty ( $_POST ['id'], 'name', $objUtil->checkPostKey ( 'filtername' ) );
			$this->setFilterProperty ( $_POST ['id'], 'type', $objUtil->checkPostKey ( 'type' ) );
			$this->setFilterProperty ( $_POST ['id'], 'color', $objUtil->checkPostKey ( 'color', 0 ) );
			$this->setFilterProperty ( $_POST ['id'], 'wratten', $objUtil->checkPostKey ( 'wratten' ) );
			$this->setFilterProperty ( $_POST ['id'], 'schott', $objUtil->checkPostKey ( 'schott' ) );
			// $this->setFilterProperty($_POST['id'], 'observer', $loggedUser);
			return LangValidateFilterMessage5;
		} else
			return _("All required fields must be filled in!");
	}
}
?>
