<?php
//Display preferences in a form
include_once('include.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Is it OKto.bike?</title>

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
<?php
function optionHours($selectedHour) {
	
	//Print options for a 24 hour slots, and if specified, set value to the selected hour
	for ($i = 0; $i<=23; $i++) {
		print '<option value="'.$i.'"';
		if ($i == $selectedHour) {
			print ' selected="selected"';
		}
		print '>'.sprintf('%02d', $i). ':00</option>';
	}
}

//Get current preferences, if any
$storedPrefs = new prefs(array());
if (array_key_exists(PREFS_COOKIE_NAME, $_COOKIE)) {
	$storedPrefs = unserialize($_COOKIE[PREFS_COOKIE_NAME]);
}
?>
    <div class="container">

      <div class="starter-template">
<h1>Is it OKto.bike?</h1>
<h2>Set your preferences</h2>
<form action="setprefs.php" method="post">


<fieldset>

<ul>

<li><label for="postcode">First part of your postcode (UK only) e.g. HP12</label>
<input type="text" name="postcode" id="postcode" size=5 value="<?php  echo strtoupper($storedPrefs->postcode); ?>">
</li>
<li><label for="mintemp">Minimum temperature in &deg;C</label>
<input type="number" name="mintemp" id="mintemp" size=5 value="<?php echo $storedPrefs->minTemp; ?>" >
</li>
<li><label for="maxtemp">Maximum temperature in &deg;C</label>
<input type="number" name="maxtemp" id="maxtemp" size=5 value="<?php  echo $storedPrefs->maxTemp; ?>" >
</li>
<li><label for="firsthour">First hour</label>
<select name="firsthour">
<?php optionHours($storedPrefs->firstHour);
?>
</select>
</li>
<li><label for="secondhour">Second hour</label>
<select name="secondhour">
<?php  optionHours($storedPrefs->secondHour);
?>
</select>
</li>

</ul>

<h3>Cycling weather:</h3>
<ul>
<?php 
foreach(prefs::$simplifiedWeatherTypes as $internal => $readable) {
	?>
    <li>
<?php
	echo '<label for="'.$internal.'">'.$readable.'</label>';
	echo '<input name="'.$internal.'" type="checkbox" value="'.$internal.'"';
	if ($storedPrefs->simplifiedWeatherChoices[$internal]) {
		echo ' checked="checked"';
	}
	echo '>';
?>
	</li>
<?php
}
?>
</ul>
</fieldset>
<input type="submit" name="save" value="Save">
</form>

  </div>

    </div><!-- /.container -->

  </body>
</html>
