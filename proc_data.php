<?PHP
/*
   proc_data.php
   -------------
	
   Aufbau Struktur _wsprData
	
   _wsprData
   {
      file_date:         Datums-Prefix der json-Datei (z.B. 190502_wsprdat.json)
		
      repcnt[n]          Report-Zaehler fuer die einzelnen Setupd (n=Anzahl definierter Setups)
		
      setup[n]
         tablerow:       angestrebte Spalte in der HTML-Ausgabetabelle
         srcname:        die verwendete Antenne (Source)
         feature:        zwischengeschaltete "Features", z.B. Preamplifier, Filter, ...
         receiver:       der verwendete Empfänger (Red Ritaya Mac)
         centerfreqs[]   Array aller Empfangsfrequenzen
			
      slot[0...720]      Die 2 Minuten Empfangsslots des Tages von 9 bis 720
         unixtime:       Der Unix-Zeitstempel des Slots
         report[]        Alle Reports des Slots
            call:        Rufzeichen
            grid:        QTH-Locator
            band:        Das erkannte Band	
            pwr:         Die Leistung des Senders
            srcArray[]
               RXF:      gemessene Empfangsfrequenz
               SNR:      gemessenes Signal-Rauschverhältnis in dB
   }
	
   Achtung: Werden die Daten mit request.php angefordert, dann ist:
   - slot-Array lueckenlos (nur noch Element-Anzahl, kein Index-Zeit Zusammenhang mehr)
   - file_date = undefined (Daten koennen auch ueber mehrere json-Dateien ermittelt werden)
*/
	
//
// Variablen
//

// Array aller Bandbezeichnungen mit den zugehoerigen Mittenfrequenzen
$_bands = 
[
	"LW" => "0.137500",
	"MW" => "0.475700",
	"160m" => "1.838100",
	"80m" => "3.570100",
	"!80m" => "3.594100",
	"60m" => "5.288700",
	"!60m" => "5.366200",
	"40m" => "7.040100",
	"30m" => "10.140200",
	"20m" => "14.097100",
	"17m" => "18.106100",
	"15m" => "21.096100",
	"12m" => "24.926100",
	"10m" => "28.126100",
	"6m" => "50.294500",
	"4m" => "70.092500",
	"2m" => "144.490500",
	"70cm" => "432.301500",
	"23cm" => "1296.501500"
];	

// setup-Array
$_setup;

// Das aktuelle wspr-Array
$_wsprData;

	
//
// Funktionen
//

// Unix-Zeit aus gegebenen Formaten berechnen ( yymmdd, HHMM )
function getUnixTime( $date_str, $time_str )
{
	$yy = substr($date_str,0,2);
	$mm = substr($date_str,2,2);
	$dd = substr($date_str,4,2);
	$HH = intval(substr($time_str,0,2));
	$MM = intval(substr($time_str,2,2));

	$eng = $mm."/".$dd."/".$yy ;
	$unix = strtotime($eng);

	return $unix + $HH * 3600 + $MM * 60 ;
}

// Luecken in einem zahlenindizierten Array fuellen
function expandNumberIndexedArray( &$array, $index )
{
	// Aktuelle Anzahl von Elementen
	$n = count($array);
	
	// Groesse der Index-Luecke
	$gap = $index - $n + 1 ;
	
	// Pruefen, ob Index-Luecke gefuellt werden muss
	if( $gap > 0 )
	{
		// Index-Luecke fuellen
		for( $i=0 ; $i < $gap ; $i++ )
			$array[$n+$i] = NULL;
	}
}

// Das zu einer Frequenz gehoerige Band ermitteln
function getBand( $freq )
{
	global $_bands;
	
	// Frequenz wird als Zahl benoetigt
	$freq = floatval($freq);
	
	// Band in Array suchen
	foreach( $_bands as $key => $value )
	{
		$fmax = floatval($value) + 0.00015 ;  
		$fmin = floatval($value) - 0.00015 ;  

		if( $freq <= $fmax && $freq >= $fmin )
			return $key;
	}
	
	// Band nicht gefunden
	return "?";
}

// wsprData-Datensatz aus json-Datei von Festplatte laden
function loadJsonData($jsonfile)
{
	global $_wsprData ;
		
	// Pruefen, ob zugehoerige json-Datei existiert
	if( file_exists( $jsonfile ) == TRUE )
	{
		// json-Datei einlesen
		$json = file_get_contents($jsonfile);
			
		if( $json != FALSE )
		{
			// json-Daten in Array dekodieren
			$_wsprData = json_decode($json, TRUE);
		}
	}
	else
		$_wsprData = FALSE;
	
	if( $_wsprData == FALSE )
		return FALSE;
	
	return TRUE;
}

// aktuellen wsprData-Datensatz als json-Datei auf Festplatte speichern
function saveJsonData()
{
	global $_wsprData ;

	// Pruefen, ob Daten zum Speichern vorhanden
	if($_wsprData == FALSE )
		return ;

	// json-Daten aus aktuellem Array erzeugen
	$json = json_encode($_wsprData,  JSON_PRETTY_PRINT);
	if( $json == FALSE )
		return;
	
	// Jahreszahl aus WSPR-Daten bereitsstellen
	$year = strval( 2000 + intval(substr($_wsprData["file_date"],0,2)));

	// Der Pfad der json-Datei
	$jsonfile = "database/".$year."/".$_wsprData["file_date"]."_wsprdat.json";
	
	// json-Daten abspeichern
	$fp = fopen( $jsonfile, "w+" );
	if( $fp != FALSE )
	{
		fprintf( $fp, "$json" );
		fclose($fp);
	}
}

// Report-Zeile auswerten
function decodeWsprReport( $slot_idx, $receiver, $line )
{
	global $_wsprData;
	global $_sourceDef;
	global $_setup;

	// Report-Zeile in Bestandteile zerlegen
	$date = trim(strtok( $line, " " )," ");
	$time = trim(strtok( " " )," ");
	$a = trim(strtok( " " )," ");
	$snr = trim(strtok( " " )," ");
	$b = trim(strtok( " " )," ");
	$freq = trim(strtok( " " )," ");
	$call = trim(strtok( " " )," ");
	$grid = trim(strtok( " " )," ");
	$pwr = trim(strtok( " " )," ");
	$c = trim(strtok( " " )," ");
	$d = trim(strtok( " " )," ");
	$e = trim(strtok( " " )," ");
	$f = trim(strtok( " " )," ");
	$g = trim(strtok( " " )," ");

	// eventuelle spitze Klammern aus Rufzeichen in * umwandeln
	$tags = array("<",">");
	$call = str_replace( $tags, "*", $call );
	
	// Falls $slot_idx Sprung macht, Luecken im slot-Array mit "null" auffuellen
	expandNumberIndexedArray($_wsprData["slot"], $slot_idx);

	// Pruefen, ob report-Array im slot bereits angelegt
	if( $_wsprData["slot"][$slot_idx]["report"] == null  )
	{
		// Unix-Zeitmarke merken
		$_wsprData["slot"][$slot_idx]["unixtime"] = getUnixTime( $date, $time);
		
		// Report-Array anlegen
		$_wsprData["slot"][$slot_idx]["report"] = array();
	}
	
	// Pruefen, ob Report-Element bereits vorhanden (Empfang eines Reports von mehreren Receivern)
	$n = count( $_wsprData["slot"][$slot_idx]["report"]);
	for( $rep_idx=0; $rep_idx<$n ; $rep_idx++ ) 
	{
		$report = $_wsprData["slot"][$slot_idx]["report"][$rep_idx];
		
		if( $report["call"] == $call )
			if( $report["grid"] == $grid )
				if( $report["band"] == getBand($freq))
				{
					// Report bereits vorhanden
					break ;
				}
	}

	// Pruefen, ob Report-Element nicht gefunden wurde
	if( $rep_idx == $n )
	{
		// Neues Report-Element anlegen
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx] = array();
		
		// ... und ausfuellen
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["call"] = $call ;
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["grid"] = $grid ;
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["band"] = getBand($freq) ;
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["pwr"] = $pwr ;
		$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"] = array() ;
	}

	
	// EMPFANGSWERTE AUF SETUPS VERTEILEN
	
	// Frequenz in Zahl umwandeln
	$freq = floatval($freq);

	// Alle Setups bearbeiten
	$setup_num = count( $_setup );
	for( $setup_idx=0 ; $setup_idx<$setup_num ; $setup_idx++ ) 
	{
		// Pruefen, ob Setup fuer aktuelle Frequenz zustaendig
		foreach($_setup[$setup_idx]["centerfreqs"] as $f )
		{
			// Das WSPR-Fenster (doppelte Groesse)
			$fmax = $f + 0.0002 ;  
			$fmin = $f - 0.0002 ;  
	
			if( $freq < $fmax && $freq > $fmin )
			{
				// EINTRAG ANLEGEN
				
				// Falls Index einen Sprung macht, Luecken in Array mit "null" auffuellen
				expandNumberIndexedArray( $_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"], $setup_idx );

				// Leeres Array bereitstellen (Fuer RXF und SNR)
				// - Wenn hier spaeter nichts eingetragen wird, erfolgt die "NO RECEIVE"-Anzeige im Browser
				if( !isset( $_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"][$setup_idx] ))
					$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"][$setup_idx] = array();
				
				// Pruefen, ob Receiver uebereinstimmt
				if( $receiver == $_setup[$setup_idx]["receiver"] )
				{
					// Report-Zaehler erhoehen (zeigt dem Browser, ob Platz fuer eine Spalte reserviert werden muss)
					$_wsprData["repcnt"][$setup_idx] ++;
				
					// Empfangswerte eintragen
					$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"][$setup_idx]["RXF"] = $freq;
					$_wsprData["slot"][$slot_idx]["report"][$rep_idx]["srcArray"][$setup_idx]["SNR"] = $snr;
				}
			}
		}
	}
}

// Eine WSPR-Report-Datei bearbeiten und loeschen
function procReportFile($srcdir,$file)
{
// printf( "procReportFile: $srcdir$file\n");	
	global $_wsprData ;
	global $_setup;
	
	// Das Datum des eingehenden Datensatzes
	$date = strtok( $file, "_" );
	$time = strtok( "_" );
	$receiver = strtok( "." );

	// Slot-Index (0...719) aus $time berechnen
	$std = intval(substr($time,0,2));
	$min = intval(substr($time,2,2));
	$slot_idx = (60 * $std + $min) >> 1 ;

	
	// BEREITSTELLUNG / WECHSEL WSPR-DATEN

	// Jahreszahl bereitsstellen
	$year = strval( 2000 + intval(substr($date,0,2)));
	
	// Pruefen, ob Jahresverzeichnis existiert, ggf. anlegen
	if( file_exists( "database/".$year ) != true )
		mkdir( "database/".$year );
	
	// Der Pfad zur zugehoerigen json-Datei
	$jsonfile = "database/".$year."/".$date."_wsprdat.json";

	// Existiert bereits ein Array ?
	if( $_wsprData != FALSE )
	{
		// Stimmt das Datum des vorhandenen wspr-Arrays mit dem des eingehenden Datensatzes nicht ueberein ?
		if( $_wsprData["file_date"] != $date )
		{
			// Das aktuelle wspr-Array als json-Datei sichern
			saveJsonData();
			
			// Das aktuelle wspr-Array ist ungueltig
			$_wsprData = FALSE ;
		}
	}
	
	// Ist ein gueltiges wspr-Array vorhanden ?
	if( $_wsprData == FALSE )
	{
		// Versuch, json-Datei zu laden ...
		loadJsonData( $jsonfile );
	}
	
	// Ist ein gueltiges wspr-Array vorhanden ?
	if( $_wsprData == FALSE )
	{
		// neues, leeres Array erzeugen
		$_wsprData = array();
		
		// das zugehoerige Datum merken
		$_wsprData["file_date"] = $date;

		// Das Setup uebernehmen (kopieren)
		$_wsprData["setup"] = $_setup;

		// Report-Counter vorbereiten
		$_wsprData["repcnt"] = array();
		$n = count($_setup);
		for( $idx=0 ; $idx<$n ; $idx++ )
			$_wsprData["repcnt"][$idx] = 0;
		
		// Das Array fuer die Slots erzeugen
		$_wsprData["slot"] = array();
	}

	// REPORT-DATEI EINLESEN / LOESCHEN
	
	// Report-Datei zeilenweise bearbeiten
	$path = $srcdir.$file ;
	$fp = fopen( $path, "r" );
	if( $fp != FALSE )
	{
		while(( $line = fgets($fp)) != FALSE )
		{
			// Leere Zeilen vermeiden
			$line = strtok($line, "\n");
			if( strlen($line) == 0 )
				continue ;

			// Eine Report-Zeile auswerten
			decodeWsprReport( $slot_idx, $receiver, $line );
		}
		
		fclose($fp);
	}
	
	// Report-Datei loeschen
	unlink($path);
}

// Alle WSPR-Report-Datein bearbeiten 
function procDataDir()
{
	// Verzeichnis, in das die Report-Dateien hochgeladen werden
	$srcdir = "./upload/" ;
	if($dh = opendir($srcdir)) 
	{
		// Alle Report-Dateien bearbeiten
		while (($file = readdir($dh)) !== false) 
		{
			if( $file == "." )
				continue ;
			if( $file == ".." )
				continue ;

			if( filetype($srcdir.$file) == "file" )
			{
				$pi = pathinfo($srcdir.$file);

				if( strtolower( $pi['extension'] )== "txt") 
				{
					// Format des Dateinamens auf Plausibilitaet pruefen
					if( substr($file,6,1 ) == "_" && substr($file,11,1 ) == "_")
					{
						// Report-Datei verarbeiten
						procReportFile( $srcdir,$file );
					}
				}
			}
		}
		
		closedir($dh);
	}
}

// Setups in Array einlesen (_setup)
function loadSetup()
{
	global $_setup ;
	
	$srcdir = "./setup/" ;
	
	$_setup = array();
	
	if($dh = opendir($srcdir)) 
	{
		$_setup = array();

		$i = 0 ;
		while (($file = readdir($dh)) !== false) 
		{
			if( $file == "." )
				continue ;
			if( $file == ".." )
				continue ;

			if( filetype($srcdir.$file) == "file" )
			{
				$pi = pathinfo($srcdir.$file);

				if( strtolower( $pi['extension'] )== "json") 
				{
					// json-Datei einlesen
					$json = file_get_contents($srcdir.$file);
			
					if( $json != FALSE )
					{
						$js = json_decode($json, TRUE);
						
						$tablerow = $js["tablerow"];
						
						expandNumberIndexedArray( $_setup, $tablerow );
						
						$_setup[$tablerow] = $js ;
					}
				}
			}
		}
		
		closedir($dh);
	}
}

//
// Main
//

// Zeitzone einstellen
date_default_timezone_set('UTC');

// Pruefen, ob Datenbank-Verzeichnis existiert (ggf. anlegen)
if( file_exists( "database" ) != true )
	mkdir( "database" );

// Setup einlesen
loadSetup();

// Alle neuen Datensaetze in wsprData-Array aufnehmen
procDataDir();

// erweitertes wsprData-Array in json-Datei speichern
saveJsonData();

printf( "ok\r\n" );
?>