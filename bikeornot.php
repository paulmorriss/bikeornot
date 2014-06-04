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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Is it OKto.bike?</title>
    <link rel="apple-touch-icon" href="logo2-152.png"/>
    <link rel="apple-touch-icon" sizes="72x72" href="logo2-152.png"/>
    <link rel="apple-touch-icon" sizes="114x114" href="logo2-152.png"/>
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <div class="container">

      <div class="starter-template">
    
<?php
	//Use URL paramters, if supplied, then try cookie, finally take defaults
	if (count($_GET) > 0) {
		$storedPrefs = new prefs($_GET);	
	} else if (array_key_exists(PREFS_COOKIE_NAME, $_COOKIE)) {
		$storedPrefs = new prefs(array());
		$storedPrefs = unserialize($_COOKIE[PREFS_COOKIE_NAME]);
		
		if ($_GET["debug"]) {
			var_dump($storedPrefs);
		}
	} else {
		$storedPrefs = new prefs(array());
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
		if (strcmp($weatherWords,"")) {
			if ($storedPrefs->checkBikingWeather($weatherWords,$temperature)) {
				print "OK ";
			} else {
				print "not OK ";
			}
			?>
		<?php
			print $storedPrefs->firstHour.':00 <img height="58" width="96" src="images/'.$weatherWords.'.png"> '.$temperature."&deg;C";
		}
		?>
</h2>
        <br>
<h1>
        <?php
		
		$index = $weatherPage->getIndex($startHour, $storedPrefs->secondHour);
		$weatherWords = $weatherPage->getWeatherWords($xpath, $index);
		$temperature = $weatherPage->getTemperature($xpath, $dom, $index);
		if (strcmp($weatherWords,"")) {
			if ($storedPrefs->checkBikingWeather($weatherWords,$temperature)) {
				print "OK ";
			} else {
				print "not OK ";
			}
			?>
		<?php
			print $storedPrefs->secondHour.':00 <img height="58" width="96" src="images/'.$weatherWords.'.png"> '.$temperature."&deg;C";
	    }
		?>
</h2>

        <?php
		
	}
?>
<a href="prefs.php">Set your location and what weather is OK to bike in</a>
</div>
</div>
  </body>
</html>
