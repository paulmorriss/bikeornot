<?php
include_once('include.php');

class prefsTest extends PHPUnit_Framework_TestCase 
{
	public function testConstructor() {
		//Test if defaults values used
		$p = new prefs(NULL);
		$this->assertEquals($p->minTemp, DEFAULT_MIN_TEMP);
		$this->assertEquals($p->maxTemp, DEFAULT_MAX_TEMP);
		$this->assertEquals($p->postcode, DEFAULT_POSTCODE);
		$this->assertEquals($p->firstHour, DEFAULT_FIRST_HOUR);
		$this->assertEquals($p->secondHour, DEFAULT_SECOND_HOUR);
	}

	public function testConstructorWithGet() {
		//Test if values are picked up from $_GET variable supplied
		$get = array(POSTCODE_PARAM => 'b23', MIN_TEMP_PARAM => -1, MAX_TEMP_PARAM =>30, FIRST_HOUR_PARAM => 13,
					 SECOND_HOUR_PARAM => 19, GOOD_WEATHER_PARAM => urlencode("sleet,heavy rain,light rain, thunder storm"));
		
		$p = new prefs($get);
		$this->assertEquals($p->minTemp, -1);
		$this->assertEquals($p->maxTemp, 30);
		$this->assertEquals($p->postcode, 'b23');
		$this->assertEquals($p->firstHour, 13);
		$this->assertEquals($p->secondHour, 19);
		$this->assertEquals($p->checkBikingWeather("sleet", 10), true);

	}
	public function testCheckBikingWeather() {
		//Test the given function using a range of weather types and temperatures, using default preferences 
		$p = new prefs(NULL);

		$this->assertEquals($p->checkBikingWeather("sleet", DEFAULT_MIN_TEMP+1), false);
		$this->assertEquals($p->checkBikingWeather("fog", DEFAULT_MIN_TEMP+1), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP+1), true);
		$this->assertEquals($p->checkBikingWeather("heavy rain", DEFAULT_MIN_TEMP+1), false);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP-1), false);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MAX_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MAX_TEMP+1), false);
	}
	public function testSetSimplifiedWeatherTypes() {
		$p = new prefs(NULL);
		$p->setSimplifiedWeatherTypes(array("lightrain"=> true));
		$this->assertEquals($p->checkBikingWeather("light rain", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("light rain shower", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("drizzle", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("heavy rain", DEFAULT_MIN_TEMP), false);
		$this->assertEquals($p->checkBikingWeather("light snow showers", DEFAULT_MIN_TEMP), false);
		$p->setSimplifiedWeatherTypes(array("mistfog" => true));
		$this->assertEquals($p->checkBikingWeather("mist", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("fog", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("light showers", DEFAULT_MIN_TEMP), false);
		$this->assertEquals($p->checkBikingWeather("heavy snow", DEFAULT_MIN_TEMP), false);

		//Always good weather
		$this->assertEquals($p->checkBikingWeather("cloudy", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP), true);
}

//TODO test url param calling method
}