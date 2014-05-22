<?php
//Set preferences from prefs.php, store in a prefs class and then serialise to a cookie

include_once('include.php');

$storePrefs = new prefs(array());

$storePrefs->postcode = strtolower($_POST['postcode']);
$storePrefs->minTemp = $_POST['mintemp'];
$storePrefs->maxTemp = $_POST['maxtemp'];
$storePrefs->firstHour = 0+$_POST['firsthour'];
$storePrefs->secondHour = 0+$_POST['secondhour'];

$weatherChoices = array();
foreach(prefs::$simplifiedWeatherTypes as $internal => $readable) {
	$weatherChoices[$internal] = $_POST[$internal];
}

$storePrefs->setSimplifiedWeatherTypes($weatherChoices);

//This is necessary on asmallorange hosting as otherwise a mod security rule prevents it working
header("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json");
setcookie(PREFS_COOKIE_NAME,serialize($storePrefs),time()+60*60*24*365 );
header("Refresh: 0; bikeornot.php");

?>