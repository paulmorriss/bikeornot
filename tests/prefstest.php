<?php
include_once('include.php');

class prefsTest extends PHPUnit_Framework_TestCase 
{
	public function testconstructor() {
		//Test if defaults values used
		$p = new prefs(NULL);
		$this->assertEquals($p->minTemp, DEFAULT_MIN_TEMP);
		$this->assertEquals($p->maxTemp, DEFAULT_MAX_TEMP);
		$this->assertEquals($p->postcode, DEFAULT_POSTCODE);
		$this->assertEquals($p->firstHour, DEFAULT_FIRST_HOUR);
		$this->assertEquals($p->secondHour, DEFAULT_SECOND_HOUR);
		$this->assertEquals($p->checkBikingWeather("sleet", DEFAULT_MIN_TEMP+1), false);
		$this->assertEquals($p->checkBikingWeather("fog", DEFAULT_MIN_TEMP+1), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP+1), true);
		$this->assertEquals($p->checkBikingWeather("heavy rain", DEFAULT_MIN_TEMP+1), false);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MIN_TEMP-1), false);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MAX_TEMP), true);
		$this->assertEquals($p->checkBikingWeather("sunny", DEFAULT_MAX_TEMP+1), false);
	}
	
	//TODO test url param calling method
}