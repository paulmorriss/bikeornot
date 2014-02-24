<?php
//Set preferences from prefs.php, store in a prefs class and then serialise to a cookie

include_once('include.php');

$storePrefs = new prefs();

//Store current version so we can be futureproffed
$storePrefs->version = PREFS_VERSION;
$storePrefs->postcode = strtolower($_POST['postcode']);
$storePrefs->minTemp = $_POST['mintemp'];
$storePrefs->maxTemp = $_POST['maxtemp'];
$storePrefs->firstHour = 0+$_POST['firsthour'];
$storePrefs->secondHour = 0+$_POST['secondhour'];

foreach($simplifiedChoices as $internal => $readable) {
	$storePrefs->weatherChoices[$internal] = $_POST[$internal];
}


setcookie(PREFS_COOKIE_NAME,serialize($storePrefs),time()+60*60*24*365 );
header("Location: bikeornot.php");

?>