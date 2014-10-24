<?php
echo "<script type=\"text/javascript\">";
echo "(function(){
		// Instance the tour
var tour = new Tour({
  steps: [
  {
    element: \".tour1\",
	placement: \"left\",
    title: \"" . LangTour1Title . "\",
    content: \"" . LangTour1Expl . "\"
  },
  {
    element: \".tour2\",
	placement: \"bottom\",
	reflex: \"true\",
	title: \"" . LangTour2Title . "\",
    content: \"" . LangTour2Expl . "\"
  },
  {
    element: \".tour3\",
	placement: \"left\",
    title: \"" . LangTour3Title . "\",
    content: \"" . LangTour3Expl . "\"
  },
  {
    element: \".tour4\",
	placement: \"bottom\",
	reflex: \"true\",
	title: \"" . LangTour4Title . "\",
    content: \"" . LangTour4Expl . "\"
  },
  {
    element: \".tour5\",
	placement: \"right\",
    title: \"" . LangTour5Title . "\",
    content: \"" . LangTour5Expl . "\"
  },
  {
    element: \".tour6\",
	placement: \"bottom\",
	title: \"" . LangTour6Title . "\",
    content: \"" . LangTour6Expl . "\"
  },
  {
    element: \".tour7\",
	placement: \"left\",
	reflex: \"true\",
    title: \"" . LangTour7Title . "\",
    content: \"" . LangTour7Expl . "\"
  }
]});

// Initialize the tour
tour.init();

// Start the tour
tour.start();
}());";
echo "</script>";
?>