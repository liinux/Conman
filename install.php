<?php

// Om config.php redan existerar så gör ingenting.
if (file_exists("config.php")) {
	echo "Fel: config.php existerar redan.<br>Redigera den manuellt eller ta bort den!";
	die();
}

// Kontrollera att systemet har skrivrättigheter.
if (!is_writable(".")) {
	echo "Fel: Inga skrivrättigheter finns till mappen Conman är installerat i.<br>Rätta till felet och försök igen!";
	die();
}

// Om inte install.csv, som innehåller installationsfrågorna, existerar så gör ingenting.
if (!file_exists("install.csv")) {
	echo "Fel: install.csv saknas, installera om Conman!";
	die();
}

// Om inte install.sql, som innehåller databasposterna, existerar så gör ingenting.
if (!file_exists("install.sql")) {
	echo "Fel: install.sql saknas, installera om Conman!";
	die();
}

// Läs in install.csv
$questions = array();
$handle = fopen("install.csv", "r");
fgetcsv($handle); // Ignorera första raden i filen, som bara innehåller syntaxbeskrivning.

while ($row = fgetcsv($handle))
	$questions[] = $row;

fclose($handle);
	
// Om post gjorts så validera config-data.
$validationerror = false;
if (!empty($_POST)) {
	foreach ($questions as $question) {
		if ($question[2] == "checkbox")
			if (isset($_POST[$question[1]]))
				$_POST[$question[1]] = "true";
			else
				$_POST[$question[1]] = "false";
		elseif (empty($_POST[$question[1]]) && $question[2] != "select")
			$validationerror = true;
	}
}

// Om valideringen misslyckats, eller config-data saknas, så fråga efter config-data.
if ($validationerror || empty($_POST)) {
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>
Conman - Förstagångsinstallation
</title>
</head>
<body>
<h1>Conman - Förstagångsinstallation</h1>
<form name="input" action="" method="post">
<?php
	foreach($questions as $question) {
		if ($curgroup != $question[0]) {
			$curgroup = $question[0];
			echo "<h2>$curgroup</h2>\n";
		}
		
		echo "<p>\n";
		echo $question[4] . "<br>\n";
		
		if ($question[2] == "text" || $question[2] == "number")
			if(isset($_POST[$question[1]]))
				echo '<input type="text" name="' . $question[1] . '" value="' . $_POST[$question[1]] . '">' . "\n";
			else
				echo '<input type="text" name="' . $question[1] . '" value="' . $question[3] . '">' . "\n";

		if ($question[2] == "date")
			if(isset($_POST[$question[1]]))
				echo '<input type="date" name="' . $question[1] . '" value="' . $_POST[$question[1]] . '">' . "\n";
			else
				echo '<input type="date" name="' . $question[1] . '" value="' . $question[3] . '">' . "\n";

		if ($question[2] == "password")
			if(isset($_POST[$question[1]]))
				echo '<input type="password" name="' . $question[1] . '" value="' . $_POST[$question[1]] . '">' . "\n";
			else
				echo '<input type="password" name="' . $question[1] . '" value="' . $question[3] . '">' . "\n";

		if ($question[2] == "select") {
			$options = str_getcsv($question[3], ";");

			echo '<select name="' . $question[1] . '">' . "\n";
		
			foreach($options as $option)
				if ($_POST[$question[1]] == $option)
					echo "\t" . '<option value="' . $option . '" selected="yes">' . $option . '</option>' . "\n";
				else
					echo "\t" . '<option value="' . $option . '">' . $option . '</option>' . "\n";
					
			echo '</select>' . "\n";
		}

		if ($question[2] == "checkbox")
			if($_POST[$question[1]] == "true")
				echo '<input type="checkbox" name="' . $question[1] . '" checked="yes">' . "\n";
			else
				echo '<input type="checkbox" name="' . $question[1] . '">' . "\n";
				
		echo "</p>\n";
	}
?>
<input type="submit" value="Submit">
</form>
</body>
</html>
<?php
	die();
}

// Eftersom valideringen lyckats så skapa config.php med config-datan.
$filedata = "<?php\n";
$filedata .= "\tclass Settings\n";
$filedata .= "\t{\n";

$filedata .= "\t\tstatic function getRoot(){\n";
$filedata .= "\t\t\treturn dirname(__FILE__);\n";
$filedata .= "\t\t}\n";

$filedata .= "\t\tstatic \$PayAPI = array('Name' => 'Payson',\n";
$filedata .= "\t\t\t'Test' => true,\n";
$filedata .= "\t\t\t'_application_email' => 'yourpaysonemail@mail.com');\n";

foreach($questions as $question) {
	if ($curgroup != $question[0]) {
		$curgroup = $question[0];
		$filedata .= "\n";
	}
	
	if ($question[2] == "number" || $question[2] == "checkbox" || $question[1] == "ErrorReporting")	// ErrorReporting är ett specialfall, då det är en konstant som inte ska quotas.
		$filedata .= "\t\tstatic \$" . $question[1] . " = " . $_POST[$question[1]] . ';';
	else
		$filedata .= "\t\tstatic \$" . $question[1] . " = \"" . $_POST[$question[1]] . '";';
		
	$filedata .= " // " . $question[4] . "\n";
}

$filedata .= "\t}\n";

file_put_contents("config.php", $filedata);

// Skapa .htaccess om den inte redan existerar.
if (!file_exists(".htaccess")) {
	$filedata = "RewriteEngine On\n";
	$filedata .= 'RewriteCond %{REQUEST_FILENAME} !-f' . "\n";
	$filedata .= 'RewriteCond %{REQUEST_FILENAME} !-d' . "\n";
	$filedata .= 'RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]' . "\n";
	
	file_put_contents(".htaccess", $filedata);
}

// Testa att ansluta till databasen och populera den.
include("config.php");

$mysqli = new mysqli(Settings::$DbHost, Settings::$DbUser, Settings::$DbPassword, Settings::$DbName);

if ($mysqli->connect_errno) {
	printf("Databasanslutningen misslyckades: %s<br><br>", $mysqli->connect_error);
	printf("Redigera config.php manuellt och rätta till inställningarna!<br>Kör efter det install.sql manuellt!");
	die();
}

if (!$mysqli->multi_query(file_get_contents("install.sql"))) {
	printf("Databasen kunde inte uppdateras, kör install.sql manuellt!");
	die();
}
