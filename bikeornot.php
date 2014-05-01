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
//TODO unit tests
// test getIndex with boundary conditions
//test getweatherwords with static bbc page (pick different slots for different conditiosn)
//test gettemperature similarly
//need new function getstarthour, test similarly
//test class with fixed cookie and _get data


//WBNice user configuration for acceptable weather, max/min temp, timeslot to choose, 
//API
//TODO rounding if hour slots go back to 3
//TODO https://travis-ci.org/ for integration testing
//TODO think about how to run automatically and spot if BBC page structure changes

error_reporting(E_ALL);

include_once('include.php');

	//Use prefs cookies, if present
	
	if (array_key_exists(PREFS_COOKIE_NAME, $_COOKIE)) {
		$storedPrefs = new prefs(array());
		$storedPrefs = unserialize($_COOKIE[PREFS_COOKIE_NAME]);
		
		if ($_GET["debug"]) {
			var_dump($storedPrefs);
		}
		if ($storedPrefs->version !== PREFS_VERSION) {
			//Only ever been one version so nothing to do yet
		}
		
		foreach ($simplifiedMapping as $weatherWord => $simplifiedWord) {
			//TODO this sort of empty string indicating always good weather is crying out for a class
			if (!strcmp($simplifiedMapping[$weatherWord],"")) { //Always good weather
				$bikingWeather[$weatherWord] = true;
			} else	{
				$bikingWeather[$weatherWord] = strcmp($storedPrefs->weatherChoices[$simplifiedMapping[$weatherWord]],"")>0;
			}
		}
		if ($_GET["debug"]) {
			var_dump($bikingWeather);
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
		$startHour = $xpath->query('//*[@id="hourly"]/div[3]/table/thead/tr/th[2]/span[1]/text()');

		if ($startHour) {
			$startHour = 0+$dom->saveHTML($startHour->item(0));
		}
		//TODO error handling if parse fails
		$index = weatherPage::getIndex($startHour, $storedPrefs->firstHour);
		$weatherWords = weatherPage::getWeatherWords($xpath, $index);
		$temperature = weatherPage::getTemperature($xpath, $dom, $index);
		?>
<h1>
        <?php
		print $weatherWords." ".$temperature."&deg;C - ";
		if ($storedPrefs->checkBikingWeather($weatherWords,$temperature)) {
			print "bike to work";
		} else {
			print "don't bike to work, ";
		}
		$index = weatherPage::getIndex($startHour, $storedPrefs->secondHour);
		$weatherWords = weatherPage::getWeatherWords($xpath, $index);
		$temperature = weatherPage::getTemperature($xpath, $dom, $index);
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