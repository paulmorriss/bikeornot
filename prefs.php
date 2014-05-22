<?php
//Display preferences in a form
include_once('include.php');

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
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Bike or not - preferences</title>
</head>

<body>
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


Cycling weather:
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
</body>
</html>
