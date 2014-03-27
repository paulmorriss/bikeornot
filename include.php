<?php 
function getGet($get, $key, $defaultValue) {
	//Gets the given key from the query string via $_GET (passed in to aid testing). Handles empty or non-existent variables
	if (isset($_GET[$key]) && $_GET[$key] !== "") {
		return $_GET[$key];
	} else {
		return $defaultValue;
	}
}

// Which weather is biking weather.
// Also useful for a list of all known words
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

//Simplified weather choices
// internvalue and human readable name for checkboxes on prefs screen
$simplifiedChoices = array (
"lightrain" => "Light rain",
"heavyrain" => "Heavy rain",
"sleetsnow" => "Sleet/snow",
"mistfog" => "Mist/fog");

// what they map to
//"" means always OK
$simplifiedMapping = array (
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

define('PREFS_COOKIE_NAME', 'prefs');
define ('PREFS_VERSION','1.0');
class prefs {
	//A class to store and supply preferences
	//knows the mapping between simplified choices and the full list
	
	public $version; //So we can stop things breaking if this changes
	public $postcode;
	public $minTemp;
	public $maxTemp;
	public $firstHour;
	public $secondHour;
	public $bikingWeather; //The full list of weather words
	public $simplifiedBikingWeather; //The simplified options

	function __construct($get) {
	//if $_GET fields present then use those
	//otherwise sets default values for the preferences
	
		//Get query parameters or defaults
		$this->postcode = strtolower(getGet($get, POSTCODE_PARAM,DEFAULT_POSTCODE));
	
		$this->minTemp = getGet($get, MIN_TEMP_PARAM, DEFAULT_MIN_TEMP);
		$this->maxTemp = getGet($get, MAX_TEMP_PARAM, DEFAULT_MAX_TEMP);
	
		$this->firstHour = getGet($get, FIRST_HOUR_PARAM, DEFAULT_FIRST_HOUR);
		$this->secondHour = getGet($get, SECOND_HOUR_PARAM, DEFAULT_SECOND_HOUR);
	
		//Get acceptable weather words
		$this->bikingWeather = array();
		
		if (isset($get[GOOD_WEATHER_PARAM]) && $get[GOOD_WEATHER_PARAM] !== "") {
			$goodWeatherList = explode(",", urldecode($get[GOOD_WEATHER_PARAM]));
			foreach ($goodWeatherList as $goodWord) {
				$this->bikingWeather[$goodWord] = true;
			}
		} else {
			$this->bikingWeather = $bikingWeatherDefault;
		}
	}

//public getSimplifiedChoices
//returns a simplified list of choices suitable for displays on the prefs page
//in an array with key = internal name, value = display name

//public bikeOrNot
//given the parameters
}

?>