<?php
echo "<script type=\"text/javascript\">";
echo "(function(){
		// Instance the tour
var tour = new Tour({
  steps: [
  {
    element: \".tour1\",
	placement: \"left\",
    title: \"" . _('Add one or more instruments') . "\",
    content: \"" . _('Click on Change and select Instruments. Add at least 1 instrument.') . "\"
  },
  {
    element: \".tour2\",
	placement: \"bottom\",
	reflex: \"true\",
	title: \"" . _('Add at least one instrument!') . "\",
    content: \"" . _('DeepskyLog can calculate visibilities of objects when an instrument is inserted. You can also only add new observations when an instrument is known by DeepskyLog.') . "\"
  },
  {
    element: \".tour3\",
	placement: \"left\",
    title: \"" . _('Add one or more locations') . "\",
    content: \"" . _('Click on Change and select Locations. Add at least 1 location.') . "\"
  },
  {
    element: \".tour4\",
	placement: \"bottom\",
	reflex: \"true\",
	title: \"" . _('Add at least one location!') . "\",
    content: \"" . _('DeepskyLog can calculate visibilities of objects when a location is inserted. You can also only add new observations when a location is known by DeepskyLog.') . "\"
  },
  {
    element: \".tour5\",
	placement: \"right\",
    title: \"" . _('Add a picture of yourself') . "\",
    content: \"" . _('Click on your name and select Settings. Add a picture of yourself.') . "\"
  },
  {
    element: \".tour6\",
	placement: \"bottom\",
	title: \"" . _('Add a picture of yourself!') . "\",
    content: \"" . _('When you add a picture, others will see your picture when you send them a message.') . "\"
  },
  {
    element: \".tour7\",
	placement: \"left\",
	reflex: \"true\",
    title: \"" . _('Read your messages') . "\",
    content: \"" . _('Other observers can send you messages in DeepskyLog. Here, you can read the messages.') . "\"
  }
]});

// Initialize the tour
tour.init();

// Start the tour
tour.start();
}());";
echo "</script>";
?>