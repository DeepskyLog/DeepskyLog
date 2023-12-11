<?php

// lists.php
// code for maintance of lists
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}
class Lists
{
    public function addObservations($thetype)
    {
        global $entryMessage, $myList, $objObject, $objDatabase, $loggedUser, $listname, $objPresentations;
        if (!$myList) {
            return;
        }
        if ($thetype == "longest") {
            $sql = "SELECT objectname FROM observerobjectlist " . "WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname <>\"\"";
            $run = $objDatabase->selectSingleArray($sql, 'objectname');
            for($i = 0; $i < count($run); $i++) {
                $theobject = $run [$i];
                $sql = "SELECT observations.id, observations.description FROM observations WHERE observations.objectname=\"" . $theobject . "\";";
                $get2 = $objDatabase->selectRecordsetArray($sql);
                $sortarray = array();
                foreach ($get2 as $key => $value) {
                    $sortarray [strlen($value ['description'])] = $value ['id'];
                }
                if (count($sortarray) > 0) {
                    ksort($sortarray, SORT_NUMERIC);
                    $temp = array_pop($sortarray);
                    $sql = "SELECT observations.objectname, observations.description, observers.name, observers.firstname, locations.name as location, instruments.name AS instrument " . "FROM observations " . "JOIN observers ON observations.observerid=observers.id " . "JOIN locations ON observations.locationid=locations.id " . "JOIN instruments ON observations.instrumentid=instruments.id " . "WHERE observations.id=" . $temp;
                    $temp = $objDatabase->selectRecordArray($sql);
                    $name = $temp ['objectname'];
                    $description = '(' . $temp ['firstname'] . ' ' . $temp ['name'];
                    $description .= '/' . $temp ['instrument'];
                    $description .= '/' . $temp ['location'];
                    $description .= ') ' . $objPresentations->br2nl($temp ['description']);
                    $get3 = $objDatabase->selectRecordArray("SELECT description FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $theobject . "\"");
                    if (strpos($get3 ['description'], $description) === false) {
                        $objDatabase->execSQL("UPDATE observerobjectlist SET description = \"" . substr((($get3 ['description']) ? ($get3 ['description'] . " ") : '') . $description, 0, 4096) . "\" WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $theobject . "\"");
                    }
                }
            }
            $entryMessage .= _("Observations added (longest)");
        }
    }
    public function removeObservations($thetype)
    {
        global $entryMessage, $myList, $objObject, $objDatabase, $loggedUser, $listname, $objPresentations;
        if (!$myList) {
            return;
        }
        if ($thetype == "all") {
            $sql = "UPDATE observerobjectlist " . "SET description = (SELECT objects.description FROM objects WHERE objects.name=observerobjectlist.objectname) " . "WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname <>\"\"";
            $run = $objDatabase->execSQL($sql);
            $entryMessage .= _("Observations removed");
        }
    }
    public function addList($name, $isPublic)
    {
        global $objDatabase, $objUtil, $loggedUser, $objObserver, $objMessages, $baseURL;
        if ($loggedUser && $name && (!($this->checkList($name)))) { // Send mail when we are creating a public list
            if ($isPublic) {
                $username = $objObserver->getObserverProperty($loggedUser, "firstname") . " " . $objObserver->getObserverProperty($loggedUser, "name");

                $subject = sprintf(
                    _('Public list created with name %s by %s'),
                    $name,
                    $username
                );
                $message = _('A new public list is available in DeepskyLog.') . '<br /><br />';
                $message = $message . _('Go to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . urlencode($name) . "\">" . $name . "</a><br /><br />";
                $message = $message . _('Send message to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=new_message&amp;receiver=" . urlencode($loggedUser) . "&amp;subject=Re:%20" . urlencode($name) . "\">" . $username . "</a>";
                $public = 1;
                $objMessages->sendMessage("DeepskyLog", "all", $subject, $message);
            } else {
                $public = 0;
            }
            $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, public) VALUES (\"" . $loggedUser . "\", \"\", \"" . $name . "\", '0', \"\", \"" . $public . "\")");
            if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
                unset($_SESSION ['QobjParams']);
            }
        }
    }
    public function addObjectToList($name, $showname = '')
    {
        global $loggedUser, $listname, $objDatabase, $myList;
        if (!$myList) {
            return;
        }
        if (!$showname) {
            $showname = $name;
        }
        if (!($objDatabase->selectSingleValue("SELECT objectplace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"", 'objectplace', 0))) {
            if ($this->isPublic($listname, $loggedUser)) {
                $public = 1;
            } else {
                $public = 0;
            }
            $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description, public) VALUES (\"" . $loggedUser . "\", \"$name\", \"$listname\", \"" . (($objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPlace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"$listname\"", 'ObjPlace', 0)) + 1) . "\", \"$showname\", \"" . $objDatabase->selectSingleValue("SELECT description FROM objects WHERE name=\"" . $name . "\"", 'description') . "\", \"" . $public . "\")");
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function addObservationToList($id)
    {
        global $objDatabase, $loggedUser, $listname, $myList, $objPresentations;
        $sql = "SELECT observations.objectname, observations.description, observers.name, observers.firstname, locations.name as location, instruments.name AS instrument " . "FROM observations " . "JOIN observers ON observations.observerid=observers.id " . "JOIN locations ON observations.locationid=locations.id " . "JOIN instruments ON observations.instrumentid=instruments.id " . "WHERE observations.id=" . $id;
        $get = $objDatabase->selectRecordArray($sql);
        if ($get) {
            $name = $get ['objectname'];
            $description = '(' . $get ['firstname'] . ' ' . $get ['name'];
            $description .= '/' . $get ['instrument'];
            $description .= '/' . $get ['location'];
            $description .= ') ' . $objPresentations->br2nl($get ['description']);
            $get = $objDatabase->selectRecordArray("SELECT objectplace AS ObjPl, description FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"");
            if (!$get) {
                $objDatabase->execSQL("INSERT INTO observerobjectlist(observerid, objectname, listname, objectplace, objectshowname, description) " . "VALUES (\"" . $loggedUser . "\", \"" . $name . "\", \"" . $listname . "\"," . " \"" . (($objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\"", 'ObjPl', 0)) + 1) . "\", " . "\"" . $name . "\", \"" . substr((($tempDescription = $objDatabase->selectSingleValue("SELECT description FROM objects WHERE name=\"" . $name . "\"", 'description')) ? ($tempDescription . ' \n') : '') . $description, 0, 1024) . "\")");
            } else {
                $objDatabase->execSQL("UPDATE observerobjectlist SET description = \"" . substr((($get ['description']) ? ($get ['description'] . " ") : '') . $description, 0, 1024) . "\" WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $name . "\"");
            }
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function checkList($name)
    {
        global $loggedUser, $objDatabase;
        $retval = 0;
        if ($this->isPublic($name, $loggedUser)) {
            $sql = "SELECT listname FROM observerobjectlist WHERE listname=\"" . $name . "\"";
            $run = $objDatabase->selectRecordset($sql);
            if ($get = $run->fetch(PDO::FETCH_OBJ)) {
                $retval = 1;
            }
        }
        if ($loggedUser) {
            $sql = "SELECT listname FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $name . "\"";
            $run = $objDatabase->selectRecordset($sql);
            if ($get = $run->fetch(PDO::FETCH_OBJ)) {
                $retval = 2;
            }
        }
        return $retval;
    }
    public function checkObjectInMyActiveList($value)
    {
        global $objDatabase, $loggedUser, $listname;
        return $objDatabase->selectSingleValue("SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND objectname=\"" . $value . "\" AND listname=\"" . $listname . "\"", 'objectplace', 0);
    }
    public function checkObjectMyOrPublicList($value, $list)
    {
        global $objDatabase, $loggedUser;
        return $objDatabase->selectSingleValue("SELECT observerobjectlist.objectplace FROM observerobjectlist WHERE " . ($this->isPublic($list, $loggedUser) ? "" : ("observerid = \"" . $loggedUser . "\" AND ")) . "objectname=\"" . $value . "\" AND listname=\"" . $list . "\"", 'objectplace', 0);
    }
    public function emptyList($listname)
    {
        global $objDatabase, $loggedUser, $myList;
        if ($loggedUser && $myList) {
            $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectplace<>0");
            if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
                unset($_SESSION ['QobjParams']);
            }
        }
    }
    public function getListObjectDescription($object)
    {
        global $loggedUser, $listname, $objDatabase;
        return $objDatabase->selectSingleValue("SELECT observerobjectlist.description FROM observerobjectlist WHERE " . ($this->isPublic($listname, $loggedUser) ? "" : "observerid = \"" . $loggedUser . "\" AND ") . "objectname=\"" . $object . "\" AND listname=\"" . $listname . "\"", 'description', '');
    }
    public function getListOwner()
    {
        global $listname, $objDatabase;
        return $objDatabase->selectSingleValue("SELECT observerobjectlist.observerid FROM observerobjectlist WHERE listname=\"" . $listname . "\" AND objectplace=0", 'observerid', '');
    }
    public function getListOwnerName($listname, $observerId)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue("SELECT observerobjectlist.observerid FROM observerobjectlist WHERE listname=\"" . $listname . "\" AND objectplace=0 AND observerid=\"" . $observerId . "\"", 'observerid', '');
    }
    public function getInPrivateLists($theobject)
    {
        global $objDatabase, $loggedUser;
        $result = '';
        $results = array();
        if ($loggedUser) {
            $sql = 'SELECT listname FROM observerobjectlist WHERE objectname="' . $theobject . '" AND observerid="' . $loggedUser . '"';
            $results = $objDatabase->selectSingleArray($sql, 'listname');
            foreach ($results as $key => $value) {
                $result .= "/" . $value;
            }
        }
        return substr($result, 1);
    }
    public function getInPublicLists($theobject)
    {
        global $objDatabase, $loggedUser;
        $result = '';
        $results = array();
        $sql = 'SELECT listname FROM observerobjectlist WHERE objectname="' . $theobject . '" AND public = "1"';
        $results = $objDatabase->selectSingleArray($sql, 'listname');
        foreach ($results as $key => $value) {
            $result .= "/" . $value;
        }
        return substr($result, 1);
    }
    public function getLists()
    {
        global $objDatabase, $loggedUser;
        $result = array();

        if (array_key_exists('deepskylog_id', $_SESSION)) {
            $run = $objDatabase->selectRecordset("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" ORDER BY observerobjectlist.listname");
            $get = $run->fetch(PDO::FETCH_OBJ);
            $result1 = array();
            if ($get) {
                while ($get) {
                    $result1 [] = $get->listname;
                    $get = $run->fetch(PDO::FETCH_OBJ);
                }
            }

            $run = $objDatabase->selectRecordset("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE public=\"1%\" ORDER BY observerobjectlist.listname");
            $get = $run->fetch(PDO::FETCH_OBJ);
            $result2 = array();
            if ($get) {
                while ($get) {
                    $result2 [] = $get->listname;
                    $get = $run->fetch(PDO::FETCH_OBJ);
                }
            }
            $result = array_merge($result1, $result2);
        }

        return $result;
    }
    public function getMyLists()
    {
        global $loggedUser, $objDatabase;
        return $objDatabase->selectSingleArray("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\"", 'listname');
    }
    public function showLists($public = false)
    {
        global $objUtil, $baseURL, $loggedUser;

        // Get all the lists of the observer
        $lists = $this->getMyLists();

        if ($public) {
            foreach ($lists as $list) {
                // Only add the public lists to the results
                if ($this->isPublic($list, $loggedUser)) {
                    $results [] = $list;
                }
            }
        } else {
            foreach ($lists as $list) {
                // Only add the private lists to the results
                if (!$this->isPublic($list, $loggedUser)) {
                    $results [] = $list;
                }
            }
        }

        $tablename = "obslist";
        if ($public) {
            $tablename .= "pub";
        }

        echo "<table class=\"table sort-table" . $tablename . " table-condensed table-striped table-hover tablesorter custom-popup\">";
        echo "<thead>";
        echo "<tr><th>";
        echo _("Name");
        echo "</th>";
        echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">";
        echo _("Change name");
        echo "</th>";
        echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">";
        if ($public) {
            echo _("Make private");
        } else {
            echo _("Make public");
        }
        echo "</th>";
        echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">";
        echo _("Remove the list");
        echo "</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        $count = 0;

        if ($public) {
            $pub = 1;
        } else {
            $pub = 0;
        }

        foreach ($results as $listname) {
            if ($listname != "") {
                echo "<tr>";
                echo "<td>";

                // Add a link to see and activate the list.
                echo "<a href=\"" . $baseURL . "index.php?indexAction=listaction&amp;activateList=true&amp;public=" . $pub . "&amp;listname=" . $listname . "\">";

                echo $listname;

                echo "</a>";

                echo "</td>";

                // Add a button to change the name.
                echo "<td style=\"vertical-align: middle\">";

                echo "<button type=\"button\" title=\"" . _("Change name") . "\" class=\"btn btn-default\" data-toggle=\"modal\" data-target=\"#changeListName" . str_replace(' ', '_', str_replace(':', '_', $listname)) . "\" >
                       <span class=\"glyphicon glyphicon-pencil\"></span>
                      </button>";

                echo "</td>";

                // Add a button to make Public / private
                echo "<td style=\"vertical-align: middle\">";

                if ($public) {
                    echo "<a title=\"" . _("Make private") . "\" class=\"btn btn-default\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;switchPublicPrivate=switchPublicPrivate&amp;listname=" . $listname . "\">
                       <span class=\"glyphicon glyphicon-user\"></span>
                      </a>";
                } else {
                    echo "<a title=\"" . _("Make public") . "\" class=\"btn btn-default\"  href=\"" . $baseURL . "index.php?indexAction=listaction&amp;switchPublicPrivate=switchPublicPrivate&amp;listname=" . $listname . "\">
                       <span class=\"glyphicon glyphicon-share\"></span>
                      </a>";
                }

                echo "</td>";

                // Add a button to remove the list.
                echo "<td style=\"vertical-align: middle\">";
                echo "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=listaction&amp;removeList=removeList&amp;listname=" . $listname . "\"><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></a>  ";
                echo "</td>";

                echo "</tr>";
                $count++;
            }
        }

        echo "</tbody>";
        echo "</table>";

        $objUtil->addPager($tablename, $count);

        foreach ($results as $listname) {
            if ($listname != "") {
                echo "<div class=\"modal fade\" id=\"changeListName" . str_replace(' ', '_', str_replace(':', '_', $listname)) . "\">
                       <div class=\"modal-dialog\">
                        <div class=\"modal-content\">
                         <div class=\"modal-header\">
                          <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
                          <h4 class=\"modal-title\">" . _("Change name") . "</h4>
                         </div>
                         <div class=\"modal-body\">
                          <!-- Ask for the new name of the list. -->
                          <h1 class=\"text-center login-title\">" . _("New name for the observing list") . "</h1>
                          <form action=\"" . $baseURL . "index.php?indexAction=listaction\">
                           <input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />
                           <input type=\"hidden\" name=\"listnamefrom\" value=\"" . $listname . "\" />";
                if ($this->isPublic($listname, $loggedUser)) {
                    $publicList = true;
                } else {
                    $publicList = false;
                }
                $listToPrint = $listname;
                echo "     <input type=\"text\" name=\"addlistname\" class=\"form-control\" required autofocus value=\"" . $listToPrint . "\">
                           <br /><br />
                           <input type=\"checkbox\" ";
                if ($publicList) {
                    echo "checked ";
                }
                echo "    name=\"PublicList\" value=\"1\" />&nbsp;" . _("Make this list a public list") . "
                          </div>
                          <div class=\"modal-footer\">
                           <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
                           <input class=\"btn btn-success\" type=\"submit\" name=\"renameList\" value=\"" . _("Rename") . "\" /></button>
   		                  </form>
                         </div>
                        </div><!-- /.modal-content -->
                       </div><!-- /.modal-dialog -->
                      </div><!-- /.modal -->";
            }
        }
    }
    public function switchPublicPrivate($listName)
    {
        global $objDatabase, $objMessages, $objObserver, $loggedUser, $entryMessage;

        $public = $this->isPublic($listName, $loggedUser);
        if ($public) {
            $objDatabase->execSQL("UPDATE observerobjectlist set public=\"0\" where listname=\"" . $listName . "\" AND observerid = \"" . $loggedUser . "\"");
        } else {
            // We first check if a public list with the same name already exists.
            $run = $objDatabase->selectRecordset("SELECT listname FROM observerobjectlist WHERE listname=\"" . $listName . "\" AND public=\"1\"");
            $get = $run->fetch(PDO::FETCH_OBJ);
            if (!empty($get)) {
                $entryMessage = sprintf(_("A public list with the same name (%s) as your list already exists. Please rename your list before making the list public."), "<strong>" . $listName . "</strong>");
                return;
            }
            $objDatabase->execSQL("UPDATE observerobjectlist set public=\"1\" where listname=\"" . $listName . "\" AND observerid = \"" . $loggedUser . "\"");

            $username = $objObserver->getObserverProperty($loggedUser, "firstname") . " " . $objObserver->getObserverProperty($loggedUser, "name");
            $subject = sprintf(
                _('Public list created with name %s by %s'),
                $listName,
                $username
            );
            $message = _('A new public list is available in DeepskyLog.') . '<br /><br />';
            $message = $message . _('Go to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . urlencode($listName) . "\">" . $listName . "</a><br /><br />";
            $message = $message . _('Send message to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=new_message&amp;receiver=" . urlencode($loggedUser) . "&amp;subject=Re:%20" . urlencode($listName) . "\">" . $username . "</a>";

            $objMessages->sendMessage("DeepskyLog", "all", $subject, $message);
        }
    }
    public function getObjectsFromList($theListname, $pub)
    {
        global $objObject, $objDatabase, $loggedUser;
        $obs = array();
        if ($pub == 1) {
            $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " . "JOIN objects ON observerobjectlist.objectname = objects.name " . "WHERE listname = \"" . $theListname . "\" AND objectname <>\"\" AND public=\"1\"";
        } else {
            $sql = "SELECT observerobjectlist.objectname, observerobjectlist.objectplace, observerobjectlist.objectshowname, observerobjectlist.description FROM observerobjectlist " . "JOIN objects ON observerobjectlist.objectname = objects.name " . "WHERE listname = \"" . $theListname . "\" AND objectname <>\"\" AND observerobjectlist.observerid=\"" . $loggedUser . "\"";
        }
        $run = $objDatabase->selectRecordset($sql);
        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            if (!in_array($get->objectname, $obs)) {
                $obs [$get->objectshowname] = array(
                        $get->objectplace,
                        $get->objectname,
                        $get->description
                );
            }
        }
        return $objObject->getSeenObjectDetails($obs, "A");
    }
    public function ObjectDownInList($place)
    {
        global $loggedUser, $listname, $objDatabase, $myList;
        if (!$myList) {
            return;
        }
        if ($place && ($place > 1)) {
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE observerid = \"" . $loggedUser . "\" AND listname =\"" . $listname . "\" AND objectplace=" . $place);
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . ($place - 1));
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=" . ($place - 1) . " WHERE observerid=\"" . $loggedUser . "\" AND listname =\"" . $listname . "\" AND objectplace=-1");
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function ObjectFromToInList($from, $to)
    {
        global $loggedUser, $listname, $objDatabase, $myList;
        if (!($myList)) {
            return '';
        }
        $max = $objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\"", 'ObjPl');
        if (($from > 0) && ($from <= $max) && ($to > 0) && ($to <= $max) && ($from != $to)) {
            if ($from < $to) {
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=" . $from . "))");
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace>" . $from . ") AND (objectplace<=" . $to . "))");
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=" . $to . " WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=-1))");
            } else {
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=" . $from . "))");
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace+1 WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace>=" . $to . ") AND (objectplace<" . $from . "))");
                $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=" . $to . " WHERE ((observerid=\"" . $loggedUser . "\") AND (listname=\"" . $listname . "\") AND (objectplace=-1))");
            }
            if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
                unset($_SESSION ['QobjParams']);
            }
            return sprintf(_("The object has been moved to place %s."), $_GET['ObjectToPlaceInList']);
        } else {
            return '';
        }
    }
    public function ObjectUpInList($place)
    {
        global $loggedUser, $listname, $objDatabase, $myList;
        if (!$myList) {
            return;
        }
        if ($place < $objDatabase->selectSingleValue("SELECT MAX(objectplace) AS ObjPl FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\"", 'ObjPl')) {
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=-1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . $place);
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=" . ($place + 1));
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=" . ($place + 1) . " WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace=-1");
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function removeList($name)
    {
        global $objDatabase, $loggedUser, $myList;
        if ($loggedUser) {
            $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $name . "\"");
            if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
                unset($_SESSION ['QobjParams']);
            }
        }
    }
    public function removeObjectFromList($name)
    {
        global $loggedUser, $listname, $objDatabase, $myList;
        if (!$myList) {
            return;
        }
        if ($place = $objDatabase->selectSingleValue("SELECT objectplace AS ObjPl FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $name . "\"", 'ObjPl')) {
            $objDatabase->execSQL("DELETE FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectname=\"" . $name . "\"");
            $objDatabase->execSQL("UPDATE observerobjectlist SET objectplace=objectplace-1 WHERE observerid = \"" . $loggedUser . "\" AND listname=\"" . $listname . "\" AND objectplace>" . $place);
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function isPublic($listName, $user)
    {
        global $objDatabase;
        $sql = "SELECT public from observerobjectlist where listname=\"" . $listName . "\" AND public=\"1\" AND observerid=\"" . $user . "\";";
        return $objDatabase->selectSingleValue($sql, "public", 0);
    }
    public function renameList($nameFrom, $nameTo, $newPublic)
    {
        global $loggedUser, $objDatabase, $myList, $objMessages, $objObserver, $baseURL;
        $isMyList = $this->getListOwnerName($nameFrom, $loggedUser);
        if ($loggedUser && $myList) {
            // Send mail when we are creating a public list
            $pos = $newPublic;
            $posOld = $this->isPublic($nameFrom, $loggedUser);

            if ($posOld == false) {
                if ($pos) {
                    $username = $objObserver->getObserverProperty($loggedUser, "firstname") . " " . $objObserver->getObserverProperty($loggedUser, "name");
                    // Remove the public from the list
                    $listname = $nameTo;

                    $subject = sprintf(
                        _('Public list created with name %s by %s'),
                        $listname,
                        $username
                    );
                    $message = _('A new public list is available in DeepskyLog.') . '<br /><br />';
                    $message = $message . _('Go to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=listaction&amp;activateList=true&amp;listname=" . urlencode($listname) . "\">" . $listname . "</a><br /><br />";
                    $message = $message . _('Send message to ') . "<a href=\"http://www.deepskylog.org/index.php?indexAction=new_message&amp;receiver=" . urlencode($loggedUser) . "&amp;subject=Re:%20" . urlencode($listname) . "\">" . $username . "</a><br /><br />";

                    $objMessages->sendMessage("DeepskyLog", "all", $subject, $message);
                }
            }
            $objDatabase->execSQL("UPDATE observerobjectlist SET listname=\"" . $nameTo . "\" WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $nameFrom . "\"");

            if ($newPublic) {
                $public = 1;
            } else {
                $public = 0;
            }
            $objDatabase->execSQL("UPDATE observerobjectlist SET public=\"" . $public . "\" WHERE observerid=\"" . $loggedUser . "\" AND listname=\"" . $nameFrom . "\"");
            if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
                unset($_SESSION ['QobjParams']);
            }
        }
    }
    public function setListObjectDescription($object, $description)
    {
        global $objDatabase, $loggedUser, $listname, $myList;
        if (!($myList)) {
            return;
        }
        $objDatabase->execSQL("UPDATE observerobjectlist SET description=\"" . $description . "\" WHERE observerid=\"" . $loggedUser . "\" AND objectname=\"" . $object . "\" AND listname=\"" . $listname . "\"");
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
}
