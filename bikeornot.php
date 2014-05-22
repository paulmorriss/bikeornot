<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bike or not</title>
</head>
<body>
<?php 
//Screenscrape BBC weather page to find a) which is the first hour and then b) weather conditions at, say, 0900 and 1800
//TODO testing before turn into something simpler, like just a picture, or a RPi LED, based on whether can bike both ways
//TODO better error handling if first hour not available, e.g. run midday


//WBNice user configuration for acceptable weather, max/min temp, timeslot to choose, 
//API
//TODO rounding if hour slots go back to 3
//TODO think about how to run automatically and spot if BBC page structure changes
//TODO cope with different version of class when reading from cookie

error_reporting(E_ALL);

include_once('include.php');

	//Use prefs cookies, if present
	
	if (array_key_exists(PREFS_COOKIE_NAME, $_COOKIE)) {
		$storedPrefs = new prefs(array());
		$storedPrefs = unserialize($_COOKIE[PREFS_COOKIE_NAME]);
		
		if ($_GET["debug"]) {
			var_dump($storedPrefs);
		}
	} else {
		$storedPrefs = new prefs($_GET);	
	}
	
	$url = 'http://bbc.co.uk/weather/'.$storedPrefs->postcode;
	if ($_GET["debug"]) {
		var_dump($url);
	}
	$pageHTML = file_get_contents($url);
	//Displayed page may look different to browser, possibly because server isn't in the UK, so this helps with debugging
	if ($_GET["debug"]) {
		var_dump($pageHTML);
	}
	if ($pageHTML) {
		$dom = new DomDocument();

		libxml_use_internal_errors(true); //Prevent warnings on HTML errors in the page
		$dom->loadHTML($pageHTML);
		$xpath = new DOMXPath($dom);
		$weatherPage = new weatherPage;
		$startHour = $weatherPage->getStartHour($xpath, $dom);
		//TODO error handling if parse fails
		$index = $weatherPage->getIndex($startHour, $storedPrefs->firstHour);
		$weatherWords = $weatherPage->getWeatherWords($xpath, $index);
		$temperature = $weatherPage->getTemperature($xpath, $dom, $index);
		?>
<h1>
        <?php
		print $weatherWords." ".$temperature."&deg;C - ";
		if ($storedPrefs->checkBikingWeather($weatherWords,$temperature)) {
			print "bike to work";
		} else {
			print "don't bike to work, ";
		}
		$index = $weatherPage->getIndex($startHour, $storedPrefs->secondHour);
		$weatherWords = $weatherPage->getWeatherWords($xpath, $index);
		$temperature = $weatherPage->getTemperature($xpath, $dom, $index);
		print $weatherWords." ".$temperature."&deg;C - ";
		if ($storedPrefs->checkBikingWeather($weatherWords,$temperature)) {
			print "bike home";
		} else {
			print "don't bike home";
		}
		?>
</h1>

        <?php
		
	}
?>
<a href="prefs.php">Set/change your location and what weather you'll cycle in</a>
</body>