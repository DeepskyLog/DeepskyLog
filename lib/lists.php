<?php

// lists.php
// code for maintance of lists
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}
class Lists
{
    // Returns the observing_lists.id for the given list name and observer username.
    private function getListId($listName, $observerId)
    {
        global $objDatabase;
        return (int)$objDatabase->selectSingleValue(
            "SELECT ol.id FROM observing_lists ol JOIN users u ON u.id = ol.owner_user_id "
            . "WHERE ol.name = \"" . $listName . "\" AND u.username = \"" . $observerId . "\"",
            'id', 0
        );
    }

    // Returns the users.id for the given username.
    private function getOwnerUserId($username)
    {
        global $objDatabase;
        return (int)$objDatabase->selectSingleValue(
            "SELECT id FROM users WHERE username = \"" . $username . "\"",
            'id', 0
        );
    }

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
                    $description .= ') ' . $objPresentations->br2nl(htmlspecialchars($temp['description'], ENT_HTML5 | ENT_QUOTES));
                    $get3 = $objDatabase->selectRecordArray("SELECT description FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND listname = \"" . $listname . "\" AND objectname=\"" . $theobject . "\"");
                    if (strpos($get3 ['description'], $description) === false) {
                        $listIdAddObs = $this->getListId($listname, $loggedUser);
                        $newDescAddObs = substr((($get3 ['description']) ? ($get3 ['description'] . " ") : '') . $description, 0, 4096);
                        $objDatabase->execSQL("UPDATE observing_list_items SET item_description = \"" . addslashes($newDescAddObs) . "\" WHERE observing_list_id = " . $listIdAddObs . " AND object_name = \"" . $theobject . "\"");
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
            $listIdRemObs = $this->getListId($listname, $loggedUser);
            $sql = "UPDATE observing_list_items SET item_description = (SELECT description FROM objects WHERE objects.name = observing_list_items.object_name) WHERE observing_list_id = " . $listIdRemObs;
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
            $slugAddList = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . strtolower($loggedUser);
            $objDatabase->execSQL("INSERT INTO observing_lists (owner_user_id, name, slug, description, public, created_at, updated_at) SELECT id, \"" . $name . "\", \"" . $slugAddList . "\", '', " . $public . ", NOW(), NOW() FROM users WHERE username = \"" . $loggedUser . "\"");
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
        $listIdAddObj = $this->getListId($listname, $loggedUser);
        if ($listIdAddObj && !$objDatabase->selectSingleValue("SELECT id FROM observing_list_items WHERE observing_list_id = " . $listIdAddObj . " AND object_name = \"" . $name . "\"", 'id', 0)) {
            $nextPlaceAddObj = (int)$objDatabase->selectSingleValue("SELECT COALESCE(MAX(sort_order), 0) + 1 AS np FROM observing_list_items WHERE observing_list_id = " . $listIdAddObj, 'np', 1);
            $descAddObj = addslashes((string)$objDatabase->selectSingleValue("SELECT description FROM objects WHERE name = \"" . $name . "\"", 'description', ''));
            $userIdAddObj = $this->getOwnerUserId($loggedUser);
            $objDatabase->execSQL("INSERT INTO observing_list_items (observing_list_id, object_name, item_description, sort_order, added_by_user_id, created_at, updated_at) VALUES (" . $listIdAddObj . ", \"" . $name . "\", \"" . $descAddObj . "\", " . $nextPlaceAddObj . ", " . $userIdAddObj . ", NOW(), NOW())");
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
            $listIdAddObsToList = $this->getListId($listname, $loggedUser);
            $get = $listIdAddObsToList ? $objDatabase->selectRecordArray("SELECT sort_order AS ObjPl, item_description AS description FROM observing_list_items WHERE observing_list_id = " . $listIdAddObsToList . " AND object_name = \"" . $name . "\"") : null;
            if (!$get) {
                if ($listIdAddObsToList) {
                    $nextPlaceAddObsToList = (int)$objDatabase->selectSingleValue("SELECT COALESCE(MAX(sort_order), 0) + 1 AS np FROM observing_list_items WHERE observing_list_id = " . $listIdAddObsToList, 'np', 1);
                    $tempDescAddObsToList = (string)$objDatabase->selectSingleValue("SELECT description FROM objects WHERE name = \"" . $name . "\"", 'description', '');
                    $fullDescAddObsToList = addslashes(substr(($tempDescAddObsToList ? ($tempDescAddObsToList . ' \n') : '') . $description, 0, 1024));
                    $userIdAddObsToList = $this->getOwnerUserId($loggedUser);
                    $objDatabase->execSQL("INSERT INTO observing_list_items (observing_list_id, object_name, item_description, sort_order, added_by_user_id, created_at, updated_at) VALUES (" . $listIdAddObsToList . ", \"" . $name . "\", \"" . $fullDescAddObsToList . "\", " . $nextPlaceAddObsToList . ", " . $userIdAddObsToList . ", NOW(), NOW())");
                }
            } else {
                $newDescAddObsToList = addslashes(substr((($get ['description']) ? ($get ['description'] . " ") : '') . $description, 0, 1024));
                $objDatabase->execSQL("UPDATE observing_list_items SET item_description = \"" . $newDescAddObsToList . "\" WHERE observing_list_id = " . $listIdAddObsToList . " AND object_name = \"" . $name . "\"");
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
            $listIdEmptyList = $this->getListId($listname, $loggedUser);
            if ($listIdEmptyList) {
                $objDatabase->execSQL("DELETE FROM observing_list_items WHERE observing_list_id = " . $listIdEmptyList);
            }
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

        // Return both private lists for the user and public lists.
        $run = $objDatabase->selectRecordset("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE observerid=\"" . $loggedUser . "\" ORDER BY observerobjectlist.listname");
        $get = $run->fetch(PDO::FETCH_OBJ);
        $result1 = array();
        if ($get) {
            while ($get) {
                $result1 [] = $get->listname;
                $get = $run->fetch(PDO::FETCH_OBJ);
            }
        }

        $run = $objDatabase->selectRecordset("SELECT DISTINCT observerobjectlist.listname FROM observerobjectlist WHERE public=\"1\" ORDER BY observerobjectlist.listname");
        $get = $run->fetch(PDO::FETCH_OBJ);
        $result2 = array();
        if ($get) {
            while ($get) {
                $result2 [] = $get->listname;
                $get = $run->fetch(PDO::FETCH_OBJ);
            }
        }
        $result = array_merge($result1, $result2);

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
        $results = array();

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
            $objDatabase->execSQL("UPDATE observing_lists ol JOIN users u ON u.id = ol.owner_user_id SET ol.public = 0 WHERE ol.name = \"" . $listName . "\" AND u.username = \"" . $loggedUser . "\"");
        } else {
            // We first check if a public list with the same name already exists.
            $run = $objDatabase->selectRecordset("SELECT name AS listname FROM observing_lists WHERE name = \"" . $listName . "\" AND public = 1");
            $get = $run->fetch(PDO::FETCH_OBJ);
            if (!empty($get)) {
                $entryMessage = sprintf(_("A public list with the same name (%s) as your list already exists. Please rename your list before making the list public."), "<strong>" . $listName . "</strong>");
                return;
            }
            $objDatabase->execSQL("UPDATE observing_lists ol JOIN users u ON u.id = ol.owner_user_id SET ol.public = 1 WHERE ol.name = \"" . $listName . "\" AND u.username = \"" . $loggedUser . "\"");

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
            $listIdObjDown = $this->getListId($listname, $loggedUser);
            if ($listIdObjDown) {
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = -1 WHERE observing_list_id = " . $listIdObjDown . " AND sort_order = " . $place);
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . $place . " WHERE observing_list_id = " . $listIdObjDown . " AND sort_order = " . ($place - 1));
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . ($place - 1) . " WHERE observing_list_id = " . $listIdObjDown . " AND sort_order = -1");
            }
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
        $listIdFromTo = $this->getListId($listname, $loggedUser);
        $max = $listIdFromTo ? (int)$objDatabase->selectSingleValue("SELECT MAX(sort_order) AS ObjPl FROM observing_list_items WHERE observing_list_id = " . $listIdFromTo, 'ObjPl', 0) : 0;
        if ($listIdFromTo && ($from > 0) && ($from <= $max) && ($to > 0) && ($to <= $max) && ($from != $to)) {
            if ($from < $to) {
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = -1 WHERE observing_list_id = " . $listIdFromTo . " AND sort_order = " . $from);
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = sort_order - 1 WHERE observing_list_id = " . $listIdFromTo . " AND sort_order > " . $from . " AND sort_order <= " . $to);
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . $to . " WHERE observing_list_id = " . $listIdFromTo . " AND sort_order = -1");
            } else {
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = -1 WHERE observing_list_id = " . $listIdFromTo . " AND sort_order = " . $from);
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = sort_order + 1 WHERE observing_list_id = " . $listIdFromTo . " AND sort_order >= " . $to . " AND sort_order < " . $from);
                $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . $to . " WHERE observing_list_id = " . $listIdFromTo . " AND sort_order = -1");
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
        $listIdObjUp = $this->getListId($listname, $loggedUser);
        if ($listIdObjUp && $place < (int)$objDatabase->selectSingleValue("SELECT MAX(sort_order) AS ObjPl FROM observing_list_items WHERE observing_list_id = " . $listIdObjUp, 'ObjPl', 0)) {
            $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = -1 WHERE observing_list_id = " . $listIdObjUp . " AND sort_order = " . $place);
            $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . $place . " WHERE observing_list_id = " . $listIdObjUp . " AND sort_order = " . ($place + 1));
            $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = " . ($place + 1) . " WHERE observing_list_id = " . $listIdObjUp . " AND sort_order = -1");
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
    public function removeList($name)
    {
        global $objDatabase, $loggedUser, $myList;
        if ($loggedUser) {
            $objDatabase->execSQL("DELETE ol FROM observing_lists ol JOIN users u ON u.id = ol.owner_user_id WHERE ol.name = \"" . $name . "\" AND u.username = \"" . $loggedUser . "\"");
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
        $listIdRemObj = $this->getListId($listname, $loggedUser);
        if ($listIdRemObj && ($place = (int)$objDatabase->selectSingleValue("SELECT sort_order FROM observing_list_items WHERE observing_list_id = " . $listIdRemObj . " AND object_name = \"" . $name . "\"", 'sort_order', 0))) {
            $objDatabase->execSQL("DELETE FROM observing_list_items WHERE observing_list_id = " . $listIdRemObj . " AND object_name = \"" . $name . "\"");
            $objDatabase->execSQL("UPDATE observing_list_items SET sort_order = sort_order - 1 WHERE observing_list_id = " . $listIdRemObj . " AND sort_order > " . $place);
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
            $newSlugRename = strtolower(preg_replace('/[^a-z0-9]+/', '-', $nameTo)) . '-' . strtolower($loggedUser);
            if ($newPublic) {
                $public = 1;
            } else {
                $public = 0;
            }
            $objDatabase->execSQL("UPDATE observing_lists ol JOIN users u ON u.id = ol.owner_user_id SET ol.name = \"" . $nameTo . "\", ol.slug = \"" . $newSlugRename . "\", ol.public = " . $public . " WHERE ol.name = \"" . $nameFrom . "\" AND u.username = \"" . $loggedUser . "\"");
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
        $listIdSetDesc = $this->getListId($listname, $loggedUser);
        if ($listIdSetDesc) {
            $objDatabase->execSQL("UPDATE observing_list_items SET item_description = \"" . addslashes($description) . "\" WHERE observing_list_id = " . $listIdSetDesc . " AND object_name = \"" . $object . "\"");
        }
        if (array_key_exists('QobjParams', $_SESSION) && array_key_exists('source', $_SESSION ['QobjParams']) && ($_SESSION ['QobjParams'] ['source'] == 'tolist')) {
            unset($_SESSION ['QobjParams']);
        }
    }
}
