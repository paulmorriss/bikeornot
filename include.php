<?php 
define('PREFS_COOKIE_NAME', 'prefs');
define ('PREFS_VERSION','1.0');

//Gap between hours on page, started off as 3, but then page changed
define('HOUR_SLOTS', 1);

//Defaults
define ('DEFAULT_MIN_TEMP', 2);
define ('DEFAULT_MAX_TEMP', 25);
define ('DEFAULT_POSTCODE', "hp14");
define ('DEFAULT_FIRST_HOUR', 8);
define ('DEFAULT_SECOND_HOUR', 17);


//URL parameter names
define ('POSTCODE_PARAM',"postcode");
define ('MIN_TEMP_PARAM',"mintemp");
define ('MAX_TEMP_PARAM',"maxtemp");
define ('FIRST_HOUR_PARAM',"firsthour");
define ('SECOND_HOUR_PARAM',"secondhour");
define ('GOOD_WEATHER_PARAM', "goodweather");



class prefs {
	//A class to store and supply preferences
	//knows the mapping between simplified choices and the full list


	// Which weather is biking weather.
	// Also useful for a list of all known words
	private static $bikingWeatherDefault = array (
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
	
	//Simplified types of weather 
	// internal value and human readable name for checkboxes on prefs screen
	public static $simplifiedWeatherTypes = array (
	"lightrain" => "Light rain",
	"heavyrain" => "Heavy rain",
	"sleetsnow" => "Sleet/snow",
	"mistfog" => "Mist/fog");
	
	// what they map to
	//"" means always OK
	private static $simplifiedMapping = array (
	"clear sky"  => "",
	"cloudy"  => "",
	"drizzle" => "lightrain",
	"fog" => "mistfog",
	"foggy" => "mistfog",
	"grey cloud" =>"",
	"heavy rain" => "heavyrain",
	"heavy rain shower" => "heavyrain",
	"heavy showers" => "heavyrain",
	"heavy snow" => "sleetsnow",
	"light cloud" =>"",
	"light rain" => "lightrain",
	"light rain shower" => "lightrain",
	"light showers" => "lightrain",
	"light snow" => "sleetsnow",
	"light snow shower" => "sleetsnow",
	"light snow showers" => "sleetsnow",
	"mist" => "mistfog",
	"misty" => "mistfog",
	"partly cloudy"  =>"",
	"sleet" => "sleetsnow",
	"sleet showers" => "lightrain",
	"sunny"  =>"",
	"sunny intervals"  =>"",
	"thick cloud"  =>"",
	"thunder storm" => "heavyrain",
	"thundery shower" => "heavyrain",
	"white cloud" => ""
	);
	
		
	public $version; //So we can stop things breaking if this changes
	public $postcode;
	public $minTemp;
	public $maxTemp;
	public $firstHour;
	public $secondHour;
	public $bikingWeather; //The full list of weather words TODO make this and the next private
	public $simplifiedBikingWeather; //The simplified options

	private function getGet($get, $key, $defaultValue) {
		//Gets the given key from the query string via $_GET (passed in to aid testing). Handles empty or non-existent variables
		if (isset($get[$key]) && $get[$key] !== "") {
			return $get[$key];
		} else {
			return $defaultValue;
		}
	}
	
	function __construct($get) {
	//if $_GET fields present then use those
	//otherwise sets default values for the preferences
		
		//Get query parameters or defaults
		$this->postcode = strtolower($this->getGet($get, POSTCODE_PARAM,DEFAULT_POSTCODE));
	
		$this->minTemp = $this->getGet($get, MIN_TEMP_PARAM, DEFAULT_MIN_TEMP);
		$this->maxTemp = $this->getGet($get, MAX_TEMP_PARAM, DEFAULT_MAX_TEMP);
	
		$this->firstHour = $this->getGet($get, FIRST_HOUR_PARAM, DEFAULT_FIRST_HOUR);
		$this->secondHour = $this->getGet($get, SECOND_HOUR_PARAM, DEFAULT_SECOND_HOUR);
	
		//Get acceptable weather words
		$this->bikingWeather = array();
		
		if (isset($get[GOOD_WEATHER_PARAM]) && $get[GOOD_WEATHER_PARAM] !== "") {
			$goodWeatherList = explode(",", urldecode($get[GOOD_WEATHER_PARAM]));
			foreach ($goodWeatherList as $goodWord) { //TODO validation on these words as they could come from anywhere
				$this->bikingWeather[$goodWord] = true;
			}
		} else {
			$this->bikingWeather = prefs::$bikingWeatherDefault;
		}
		foreach (prefs::$simplifiedWeatherTypes as $internal => $external) {
			$this->simplifiedBikingWeather[$internal] = false; 
		}
	}
	
	function checkBikingWeather($weatherWords, $temperature) {
	//checks if this is biking weather according to user preferences
		if (!array_key_exists(strtolower($weatherWords), prefs::$bikingWeatherDefault) && strcmp($weatherWords, "")) {
			//Let me know if this is a new word, and assume it's not biking weather
			mail("paulmorriss@iname.com","new weather word: ".$weatherWords,"From: Paul Morriss <paulmorriss@iname.com>\r\n");
			return false;
		}
		if (!array_key_exists($weatherWords, $this->bikingWeather) || !strcmp($temperature,"")) {
			return false;
		} else if ($this->bikingWeather[$weatherWords] && ($temperature >= $this->minTemp) && ($temperature <= $this->maxTemp)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * sets the internal simplified preferences and also updates the full list of weather
	 *
	 * @param mixed An array of simplified weather types and booleans
	 * @return boolean Whether they would cycle in that or not
	 */
	public function setSimplifiedWeatherTypes($simplifiedChoices) {
		$this->simplifiedWeatherChoices = $simplifiedChoices;
		foreach (prefs::$simplifiedMapping as $weatherWord => $simplifiedWord) {
			if (!strcmp($simplifiedWord,"")) { //Always good weather
				$this->bikingWeather[$weatherWord] = true;
			} else	{
				if (array_key_exists(prefs::$simplifiedMapping[$weatherWord], $simplifiedChoices)) {
					$this->bikingWeather[$weatherWord] = $simplifiedChoices[prefs::$simplifiedMapping[$weatherWord]];
				} else {
					//This option not specified, assume false
					$this->bikingWeather[$weatherWord] = false;
				}
			}
		}
		
	}
}



class weatherPage {

/**
 * Gets the first hour displayed on the page
 * Returns 0 if it can't find it
 *
 * @param mixed $xpath The xpath object for the webpage
 * @param mixed $dom The dom object for the webpage
 * @return int The first hour
 */	
	public function getStartHour ($xpath, $dom) {
		
		$startHour = $xpath->query('//*[@id="hourly"]/div[3]/table/thead/tr/th[2]/span[1]/text()');

		if ($startHour) {
			$startHour = 0+$dom->saveHTML($startHour->item(0));
		} else {
			$startHour = 0;
		}
		return $startHour;
	}

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
 * @return string The description of that weather type or "" if no such slot on page
 */	
	public function getWeatherWords($xpath, $index) {
		
		
		$weatherWordTitle = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[1]/td['.strval($index).']/span/img/@title');#
		if ($weatherWordTitle->length <> 0) { /*This means we found it */
			$weatherWords = $weatherWordTitle->item(0)->nodeValue;
		} else {
			return "";
		}
	
		if ($weatherWords) {
			return(strtolower($weatherWords));
		} else {
			return "";
		}
		
	}
	
/**
 * Find the temperature for the given index (hour slot)  
 *
 * @param mixed $xpath The xpath object for the webpage
 * @param mixed $dom The dom object for the webpage
 * @param int $index The index (i.e. column) in the table of weather slots on the webpage
 * @return mixed The temperature or "" if no such slot on page
 */	
	public function getTemperature($xpath, $dom, $index) {
		//Find the temperature figure
		$temperatureTitle = $xpath->query('//*[@id="hourly"]/div[3]/table/tbody/tr[2]/td['.strval($index).']/span/span/span[1]/text()');
		if ($temperatureTitle->length <> 0) { /*This means we found it */
			$temperature = $temperatureTitle->item(0);
		} else {
			return "";
		}
		if ($temperature) {
			return($dom->saveHTML($temperature));
		} else {
			return "";
		}
	}
	
}


?>