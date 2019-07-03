<!DOCTYPE html PUBLIC
    "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TRxhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>DF5FH WSPR-Gateway</title>
</head>
<body>
<div>
<?php
//
// Main
//

// Pruefen, ob upload-Verzeichnis existiert (ggf. anlegen)
if( file_exists( "upload" ) != true )
	mkdir( "upload" );

// Vorbesetzungen
$source = "" ;
$uploader = "";
$date = "";
$time = "";

// Wurde die Datei von dem Redpitaya mit der Mac-Adresse f01f60... hochgeladen
if (isset($_FILES['f01f60'])) 
{
   $source = $_FILES['f01f60']['tmp_name'];
   $uploader = "f01f60" ;
}

// Wurde die Datei von dem Redpitaya mit der Mac-Adresse f04e90... hochgeladen
if (isset($_FILES['f04e90'])) 
{
   $source = $_FILES['f04e90']['tmp_name'];
   $uploader = "f04e90" ;
}

// Wurde die Datei von dem Redpitaya mit der Mac-Adresse f07b5a... hochgeladen
if (isset($_FILES['f07b5a'])) 
{
   $source = $_FILES['f07b5a']['tmp_name'];
   $uploader = "f07b5a" ;
}

// Wurde eine Datei von einem bekannten Geraet hochgeladen
if( $source != "" )
{
	// DATUM DES WSPR-DATENSATZES BESTIMMEN
	
	// Datei-Inhalt in String holen
	$content = file_get_contents($source);
	if( $content != FALSE )
	{
		// String in Array aufsplitten
		$lines = explode( "\n", $content );
		$c = count($lines);
		if( $c > 0 )
		{
			// Alle Zeilen bearbeiten
			for($i=0; $i<$c ; $i++)
			{
				// Wenn Zahl am Zeilenanfang, dann ist es der Zeitstempel eines wspr-Datensatzes
				$datval = intval( strtok( $lines[$i], " " ));
				$timval = intval( strtok( " " ));

				// Pruefen, ob Datum plausibel
				if( $datval > 190101 && $datval < 991231 )
					if( $timval >= 0 && $timval <= 2359 )
					{
						$date = strval($datval);
						
						$time = strval($timval);
						while( strlen($time)< 4 )
							$time = "0".$time ;
						
						// Das erste erkannte Datum genuegt
						break;
					}
			}
		}
	}

	// Pruefen, ob wspr-Zeitstempel gefunden wurde
	if( $date != "" && time != "")
	{
		// Zielpath mit Zeitstempel und Quelle zusammenbauen
		$target = "upload/".$date."_".$time."_".$uploader.".txt" ;
		
		// Hochgeladene Datei an Zielort verschieben
		move_uploaded_file($source,$target);
	}
}

?>	
</div>
<form enctype="multipart/form-data"
   action="<?php print $_SERVER['PHP_SELF']?>" method="post">
<p>
<input type="hidden" name="MAX_FILE_SIZE" value="1800000" />
<input type="file" name="f01f60" /><br/>
<input type="submit" value="Hochladen" />
</p>
</form>
</body>
</html>
