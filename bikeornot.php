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

//Gap between hours on page, started off as 3, but then page changed
define('HOUR_SLOTS', 1);

//URL parameter names
define ('POSTCODE_PARAM',"postcode");
define ('MIN_TEMP_PARAM',"mintemp");
define ('MAX_TEMP_PARAM',"maxtemp");
define ('FIRST_HOUR_PARAM',"firsthour");
define ('SECOND_HOUR_PARAM',"secondhour");
define ('GOOD_WEATHER_PARAM', "goodweather");


class weatherPage {
/**
 * Gets the index in the table containing weather symbols and temps
 * The images with the words as a title, are in a table with one hour (aka HOUR_SLOTS) increments
 * Returns 0 if data not available, e.g. 6am required and the earliest slot is 9am
 *
 * @param int $startHour The first hour on the weather page
 * @param int $requiredHour The hour slot required
 * @return int The index (i.e. column) in the table of weather slots on the webpage
 */	
	public function getIndex($startHour, $requiredHour) {
		//
		if ($startHour <= $requiredHour) {
			return (($requiredHour - $startHour) / HOUR_SLOTS) + 1;
		} else {
			return 0;
		}
		
	}
	
/**
 * Find the image for the weather and get the title attribute 
 *
 * @param mixed $xpath The xpath object for the webpage
 * @param int $index The index (i.e. column) in the table of weather slots on the webpage
 * @return string The description of that weather type or "(not found)" if no such slot on page
 */	
	public function getWeatherWords($xpath, $index) {
		
		
		$weatherWordTitle = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[1]/td['.strval($index).']/span/img/@title');#
		if ($weatherWordTitle->length <> 0) { /*This means we found it */
			$weatherWords = $weatherWordTitle->item(0)->nodeValue;
		} else {
			return "(not found)";
		}
	
		if ($weatherWords) {
			return(strtolower($weatherWords));
		} else {
			return "(not found)";
		}
		
	}
	
	public function getTemperature($xpath, $dom, $index) {
		//Find the temperature figure
		$temperatureTitle = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[2]/td['.strval($index).']/span/span/span[1]/text()');
		if ($temperatureTitle->length <> 0) { /*This means we found it */
			$temperature = $temperatureTitle->item(0);
		} else {
			return "(not found)";
		}
		if ($temperature) {
			return($dom->saveHTML($temperature));
		} else {
			return "(not found)";
		}
	}
	
}

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