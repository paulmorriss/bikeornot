<?php
include_once('include.php');

class weatherPageTest extends PHPUnit_Framework_TestCase 
{
	public function testGetIndex() {
		$p = new weatherPage();
		$this->assertEquals($p->getIndex(2,1), 0);
		$this->assertEquals($p->getIndex(1,1), 1);
		$this->assertEquals($p->getIndex(3,3+HOUR_SLOTS), 2);
		$this->assertEquals($p->getIndex(3,3+(HOUR_SLOTS*2)), 3);
		
	}
	
	public function testGetWeatherWords() {
		$p = new weatherPage();
		$pageHTML = file_get_contents(realpath(dirname(__FILE__)).'/staticpage.html');
		$dom = new DomDocument();
		
		$dom->loadHTML($pageHTML);
		$xpath = new DOMXPath($dom);
		$this->assertEquals($p->getWeatherWords($xpath, 1), 'light rain shower');		
		$this->assertEquals($p->getWeatherWords($xpath, 3), 'light cloud');		
		$this->assertEquals($p->getWeatherWords($xpath, 6), 'partly cloudy');		
		$this->assertEquals($p->getWeatherWords($xpath, 16), 'thick cloud');		
		$this->assertEquals($p->getWeatherWords($xpath, 17), '(not found)');
	}
	public function testGetTemperature() {
		$p = new weatherPage();
		$pageHTML = file_get_contents(realpath(dirname(__FILE__)).'/staticpage.html');
		$dom = new DomDocument();
		
		$dom->loadHTML($pageHTML);
		$xpath = new DOMXPath($dom);
		$this->assertEquals($p->getTemperature($xpath, $dom, 17), '(not found)');
		$this->assertEquals($p->getTemperature($xpath, $dom, 1), 8);		
		$this->assertEquals($p->getTemperature($xpath, $dom, 4), 7);		
		$this->assertEquals($p->getTemperature($xpath, $dom, 11), 3);		
		$this->assertEquals($p->getTemperature($xpath, $dom, 16), 4);
	}

}