<?php
// version 3.1, DE 20061119

define("LangYouAreHere", "You are here:");
define("LangHome","Home");
define("LangBecomeMember", "Become member");
define("LangSearch","Search");
define("LangContact","Contact");
define("LangDeepskyLogModules","Deepskylog Modules");

define("LangValidateSubject", "DeepskyLog - account application approved");
define("LangValidateAdmin", "\nOne of the administrators made you a new administrator.\n");
define("LangValidateMail1", "Dear ");
define("LangValidateMail2", "\n\nYour application for the account of deepskylog is approved.\nYou can now log in using your userid and password.\n");
define("LangValidateMail3", "\nGreetings,\n\nThe deepskylog administrators");


define("LangObjectYSeen", "Object alreay observed by me");
define("LangObjectXSeen", "Object already observed by others, but not by me");
define("LangObjectNSeen", "Object not logged in Deepskylog");
define("LangBack", "Back");

// content/result_query_observations.php

define("LangResultPrevious", "Previous");
define("LangResultNext", "Next");

define("LangDetails", "Details");
define("LangDrawing", "drawing");

// content/confirm.php

define("LangRegisterTitle", "Confirmation registration");
define("LangRegisterNotify", "Thank you, your information has been sent to the administrator.<br />You will be notified by email, if your registration has been validated.");

// Google Maps 

define("LangGooglemaps", "Click on the map to view location in Google Maps");

// Tooltips

define("LangSortOn", "Sort on ");

//ToList
define("LangToListList", "The list \"");
define("LangToListActivation1", "\" is activated, click '");
define("LangToListActivation2", "' to continue as you were.");
define("LangToListExists", "\" already exists.");
define("LangToListAdded", "\" has been added.");
define("LangToListNotExists", "\" does not exist.");
define("LangToListAddNew", "New list: ");
define("LangToListAdd", "Add");
define("LangToListRename", "Rename");
define("LangToListPublic", "Public List");
define("LangToListEmpty", "Empty the List");
define("LangToListMyLists", "My lists: ");
define("LangToListMyListsView", "Consult");
define("LangToListMyListsRemove", "Remove the list");
define("LangToListRemovePageObjectsFromList", "Remove the objects from this page from the list.");
define("LangToListRemovePageObjectsFromListText", "R");
define("LangToListEmptyList", "The list is empty, there are no objects in the list.");
define("LangToListRemoved", "List removed: ");
define("LangToListEmptied", "List emptied: ");
define("LangToListMoved1", "The object ");
define("LangToListMoved2", " is moved down in the list ");
define("LangToListMoved3", " is moved up in the list ");
define("LangToListMoved4", "Move the object one place towards the front of the list.");
define("LangToListMoved5", "Move the object one place towards the back of the list.");
define("LangToListMoved6", "Move the object to a specific place in the list.");
define("LangToListMoved7", "The object has been moved to place ");
define("LangToListObjectRemoved", " wurde von der Liste entfernt ");
define("LangToListPageRemoved", "The objects have been removed from the list.");
define("LangToListRemoveObjectFromList", "Remove the object from the list.");
define("LangToListPopupTitle", "Move the object to place:");
define("LangToListImport","Import data");
define("LangInvalidCSVListFile", "Invalid list data.");
define("LangListImportError1", "You have to be logged in to import lists.");
define("LangListImportError2", "You have to select a list of your own if you want to import into it.");
define("LangCSVListError1","Er zijn fouten opgetreden bij het importeren.");
define("LangCSVListError2","Niet-gekende objecten:");

// List menu
define("LangListManage", "Manage/Consult");
define("LangListActivate", "Activate");
define("LangListOnlyMembers", "Lists are only available to the registered users.");

// Quickpick menu
define("LangQuickPickHelp", "Give the name of an object, with first the catalog, followed by a SPACE and the catalog number (eg: 'NGC&nbsp;7000', 'Mel 20' or also 'Ring&nbsp;Nebula').");
define("LangQuickPickTitle", "Quick&nbsp;Pick");
define("LangQuickPickSearchObject", "Search&nbsp;Object");
define("LangQuickPickSearchObservations", "Search&nbsp;Observations");
define("LangQuickPickNewObservation", "New&nbsp;Observation");
define("LangListsTitle", "Observing Lists");
define("LangListsAnnouncement", "In the next version, registered observers will be able to compose their own observing lists, as well as edit, save and print them, all of this based on the position of the object in the skies, the type of object, whether they already saw it or not, etc. When they see an observation of antoher user, they will be able to add the object of concern to their own lists.");
define("LangMailtoTitle", "Tell us!");
define("LangMailtoLink", "Use this <a href=\"mailto:&#100;&#101;v&#101lop&#101rs&#64&#100;&#101;&#101;pskylog.&#98;&#101;\">link </a> to ask questions or to make remarks.");

// deepsky/content/view_object
define("LangObjectNewName", "New name");
define("LangObjectNewAltName", "New Alt Name");
define("LangObjectRemoveAltNameName", "Remove Alt name");
define("LangObjectNewPartOf", "New Part Of");
define("LangObjectRemovePartOf", "Remove Part Of");
define("LangObjectRemoveAndReplaceObjectBy", "Remove the object and replace observations of it by object");
define("LangObjectSetRA", "Set RA (Hour Format: 1.50 for 1h30m)");
define("LangObjectSetDECL", "Set Decl (Degree Format: 25.50 for 25�30')");
define("LangObjectSetCon", "Set Constellation (Three letter capital abbrevation !)");
define("LangObjectSetType", "Set Type (Three five capital abbrevation !)");
define("LangObjectSetMag", "Set Magnitude");
define("LangObjectSetSUBR", "Set Surface Brightness");
define("LangObjectSetDiam1", "Set Diameter 1 (seconds !)");
define("LangObjectSetDiam2", "Set Diameter 2 (seconds !)");
define("LangObjectSetPA", "Set Position Angle");


// content/change_account.php

define("LangChangeAccountTitle", "Account details");
define("LangChangeAccountField1", "Username");
define("LangChangeAccountField1Expl", "This is how you will be known by other users");
define("LangChangeAccountField2", "Email address");
define("LangChangeAccountField2Expl", "Your email address will remain confidential");
define("LangChangeAccountField3", "First name");
define("LangChangeAccountField3Expl", "");
define("LangChangeAccountField4", "Name");
define("LangChangeAccountField4Expl", "");
define("LangChangeAccountField5", "Password");
define("LangChangeAccountField5Expl", "This is <em>not</em> your email account's password");
define("LangChangeAccountField6", "Confirm password");
define("LangChangeAccountField6Expl", "");
define("LangChangeAccountField7", "Default observing site");
define("LangChangeAccountField7Expl", "Add new observing site");
define("LangChangeAccountField8", "Default instrument");
define("LangChangeAccountField8Expl", "Add instrument");
define("LangChangeAccountField9", "Default atlas");
define("LangChangeAccountLanguage", "Default language");
define("LangChangeAccountLanguageExpl", "The language for DeepskyLog");
define("LangChangeAccountObservationLanguage", "Standard language for observations");
define("LangChangeAccountObservationLanguageExpl", "The standard language to enter the languages");
define("LangChangeVisibleLanguages", "Languages for observations");
define("LangChangeVisibleLanguagesExpl", "Define which languages you want to see in the descriptions");
define("LangChangeAccountPicture", "Picture");
define("LangChangeAccountButton", "Change");
define("LangChangeAccountField10", "ICQ observercode");
define("LangChangeAccountField10Expl", "<a href=\"http://cfa-www.harvard.edu/icq/icq.html\" target=\"_blank\">ICQ</a> code for comet observations");
define("LangChangeAccountField11", "Use local time");
define("LangChangeAccountField11Expl", "Use local time to enter and query observations");

define("LangLoginMenuTitle", "Login");
define("LangLoginMenuItem1", "Username");
define("LangLoginMenuItem2", "Password");
define("LangLoginMenuButton", "Log in");
define("LangLoginMenuRegister", "Register");

define("LangSearchMenuTitle", "View");
define("LangSearchMenuItem1", "My observations");
define("LangSearchMenuItem2", "All observations");
define("LangSearchMenuItem3", "Query observations");
define("LangSearchMenuItem4", "All objects");
define("LangSearchMenuItem5", "Query objects");
define("LangSearchMenuItem6", "Observers");
define("LangSearchMenuItem7", "Popular objects");

define("LangChangeMenuTitle", "Add / Change");
define("LangChangeMenuItem1", "Account details");
define("LangChangeMenuItem2", "New observation");
define("LangChangeMenuItem3", "Instruments");
define("LangChangeMenuItem4", "Locations");
define("LangChangeMenuItem5", "New object");
define("LangChangeMenuItem6", "Eyepieces");
define("LangChangeMenuItem7", "Filters");
define("LangChangeMenuItem8", "Lenses");

define("LangAdminMenuTitle", "Administration");
define("LangAdminMenuItem1", "Observers");
define("LangAdminMenuItem2", "Locations");
define("LangAdminMenuItem3", "Instruments");
define("LangAdminMenuItem4", "Eyepieces");
define("LangAdminMenuItem5", "Filters");
define("LangAdminMenuItem6", "Lenses");

define("LangLogoutMenuTitle", "Logout");
define("LangLogoutMenuItem1", "Log out");

define("LangLocationMenuTitle", "Location");
define("LangInstrumentMenuTitle", "Instrument");

define("LangLanguageMenuTitle", "Language");
define("LangLanguageMenuButton", "Change");

define("LangOverviewSiteTitle", "Overview observing sites");
define("LangAddSiteExisting", "Add existing location");

define("LangOverviewEyepieceTitle", "Overview eyepieces");
define("LangAddEyepieceExisting", "Add existing eyepiece");
define("LangAddEyepieceTitle", "Add a new eyepiece");
define("LangAddEyepieceField1", "Name");
define("LangAddEyepieceField1Expl", "(eg Televue 31mm Nagler)"); 
define("LangAddEyepieceField2", "Focal length (mm)");
define("LangAddEyepieceField2Expl", "eg 31"); 
define("LangAddEyepieceField3", "Apparent FOV (in &deg;)");
define("LangAddEyepieceField3Expl", "eg 82"); 
define("LangAddEyepieceField4", "Maximum focal length (in mm)");
define("LangAddEyepieceField4Expl", "only needed for zoom eyepieces"); 
define("LangAddEyepieceButton", "Add eyepiece"); 
define("LangAddEyepieceButton2", "Adapt eyepiece"); 

define("LangOverviewLensTitle", "Overview lenses");
define("LangViewLensName", "Name");
define("LangViewLensFactor", "Factor");
define("LangAddLensTitle", "Add a new lens");
define("LangAddLensExisting", "Add an existing lens");
define("LangAddLensFieldManually", "Specify your filter details manually");
define("LangAddLensField1", "Name");
define("LangAddLensField1Expl", "eg. Televue 2x Barlow");
define("LangAddLensField2", "Factor");
define("LangAddLensField2Expl", "> 1.0 for Barlow lenses, < 1.0 for shapley lenses.");
define("LangAddLensButton", "Add lens");
define("LangChangeLensButton", "Change lens");
define("LangValidateLensMessage2", "The lens is added to the database");
define("LangValidateLensMessage3", "Lens added");
define("LangValidateLensMessage4", "Lens changed");
define("LangValidateLensMessage5", "The lens is changed in the database");

define("LangOverviewFilterTitle", "Overview filters");
define("LangViewFilterName", "Name");
define("LangViewFilterType", "Type");
define("LangViewFilterColor", "Color");
define("LangViewFilterWratten", "Wratten number");
define("LangViewFilterSchott", "Schott number");
define("LangAddFilterTitle", "Add new filter");
define("LangAddFilterExisting", "Add an existing filter");
define("LangAddFilterFieldManually", "Specify your filter details manually");
define("LangAddFilterField1", "Name");
define("LangAddFilterField1Expl", "(ex Lumicon O-III)"); 
define("LangAddFilterField2", "Type");
define("LangAddFilterField3", "Color");
define("LangAddFilterField4", "Wratten number");
define("LangAddFilterField5", "Schott number");
define("LangAddFilterButton", "Add filter");
define("LangChangeFilterButton", "Change filter");

define("FiltersColorLightRed", "Light red");
define("FiltersColorRed", "Red");
define("FiltersColorDeepRed", "Deep red");
define("FiltersColorOrange", "Orange");
define("FiltersColorLightYellow", "Light yellow");
define("FiltersColorDeepYellow", "Deep yellow");
define("FiltersColorYellow", "Yellow");
define("FiltersColorYellowGreen", "Yellow-Green");
define("FiltersColorLightGreen", "Light green");
define("FiltersColorGreen", "Green");
define("FiltersColorMediumBlue", "Medium blue");
define("FiltersColorPaleBlue", "Pale blue");
define("FiltersColorBlue", "Blue");
define("FiltersColorDeepBlue", "Deep blue");
define("FiltersColorDeepViolet", "Violet");

define("FiltersOther", "Other filter");
define("FiltersBroadBand", "Broadband filter");
define("FiltersNarrowBand", "Narrowband filter");
define("FiltersOIII", "O-III filter");
define("FiltersHBeta", "H beta filter");
define("FiltersHAlpha", "H alpha filter");
define("FiltersColor", "Color filter");
define("FiltersNeutral", "Neutral filter");
define("FiltersCorrective", "Corrective filter");

define("LangAddSiteTitle", "Add new observing site");
define("LangAddSiteField1", "Location name");
define("LangAddSiteField1Expl", "(eg Aalst)");
define("LangAddSiteField2", "State / Province");
define("LangAddSiteField2Expl", "(eg Oost-Vlaanderen)");
define("LangAddSiteField3", "Country");
define("LangAddSiteField3Expl", "(eg Belgium)");
define("LangAddSiteField4", "Latitude");
define("LangAddSiteField4Expl", "(eg New York 40&deg;43&#39; or Melbourne -37&deg;49&#39;)");
define("LangAddSiteField5", "Longitude");
define("LangAddSiteField5Expl", "(eg New York -74&deg;01&#39; or Melbourne 144&deg;58&#39;)");
define("LangAddSiteField6", "Time Zone");
define("LangAddSiteField6Expl", "(hours, positive for sites east of Greenwich)");
define("LangAddSiteField7", "Typical limiting magnitude");
define("LangAddSiteField7Expl", "(Typical limiting magnitude of this location)");
define("LangAddSiteField8", "Typical sky brightness");
define("LangAddSiteField8Expl", "(Number returned from Sky Quality Meter)");
define("LangAddSiteFieldSearchDatabase", "Search a location from the database");
define("LangAddSiteFieldOr", "or");
define("LangAddSiteFieldManually", "Specify your location details manually");
define("LangAddSiteButton", "Add site");
define("LangAddSiteButton2", "Change site");
define("LangAddSiteStdLocation", "Adapt standard location");

define("LangSearchLocations1", "Select your country");
define("LangSearchLocations2", "Country");
define("LangSearchLocations3", "If your country is not listed, please contact the administrator to add your country to the database.");
define("LangSearchLocations4", "Specify your location");
define("LangSearchLocations5", "Locatie");
define("LangSearchLocations6", "Exact match (not case-sensitive).");

define("LangGetLocation1", "Results");
define("LangGetLocation2", "Click on the result which matches your location or <a href=\"common/search_sites.php\">perform another search</a>");
define("LangGetLocation3", "Location");
define("LangGetLocation4", "Longitude");
define("LangGetLocation5", "Latitude");
define("LangGetLocation6", "Region / State");
define("LangGetLocation7", "Country");
define("LangGetLocation8", "Sorry, your search did not yield any results.<p><a href=\"common/search_sites.php\">Perform another search</a> or <a href=\"common/add_site.php\">Specify your location details manually</a>");

define("LangAddInstrumentTitle", "Add new instrument");
define("LangAddInstrumentField1", "Instrument name");
define("LangAddInstrumentField1Expl", "");
define("LangAddInstrumentField2", "Diameter");
define("LangAddInstrumentField2Expl", "(eg 6 inch or 1500mm)");
define("LangAddInstrumentField3", "F/D");
define("LangAddInstrumentField3Expl", "");
define("LangAddInstrumentField4", "Focal length");
define("LangAddInstrumentField4Expl", "(eg 1200mm)");
define("LangAddInstrumentField5", "Type");
define("LangAddInstrumentField5Expl", "");
define("LangAddInstrumentField6", "Fixed magnification");
define("LangAddInstrumentField6Expl", "Only for binoculars, finder scopes, ...");
define("LangAddInstrumentOr", "OR");
define("LangAddInstrumentAdd", "Add instrument");
define("LangAddInstrumentStdTelescope", "Adapt standard telescope");
define("LangAddInstrumentExisting", "Add an existing instrument");
define("LangAddInstrumentManually", "Add instrument manually");

// content/change_instrument.php
 
define("LangChangeInstrumentButton", "Change instrument");

// content/welcome.php

define("LangWelcomeTitle", "DeepskyLog");

// content/new_object.php

define("LangNewObjectTitle", "Add new object (*= required fields)");
define("LangNewObjectButton1", "Add object");
define("LangNewObjectSizeUnits1", "arcminutes");
define("LangNewObjectSizeUnits2", "arcseconds");
define("LangNewObjectIcqname", "ICQ name");

// control/validate_object.php

define("LangValidateObjectMessage1", "You didn't fill in a required field!");
define("LangValidateObjectMessage2", "There is already an object with this (alternative) name!");
define("LangValidateObjectMessage3", "Please fill in both catalogue and number!");
define("LangValidateObjectMessage4", "Wrong right ascension!");
define("LangValidateObjectMessage5", "Wrong declination!");
define("LangValidateObjectMessage6", "Wrong position angle!");
define("LangValidateObjectMessage7", "You did not provide any object size units!");
define("LangValidateObjectMessage8", "Wrong magnitude format!");

// content/overview_objects.php

define("LangOverviewObjectsTitle", "Overview all objects");
define("LangOverviewObjectsFirstlink", "FIRST");
define("LangOverviewObjectsLastlink", "LAST");
define("LangOverviewObjectsHeader0", "Nr");
define("LangOverviewObjectsHeader1", "Name");
define("LangOverviewObjectsHeader1bis", "Alternative name");
define("LangOverviewObjectsHeader2", "Constellation");
define("LangOverviewObjectsHeader3", "Mag");
define("LangOverviewObjectsHeader3b", "SB");
define("LangOverviewObjectsHeader4", "Type");
define("LangOverviewObjectsHeader5", "RA");
define("LangOverviewObjectsHeader6", "Decl");
define("LangOverviewObjectsHeader7", "Seen");

// content/execute_query_objects.php

define("LangSelectedObjectsTitle", "Overview selected objects");
define("LangExecuteQueryObjectsMessage1", "Perform another search");
define("LangExecuteQueryObjectsMessage2", "Sorry, your query did not yield any results.");
define("LangExecuteQueryObjectsMessage2a", "Perform another search");
define("LangExecuteQueryObjectsMessage2b", " or ");
define("LangExecuteQueryObjectsMessage2c", "View all objects");
define("LangExecuteQueryObjectsMessage3", "You didn't specify any queries to search on.");
define("LangExecuteQueryObjectsMessage4", "Download pdf-file");
define("LangExecuteQueryObjectsMessage4b", "Download names pdf-file");
define("LangExecuteQueryObjectsMessage4c", "Download details pdf-file");
define("LangExecuteQueryObjectsMessage5", "Download csv-file");
define("LangExecuteQueryObjectsMessage6", "Download csv- file");
define("LangExecuteQueryObjectsMessage7", "Download icq-file");
define("LangExecuteQueryObjectsMessage8", "Download Argo Navis-file");
define("LangInvalidCSVfile", "You didn't provide a valid CSV file!");
define("LangSeenDontCare", "Don't care");
define("LangSeenByMe", "Already saw it");
define("LangSeenSomeoneElse", "Already seen by someone else");
define("LangSeenByMeOrSomeoneElse", "Already seen by me or someone else");
define("LangNotSeenByMeOrNotSeenAtAll", "Not seen by me or not seen at all");
define("LangNotSeen", "Not seen at all");
define("LangSeen", "Seen");
define("LangListQueryObjectsMessage1", "Add&nbsp;all&nbsp;results&nbsp;of&nbsp;the&nbsp;page&nbsp;to&nbsp;list&nbsp;");
define("LangListQueryObjectsMessage2", "&nbsp;to&nbsp;add&nbsp;to&nbsp;the&nbsp;list&nbsp;");
define("LangListQueryObjectsMessage3", "to&nbsp;remove&nbsp;from&nbsp;the&nbsp;list&nbsp;");
define("LangListQueryObjectsMessage4", "Add&nbsp;all&nbsp;resultats&nbsp;to&nbsp;the&nbsp;list&nbsp;");
define("LangListQueryObjectsMessage5", "Actif&nbsp;list:&nbsp;");
define("LangListQueryObjectsMessage6", "&nbsp;is added to the list&nbsp;");
define("LangListQueryObjectsMessage7", "&nbsp;is removed from the list&nbsp;");
define("LangListQueryObjectsMessage8", "&nbsp;The object&nbsp;");
define("LangListQueryObjectsMessage9", "&nbsp;The objects have been added to the list&nbsp;");
define("LangListQueryObjectsMessage10", " (with associated objects)");
define("LangListQueryObjectsMessage11", " (without associated objects)");
define("LangListQueryObjectsMessage12", "Show no associated objects");
define("LangListQueryObjectsMessage13", "Show associated objects");
define("LangListQueryObjectsMessage14", "'Please enter the title'");
define("LangListQueryObjectsMessage15", "'DeepskyLog Objects'");

// content/register.php

define("LangRegisterNewTitle", "Register");

// content/view_object.php

define("LangViewObjectTitle", "Object details");
define("LangViewObjectField1", "Name");
define("LangViewObjectField2", "Alternative name");
define("LangViewObjectField2b", "(Contains)/Part of");
define("LangViewObjectField3", "RA");
define("LangViewObjectField4", "Declination");
define("LangViewObjectField5", "Constellation");
define("LangViewObjectField6", "Type");
define("LangViewObjectField7", "Magnitude");
define("LangViewObjectField8", "Surface brightness");
define("LangViewObjectField9", "Size");
define("LangViewObjectField10", "Uranometria page");
define("LangViewObjectField11", "New Uranometria page");
define("LangViewObjectField12", "Position angle");
define("LangViewObjectField13", "Sky Atlas page");
define("LangViewObjectField14", "Millenium Star Atlas page");
define("LangViewObjectField15", "Taki Atlas page");
define("LangViewObjectFieldContrastReserve", "Contrast reserve");
define("LangViewObjectFieldMagnification", "Preferred magnification");
define("LangViewObjectFieldOptimumDetectionMagnification", "Optimum detection magnification");
define("LangViewObjectDSS", "Retrieve DSS image");
define("LangViewObjectDSL", "Deepskylive chart");
define("LangViewObjectObservations", "View all observations of ");
define("LangViewObjectViewNearbyObject", "Show nearby objects of ");
define("LangViewObjectAddObservation", "New observation of ");
define("LangViewObjectInexistant", "This object doesn't exist!");
define("LangViewObjectNearbyObjects", "Nearby objects: ");
define("LangViewObjectNearbyObjectsMore", "More objects");
define("LangViewObjectNearbyObjectsLess", "Less objects");
define("LangViewObjectNearbyObjectsMoreLess", "up to about ");

// content/view_observers.php

define("LangViewObserverTitle", "Observers overview");
define("LangViewObserverName", "Name");
define("LangViewObserverFirstName", "First Name");
define("LangViewObserverRole", "Role");
define("LangViewObserverAdmin", "Admin");
define("LangViewObserverWaitlist", "Waitlist");
define("LangViewObserverUser", "User");
define("LangViewObserverCometAdmin", "Comet admin");
define("LangViewObserverValidate", "Validate");
define("LangViewObserverChange", "Change role");
define("LangViewObserverNumberOfObservations", "Number of observations");
define("LangViewObserverRank", "Rank");
define("LangViewObserverInexistant", "This observer doesn't exist!");

// comets/content/change_object.php

define("LangChangeObject", "Change comet");

// content/overview_locations.php

define("LangViewLocationTitle", "Locations overview");
define("LangViewLocationLocation", "Location");
define("LangViewLocationProvince", "Province / State");
define("LangViewLocationCountry", "Country");
define("LangViewLocationLongitude", "Longitude");
define("LangViewLocationLatitude", "Latitude");
define("LangViewLocationLimMag", "NELM");
define("LangViewLocationSB", "SQM");
define("LangViewLocationStd", "Std location");

// content/overview_eyepieces.php

define("LangViewEyepieceTitle", "Eyepiece overview");
define("LangViewEyepieceName", "Name");
define("LangViewEyepieceFocalLength", "Focal Length");
define("LangViewEyepieceMaxFocalLength", "Max focal length");
define("LangViewEyepieceApparentFieldOfView", "Apparent FOV");

// content/view_location.php

define("LangViewLocationTitle2", "Location detail");

// content/overview_instruments.php

define("LangOverviewInstrumentsTitle", "Instruments overview");
define("LangOverviewInstrumentsName", "Name");
define("LangOverviewInstrumentsDiameter", "Diameter (mm)");
define("LangOverviewInstrumentsFD", "F/D");
define("LangOverviewInstrumentsType", "Type");
define("LangOverviewInstrumentsFixedMagnification", "Fixed magnification");
define("InstrumentsNakedEye", "Naked Eye");
define("InstrumentsFinderscope", "Finderscope");
define("InstrumentsReflector", "Reflector");
define("InstrumentsRefractor", "Refractor");
define("InstrumentsOther", "Other");
define("InstrumentsBinoculars", "Binoculars");
define("InstrumentsCassegrain", "Cassegrain");
define("InstrumentsSchmidtCassegrain", "Schmidt Cassegrain");
define("InstrumentsKutter", "Kutter");
define("InstrumentsMaksutov", "Maksutov");


// content/view_instrument.php

define("LangViewInstrumentTitle", "Instrument details");
define("LangViewInstrumentField1", "Name");
define("LangViewInstrumentField2", "Diameter");
define("LangViewInstrumentField3", "F/D");
define("LangViewInstrumentField4", "Focal length");
define("LangViewInstrumentField5", "Type");

// content/view_observation.php

define("LangViewObservationTitle", "Observation details");
define("LangViewObservationField1", "Object name");
define("LangViewObservationField1b", "Constellation");
define("LangViewObservationField2", "Observer");
define("LangViewObservationField3", "Instrument");
define("LangViewObservationField4", "Location");
define("LangViewObservationField5", "Date");
define("LangViewObservationField6", "Seeing");
define("LangViewObservationField7", "Limiting magnitude");
define("LangViewObservationField8", "Description");
define("LangViewObservationField9", "Time (UT)");
define("LangViewObservationField9lt", "Time (local time)");
define("LangViewObservationField10", "(day-month-year)");
define("LangViewObservationField11", "(hours-minutes)");
define("LangViewObservationField12", "Drawing");
define("LangViewObservationField13", "Minimum instrument diameter");
define("LangViewObservationField14", "Maximum instrument diameter");
define("LangViewObservationField15", "Magnitude method");
define("LangViewObservationField16", "Magnitude");
define("LangViewObservationField17", "Magnitude reference chart");
define("LangViewObservationField18", "Degree of condensation");
define("LangViewObservationField18b", "DC");
define("LangViewObservationField19", "Coma");
define("LangViewObservationField20", "Tail");
define("LangViewObservationField20b", "Tail");
define("LangViewObservationField21", "Position Angle");
define("LangViewObservationField22", "Visibility");
define("LangViewObservationField23", "Minimum visibility");
define("LangViewObservationField24", "Maximum visibility");
define("LangViewObservationField25", "Minimum limiting magnitude");
define("LangViewObservationField26", "Maximum limiting magnitude");
define("LangViewObservationField27", "Minimum seeing");
define("LangViewObservationField28", "Maximum seeing");
define("LangViewObservationField29", "Language for description");
define("LangViewObservationField30", "Eyepiece");
define("LangViewObservationField30Expl", "Add a new eyepiece");
define("LangViewObservationField31", "Filter");
define("LangViewObservationField31Expl", "Add a new filter");
define("LangViewObservationField32", "Lens");
define("LangViewObservationField32Expl", "Add a new lens");
define("LangViewObservationButton1", "Add observation");
define("LangViewObservationButton2", "Clear fields");
define("SeeingExcellent", "Excellent");
define("SeeingGood", "Good");
define("SeeingModerate", "Moderate");
define("SeeingPoor", "Poor");
define("SeeingBad", "Bad");
define("LangViewObservationNew", "Add new observation");
define("LangDeleteObservation", "Delete observation");
define("LangOverviewObservations", "List");
define("LangCompactObservations", "Compact"); 
define("LangCompactObservationsLO", "Compact LO"); 
define("LangOverviewObservationTitle", "Overview with the basic information on one line per observation");
define("LangCompactObservationsTitle", "Overview with an information line and a description per observation");
define("LangCompactObservationsLOTitle", "Overview with an information line, a description and your last observation");

// content/change_observation.php

define("LangChangeObservationTitle", "Change observation");
define("LangChangeObservationButton", "Change observation");

define("LangViewObservationFieldHelpDescription", "How to describe (dutch)");

// view_image.php

define("LangViewDSSImageTitle", "DSS image - ");

// content/selected_observations.php

define("LangSelectedObservationsTitle", "Overview of all observations of ");
define("LangNoObservations", "No observations available"); 

// content/overview_observations.php

define("LangOverviewObservationsTitle", "Overview all observations");
define("LangOverviewObservationsHeader1", "Object name &nbsp;");
define("LangOverviewObservationsHeader2", "Observer");
define("LangOverviewObservationsHeader3", "Instrument");
define("LangOverviewObservationsHeader4", "Date");
define("LangOverviewObservationsHeader5", "(*)");
define("LangOverviewObservationsHeader6", "");
define("LangOverviewObservationsHeader7", "");
define("LangOverviewObservationsHeader5a", "(*) All Observations(AO), My observations(MO), my Last observations(LO) of this object &nbsp;of this object");
define("LangOverviewObservationsHeader5b", "(*) Details(D) with drawing(DD), All Observations(AO), My observations(MO), my Last observations(LO) of this object &nbsp;of this object");
define("LangOverviewObservationsHeader8", "My&nbsp;&nbsp;LO&nbsp;&nbsp;instrument");
define("LangOverviewObservationsHeader9", "My&nbsp;&nbsp;LO&nbsp;&nbsp;date");

// tooltips

define("LangAO", "Compare this observation with all observations of this object");
define("LangAOText", "AO");
define("LangLO", "Compare this observation with my last observation of this object");
define("LangLOText", "LO");
define("LangMO", "Compare this observation with all my observations of this object");
define("LangMOText", "MO");
define("LangDetail", "Details of this observation");
define("LangDetailText", "D");
define("LangDetailDrawingText", "D");
define("LangPreviousObservation", "Previous observation");
define("LangNextObservation", "Next observation");

define("LangIndex1", "Last 10 observations:");

// content/new_observation.php

define("LangNewObservationTitle",  "New observation");
define("LangNewObservationSubtitle1a", "Search the object in the database");
define("LangNewObservationSubtitle1abis", " or ");
define("LangNewObservationSubtitle1b", "import observations from a CSV file");
define("LangNewObservationSubtitle2", "Check object details");
define("LangNewObservationSubtitle3", "Enter observation details (* required fields)");
define("LangNewObservationButton1", "Search object");
define("LangNewObservationField1", "Date *");
define("LangNewObservationField2", "Time (UT)");
define("LangNewObservationField3", "Time (local time)");

define("LangNewObservationMonth1", "January");
define("LangNewObservationMonth2", "February");
define("LangNewObservationMonth3", "March");
define("LangNewObservationMonth4", "April");
define("LangNewObservationMonth5", "May");
define("LangNewObservationMonth6", "June");
define("LangNewObservationMonth7", "July");
define("LangNewObservationMonth8", "August");
define("LangNewObservationMonth9", "September");
define("LangNewObservationMonth10", "October");
define("LangNewObservationMonth11", "November");
define("LangNewObservationMonth12", "December");

define("LangNewComet1", "Magnitude");
define("LangNewComet2", "Uncertain");
define("LangNewComet3", "Weaker than");
define("LangNewComet4", "Magnification");
define("LangNewComet5", "Magnitude method key");
define("LangNewComet6", "Magnitude reference key");
define("LangNewComet7", "More info about the codes");
define("LangNewComet8", "Degree of condensation");
define("LangNewComet9", "Coma");
define("LangNewComet10", "Tail length");
define("LangNewComet11", "Position angle of tail");
define("LangNewComet12", "degrees");
define("LangNewComet13", "arcminutes");

// control/validate_account.php

define("LangValidateAccountMessage1", "Please, fill in all fields!");
define("LangValidateAccountMessage2", "Password not confirmed!");
define("LangValidateAccountMessage3", "Wrong emailaddress!");
define("LangValidateAccountEmailLine1", "Details deepskylog account: ");
define("LangValidateAccountEmailLine1bis", "Account name: ");
define("LangValidateAccountEmailLine2", "Email: ");
define("LangValidateAccountEmailLine3", "Name : ");
define("LangValidateAccountEmailLine4", "This email has automatically been sent by the deepskylog application");
define("LangValidateAccountEmailTitle", "DeepskyLog - registration");
define("LangValidateAccountEmailTitleObject", "DeepskyLog - Object - ");
define("LangValidateAccountMessage4", "There is already someone with this account name, please choose another one!");
define("LangValidateAccountMessage5", "Your account has been successfully updated!");
define("LangValidateAccountMessage", "Message");

// control/validate_observation.php

define("LangValidateObservationMessage1", "You did not fill in a required field!");
define("LangValidateObservationMessage6", "Please, only upload drawings smaller than 100kb!");

// control/validate_search_object.php

define("LangValidateSearchObjectTitle1", "No objects found!");
define("LangValidateSearchObjectMessage1", "All fields must be filled in!");
define("LangValidateSearchObjectMessage2", "Sorry, your search did not yield any results.");
define("LangValidateSearchObjectMessage3", "Perform another search");

// control/validate_site.php

define("LangValidateSiteMessage1",  "All fields must be filled in!");
define("LangValidateSiteMessage2", "The location is added to the database");
define("LangValidateSiteMessage3", "Location added");
define("LangValidateSiteMessage4", "Location changed");
define("LangValidateSiteMessage5", "The location is changed in the database");

// control/validate_eyepiece.php

define("LangValidateEyepieceMessage1",  "All fields must be filled in!");
define("LangValidateEyepieceMessage2", "The eyepiece is added to the database");
define("LangValidateEyepieceMessage3", "Eyepiece added");
define("LangValidateEyepieceMessage4", "Eyepiece changed");
define("LangValidateEyepieceMessage5", "The eyepiece is changed in the database");

// control/validate_observer.php

define("LangValidateObserverMessage1", "The user has successfully been updated!");
define("LangValidateObserverMessage2", "User updated");

// error.php

define("LangErrorTitle", "Error");

// message.php

define("LangMessageTitle", "Message");

// control/validate_location.php

define("LangValidateLocationMessage1", "Please, fill in all fields!");
define("LangValidateLocationMessage2", "The location is added to the database!");

// control/validate_intrument.php

define("LangValidateInstrumentMessage1", "Please, fill in all fields!");
define("LangValidateInstrumentMessage2", "Please, provide one of both: focallength OR f/d!");
define("LangValidateInstrumentMessage3", "The instrument is added to the database!");
define("LangValidateInstrumentMessage4", "The instrument details have been changed in the database!");
define("LangValidateInstrumentMessage", "Message");

// content/setup_query_objects.php

define("LangQueryObjectsTitle", "Query objects");
define("LangQueryObjectsField1", "Object name");
define("LangQueryObjectsField2", "Constellation");
define("LangQueryObjectsField3", "Magnitude fainter than");
define("LangQueryObjectsField4", "Magnitude brighter than");
define("LangQueryObjectsField4Explanation", "");
define("LangQueryObjectsField5", "Surface brightness lower than");
define("LangQueryObjectsField6", "Surface brightness higher than");
define("LangQueryObjectsField6Explanation", "");
define("LangQueryObjectsField7", "Minimum RA");
define("LangQueryObjectsField8", "Maximum RA");
define("LangQueryObjectsField9", "Minimum declination");
define("LangQueryObjectsField10", "Maximum declination");
define("LangQueryObjectsField11", "Type");
define("LangQueryObjectsField12", "Atlas Page");
define("LangQueryObjectsField13", "Minimum size");
define("LangQueryObjectsField14", "Maximum size");
define("LangQueryObjectsField15", "Minimum Latitude");
define("LangQueryObjectsField16", "Maximum Latitude");
define("LangQueryObjectsField17", "Maximum contrast reserve");
define("LangQueryObjectsField18", "Minimum contrast reserve");
define("LangQueryObjectsField19", "In the list");
define("LangQueryObjectsField20", "Not in the list");
define("LangQueryObjectsButton1", "Submit query");
define("LangQueryObjectsButton2", "Clear fields");
define("LangQueryObjectsUrano", "Uranometria");
define("LangQueryObjectsUranonew", "Uranometria (2nd edition)");
define("LangQueryObjectsTaki", "Taki");
define("LangQueryObjectsSkyAtlas", "Sky Atlas");
define("LangQueryObjectsMsa", "Millenium Star Atlas");

define("LangQueryCometObjectsField1", "Minimum magnification");
define("LangQueryCometObjectsField2", "Maximum magnification");
define("LangQueryCometObjectsField3", "Minimum degree of condensation");
define("LangQueryCometObjectsField4", "Maximum degree of condensation");
define("LangQueryCometObjectsField5", "Minimum coma (arcminutes)");
define("LangQueryCometObjectsField6", "Maximum coma (arcminutes)");
define("LangQueryCometObjectsField7", "Minimum tail length (arcminutes)");
define("LangQueryCometObjectsField8", "Maximum tail length (arcminutes)");
define("LangQueryCometObjectsField9", "Minimum pa");
define("LangQueryCometObjectsField10", "Maximum pa");

// content/top_observers.php

define("LangTopObserversTitle", "Most active observers");
define("LangTopObserversHeader1", "Rank");
define("LangTopObserversHeader2", "Observer");
define("LangTopObserversHeader3", "Number of observations");
define("LangTopObserversHeader4", "Observations last year");
define("LangTopObserversHeader5", "Messier objects");
define("LangTopObserversHeader5b", "Caldwell objects");
define("LangTopObserversHeader5c", "H400 objects");
define("LangTopObserversHeader5d", "H II objects");
define("LangTopObserversHeader6", "Different objects");
define("LangTopObservers1", "Total");

// content/details_observer_messier
define("LangTopObserversMessierHeader1", "Overview observed Messier objects");
define("LangTopObserversMessierHeader2", "Overview observed");
define("LangTopObserversMessierHeader3", "objects");

// content/top_objects.php

define("LangTopObjectsTitle", "Most popular objects");
define("LangTopObjectsHeader1", "Rank");
define("LangTopObjectsHeader2", "Object");
define("LangTopObjectsHeader3", "Type");
define("LangTopObjectsHeader4", "Constellation");
define("LangTopObjectsHeader5", "Number of observations");

// new variables defined from version 1.1 onwards

// content/setup_observations_query.php
 
define("LangQueryObservationsTitle", "Query observations");
define("LangQueryObservationsMessage1", "Only observations with drawing");
define("LangQueryObservationsMessage2", "Description contains");
define("LangFromDate", "From");
define("LangTillDate", "Till");
define("LangObservationQueryError1", "You didn't specify any queries to search on!");
define("LangObservationOR", "or");
define("LangObservationQueryError2", "Perform another search");
define("LangObservationQueryError3", "View all observations");
define("LangObservationNoResults", "Sorry, your query did not yield any results"); 
define("LangQueryObservationsButton1", "Submit Query");
define("LangQueryObservationsButton2", "Clear Fields");

// remove instrument/location column
define("LangRemove","delete");

// content/new_observationcsv.php
define("LangCSVTitle", "Import observations from a CSV file");
define("LangCSVMessage1", "This form allows you to submit multiple observations at once by importing them directly from a CSV file (comma seperated value file). This will facilitate and speed up the number of observations you can submit at once. It also allows you to easily add former observations already kept by in some sort of database program. For your interest: only observations with your name (in full) will be inserted.");
define("LangCSVMessage2", "The CSV file should start with a line containing the following definition: <b>(NEW FORMAT!!!)</b>");
define("LangCSVMessage3", "Object;Observer;Date;UT;Location;Instrument;Eyepiece;Filter;Lens;Seeing;LimMag;Visibility;Language;Description");
define("LangCSVMessage4", "Followed by the actual observations in the same format, e.g.: <br><br>Object;Observer;Date;UT;Location;Instrument;Eyepiece;Filter;Lens;Seeing;LimMag;Visibility;Language;Description<br>NGC 2392;John Smith;21-01-2005;20:45;Aalst;Obsession 15\";31mm Nagler;Lumicon O-III filter;Televue 2x Barlow;2;4.0;3;en;Nice planetary nebula with a very bright central star!<br>M 35;John Smith;21-01-2005;20:53;Aalst;Obsession 15\";;;;2;4.0;1;en;About thirty members with several curved chains of stars.<br>...<br><br>Seeing should be given as a number between 1 and 5 (1=excellent, 2=good, 3=moderate, 4=poor, 5=bad).<br>Visibility should be given as a number between 1 and 7 (1=Very simple, prominent object, 2=Object easily percepted with direct vision, 3=Object perceptable with direct vision, 4=Averted vision required to percept object, 5=Object barely perceptable with averted vision, 6=Perception of object is very questionable, 7=Object definitely not seen).<br>If an observation has been done by naked eye, 'Naked Eye' should be given as instrument.<br>Language should be the short name for the language the description is in  (en for English)");
define("LangCSVMessage5", "Caution!<p>The instruments, the locations, eyepieces, filters and the objects in the CSV file should already be known by DeepskyLog otherwise an error message will be shown and no (!) observations will be added. Insert the missing data manually until there are no error messages left. If everything went well, your observations will be shown in the \"All observations\" overview. Please be careful not to re-insert the same file twice as observations can only be deleted one by one afterwards!");
define("LangCSVMessage6", "CSV file to import ");
define("LangCSVMessage7", "Name;Altname;RA;Decl;Constellation;Type;Magnitude;SurfaceBrightness;Diameter;Position Angle;Page;ContrastReserve;OptimalMagnification;Seen");
define("LangCSVError1", "The CSV file could not be imported because: ");
define("LangCSVError2", "The following objects are not available in DeepskyLog:");
define("LangCSVError3", "The following locations are not available in DeepskyLog:");
define("LangCSVError4", "The following instruments are not available in DeepskyLog:");
define("LangCSVError5", "The following filters are not available in DeepskyLog");
define("LangCSVError6", "The following eyepieces are not available in DeepskyLog");
define("LangCSVError7", "The following lenses are not available in DeepskyLog");
define("LangCSVButton", "Import!");
define("LangValidateCSVMessage", "Import of CSV file successfull!");

//List import
define("LangCSVListTitle", "Import objects from a CSV file to your list");
define("LangCSVListMessage1", "This form gives you the possibility to add different objects at once using a CSV file (comma seperated value). The form makes ot also possible to easily add objects from another database to your DeepskyLog list.");
define("LangCSVListMessage2", "The CSV file has to start with the following definition on the first line, the next lines contain the data:");
define("LangCSVListMessage3", "Objectname;free fields;These fields will not be taken into account...");
define("LangCSVListMessage4", "");
define("LangCSVListMessage5", "Watch out!<p>The objects in the CSV file should be know already by DeepskyLog. When this is not the case, a error message will appear and no object will be added at all! The non existing objects should be added manually till no error messages are shown. When everything goes fine, the added objects will be shown in your list. Double objects will not be duplicated in the list.!");
define("LangCSVListMessage6", "CSV file ");
define("LangCSVListMessage7", "NGC 7000;NA Nebula;...");
define("LangCSVListButton", "Import!");

// content/manage_csv.php
define("LangNewObjectSubtitle1b", "Manage objects from CSV file");
define("LangCSVObjectTitle", "Managing of objects from CSV file");
define("LangCSVObjectMessage1", "This form gives you the possibility to manage several objects using one single csv file (comma seperated value). This way you can easily and quickly introduce several objects, alternative names, etc.");
define("LangCSVObjectMessage2", "The csv file must adhere following syntax if the instructions concern object naming: </b>");
define("LangCSVObjectMessage3", "Instruction;Object;Catalog;Catalogindex;");
define("LangCSVObjectMessage4", "or if it concerns data");
define("LangCSVObjectMessage5", "Instruction;Object;;Data");
define("LangCSVObjectMessage6", "CSV file ");
define("LangCSVObjectMessage7", "");
define("LangCSVObjectError1", "The CSV file could not be imported because: ");
define("LangCSVObjectError2", "The following objects are not available in DeepskyLog:");
define("LangCSVObjectError3", "The following instructions are not available:");
define("LangCSVObjectError4", "The following dat is not compliant:");
define("LangCSVObjectButton", "Import!");
define("LangValidateCSVObjectMessage", "Import of CSV file successfull!");

// control/check_login.php

define("LangErrorWrongPassword", "Wrong password, please try again!");
define("LangErrorEmptyPassword", "You forgot to enter your username and/or password!");
define("LangErrorPasswordNotValidated", "Your account hasn't been validated yet!");

// Visibility for objects
define("LangVisibility1", "Very simple, prominent object");
define("LangVisibility2", "Object easily percepted with direct vision");
define("LangVisibility3", "Object perceptable with direct vision");
define("LangVisibility4", "Averted vision required to percept object");
define("LangVisibility5", "Object barely perceptable with averted vision");
define("LangVisibility6", "Perception of object is very questionable");
define("LangVisibility7", "Object definitely not seen");

// content/selected_observations.php

define("LangSelectedObservationsTitle2", "Overview selected observations");

// lib/util.php

define("LangPDFTitle", "DeepskyLog Object List");
define("LangPDFTitle2", "DeepskyLog Observations");
define("LangPDFMessage1", "Name");
define("LangPDFMessage2", "Alternative name");
define("LangPDFMessage3", "Right Ascension");
define("LangPDFMessage4", "Declination");
define("LangPDFMessage5", "Type");
define("LangPDFMessage6", "Constellation");
define("LangPDFMessage7", "Mag.");
define("LangPDFMessage8", "Surf. Brig.");
define("LangPDFMessage9", "Diameter");
define("LangPDFMessage10", "Location");
define("LangPDFMessage11", "Instrument");
define("LangPDFMessage12", " in ");
define("LangPDFMessage13", "Observed by ");
define("LangPDFMessage14", " on ");
define("LangPDFMessage15", "Description");
define("LangPDFMessage16", "Pos. Angle");
define("LangPDFMessage17", "Contr. res.");
define("LangPDFMessage18", "Opt. mag.");
define("LangNumberOfRecords", "results");
define("LangPDFTitle3", "DeepskyLog Comet Observations");

// deepsky/content/overview_observations_compact.php
define("LangOverviewCompactDescription", "Description");

define("LangContrastNotLoggedIn", "Contrast reserve can only be calculated when you are logged in...");
define("LangContrastNoStandardLocation", "Contrast reserve can only be calculated when you have set a standard location...");
define("LangContrastNoStandardInstrument", "Contrast reserve can only be calculated when you have set a standard instrument...");
define("LangContrastNoEyepiece", "Contrast reserve can only be calculate when the standard instrument has a fixed magnification or when there are eyepieces defined...");
define("LangContrastNoLimMag", "Contrast reserve can only be calculated when you have set a typical limiting magnitude or sky background for your standard location...");
define("LangContrastNoDiameter", "Contrast reserve can only be calculated when the object has a known diameter");
define("LangContrastNoMagnitude", "Contrast reserve can only be calculated when the object has a known magnitude");
define("LangContrastNotVisible", " is not visible from ");
define("LangContrastQuestionable", "Visibility of ");
define("LangContrastQuestionableB", " is questionable from ");
define("LangContrastDifficult", " is difficult to see from ");
define("LangContrastQuiteDifficult", " is quite difficult to see from ");
define("LangContrastEasy", " is easy to see from ");
define("LangContrastVeryEasy", " is very easy to see from ");
define("LangContrastPlace", " with your ");

// Types of Observations
$ASTER = "Asterism";
$BRTNB = "Bright nebula";
$CLANB = "Cluster with nebulosity";
$DS    = "Double Star";
$DRKNB = "Dark nebula";
$EMINB = "Emission nevel";
$ENRNN = "Emisson and Reflection nebula";
$ENSTR = "Emission nebula around a ster";
$GALCL = "Galaxy cluster";
$GALXY = "Galaxy";
$GLOCL = "Globular cluster";
$GXADN = "Diffuse nebula in galaxy";
$GXAGC = "Globular cluster in galaxy";
$GACAN = "Cluster with nebulosity in galaxy";
$HII = "H-II";
$LMCCN = "Cluster with nebulosity in LMC";
$LMCDN = "Diffuse nebula in LMC";
$LMCGC = "Globular cluster in LMC";
$LMCOC = "Open cluster in LMC";
$NONEX = "Nonexistent";
$OPNCL = "Open cluster";
$PLNNB = "Planetary nebula";
$REFNB = "Reflection nebula";
$RNHII = "Reflection nebula and H-II";
$SMCCN = "Cluster with nebulosity in SMC";
$SMCDN = "Diffuse nebula in SMC";
$SMCGC = "Globular cluster in SMC";
$SMCOC = "Open cluster in SMC";
$SNREM = "Supernova remnant";
$STNEB = "Nebula around star";
$QUASR = "Quasar";
$WRNEB = "Wolf Rayet nebula";
$AA1STAR = "Star";
$AA2STAR = "Double star";
$AA3STAR = "3 stars";
$AA4STAR = "4 stars";
$AA8STAR = "8 stars";

// Types of Observations
$argoASTER = "ASTERISM";
$argoBRTNB = "BRIGHT";
$argoCLANB = "NEBULA";
$argoDRKNB = "DARK";
$argoEMINB = "NEBULA";
$argoENRNN = "NEBULA";
$argoENSTR = "NEBULA";
$argoGALCL = "GALAXY CL";
$argoGALXY = "GALAXY";
$argoGLOCL = "GLOBULAR";
$argoGXADN = "NEBULA";
$argoGXAGC = "GLOBULAR";
$argoGACAN = "NEBULA";
$argoHII = "NEBULA";
$argoLMCCN = "NEBULA";
$argoLMCDN = "NEBULA";
$argoLMCGC = "GLOBULAR";
$argoLMCOC = "OPEN";
$argoNONEX = "USER";
$argoOPNCL = "OPEN";
$argoPLNNB = "PLANETARY";
$argoREFNB = "NEBULA";
$argoRNHII = "NEBULA";
$argoSMCCN = "OPEN";
$argoSMCDN = "NEBULA";
$argoSMCGC = "GLOBULAR";
$argoSMCOC = "OPEN";
$argoSNREM = "NEBULA";
$argoSTNEB = "NEBULA";
$argoQUASR = "USER";
$argoWRNEB = "NEBULA";
$argoAA1STAR = "STAR";
$argoAA2STAR = "DOUBLE";
$argoAA3STAR = "TRIPLE";
$argoAA4STAR = "ASTERISM";
$argoAA8STAR = "ASTERISM";

// Constellations
$AND = "Andromeda";
$ANT = "Antlia";
$APS = "Apus";
$AQR = "Aquarius";
$AQL = "Aquila";
$ARA = "Ara";
$ARI = "Aries";
$AUR = "Auriga";
$BOO = "Bootes";
$CAE = "Caelum";
$CAM = "Camelopardalis";
$CNC = "Cancer";
$CVN = "Canes Venatici";
$CMA = "Canis Major";
$CMI = "Canis Minor";
$CAP = "Capricornus";
$CAR = "Carina";
$CAS = "Cassiopeia";
$CEN = "Centaurus";
$CEP = "Cepheus";
$CET = "Cetus";
$CHA = "Chamaeleon";
$CIR = "Circinus";
$COL = "Columba";
$COM = "Coma Berenices";
$CRA = "Corona Australis";
$CRB = "Corona Borealis";
$CRV = "Corvus";
$CRT = "Crater";
$CRU = "Crux";
$CYG = "Cygnus";
$DEL = "Delphinus";
$DOR = "Dorado";
$DRA = "Draco";
$EQU = "Equuleus";
$ERI = "Eridanus";
$FOR = "Fornax";
$GEM = "Gemini";
$GRU = "Grus";
$HER = "Hercules";
$HOR = "Horologium";
$HYA = "Hydra";
$HYI = "Hydrus";
$IND = "Indus";
$LAC = "Lacerta";
$LEO = "Leo";
$LMI = "Leo Minor";
$LEP = "Lepus";
$LIB = "Libra";
$LUP = "Lupus";
$LYN = "Lynx";
$LYR = "Lyra";
$MEN = "Mensa";
$MIC = "Microscopium";
$MON = "Monoceros";
$MUS = "Musca";
$NOR = "Norma";
$OCT = "Octans";
$OPH = "Ophiuchus";
$ORI = "Orion";
$PAV = "Pavo";
$PEG = "Pegasus";
$PER = "Perseus";
$PHE = "Phoenix";
$PIC = "Pictor";
$PSC = "Pisces";
$PSA = "Pisces Austrinus";
$PUP = "Puppis";
$PYX = "Pyxis";
$RET = "Reticulum";
$SGE = "Sagitta";
$SGR = "Sagittarius";
$SCO = "Scorpius";
$SCL = "Sculptor";
$SCT = "Scutum";
$SER = "Serpens";
$SEX = "Sextans";
$TAU = "Taurus";
$TEL = "Telescopium";
$TRA = "Triangulum Australe";
$TRI = "Triangulum";
$TUC = "Tucana";
$UMA = "Ursa Major";
$UMI = "Ursa Minor";
$VEL = "Vela";
$VIR = "Virgo";
$VOL = "Volans";
$VUL = "Vulpecula";

$ICQ_METHOD_a = "a";
$ICQ_METHOD_B = "B";
$ICQ_METHOD_b = "b";
$ICQ_METHOD_C = "C";
$ICQ_METHOD_c = "c";
$ICQ_METHOD_d = "d";
$ICQ_METHOD_D = "D";
$ICQ_METHOD_E = "E";
$ICQ_METHOD_e = "e";
$ICQ_METHOD_F = "F";
$ICQ_METHOD_f = "f";
$ICQ_METHOD_G = "G";
$ICQ_METHOD_g = "g";
$ICQ_METHOD_H = "H";
$ICQ_METHOD_I = "I";
$ICQ_METHOD_i = "i";
$ICQ_METHOD_J = "J";
$ICQ_METHOD_j = "j";
$ICQ_METHOD_K = "K";
$ICQ_METHOD_k = "k";
$ICQ_METHOD_L = "L";
$ICQ_METHOD_l = "l";
$ICQ_METHOD_M = "M";
$ICQ_METHOD_m = "m";
$ICQ_METHOD_N = "N";
$ICQ_METHOD_n = "n";
$ICQ_METHOD_O = "O";
$ICQ_METHOD_o = "o";
$ICQ_METHOD_P = "P";
$ICQ_METHOD_p = "p";
$ICQ_METHOD_Q = "Q";
$ICQ_METHOD_q = "q";
$ICQ_METHOD_R = "R";
$ICQ_METHOD_r = "r";
$ICQ_METHOD_S = "S";
$ICQ_METHOD_s = "s";
$ICQ_METHOD_T = "T";
$ICQ_METHOD_t = "t";
$ICQ_METHOD_U = "U";
$ICQ_METHOD_u = "u";
$ICQ_METHOD_V = "V";
$ICQ_METHOD_v = "v";
$ICQ_METHOD_W = "W";
$ICQ_METHOD_w = "w";
$ICQ_METHOD_X = "X";
$ICQ_METHOD_Y = "Y";

$ICQ_REFERENCE_KEY_AE = "AE - American Ephemeris and Nautical Almanac";
$ICQ_REFERENCE_KEY_AT = "AT";
$ICQ_REFERENCE_KEY_AU = "AU";
$ICQ_REFERENCE_KEY_BR = "BR";
$ICQ_REFERENCE_KEY_BS = "BS";
$ICQ_REFERENCE_KEY_C = "C";
$ICQ_REFERENCE_KEY_CA = "CA";
$ICQ_REFERENCE_KEY_CD = "CD";
$ICQ_REFERENCE_KEY_CE = "CE";
$ICQ_REFERENCE_KEY_CF = "CF";
$ICQ_REFERENCE_KEY_CG = "CG";
$ICQ_REFERENCE_KEY_CH = "CH";
$ICQ_REFERENCE_KEY_CI = "CI";
$ICQ_REFERENCE_KEY_CJ = "CJ";
$ICQ_REFERENCE_KEY_CK = "CK";
$ICQ_REFERENCE_KEY_CL = "CL";
$ICQ_REFERENCE_KEY_CM = "CM";
$ICQ_REFERENCE_KEY_CN = "CN";
$ICQ_REFERENCE_KEY_CO = "CO";
$ICQ_REFERENCE_KEY_CR = "CR";
$ICQ_REFERENCE_KEY_CS = "CS";
$ICQ_REFERENCE_KEY_D = "D";
$ICQ_REFERENCE_KEY_E = "E";
$ICQ_REFERENCE_KEY_EA = "EA";
$ICQ_REFERENCE_KEY_EB = "EB";
$ICQ_REFERENCE_KEY_EC = "EC";
$ICQ_REFERENCE_KEY_FA = "FA";
$ICQ_REFERENCE_KEY_GA = "GA";
$ICQ_REFERENCE_KEY_GP = "GP";
$ICQ_REFERENCE_KEY_HD = "HD";
$ICQ_REFERENCE_KEY_HE = "HE";
$ICQ_REFERENCE_KEY_HI = "HI";
$ICQ_REFERENCE_KEY_HJ = "HJ";
$ICQ_REFERENCE_KEY_HK = "HK";
$ICQ_REFERENCE_KEY_HN = "HN";
$ICQ_REFERENCE_KEY_HP = "HP";
$ICQ_REFERENCE_KEY_HR = "HR";
$ICQ_REFERENCE_KEY_HV = "HV";
$ICQ_REFERENCE_KEY_JT = "JT";
$ICQ_REFERENCE_KEY_L = "L";
$ICQ_REFERENCE_KEY_LA = "LA";
$ICQ_REFERENCE_KEY_LB = "LB";
$ICQ_REFERENCE_KEY_LC = "LC";
$ICQ_REFERENCE_KEY_MC = "MC";
$ICQ_REFERENCE_KEY_ME = "ME";
$ICQ_REFERENCE_KEY_MK = "MK";
$ICQ_REFERENCE_KEY_MP = "MP";
$ICQ_REFERENCE_KEY_MS = "MS";
$ICQ_REFERENCE_KEY_MT = "MT";
$ICQ_REFERENCE_KEY_MV = "MV";
$ICQ_REFERENCE_KEY_NH = "NH";
$ICQ_REFERENCE_KEY_NN = "NN";
$ICQ_REFERENCE_KEY_NO = "NO";
$ICQ_REFERENCE_KEY_NP = "NP";
$ICQ_REFERENCE_KEY_NS = "NS";
$ICQ_REFERENCE_KEY_OB = "OB";
$ICQ_REFERENCE_KEY_PA = "PA";
$ICQ_REFERENCE_KEY_PB = "PB";
$ICQ_REFERENCE_KEY_PC = "PC";
$ICQ_REFERENCE_KEY_PI = "PI";
$ICQ_REFERENCE_KEY_RB = "RB";
$ICQ_REFERENCE_KEY_RC = "RC";
$ICQ_REFERENCE_KEY_SD = "SD";
$ICQ_REFERENCE_KEY_SE = "SE";
$ICQ_REFERENCE_KEY_SK = "SK";
$ICQ_REFERENCE_KEY_SM = "SM";
$ICQ_REFERENCE_KEY_SP = "SP";
$ICQ_REFERENCE_KEY_SS = "SS";
$ICQ_REFERENCE_KEY_SW = "SW";
$ICQ_REFERENCE_KEY_TA = "TA";
$ICQ_REFERENCE_KEY_TG = "TG";
$ICQ_REFERENCE_KEY_TI = "TI";
$ICQ_REFERENCE_KEY_TJ = "TJ";
$ICQ_REFERENCE_KEY_TK = "TK";
$ICQ_REFERENCE_KEY_TS = "TS";
$ICQ_REFERENCE_KEY_TT = "TT";
$ICQ_REFERENCE_KEY_VG = "VG";
$ICQ_REFERENCE_KEY_Y = "Y";
$ICQ_REFERENCE_KEY_YF = "YF";
$ICQ_REFERENCE_KEY_YG = "YG";

$ICQ_REFERENCE_KEY_AA = "AA";
$ICQ_REFERENCE_KEY_AC = "AC";
$ICQ_REFERENCE_KEY_AP = "AP";
$ICQ_REFERENCE_KEY_AS = "AS";
$ICQ_REFERENCE_KEY_FD = "FD";
$ICQ_REFERENCE_KEY_FG = "FG";
$ICQ_REFERENCE_KEY_LM = "LM";
$ICQ_REFERENCE_KEY_ML = "ML";
$ICQ_REFERENCE_KEY_MM = "MM";
$ICQ_REFERENCE_KEY_OH = "OH";
$ICQ_REFERENCE_KEY_PK = "PK";
$ICQ_REFERENCE_KEY_S = "S";
$ICQ_REFERENCE_KEY_SA = "SA";
$ICQ_REFERENCE_KEY_SC = "SC";
$ICQ_REFERENCE_KEY_VB = "VB";
$ICQ_REFERENCE_KEY_VF = "VF";
$ICQ_REFERENCE_KEY_VN = "VN";
$ICQ_REFERENCE_KEY_W = "W";
$ICQ_REFERENCE_KEY_WA = "WA";
$ICQ_REFERENCE_KEY_WB = "WB";
$ICQ_REFERENCE_KEY_WC = "WC";
$ICQ_REFERENCE_KEY_WD = "WD";
$ICQ_REFERENCE_KEY_WE = "WE";
$ICQ_REFERENCE_KEY_WF = "WF";
$ICQ_REFERENCE_KEY_WG = "WG";
$ICQ_REFERENCE_KEY_WH = "WH";
$ICQ_REFERENCE_KEY_WW = "WW";

$deepsky = "Deepsky";
$comets = "Comets";
?>
