<?php
// version 3.1, DE 20061119

define("LangYouAreHere", "U bevindt zich hier:");
define("LangHome","Home");
define("LangBecomeMember", "Word lid");
define("LangSearch","Zoeken");
define("LangContact","Contact");
define("LangDeepskyLogModules","Deepskylog Modules");

define("LangValidateSubject", "DeepskyLog - account aanvraag goedgekeurd");
define("LangValidateAdmin", "\nEen van de administrators heeft je administrator rechten gegeven.\n");
define("LangValidateMail1", "Beste ");
define("LangValidateMail2", "\n\nJe deepskylog account aanvraag is goedgekeurd.\nJe kan nu inloggen met je userid en paswoord.\n");
define("LangValidateMail3", "\nGroeten,\n\nDe deepskylog administrators");


define("LangObjectYSeen", "Object reeds zelf waargenomen");
define("LangObjectXSeen", "Object reeds waargenomen door anderen maar niet door mezelf");
define("LangObjectNSeen", "Object nog niet waargenomen in Deepskylog");
define("LangBack", "Terug");

// content/result_query_observations.php

define("LangResultPrevious", "Vorige");
define("LangResultNext", "Volgende");

define("LangDetails", "Details");
define("LangDrawing", "tekening");

// content/confirm.php

define("LangRegisterTitle", "Bevestiging registratie");
define("LangRegisterNotify", "Dank u, uw gegevens werden doorgestuurd naar de administrator.<br />U zal een email ontvangen indien uw aanvraag werd bevestigd.");

// Google Maps 

define("LangGooglemaps", "Klik op de kaart om de locatie in Google Maps te bekijken");

// Tooltips

define("LangSortOn", "Sorteer op ");
define("LangSortOnAsc", "Sorteer oplopend ");
define("LangSortOnDesc", "Sorteer omgekeerd");

//ToList
define("LangToListList", "De lijst \"");
define("LangToListActivation1", "\" geactiveerd, klik op '");
define("LangToListActivation2", "' om door te gaan waar u was.");
define("LangToListExists", "\" bestaat reeds.");
define("LangToListNotExists", "\" bestaat niet.");
define("LangToListAdded", "\" is toegevoegd.");
define("LangToListAddNew", "Nieuwe lijst: ");
define("LangToListAdd", "Voeg toe");
define("LangToListRename", "Hernoem");
define("LangToListPublic", "Publieke lijst");
define("LangToListEmpty", "Lijst leeg maken");
define("LangToListMyLists", "Mijn lijsten: ");
define("LangToListMyListsView", "Bekijk");
define("LangToListMyListsRemove", "Verwijder de lijst");
define("LangToListRemovePageObjectsFromList", "Verwijder de objecten van deze pagina uit de lijst.");
define("LangToListRemovePageObjectsFromListText", "R");
define("LangToListEmptyList", "De lijst is leeg, er zijn geen objecten in de lijst.");
define("LangToListRemoved", "Lijst verwijderd: ");
define("LangToListEmptied", "Lijst geledigd: ");
define("LangToListMoved1", "Het object ");
define("LangToListMoved2", " is naar achter geschoven in de lijst ");
define("LangToListMoved3", " is naar voor geschoven in de lijst ");
define("LangToListMoved4", "Schuif het object &eacute;&eacute;n plaats naar voor in de lijst.");
define("LangToListMoved5", "Schuif het object &eacute;&eacute;n plaats naar achter in de lijst.");
define("LangToListMoved6", "Plaats het object op een bepaalde plaats in de lijst.");
define("LangToListMoved7", "Het object is verplaatst naar plaats ");
define("LangToListObjectRemoved", " is verwijderd uit de lijst ");
define("LangToListPageRemoved", "De objecten zijn verwijderd uit de lijst.");
define("LangToListRemoveObjectFromList", "Verwijder het object uit de lijst.");
define("LangToListPopupTitle", "Verplaats het object naar plaats:");
define("LangToListImport","Importeer gegevens");
define("LangInvalidCSVListFile", "Ongeldige lijst data.");
define("LangListImportError1", "Je moet ingelogd zijn om lijsten te importeren.");
define("LangListImportError2", "Je moet een eigen lijst selecteren om te kunnen importeren in die lijst.");
define("LangCSVListError1","Er zijn fouten opgetreden bij het importeren.");
define("LangCSVListError2","Niet-gekende objecten:");

// List menu
define("LangListManage", "Beheer/Bekijk");
define("LangListActivate", "Activeer");
define("LangListOnlyMembers", "Lijsten zijn enkel beschikbaar voor de geregistreerde gebruikers.");

// Quickpick menu
define("LangQuickPickHelp", "Geef de naam van een object, met de catalogus, gevolgd door een SPATIE en het catalogusnummer (bv: 'NGC&nbsp;7000', 'Mel 20', of ook 'Ring&nbsp;Nebula').");
define("LangQuickPickTitle", "Quick&nbsp;Pick");
define("LangQuickPickSearchObject", "Zoek&nbsp;object");
define("LangQuickPickSearchObservations", "Zoek&nbsp;waarnemingen");
define("LangQuickPickNewObservation", "Nieuwe&nbsp;waarneming");
define("LangListsTitle", "Waarneemlijsten");
define("LangListsAnnouncement", "In de volgende versie kunnen geregistreerde waarnemers zelf hun eigen waarneemlijsten samenstellen, bewerken, bewaren en afdrukken, op basis van wat ze reeds zagen, of niet, de plaats aan de hemel, het type object enz. Ook wanneer ze een andere waarneming van iemand zien, zullen ze het betreffende object aan hun lijsten kunnen toevoegen.");
define("LangMailtoTitle", "Vertel het ons!");
define("LangMailtoLink", "Gebruik deze <a href=\"mailto:&#100;&#101;v&#101lop&#101rs&#64&#100;&#101;&#101;pskylog.&#98;&#101;\">link </a> om vragen of opmerkingen over te maken.");

// deepsky/content/view_object
define("LangObjectNewName", "Nieuwe naam");
define("LangObjectNewAltName", "Nieuwe alternatieve naam");
define("LangObjectRemoveAltNameName", "Verwijder de alternatieve naam");
define("LangObjectNewPartOf", "Nieuw deel van");
define("LangObjectRemovePartOf", "Verwijder deel van");
define("LangObjectRemoveAndReplaceObjectBy", "Verwijder het object en vervang de observaties ervan door object");
define("LangObjectSetRA", "Zet RA (uurformaat: 1.50 voor 1h30m !)");
define("LangObjectSetDECL", "Zet Decl (gradenformaat: 25.50 voor 25&deg;30')");
define("LangObjectSetCon", "Zet sterrenbeeld (afkorting in drie hoofdletters !)");
define("LangObjectSetType", "Zet type (afkorting in vijf hoofdletters !)");
define("LangObjectSetMag", "Zet magnitude");
define("LangObjectSetSUBR", "Zet oppervlaktehelderheid");
define("LangObjectSetDiam1", "Zet diameter 1 (seconden !)");
define("LangObjectSetDiam2", "Zet diameter 2 (seconden !)");
define("LangObjectSetPA", "Zet positiehoek");


// content/change_account.php

define("LangChangeAccountTitle", "Persoonlijke data");
define("LangChangeAccountField1", "Gebruikersnaam");
define("LangChangeAccountField1Expl", "Onder deze naam ben je bekend bij de andere gebruikers");
define("LangChangeAccountField2", "Emailadres");
define("LangChangeAccountField2Expl", "Uw emailadres wordt niet verder verspreid"); 
define("LangChangeAccountField3", "Voornaam");
define("LangChangeAccountField3Expl", ""); 
define("LangChangeAccountField4", "Familienaam");
define("LangChangeAccountField4Expl", "");
define("LangChangeAccountField5", "Paswoord");
define("LangChangeAccountField5Expl", "Dit is <em>niet</em> het paswoord van je provider");
define("LangChangeAccountField6", "Bevestig paswoord");
define("LangChangeAccountField6Expl", "");                                      
define("LangChangeAccountField7", "Standaard waarnemingsplaats");
define("LangChangeAccountField7Expl", "Nieuwe plaats");
define("LangChangeAccountField8", "Standaard instrument");
define("LangChangeAccountField8Expl", "Nieuw instrument");
define("LangChangeAccountField9", "Standaard atlas");
define("LangChangeAccountLanguage", "Standaard taal");
define("LangChangeAccountLanguageExpl", "De taal voor DeepskyLog");
define("LangChangeAccountObservationLanguage", "Standaard taal waarnemingen");
define("LangChangeAccountObservationLanguageExpl", "De standaardtaal waarin je je waarnemingen invoert");
define("LangChangeVisibleLanguages", "Talen voor beschrijvingen");
define("LangChangeVisibleLanguagesExpl", "Kies uit deze lijst welke waarnemingen je wilt zien");
define("LangChangeAccountPicture", "Foto");
define("LangChangeAccountButton", "Pas aan");
define("LangChangeAccountField10", "ICQ waarnemercode");
define("LangChangeAccountField10Expl", "<a href=\"http://cfa-www.harvard.edu/icq/icq.html\" target=\"_blank\">ICQ</a> code voor komeetwaarnemingen");
define("LangChangeAccountField11", "Gebruik lokale tijd");
define("LangChangeAccountField11Expl", "Gebruik lokale tijd voor ingeven en opvragen van waarnemingen");

define("LangLoginMenuTitle", "Aanmelden");
define("LangLoginMenuItem1", "Gebruikersnaam");
define("LangLoginMenuItem2", "Paswoord");
define("LangLoginMenuButton", "Log in");
define("LangLoginMenuRegister", "Registreer");

define("LangSearchMenuTitle", "Overzicht");
define("LangSearchMenuItem1", "Mijn waarnemingen");
define("LangSearchMenuItem2", "Alle waarnemingen");
define("LangSearchMenuItem3", "Zoek waarnemingen");
define("LangSearchMenuItem4", "Alle objecten");
define("LangSearchMenuItem5", "Zoek objecten");
define("LangSearchMenuItem6", "Waarnemers");
define("LangSearchMenuItem7", "Populaire&nbsp;objecten");
define("LangSearchMenuItem8", "Jaarwaarnemingen");

define("LangChangeMenuTitle", "Toevoegen / Wijzigen");
define("LangChangeMenuItem1", "Persoonlijke data");
define("LangChangeMenuItem2", "Nieuwe waarneming");
define("LangChangeMenuItem3", "Instrumenten");
define("LangChangeMenuItem4", "Waarneemplaatsen");
define("LangChangeMenuItem5", "Nieuw object");
define("LangChangeMenuItem6", "Oculairs");
define("LangChangeMenuItem7", "Filters");
define("LangChangeMenuItem8", "Lenzen");

define("LangAdminMenuTitle", "Administratie");
define("LangAdminMenuItem1", "Waarnemers");
define("LangAdminMenuItem2", "Waarneemplaatsen");
define("LangAdminMenuItem3", "Instrumenten");
define("LangAdminMenuItem4", "Oculairs");
define("LangAdminMenuItem5", "Filters");
define("LangAdminMenuItem6", "Lenzen");

define("LangLogoutMenuTitle", "Afmelden");
define("LangLogoutMenuItem1", "Log uit");

define("LangLocationMenuTitle", "Locatie");
define("LangInstrumentMenuTitle", "Instrument");

define("LangLanguageMenuTitle", "Taal");
define("LangLanguageMenuButton", "Verander");

define("LangOverviewSiteTitle", "Overzicht waarnemingsplaatsen");
define("LangAddSiteExisting", "Voeg bestaande waarneemplaats toe");

define("LangOverviewEyepieceTitle", "Overzicht oculairs");
define("LangAddEyepieceExisting", "Voeg bestaand oculair toe");
define("LangAddEyepieceTitle", "Voeg een nieuw oculair toe");
define("LangAddEyepieceField1", "Naam");
define("LangAddEyepieceField1Expl", "(bijv. Televue 31mm Nagler)"); 
define("LangAddEyepieceField2", "Brandpunt (in mm)");
define("LangAddEyepieceField2Expl", "bijv. 31"); 
define("LangAddEyepieceField3", "Schijnbaar beeldveld (in &deg;)");
define("LangAddEyepieceField3Expl", "eg 82"); 
define("LangAddEyepieceField4", "Maximaal brandpunt (in mm)");
define("LangAddEyepieceField4Expl", "enkel van toepassing met zoom oculairen"); 
define("LangAddEyepieceButton", "Voeg oculair toe"); 
define("LangAddEyepieceButton2", "Wijzig oculair"); 

define("LangOverviewLensTitle", "Overzicht lenzen");
define("LangViewLensName", "Naam");
define("LangViewLensFactor", "Factor");
define("LangAddLensTitle", "Nieuwe lens");
define("LangAddLensExisting", "Voeg bestaande lens toe");
define("LangAddLensFieldManually", "Voeg de gegevens manueel in");
define("LangAddLensField1", "Naam");
define("LangAddLensField1Expl", "Bijv. Televue 2x Barlow");
define("LangAddLensField2", "Factor");
define("LangAddLensField2Expl", "> 1.0 voor Barlow lenzen, < 1.0 voor shapley lenzen.");
define("LangAddLensButton", "Voeg lens toe");
define("LangChangeLensButton", "Verander lens");
define("LangValidateLensMessage2", "De lens in toegevoegd in de database");
define("LangValidateLensMessage3", "Lens toegevoegd");
define("LangValidateLensMessage4", "Lens aangepast");
define("LangValidateLensMessage5", "De lens is aangepast in de database");

define("LangOverviewFilterTitle", "Overzicht filters");
define("LangViewFilterName", "Naam");
define("LangViewFilterType", "Type");
define("LangViewFilterColor", "Kleur");
define("LangViewFilterWratten", "Wratten nummer");
define("LangViewFilterSchott", "Schott nummer");
define("LangAddFilterTitle", "Nieuwe filter");
define("LangAddFilterExisting", "Voeg bestaande filter toe");
define("LangAddFilterFieldManually", "Voeg de gegevens manueel in");
define("LangAddFilterField1", "Naam");
define("LangAddFilterField1Expl", "(bv Lumicon O-III)"); 
define("LangAddFilterField2", "Type");
define("LangAddFilterField3", "Kleur");
define("LangAddFilterField4", "Wratten nummer");
define("LangAddFilterField5", "Schott nummer");
define("LangAddFilterButton", "Voeg filter toe");
define("LangChangeFilterButton", "Verander filter");

define("FiltersColorLightRed", "Licht rood");
define("FiltersColorRed", "Rood");
define("FiltersColorDeepRed", "Diep rood");
define("FiltersColorOrange", "Oranje");
define("FiltersColorLightYellow", "Licht geel");
define("FiltersColorDeepYellow", "Diep geel");
define("FiltersColorYellow", "Geel");
define("FiltersColorYellowGreen", "Geel-groen");
define("FiltersColorLightGreen", "Licht groen");
define("FiltersColorGreen", "Groen");
define("FiltersColorMediumBlue", "Medium blauw");
define("FiltersColorPaleBlue", "Bleek blauw");
define("FiltersColorBlue", "Blauw");
define("FiltersColorDeepBlue", "Diep blauw");
define("FiltersColorDeepViolet", "Violet");

define("FiltersOther", "Andere filter");
define("FiltersBroadBand", "Breedband filter");
define("FiltersNarrowBand", "Smalband filter");
define("FiltersOIII", "O-III filter");
define("FiltersHBeta", "H beta filter");
define("FiltersHAlpha", "H alpha filter");
define("FiltersColor", "Kleuren filter");
define("FiltersNeutral", "Neutrale filter");
define("FiltersCorrective", "Correctieve filter");

define("LangAddSiteTitle", "Voeg nieuwe waarnemingsplaats toe");
define("LangAddSiteField1", "Plaatsnaam");
define("LangAddSiteField1Expl", "(bv Aalst)"); 
define("LangAddSiteField2", "Provincie / Staat");
define("LangAddSiteField2Expl", "(bv Oost-Vlaanderen)");
define("LangAddSiteField3", "Land");
define("LangAddSiteField3Expl", "(bv Belgium)");
define("LangAddSiteField4", "Breedtegraad");
define("LangAddSiteField4Expl", "(bv New York 40&deg;43&#39; of Melbourne -37&deg;49&#39;)");
define("LangAddSiteField5", "Lengtegraad");
define("LangAddSiteField5Expl", "(bv New York -74&deg;01&#39; of Melbourne 144&deg;58&#39;)");
define("LangAddSiteField6", "Tijdszone");
define("LangAddSiteField6Expl", "(uren, positief voor locaties ten oosten van Greenwich)");
define("LangAddSiteField7", "Typische grensmagnitude");
define("LangAddSiteField7Expl", "(Typische grensmagnitude van deze plaats)");
define("LangAddSiteField8", "Typische hemelachtergrond");
define("LangAddSiteField8Expl", "(Typisch getal van de Sky Quality Meter)");
define("LangAddSiteFieldSearchDatabase", "Zoek een locatie uit de database");
define("LangAddSiteFieldOr", "of");
define("LangAddSiteFieldManually", "Voeg de gegevens manueel in");
define("LangAddSiteButton", "Voeg locatie toe");
define("LangAddSiteButton2", "Wijzig locatie");
define("LangAddSiteStdLocation", "Wijzig standaard locatie");

define("LangSearchLocations0", "Zoek locatie");
define("LangSearchLocations1", "Kies je land");
define("LangSearchLocations2", "Land");
define("LangSearchLocations3", "Als je land niet voorkomt in de lijst, contacteer dan de administrator om je land aan de database toe te voegen.");
define("LangSearchLocations4", "Specifieer je locatie");
define("LangSearchLocations5", "Locatie");
define("LangSearchLocations6", "Exacte naam");
define("LangSearchLocations7", "Zoeken");

define("LangGetLocation1", "Resultaten");
define("LangGetLocation2", "Klik op het resultaat dat overeenkomt met je locatie of <a href=\"common/search_sites.php\">zoek opnieuw</a>");
define("LangGetLocation3", "Locatie");
define("LangGetLocation4", "Lengtegraad");
define("LangGetLocation5", "Breedtegraad");
define("LangGetLocation6", "Provincie / staat");
define("LangGetLocation7", "Land");
define("LangGetLocation8", "Sorry, je zoekopdracht heeft geen resultaten opgeleverd.<p><a href=\"common/search_sites.php\">Zoek opnieuw</a> of <a href=\"common/add_site.php\">Voeg de gegevens manueel in</a>");

define("LangAddInstrumentTitle", "Voeg nieuw instrument toe");
define("LangAddInstrumentField1", "Instrumentnaam");
define("LangAddInstrumentField1Expl", ""); 
define("LangAddInstrumentField2", "Diameter");
define("LangAddInstrumentField2Expl", "(bv 1500mm of 6 inch)");
define("LangAddInstrumentField3", "F/D");
define("LangAddInstrumentField3Expl", ""); 
define("LangAddInstrumentField4", "Brandpuntsafstand");
define("LangAddInstrumentField4Expl", "(bv 1200mm)");
define("LangAddInstrumentField5", "Type");
define("LangAddInstrumentField5Expl", "");
define("LangAddInstrumentField6", "Vaste vergroting");
define("LangAddInstrumentField6Expl", "Enkel voor verrekijkers, zoekers, ...");
define("LangAddInstrumentOr", "OF");
define("LangAddInstrumentAdd", "Voeg instrument toe");
define("LangAddInstrumentStdTelescope", "Pas standaard instrument aan");
define("LangAddInstrumentExisting", "Voeg een bestaand instrument toe");
define("LangAddInstrumentManually", "Voeg de gegevens manueel in");

// content/change_instrument.php

define("LangChangeInstrumentButton", "Wijzig instrument");

// content/welcome.php

define("LangWelcomeTitle", "DeepskyLog");

// content/new_object.php

define("LangNewObjectTitle", "Voeg nieuw object toe (*=verplichte velden)");
define("LangNewObjectButton1", "Voeg object toe");
define("LangNewObjectSizeUnits1", "boogminuten");
define("LangNewObjectSizeUnits2", "boogseconden");
define("LangNewObjectIcqname", "ICQ naam");

// control/validate_object.php

define("LangValidateObjectMessage1", "Gelieve alle verplichte velden in te vullen!");
define("LangValidateObjectMessage2", "Er bestaat al een object met deze (alternatieve) naam!");
define("LangValidateObjectMessage3", "Gelieve zowel catalogus als nummer in te vullen!");
define("LangValidateObjectMessage4", "Verkeerde rechte klimming!");
define("LangValidateObjectMessage5", "Verkeerde delinatie!");
define("LangValidateObjectMessage6", "Verkeerde positiehoek!");
define("LangValidateObjectMessage7", "Gelieve de eenheden van de grootte van het object in te vullen!");
define("LangValidateObjectMessage8", "Verkeerde magnitude!");

// content/overview_objects.php
 
define("LangOverviewObjectsTitle", "Overzicht alle objecten");
define("LangOverviewObjectsFirstlink", "BEGIN");
define("LangOverviewObjectsLastlink", "EINDE");
define("LangOverviewObjectsHeader0", "Nr");
define("LangOverviewObjectsHeader1", "Naam");
define("LangOverviewObjectsHeader1bis", "Alternatieve naam");
define("LangOverviewObjectsHeader2", "Sterrenbeeld");
define("LangOverviewObjectsHeader3", "Mag");
define("LangOverviewObjectsHeader3b", "SB");
define("LangOverviewObjectsHeader4", "Type");
define("LangOverviewObjectsHeader5", "RA");
define("LangOverviewObjectsHeader6", "Decl");
define("LangOverviewObjectsHeader7", "Gezien");
define("LangOverviewObjectsHeader8", "Laatst gezien");

// content/execute_query_objects.php

define("LangSelectedObjectsTitle", "Overzicht geselecteerde objecten");
define("LangExecuteQueryObjectsMessage1", "Zoek opnieuw");
define("LangExecuteQueryObjectsMessage2", "Sorry, je zoekopdracht heeft geen resultaten opgeleverd.");
define("LangExecuteQueryObjectsMessage2a", "Zoek opnieuw");
define("LangExecuteQueryObjectsMessage2b", " of ");
define("LangExecuteQueryObjectsMessage2c", "Bekijk alle objecten");
define("LangExecuteQueryObjectsMessage3", "Je hebt geen zoekopdracht gespecifieerd.");
define("LangExecuteQueryObjectsMessage4", "Download list pdf-file");
define("LangExecuteQueryObjectsMessage4b", "Download namen pdf-file");
define("LangExecuteQueryObjectsMessage4c", "Download details pdf-file");
define("LangExecuteQueryObjectsMessage5", "Download csv-file");
define("LangExecuteQueryObjectsMessage6", "Download csv- file");
define("LangExecuteQueryObjectsMessage7", "Download icq-file");
define("LangExecuteQueryObjectsMessage8", "Download Argo Navis-file");
define("LangExecuteQueryObjectsMessage9", "Filter objecten");
define("LangInvalidCSVfile", "U gaf geen geldige CSV file!");
define("LangSeenDontCare", "Alle objecten, ongeacht of ik ze waarnam of niet");
define("LangSeenByMe", "Enkel objecten die ik reeds heb waargenomen");
define("LangSeenSomeoneElse", "Enkel objecten die anderen al waarnamen maar ik nog niet");
define("LangSeenByMeOrSomeoneElse", "Enkel objecten die reeds door iemand zijn waargenomen");
define("LangNotSeenByMeOrNotSeenAtAll", "Enkel objecten die ik nog niet waargenomen heb");
define("LangNotSeen", "Enkel objecten die nog niemand waarnam");
define("LangSeen", "Waargenomen");
define("LangListQueryObjectsMessage1", "Resultaten&nbsp;van&nbsp;de&nbsp;hele&nbsp;pagina&nbsp;toevoegen&nbsp;aan&nbsp;lijst&nbsp;");
define("LangListQueryObjectsMessage2", "&nbsp;toevoegen&nbsp;aan&nbsp;lijst&nbsp;");
define("LangListQueryObjectsMessage3", "&nbsp;verwijderen&nbsp;uit&nbsp;lijst&nbsp;");
define("LangListQueryObjectsMessage4", "Alle&nbsp;resultaten&nbsp;toevoegen&nbsp;aan&nbsp;lijst&nbsp;");
define("LangListQueryObjectsMessage5", "Actieve&nbsp;lijst:&nbsp;");
define("LangListQueryObjectsMessage6", "&nbsp;is toegevoegd aan de lijst&nbsp;");
define("LangListQueryObjectsMessage7", "&nbsp;is verwijderd uit de &nbsp;");
define("LangListQueryObjectsMessage8", "&nbsp;Het object&nbsp;");
define("LangListQueryObjectsMessage9", "&nbsp;De objecten werden toegevoegd aan de lijst&nbsp;");
define("LangListQueryObjectsMessage10", " (met geassocieerde objecten)");
define("LangListQueryObjectsMessage11", " (zonder geassocieerde objecten)");
define("LangListQueryObjectsMessage12", "Toon geen geassocieerde objecten");
define("LangListQueryObjectsMessage13", "Toon geassocieerde objecten");
define("LangListQueryObjectsMessage14", "'Gelieve de titel in te geven'");
define("LangListQueryObjectsMessage15", "'DeepskyLog Objecten'");
define("LangListQueryObjectsMessage16", "&nbsp;De observatie&nbsp;");

// content/register.php

define("LangRegisterNewTitle", "Registreer");

// content/view_object.php
 
define("LangViewObjectTitle", "Object details");
define("LangViewObjectField1", "Naam");
define("LangViewObjectField2", "Alternatieve naam");
define("LangViewObjectField2b", "(Bevat)/Deel van");
define("LangViewObjectField3", "RA");
define("LangViewObjectField4", "Declinatie");
define("LangViewObjectField5", "Sterrenbeeld");
define("LangViewObjectField6", "Type");
define("LangViewObjectField7", "Magnitude");
define("LangViewObjectField8", "Oppervlaktehelderheid");
define("LangViewObjectField9", "Grootte");
define("LangViewObjectField10", " pagina");
define("LangViewObjectField11", "Nieuwe Uranometria pagina");
define("LangViewObjectField12", "Positiehoek");
define("LangViewObjectField13", "Sky Atlas pagina");
define("LangViewObjectField14", "Millenium Star Atlas pagina");
define("LangViewObjectField15", "Taki Atlas pagina");
define("LangViewObjectField16", "Pocket Sky Atlas pagina");
define("LangViewObjectField17", "Torres B pagina");
define("LangViewObjectField18", "Torres BC pagina");
define("LangViewObjectField19", "Torres C pagina");
define("LangViewObjectFieldContrastReserve", "Contrast reserve");
define("LangViewObjectFieldMagnification", "Aanbevolen vergroting");
define("LangViewObjectFieldOptimumDetectionMagnification", "Optimale detectie vergroting");
define("LangViewObjectDSS", "Bekijk DSS beeld");
define("LangViewObjectDSL", "Deepskylive kaart");
define("LangViewObjectObservations", "Bekijk alle waarnemingen van ");
define("LangViewObjectViewNearbyObject", "Bekijk alle nabije objecten van ");
define("LangViewObjectAddObservation", "Nieuwe waarneming van ");
define("LangViewObjectInexistant", "Dit object bestaat niet!");
define("LangViewObjectNearbyObjects", "Nabij gelegen objecten: ");
define("LangViewObjectNearbyObjectsMore", "Meer objecten");
define("LangViewObjectNearbyObjectsLess", "Minder objecten");
define("LangViewObjectNearbyObjectsMoreLess", "tot op ongeveer ");
define("LangViewObjectNGCDescription", "NGC omschrijving");
define("LangViewObjectListDescription", "Lijst omschrijving");

// content/view_observers.php

define("LangViewObserverTitle", "Waarnemers overzicht");
define("LangViewObserverName", "Naam");
define("LangViewObserverFirstName", "Voornaam");
define("LangViewObserverRole", "Rol");
define("LangViewObserverAdmin", "Admin");
define("LangViewObserverWaitlist", "Wachtlijst");
define("LangViewObserverUser", "Gebruiker");
define("LangViewObserverCometAdmin", "Komeet admin");
define("LangViewObserverValidate", "Valideer");
define("LangViewObserverChange", "Verander rol");
define("LangViewObserverNumberOfObservations", "Aantal waarnemingen");
define("LangViewObserverRank", "Rang");
define("LangViewObserverInexistant", "Deze waarnemer bestaat niet!");

// comets/content/change_object.php

define("LangChangeObject", "Wijzig komeet");

// content/overview_locations.php

define("LangViewLocationTitle", "Locatie overzicht");
define("LangViewLocationLocation", "Locatie");
define("LangViewLocationProvince", "Provincie / Staat");
define("LangViewLocationCountry", "Land");
define("LangViewLocationLongitude", "Lengtegraad");
define("LangViewLocationLatitude", "Breedtegraad");
define("LangViewLocationLimMag", "NELM");
define("LangViewLocationSB", "SQM");
define("LangViewLocationStd", "Std locatie");

// content/overview_eyepieces.php

define("LangViewEyepieceTitle", "Oculair overzicht");
define("LangViewEyepieceName", "Naam");
define("LangViewEyepieceFocalLength", "Brandpunt");
define("LangViewEyepieceMaxFocalLength", "Max. brandpunt");
define("LangViewEyepieceApparentFieldOfView", "Schijnbaar beeldveld");

// content/view_location.php

define("LangViewLocationTitle2", "Locatie detail");

// content/overview_instruments.php

define("LangOverviewInstrumentsTitle", "Instrumenten overzicht");
define("LangOverviewInstrumentsName", "Naam");
define("LangOverviewInstrumentsDiameter", "Diameter (mm)");
define("LangOverviewInstrumentsFD", "F/D");
define("LangOverviewInstrumentsType", "Type");
define("LangOverviewInstrumentsFixedMagnification", "Vaste vergroting");
define("InstrumentsNakedEye", "Blote oog");
define("InstrumentsFinderscope", "Zoeker");
define("InstrumentsReflector", "Reflector");
define("InstrumentsRefractor", "Refractor");
define("InstrumentsOther", "Ander");
define("InstrumentsBinoculars", "Verrekijker");
define("InstrumentsCassegrain", "Cassegrain");
define("InstrumentsSchmidtCassegrain", "Schmidt Cassegrain");
define("InstrumentsKutter", "Kutter");
define("InstrumentsMaksutov", "Maksutov");


// content/view_instrument.php

define("LangViewInstrumentTitle", "Instrument details");
define("LangViewInstrumentField1", "Naam");
define("LangViewInstrumentField2", "Diameter");
define("LangViewInstrumentField3", "F/D");
define("LangViewInstrumentField4", "Brandpuntsafstand");
define("LangViewInstrumentField5", "Type");

// content/view_observation.php

define("LangViewObservationTitle", "Details waarneming");
define("LangViewObservationField1", "Objectnaam");
define("LangViewObservationField1b", "Sterrenbeeld");
define("LangViewObservationField2", "Waarnemer");
define("LangViewObservationField3", "Instrument");
define("LangViewObservationField4", "Waarnemingsplaats");
define("LangViewObservationField5", "Datum");
define("LangViewObservationField6", "Seeing");
define("LangViewObservationField7", "Grensmagnitude");
define("LangViewObservationField8", "Beschrijving");
define("LangViewObservationField9", "Tijd (UT)");
define("LangViewObservationField9lt", "Tijd (Lokale tijd)");
define("LangViewObservationField10", "(dag-maand-jaar)");
define("LangViewObservationField11", "(uren-minuten)");
define("LangViewObservationField12", "Tekening");
define("LangViewObservationField13", "Minimum diameter instrument");
define("LangViewObservationField14", "Maximum diameter instrument");
define("LangViewObservationField15", "Schattingsmethode");
define("LangViewObservationField16", "Magnitude");
define("LangViewObservationField17", "Gebruikte sterrenkaart");
define("LangViewObservationField18", "Condensatiegraad");
define("LangViewObservationField18b", "DC");
define("LangViewObservationField19", "Coma");
define("LangViewObservationField20", "Lengte van de staart");
define("LangViewObservationField20b", "Staart");
define("LangViewObservationField21", "Positiehoek van de staart");
define("LangViewObservationField22", "Zichtbaarheid");
define("LangViewObservationField23", "Minimale zichtbaarheid");
define("LangViewObservationField24", "Maximale zichtbaarheid");
define("LangViewObservationField25", "Minimale grensmagnitude");
define("LangViewObservationField26", "Maximale grensmagnitude");
define("LangViewObservationField27", "Minimale seeing");
define("LangViewObservationField28", "Maximale seeing");
define("LangViewObservationField29", "Taal voor beschrijving");
define("LangViewObservationField30", "Oculair");
define("LangViewObservationField30Expl", "Nieuw oculair");
define("LangViewObservationField31", "Filter");
define("LangViewObservationField31Expl", "Nieuwe filter");
define("LangViewObservationField32", "Lens");
define("LangViewObservationField32Expl", "Nieuwe lens");
define("LangViewObservationField33", "Geschatte diameter");
define("LangViewObservationField34", "SQM");
define("LangViewObservationButton1", "Voeg toe");
define("LangViewObservationButton2", "Wis velden");
define("SeeingExcellent", "Uitstekend");
define("SeeingGood", "Goed");
define("SeeingModerate", "Middelmatig");
define("SeeingPoor", "Zwak");
define("SeeingBad", "Slecht");
define("LangViewObservationNew", "Voeg nieuwe waarneming toe");
define("LangDeleteObservation", "Verwijder waarneming");
define("LangOverviewObservations", "Lijst");
define("LangCompactObservations", "Compact");
define("LangCompactObservationsLO", "Compact LO");
define("LangOverviewObservationTitle", "Overzicht met &eacute;&eacute;n enkele lijn van informatie per waarneming");
define("LangCompactObservationsTitle", "Overzicht met naast de informatielijn eveneens de beschrijving van de waarneming");
define("LangCompactObservationsLOTitle", "Overzicht met de informatielijn, de beschrijving en uw laatste waarneming");
 
// content/change_observation.php

define("LangChangeObservationTitle", "Wijzig waarneming");
define("LangChangeObservationButton", "Wijzig waarneming");

define("LangViewObservationFieldHelpDescription", "Hulp bij het beschrijven");

// view_image.php

define("LangViewDSSImageTitle", "DSS beeld - ");

// content/selected_observations.php

define("LangSelectedObservationsTitle", "Overzicht alle waarnemingen van ");
define("LangNoObservations", "Geen waarnemingen beschikbaar"); 

// content/overview_observations.php

define("LangOverviewObservationsTitle", "Overzicht alle waarnemingen");
define("LangOverviewObservationsHeader1", "Objectnaam&nbsp;");
define("LangOverviewObservationsHeader2", "Waarnemer");
define("LangOverviewObservationsHeader3", "Instrument");
define("LangOverviewObservationsHeader4", "Datum");
define("LangOverviewObservationsHeader5", "(*)");
define("LangOverviewObservationsHeader6", "");
define("LangOverviewObservationsHeader7", "");
define("LangOverviewObservationsHeader5a", "(*) Alle observaties(AO) , Mijn observaties(MO), mijn Laatste observatie(LO)&nbsp;van dit object");
define("LangOverviewObservationsHeader5b", "(*) Details(D) met tekening(DT), Alle observaties(AO) , Mijn observaties(MO), mijn Laatste observatie(LO)&nbsp;van dit object");
define("LangOverviewObservationsHeader8", "Mijn&nbsp;LO&nbsp;instrument");
define("LangOverviewObservationsHeader9", "Mijn&nbsp;LO&nbsp;datum");

// tooltips

define("LangAO", "Vergelijk deze waarneming met alle waarnemingen van dit object");
define("LangAOText", "AO");
define("LangLO", "Vergelijk deze waarneming met mijn laatste waarneming van dit object");
define("LangLOText", "LO");
define("LangMO", "Vergelijk deze waarneming met al mijn waarnemingen van dit object");
define("LangMOText", "MO");
define("LangDetail", "Details van deze waarneming");
define("LangDetailText", "D");
define("LangDetailDrawingText", "T");
define("LangPreviousObservation", "Vorige waarneming");
define("LangNextObservation", "Volgende waarneming");

define("LangIndex1", "10 laatst toegevoegde waarnemingen:");

// content/new_observation.php

define("LangNewObservationTitle",  "Nieuwe waarneming");
define("LangNewObservationSubtitle1a", "Zoek het object op in de databank");
define("LangNewObservationSubtitle1abis", " of ");
define("LangNewObservationSubtitle1b", "importeer waarnemingen vanuit CSV bestand");
define("LangNewObservationSubtitle2", "Controleer de gegevens van het object");
define("LangNewObservationSubtitle3", "Vul de details van de waarneming in (* verplichte velden)");
define("LangNewObservationButton1", "Zoek object");
define("LangNewObservationField1", "Datum *");
define("LangNewObservationField2", "Tijd (UT)");
define("LangNewObservationField3", "Tijd (local time)");

define("LangNewObservationMonth1", "Januari");
define("LangNewObservationMonth2", "Februari");
define("LangNewObservationMonth3", "Maart");
define("LangNewObservationMonth4", "April");
define("LangNewObservationMonth5", "Mei");
define("LangNewObservationMonth6", "Juni");
define("LangNewObservationMonth7", "Juli");
define("LangNewObservationMonth8", "Augustus");
define("LangNewObservationMonth9", "September");
define("LangNewObservationMonth10", "Oktober");
define("LangNewObservationMonth11", "November");
define("LangNewObservationMonth12", "December");

define("LangNewComet1", "Magnitude");
define("LangNewComet2", "Onzeker");
define("LangNewComet3", "Zwakker dan");
define("LangNewComet4", "Vergroting");
define("LangNewComet5", "Schattingsmethode");
define("LangNewComet6", "Gebruikte sterrenkaart");
define("LangNewComet7", "Meer info over de codes");
define("LangNewComet8", "Condensatiegraad");
define("LangNewComet9", "Coma");
define("LangNewComet10", "Lengte van de staart");
define("LangNewComet11", "Positiehoek van de staart");
define("LangNewComet12", "graden");
define("LangNewComet13", "boogminuten");

// control/validate_account.php

define("LangValidateAccountMessage1", "Gelieve alle velden in te vullen!");
define("LangValidateAccountMessage2", "Gelieve uw paswoord te bevestigen!");
define("LangValidateAccountMessage3", "Fout emailadres!");
define("LangValidateAccountEmailLine1", "Details deepskylog account: ");
define("LangValidateAccountEmailLine1bis", "Gebruikersnaam: ");
define("LangValidateAccountEmailLine2", "Email: ");
define("LangValidateAccountEmailLine3", "Naam : ");
define("LangValidateAccountEmailLine4", "Deze email werd automatisch verstuurd door de deepskylog toepassing");
define("LangValidateAccountEmailTitle", "DeepskyLog - registratie");
define("LangValidateAccountEmailTitleObject", "DeepskyLog - Object - ");
define("LangValidateAccountEmailTitleObjectObserver", "door waarnemer ");
define("LangValidateAccountMessage4", "Er is al een gebruiker met dezelfde naam, gelieve een andere te kiezen!");
define("LangValidateAccountMessage5", "De aanpassingen zijn succesvol weggeschreven!");
define("LangValidateAccountMessage", "Boodschap");

// control/validate_observation.php

define("LangValidateObservationMessage1", "Je hebt de verplichte velden niet allemaal ingevuld!");
define("LangValidateObservationMessage6", "Gelieve alleen tekeningen kleiner dan 100kb toe te voegen!");

// control/validate_search_object.php

define("LangValidateSearchObjectTitle1", "Geen objecten gevonden!");
define("LangValidateSearchObjectMessage1", "Alle velden moeten worden ingevuld!");
define("LangValidateSearchObjectMessage2", "Sorry, je zoekopdracht heeft geen resultaat opgeleverd.");
define("LangValidateSearchObjectMessage3", "Zoek opnieuw");

// control/validate_site.php

define("LangValidateSiteMessage1",  "Alle velden moeten worden ingevuld!");
define("LangValidateSiteMessage2", "De locatie is toegevoegd aan de databank");
define("LangValidateSiteMessage3", "Locatie toegevoegd");
define("LangValidateSiteMessage4", "Locatie gewijzigd");
define("LangValidateSiteMessage5", "De locatiegegevens zijn gewijzigd in de databank");

// control/validate_eyepiece.php

define("LangValidateEyepieceMessage1",  "Alle velden moeten worden ingevuld!");
define("LangValidateEyepieceMessage2", "Het oculair is toegevoegd aan de databank");
define("LangValidateEyepieceMessage3", "Oculair toegevoegd");
define("LangValidateEyepieceMessage4", "Oculair aangepast");
define("LangValidateEyepieceMessage5", "Het oculair is aangepast in de databank");

// control/validate_observer.php

define("LangValidateObserverMessage1", "De update van de gebruiker was succesvol!");
define("LangValidateObserverMessage2", "Gebruiker geupdated");

// error.php

define("LangErrorTitle", "Fout");

// message.php

define("LangMessageTitle", "Boodschap");

// control/validate_location.php

define("LangValidateLocationMessage1", "Gelieve alle velden in te vullen!");
define("LangValidateLocationMessage2", "De waarnemingsplaats is toegevoegd aan de database");

// control/validate_intrument.php

define("LangValidateInstrumentMessage1", "Gelieve alle velden in te vullen!");
define("LangValidateInstrumentMessage2", "Gelieve een van beiden in te vullen: brandpuntsafstand OF f/d!");
define("LangValidateInstrumentMessage3", "Het instrument is toegevoegd aan de databank!");
define("LangValidateInstrumentMessage4", "De instrumentgegevens zijn gewijzigd in de databank!");
define("LangValidateInstrumentMessage", "Boodschap");

// content/setup_query_objects.php
 
define("LangQueryObjectsTitle", "Zoek objecten");
define("LangQueryObjectsField1", "Objectnaam");
define("LangQueryObjectsField2", "Sterrenbeeld");
define("LangQueryObjectsField3", "Magnitude zwakker dan");
define("LangQueryObjectsField4", "Magnitude helderder dan");
define("LangQueryObjectsField4Explanation", "");
define("LangQueryObjectsField5", "Oppervlaktehelderheid zwakker dan");
define("LangQueryObjectsField6", "Oppervlaktehelderheid helderder dan");
define("LangQueryObjectsField6Explanation", "");
define("LangQueryObjectsField7", "Minimum rechte klimming");
define("LangQueryObjectsField8", "Maximum rechte klimming");
define("LangQueryObjectsField9", "Minimum declinatie"); 
define("LangQueryObjectsField10", "Maximum declinatie");
define("LangQueryObjectsField11", "Type"); 
define("LangQueryObjectsField12", "Atlaspagina");
define("LangQueryObjectsField13", "Minimum grootte");
define("LangQueryObjectsField14", "Maximum grootte");
define("LangQueryObjectsField15", "Minimum breedtegraad");
define("LangQueryObjectsField16", "Maximum breedtegraad");
define("LangQueryObjectsField17", "Maximum contrast reserve");
define("LangQueryObjectsField18", "Minimum contrast reserve");
define("LangQueryObjectsField19", "In lijst");
define("LangQueryObjectsField20", "Niet in lijst");
define("LangQueryObjectsButton1", "Zoek objecten");
define("LangQueryObjectsButton2", "Wis velden");
define("LangQueryObjectsUrano", "Uranometria");
define("LangQueryObjectsUranonew", "Uranometria (2de editie)");
define("LangQueryObjectsSkyAtlas", "Sky Atlas");
define("LangQueryObjectsMsa", "Millenium Star Atlas");
define("LangQueryObjectsTaki", "Taki Atlas");
define("LangQueryObjectsPsa", "Pocket Sky Atlas");
define("LangQueryObjectsTorresB", "Tritatlas B (Torres)");
define("LangQueryObjectsTorresBC", "Triatlas BC (Torres)");
define("LangQueryObjectsTorresC", "Triatlas C (Torres)");

define("LangQueryCometObjectsField1", "Minimum vergroting");
define("LangQueryCometObjectsField2", "Maximum vergroting");
define("LangQueryCometObjectsField3", "Minimum condensatiegraad");
define("LangQueryCometObjectsField4", "Maximum condensatiegraad");
define("LangQueryCometObjectsField5", "Minimum coma (boogminuten)");
define("LangQueryCometObjectsField6", "Maximum coma (boogminuten)");
define("LangQueryCometObjectsField7", "Minimum staartlengte (boogminuten)");
define("LangQueryCometObjectsField8", "Maximum staartlengte (boogminuten)");
define("LangQueryCometObjectsField9", "Minimum positiehoek");
define("LangQueryCometObjectsField10", "Maximum positiehoek");

// content/top_observers.php

define("LangTopObserversTitle", "Meest actieve waarnemers");
define("LangTopObserversHeader1", "Positie");
define("LangTopObserversHeader2", "Waarnemer");
define("LangTopObserversHeader3", "Aantal waarnemingen");
define("LangTopObserversHeader4", "Waarnemingen laatste jaar");
define("LangTopObserversHeader5",  "Messier objecten");
define("LangTopObserversHeader5b", "Caldwell objecten");
define("LangTopObserversHeader5c", "H400 objecten");
define("LangTopObserversHeader5d", "H II objecten");
define("LangTopObserversHeader6", "Verschillende objecten");
define("LangTopObservers1", "Totaal");

// content/details_observer_messier
define("LangTopObserversMessierHeader1", "Overzicht waargenomen Messier objecten");
define("LangTopObserversMessierHeader2", "Overzicht waargenomen");
define("LangTopObserversMessierHeader3", "objecten");

// content/top_objects.php

define("LangTopObjectsTitle", "Meest bekeken objecten");
define("LangTopObjectsHeader1", "Positie");
define("LangTopObjectsHeader2", "Object");
define("LangTopObjectsHeader3", "Type");
define("LangTopObjectsHeader4", "Sterrenbeeld");
define("LangTopObjectsHeader5", "Aantal waarnemingen");

// new variables defined from version 1.1 onwards
 
// content/setup_observations_query.php
 
define("LangQueryObservationsTitle", "Zoek waarnemingen");
define("LangQueryObservationsMessage1", "Enkel waarnemingen met tekening");
define("LangQueryObservationsMessage2", "Beschrijving bevat");
define("LangFromDate", "Vanaf");
define("LangTillDate", "Tot en met");
define("LangObservationQueryError1", "Je hebt geen zoekopdracht gespecifieerd.");
define("LangObservationOR", "of");
define("LangObservationQueryError2", "Zoek opnieuw");
define("LangObservationQueryError3", "Bekijk alle waarnemingen");
define("LangObservationNoResults", "Sorry, je zoekopdracht heeft geen resultaten opgeleverd");
define("LangQueryObservationsButton1", "Zoek waarnemingen");
define("LangQueryObservationsButton2", "Wis velden");

// remove instrument/location column
define("LangRemove","verwijder");

// content/new_observationcsv.php
define("LangCSVTitle", "Importeer waarnemingen vanuit CSV bestand");
define("LangCSVMessage1", "Dit formulier geeft u de mogelijkheid om meerdere waarnemingen tegelijkertijd toe te voegen door middel van een CSV bestand (comma seperated value). Op deze manier kan u gemakkelijk en snel meerdere waarnemingen ineens invoeren. Het formulier laat u ook toe om vroegere waarnemingen, reeds bijgehouden in een of andere databank, op een gemakkelijke manier toe te voegen aan DeepskyLog. Ter informatie: alleen waarnemingen met uw naam (voornaam + naam voluit) zullen toegevoegd worden.");
define("LangCSVMessage2", "Het CSV bestand moet beginnen met onderstaande definitie: <b>(NIEUW FORMAAT!!!)</b>");
define("LangCSVMessage3", "Object;Observer;Datum;UT;Locatie;Instrument;Oculair;Filter;Lens;Seeing;LimMag;Zichtbaarheid;Taal;Beschrijving");
define("LangCSVMessage4", "Gevolgd door de eigenlijke waarnemingen in hetzelfde formaat, bv: <br><br>Object;Observer;Date;UT;Location;Instrument;Oculair;Filter;Lens;Seeing;LimMag;Visibility;Language;Description<br>NGC 2392;Piet Janssens;21-01-2005;20:45;Aalst;Obsession 15\";31mm Nagler;Lumicon O-III filter;Televue 2x Barlow;2;4.0;3;nl;Mooie planetaire nevel met een zeer heldere centrale ster!<br>M 35;Piet Janssens;21-01-2005;20:53;Aalst;Obsession 15\";;;;2;4.0;1;nl;Ongeveer dertig leden tellend in gebogen lijnen.<br>...<br><br>Seeing wordt aangegeven door een nummer tussen 1 en 5 (1=uitstekend, 2=goed, 3=middelmatig, 4=zwak, 5=slecht).<br>Zichtbaarheid wordt aangegeven door een nummer tussen 1 en 7 (1=Zeer eenvoudig, helder object, 2=Object eenvoudig te zien bij direct kijken, 3=Object zichtbaar bij direct kijken, 4=Perifeer kijken nodig om object te zien, 5=Object amper zichtbaar bij perifeer kijken, 6=Zichtbaarheid van object is twijfelachtig, 7=Object niet zichtbaar).<br>Een waarneming met het blote oog moet 'Naked Eye' als instrument bevatten.<br>Taal moet de korte naam van de taal zijn (nl voor Nederlands)");
define("LangCSVMessage5", "Opgepast!<p>De instrumenten, waarnemingsplaatsen, oculairs, filters en objecten in het CSV bestand moeten reeds vooraf bekend zijn in DeepskyLog. Indien dit niet het geval is, zal er een foutboodschap verschijnen en worden er geen(!) waarnemingen toegevoegd. De ontbrekende informatie moet manueel toegevoegd worden totdat er geen foutboodschappen meer verschijnen. Indien alles goed gaat, worden de ingevoerde waarnemingen getoond in het overzicht van alle waarnemingen. Vergewis u er van dat u geen twee maal hetzelfde bestand importeert, aangezien dubbele waarnemingen achteraf &eacute;&eacute;n voor &eacute;&eacute;n manueel verwijderd moeten worden!");
define("LangCSVMessage6", "CSV bestand ");
define("LangCSVMessage7", "Naam;AlternatieveNaam;RA;Decl;Sterrenbeeld;Type;Magnitude;OppervlakteHelderheid;Diameter;Positiehoek;Pagina;ContrastReserve;OptimaleVergroting;Gezien");
define("LangCSVError1", "Het CSV bestand kon niet ingelezen worden omdat: ");
define("LangCSVError2", "De onderstaande objecten niet gekend zijn in DeepskyLog");
define("LangCSVError3", "De onderstaande waarnemingsplaatsen niet gekend zijn in DeepskyLog");
define("LangCSVError4", "De onderstaande instrumenten niet gekend zijn in DeepskyLog");
define("LangCSVError5", "De onderstaande filters niet gekend zijn in DeepskyLog");
define("LangCSVError6", "De onderstaande oculairs niet gekend zijn in DeepskyLog");
define("LangCSVError7", "De onderstaande lenzen niet gekend zijn in DeepskyLog");
define("LangCSVButton", "Importeer!");
define("LangValidateCSVMessage", "Importeren CSV bestand succesvol!");

//List import
define("LangCSVListTitle", "Importeer objecten vanuit CSV bestand naar uw lijst");
define("LangCSVListMessage1", "Dit formulier geeft u de mogelijkheid om meerdere objecten tegelijkertijd toe te voegen door middel van een CSV bestand (comma seperated value). Op deze manier kan u gemakkelijk en snel meerdere objecten ineens invoeren. Het formulier laat u ook toe om vroegere objecten, reeds bijgehouden in een of andere databank, op een gemakkelijke manier toe te voegen aan uw DeepskyLog lijst.");
define("LangCSVListMessage2", "Het CSV bestand moet beginnen met onderstaande definitie in de eerste lijn, de volgende lijnen bevatten de data:");
define("LangCSVListMessage3", "Objectnaam;Te tonen naam (optioneel);vrije velden(ze worden niet in rekening gebracht...)");
define("LangCSVListMessage4", "");
define("LangCSVListMessage5", "Opgepast!<p>De objecten in het CSV bestand moeten reeds vooraf bekend zijn in DeepskyLog. Indien dit niet het geval is, zal er een foutboodschap verschijnen en worden ze niet toegevoegd. De verkeerde informatie moet manueel aangepast worden totdat er geen foutboodschappen meer verschijnen. Indien alles goed gaat, worden de ingevoerde objecten getoond in het overzicht van de lijst. Dubbele vermeldingen van objecten worden niet herhaald in de lijst!");
define("LangCSVListMessage6", "CSV bestand ");
define("LangCSVListMessage7", "NGC 7000;NA Nebula;...");
define("LangCSVListButton", "Importeer!");

// content/manage_csv.php
define("LangNewObjectSubtitle1b", "Manage objecten vanuit CSV bestand");
define("LangCSVObjectTitle", "Managen van objecten vanuit CSV bestand");
define("LangCSVObjectMessage1", "Dit formulier geeft u de mogelijkheid om meerdere objecten tegelijkertijd te managen door middel van een CSV bestand (comma seperated value). Op deze manier kan u gemakkelijk en snel meerdere objecten ineens invoeren, alternatieve namen geven, part-ofs instellen enz.");
define("LangCSVObjectMessage2", "Het CSV bestand moet beginnen met onderstaande definitie als het om benamingen gaat: </b>");
define("LangCSVObjectMessage3", "Opdracht;Object;Catalog;Catalogindex;");
define("LangCSVObjectMessage4", "of als het om data gaat");
define("LangCSVObjectMessage5", "Opdracht;Object;;Data");
define("LangCSVObjectMessage6", "CSV bestand ");
define("LangCSVObjectMessage7", "");
define("LangCSVObjectError1", "Het CSV bestand kon niet ingelezen worden omdat: ");
define("LangCSVObjectError2", "De onderstaande objecten niet gekend zijn in DeepskyLog");
define("LangCSVObjectError3", "De onderstaande opdrachten niet gekend zijn");
define("LangCSVObjectError4", "De onderstaande data niet past");
define("LangCSVObjectButton", "Importeer!");
define("LangValidateCSVObjectMessage", "Importeren CSV bestand succesvol!");

// control/check_login.php

define("LangErrorWrongPassword", "Verkeerd paswoord, probeer opnieuw!");
define("LangErrorEmptyPassword", "Gelieve uw paswoord en/of gebruikersnaam in te vullen!");
define("LangErrorPasswordNotValidated", "Uw account is nog niet gevalideerd door een administrator!");

// Visibility for objects
define("LangVisibility1", "Zeer eenvoudig, helder object");
define("LangVisibility2", "Object eenvoudig te zien bij direct kijken");
define("LangVisibility3", "Object zichtbaar bij direct kijken");
define("LangVisibility4", "Perifeer kijken nodig om object te zien");
define("LangVisibility5", "Object amper zichtbaar bij perifeer kijken");
define("LangVisibility6", "Zichtbaarheid van object is twijfelachtig");
define("LangVisibility7", "Object niet zichtbaar");

// content/selected_observations.php
 
define("LangSelectedObservationsTitle2", "Overzicht geselecteerde waarnemingen");
define("LangSelectedObservationsTitle3", "Overzicht van de waarnemingen van het laatste jaar");
 
// lib/util.php

define("LangPDFTitle", "DeepskyLog lijst met objecten");
define("LangPDFTitle2", "DeepskyLog waarnemingen");
define("LangPDFMessage1", "Naam");
define("LangPDFMessage2", "Alternatieve naam");
define("LangPDFMessage3", "Rechte klimming");
define("LangPDFMessage4", "Declinatie");
define("LangPDFMessage5", "Type");
define("LangPDFMessage6", "Sterrenbeeld");
define("LangPDFMessage7", "Mag.");
define("LangPDFMessage8", "Opp. mag.");
define("LangPDFMessage9", "Diameter");
define("LangPDFMessage10", "Locatie");
define("LangPDFMessage11", "Instrument");
define("LangPDFMessage12", " in ");
define("LangPDFMessage13", "Waargenomen door ");
define("LangPDFMessage14", " op ");
define("LangPDFMessage15", "Beschrijving");
define("LangPDFMessage16", "Pos. hoek");
define("LangPDFMessage17", "Contr. res.");
define("LangPDFMessage18", "Opt. vergr.");
define("LangPDFMessage19", "Klaargemaakt voor ");
define("LangPDFMessage20", "met een  ");
define("LangPDFMessage21", "te ");
define("LangPDFMessage22", "Pagina ");
define("LangNumberOfRecords", "resultaten");
define("LangPDFTitle3", "DeepskyLog komeet waarnemingen");

// deepsky/content/overview_observations_compact.php
define("LangOverviewCompactDescription", "Beschrijving");

define("LangContrastNotLoggedIn", "Contrast reserve kan enkel worden berekend als je  bent ingelogd...");
define("LangContrastNoStandardLocation", "Contrast reserve kan enkel worden berekend als de standaard waarneemplaats is gezet...");
define("LangContrastNoStandardInstrument", "Contrast reserve kan enkel worden berekend als een standaard instrument is gezet...");
define("LangContrastNoEyepiece", "Contrast reserve kan enkel worden berekend als het standaardinstrument een vaste vergroting heeft of als er oculairs gedefinieerd zijn...");
define("LangContrastNoLimMag", "Contrast reserve kan enkel worden berekend als er een typische grensmagnitude of hemelachtergrond is gezet voor je standaard waarneemplaats...");
define("LangContrastNoDiameter", "Contrast reserve kan enkel worden berekend als het object een gekende diameter heeft");
define("LangContrastNoMagnitude", "Contrast reserve kan enkel worden berekend als het object een gekende magnitude heeft");
define("LangContrastNotVisible", " is niet zichtbaar vanuit ");
define("LangContrastQuestionable", "Zichtbaarheid van ");
define("LangContrastQuestionableB", " is twijfelachtig vanuit ");
define("LangContrastDifficult", " is moeilijk zichtbaar vanuit ");
define("LangContrastQuiteDifficult", " is tamelijk moeilijk zichtbaar vanuit ");
define("LangContrastEasy", " is eenvoudig zichtbaar vanuit ");
define("LangContrastVeryEasy", " is zeer eenvoudig zichtbaar vanuit ");
define("LangContrastPlace", " met je ");

// Names of Atlasses
$AtlasNameurano = "Uranometria";
$AtlasNameurano_new = "Uranometria (2de editie)";
$AtlasNamesky = "Sky Atlas";
$AtlasNamemilleniumbase = "Millenium Star Atlas";
$AtlasNametaki = "Taki Atlas";
$AtlasNamepsa = "Pocket Sky Atlas";
$AtlasNametorresB = "Triatlas B (Torres)";
$AtlasNametorresBC = "Triatlas BC (Torres)";
$AtlasNametorresC = "Triatlas C (Torres)";

// Types of Observations
$ASTER = "Asterisme";
$BRTNB = "Heldere nevel";
$CLANB = "Cluster met nevel";
$DRKNB = "Donkere nevel";
$DS    = "Dubbelster";
$EMINB = "Emissie nevel";
$ENRNN = "Emissie en Reflectie nevel";
$ENSTR = "Emissienevel rond ster";
$GALCL = "Galaxy cluster";
$GALXY = "Sterrenstelsel";
$GLOCL = "Bolvormige sterrenhoop";
$GXADN = "Diffuse Nevel in galaxy";
$GXAGC = "Bolhoop in galaxy";
$GACAN = "Cluster met nevel in galaxy";
$HII = "H-II gebied";
$LMCCN = "Cluster met nevel in LMC";
$LMCDN = "Diffuse Nevel in LMC";
$LMCGC = "Bolhoop in LMC";
$LMCOC = "Open sterrenhoop in LMC";
$NONEX = "Niet bestaand";
$OPNCL = "Open sterrenhoop";
$PLNNB = "Planetaire nevel";
$REFNB = "Reflectie nevel";
$RNHII = "Reflectie nevel en H-II gebied";
$SMCCN = "Cluster met nevel in SMC";
$SMCDN = "Diffuse nevel in SMC";
$SMCGC = "Bolhoop in SMC";
$SMCOC = "Open sterrenhoop in SMC";
$SNREM = "Supernova restant";
$STNEB = "Nevel rond ster";
$QUASR = "Quasar";
$WRNEB = "Wolf Rayet nevel";
$AA1STAR = "Ster";
$AA2STAR = "Dubbelster";
$AA3STAR = "3 sterren";
$AA4STAR = "4 sterren";
$AA8STAR = "8 sterren";

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

$ICQ_REFERENCE_KEY_AA = "AA - A.A.V.S.O. Variable Star Atlas";
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
$comets = "Kometen";
?>
