<?php /* Entry page for bike or not web app.
   ** See bikeornot.php for more detail.
   
   ** Check to see if preferences cookie set. 
   ** If so then go to bikeornot.php otherwise go to prefs page
   */
include_once('include.php');


if (array_key_exists(PREFS_COOKIE_NAME, $_COOKIE)) {
	header('Location: bikeornot.php');
} else {
	header('Location: prefs.php');
}