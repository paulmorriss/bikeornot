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
//WBNice user configuration for acceptable weather, max/min temp, timeslot to choose, 
//HTML5 local storage to store that (is a cookie enough?), API
//TODO rounding if hour slots go back to 3

//Gap between hours on page, started off as 3, but then page changed
define('HOUR_SLOTS', 1);

//URL parameter names
define ('POSTCODE_PARAM',"postcode");
define ('MIN_TEMP_PARAM',"mintemp");
define ('MAX_TEMP_PARAM',"maxtemp");
define ('FIRST_HOUR_PARAM',"firsthour");
define ('SECOND_HOUR_PARAM',"secondhour");
define ('GOOD_WEATHER_PARAM', "goodweather");

//Defaults
define ('DEFAULT_MIN_TEMP', 2);
define ('DEFAULT_MAX_TEMP', 25);
define ('DEFAULT_POSTCODE', "hp14");
define ('DEFAULT_FIRST_HOUR', 8);
define ('DEFAULT_SECOND_HOUR', 17);

// Which weather is biking weather
$bikingWeatherDefault = array (
"clear sky" => true,
"cloudy" => true,
"drizzle" => false,
"fog" => true,
"foggy" => true,
"grey cloud" => true,
"heavy rain" => false,
"heavy rain shower" => false,
"heavy showers" => false,
"heavy snow" => false,
"light cloud" => true,
"light rain" => false,
"light rain shower" => false,
"light showers" => false,
"light snow" => false,
"light snow shower" => false,
"light snow showers" => false,
"mist" => true,
"misty" => true,
"partly cloudy" => true,
"sleet" => false,
"sleet showers" => false,
"sunny" => true,
"sunny intervals" => true,
"thick cloud" => true,
"thunder storm" => false,
"thundery shower" => false,
"white cloud" => true
);

function getIndex($startHour, $requiredHour) {
	//Gets the index in the table containing weather symbols and temps
	//The images with the words as a title, are in a table with three hour increments
	//Returns 0 if data not available, e.g. 6am required and the earliest slot is 9am
	if ($startHour <= $requiredHour) {
		return (($requiredHour - $startHour) / HOUR_SLOTS) + 1;
	} else {
		return 0;
	}
	
}

function getWeatherWords($xpath, $index) {
	//Find the image for the weather and get the title attribute
	
	global $bikingWeatherDefault;
	
	$weatherWords = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[1]/td['.strval($index).']/span/img/@title')->item(0)->nodeValue;

	if ($weatherWords) {
		if (!array_key_exists(strtolower($weatherWords),$bikingWeatherDefault)) {
			//Let me know if this is a new word
			mail("paulmorriss@iname.com","new weather word: ".$weatherWords,"");
		}
		return($weatherWords);
	} else {
		return "(not found)";
	}
	
}

function getTemperature($xpath, $dom, $index) {
	//Find the temperature figure
	$temperature = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[2]/td['.strval($index).']/span/span/span[1]/text()')->item(0);
	if ($temperature) {
		return($dom->saveHTML($temperature));
	} else {
		return "(not found)";
	}
}


function getGet($key, $defaultValue) {
	//Gets the given key from the query string via $_GET. Handles empty or non-existent variables
	if (isset($_GET[$key]) && $_GET[$key] !== "") {
		return $_GET[$key];
	} else {
		return $defaultValue;
	}
}
	//Get query parameters or defaults
	$postcode = strtolower(getGet(POSTCODE_PARAM,DEFAULT_POSTCODE));
	$url = 'http://bbc.co.uk/weather/'.$postcode;

	$minTemp = getGet(MIN_TEMP_PARAM, DEFAULT_MIN_TEMP);
	$maxTemp = getGet(MAX_TEMP_PARAM, DEFAULT_MAX_TEMP);

	$firstHour = getGet(FIRST_HOUR_PARAM, DEFAULT_FIRST_HOUR);
	$secondHour = getGet(SECOND_HOUR_PARAM, DEFAULT_SECOND_HOUR);

	//Get acceptable weather words
	$bikingWeather = array();
	
	if (isset($_GET[GOOD_WEATHER_PARAM]) && $_GET[GOOD_WEATHER_PARAM] !== "") {
		$goodWeatherList = explode(",", urldecode($_GET[GOOD_WEATHER_PARAM]));
		foreach ($goodWeatherList as $goodWord) {
			$bikingWeather[$goodWord] = true;
		}
	} else {
		$bikingWeather = $bikingWeatherDefault;
	}
	
	$pageHTML = file_get_contents($url);
	//Displayed page looks different to browser, possibly because server isn't in the UK, so this helps with debugging
	if ($_GET["debug"]) {
		var_dump($pageHTML);
	}
	if ($pageHTML) {
		$dom = new DomDocument();
		
		$dom->loadHTML($pageHTML);
		$xpath = new DOMXPath($dom);
		$startHour = $xpath->query('//*[@id="hourly"]/div[3]/table/thead/tr/th[2]/span[1]/text()');

		if ($startHour) {
			$startHour = 0+$dom->saveHTML($startHour->item(0));
		}
		//TODO error handling if parse fails
		$index = getIndex($startHour, $firstHour);
		$weatherWords = getWeatherWords($xpath, $index);
		$temperature = getTemperature($xpath, $dom, $index);
		?>
<h1>
        <?php
		print $weatherWords." ".$temperature."&deg;C - ";
		if (!array_key_exists(strtolower($weatherWords),$bikingWeather)) {
			print "don't bike to work, ";
		} else if ($bikingWeather[strtolower($weatherWords)] && ($temperature >= $minTemp) && ($temperature <= $maxTemp)) {
			print "bike to work, ";
		} else {
			print "don't bike to work, ";
		}
		$index = getIndex($startHour, $secondHour);
		$weatherWords = getWeatherWords($xpath, $index);
		$temperature = getTemperature($xpath, $dom, $index);
		print $weatherWords." ".$temperature."&deg;C - ";
		if (!array_key_exists(strtolower($weatherWords),$bikingWeather)) {
			print "don't bike home";
		} else if ($bikingWeather[strtolower($weatherWords)] && ($temperature >= $minTemp) && ($temperature <= $maxTemp)) {
			print "bike home";
		} else {
			print "don't bike home";
		}
		?>
</h1>

        <?php
		
	}
?>
</body>