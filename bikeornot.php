<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bike or not</title>
</head>
<body>
<?php 
//Screenscrape BBC weather page to find a) which is the first hour and then b) weather conditions at, say, 0900 and 1800
//TODO testing before turn into something simpler, like just a picture, or a RPi LED, based on whether can bike both ways
//TODO better error handling if 6am not available
//WBNice user configuration for acceptable weather, max/min temp, timeslot to choose, 
//HTML5 local storage to store that, API

define('HOUR_SLOTS', 3);

// Which weather is biking weather
$bikingWeather = array (
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
"light rain" => false,
"light rain shower" => false,
"light showers" => false,
"light snow" => false,
"light snow shower" => false,
"light snow showers" => false,
"mist" => true,
"misty" => true,
"sleet" => false,
"sleet showers" => false,
"sunny" => true,
"sunny intervals" => true,
"thunder storm" => false,
"thundery shower" => false,
"white cloud" => true
);

define ('MIN_TEMP', 2);
define ('MAX_TEMP', 25);

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
	$weatherWords = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[1]/td['.strval($index).']/span/img/@title')->item(0)->nodeValue;
	if ($weatherWords) {
		return($weatherWords);
	} else {
		return "";
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

	$url = 'http://bbc.co.uk/weather/hp14';
	$pageHTML = file_get_contents($url);
	//Displayed page looks different to browser, possibly because server isn't in the UK, so this helps with debugging
//	var_dump($pageHTML);
	if ($pageHTML) {
		$dom = new DomDocument();
		
		$dom->loadHTML($pageHTML);
		$xpath = new DOMXPath($dom);
		$firstHour = $xpath->query('//*[@id="hourly"]/div[3]/table/thead/tr/th[2]/span[1]/text()');

		if ($firstHour) {
			$firstHour = 0+$dom->saveHTML($firstHour->item(0));
		}
		//TODO error handling if parse fails
		$index = getIndex($firstHour, 9);
		$weatherWords = getWeatherWords($xpath, $index);
		$temperature = getTemperature($xpath, $dom, $index);
		?>
        <h1>
        <?php
		print $weatherWords." ".$temperature."&deg;C - ";
		
		if ($bikingWeather[strtolower($weatherWords)] && ($temperature >= MIN_TEMP) && ($temperature <= MAX_TEMP)) {
			print "bike to work, ";
		} else {
			print "don't bike to work, ";
		}
		$index = getIndex($firstHour, 18);
		$weatherWords = getWeatherWords($xpath, $index);
		$temperature = getTemperature($xpath, $dom, $index);
		print $weatherWords." ".$temperature."&deg;C - ";
		if ($bikingWeather[strtolower($weatherWords)] && ($temperature >= MIN_TEMP) && ($temperature <= MAX_TEMP)) {
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