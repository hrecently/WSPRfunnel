<?PHP

//
// Variables
//

// Das "Bit" zum Band
$_bandBits =
[
	"LW" => 1,			// 0
	"MW" => 2,			// 1
	"160m" => 4,		// 2
	"80m" => 8,			// 3
	"!80m" => 16,		// 4
	"60m" => 32,		// 5
	"!60m" => 64,		// 6
	"40m" => 128,		// 7
	"30m" => 256,		// 8
	"20m" => 512,		// 9
	"17m" => 1024,		// 10
	"15m" => 2048,		// 11
	"12m" => 4096,		// 12
	"10m" => 8192,		// 13
	"6m" => 16384,		// 14
	"4m" => 32768,		// 15
	"2m" => 65536,		// 16
	"70cm" => 131072,	// 17
	"23cm" => 262144	// 18
];	

// Das aktuelle wspr-Array
$_wsprData;

// Die Daten, die zum Browser geschickt werden
$_browserOut;

// Das Cookie-Objekt
$_cookieObj;


//
// Functions
//
/*
function searchBegYear( $year )
{
	$year = intval($year);
	$dir = "./database/" ;

	// Gibt es das Verzeichnis ?
	$ystr = (string) $year ;
	if( file_exists( $dir.$ystr ))
	{
		// fertig
		return $year ;
	}
	
	$oldest = 9999;
	
	if($dh = opendir($dir)) 
	{
		while (($file = readdir($dh)) !== false) 
		{
			if( filetype($dir.$file) == "dir" )
			{
				if( $file == "." || $file == ".." )
					continue ;
				
				$f = intval($file);
				if( $f < $oldest )
					if( $f > $year )
						$oldest = $f;
			}
		}

		$year = $oldest ;
		closedir($dh);
	}
	
	if( $year == 9999 )
		return false;
	
	return $year ;
}


function searchBegFile( $setDay, $setMonth, $begYear )
{
	$res = array();
	
	$dir = "./database/$begYear/" ;
	if( !file_exists($dir))
		return false;
	
	$setDay   = (string) $setDay;
	$setMonth = (string) $setMonth;
	$begYear  = substr((string) $begYear,2,2);

	if( strlen($setDay) < 2 )
		$setDay = "0".$setDay ;
	if( strlen($setMonth) < 2 )
		$setMonth = "0".$setMonth ;

	$file = $begYear.$setMonth.$setDay."_wsprdat.json";

	// Gibt es die Datei ?
	if( file_exists($dir.$file))
	{
		$res["begDay"] = $setDay ;
		$res["begMonth"] = $setMonth ;
		
		//fertig
		return $res;
	}
	
	// SUCHE MONTH
	
	$oldest = 13;
	if($dh = opendir($dir)) 
	{
		while (($file = readdir($dh)) !== false) 
		{
			if( filetype($dir.$file) == "file" )
			{
				$m = intval(substr($file,2,2));
				if( $m < $oldest )
					if( $m > $setMonth )
						$oldest = $m;
			}
		}

		$begMonth = $oldest ;
		closedir($dh);
	}
	
	if( $begMonth == 13 )
		return false;

	
	// SUCHE DAY

	$oldest = 32;
	if($dh = opendir($dir)) 
	{
		while (($file = readdir($dh)) !== false) 
		{
			if( filetype($dir.$file) == "file" )
			{
				$m = intval(substr($file,2,2));
				if( $m != $begMonth )
					continue ;

				$d = intval(substr($file,4,2));
				if( $d < $oldest )
					if( $d > $setDay )
						$oldest = $d;
			}
		}

		$begDay = $oldest ;
		closedir($dh);
	}
	
	if( $begDay == 32 )
		return false;
	
	$begDay   = (string) $begDay;
	$begMonth = (string) $begMonth;

	if( strlen($begDay) < 2 )
		$begDay = "0".$begDay ;
	if( strlen($begMonth) < 2 )
		$begMonth = "0".$begMonth ;
	
	$res["begDay"] = $begDay ;
	$res["begMonth"] = $begMonth ;

	return $res;
}


function processFile( $file, $begTime, $endTime )
{
	printf("process $file from $begTime to $endTime \n");
	
	return false ;
}
*/

/*
// DIE ERSTE WSPR-DATEI SUCHEN

$setYear = gmdate("Y", $_cookieObj["unixBegTime"]);
$begYear = searchBegYear($setYear);
printf("begYear = $begYear\n");

if( $setYear != $begYear )
{
	$setMonth = 1; // Im 1. Januar anfangen
	$setDay = 1 ; 
}
else
{
	$setMonth = gmdate("m", $_cookieObj["unixBegTime"]); // Im expliziten Monat anfangen
	$setDay   = gmdate("m", $_cookieObj["unixBegTime"]); // Am expliziten Tag anfangen1;    
}

while(($res = searchBegFile( $setDay, $setMonth, $begYear )) == false )
{
	$begYear = intval($begYear);
	$begYear ++ ;
	
	if(begYear > 9999 )
		break ;
};

if( $res != false )
{
	$year = 
	$begDay = $res["begDay"] ;
	$begMonth = $res["begMonth"];
	
	$year  = substr($begYear,2,2);
	
	// ALLE WSPR-DATEIEN BEARBEITEN
	do 
	{
		$file = "./database/$begYear/".$year.$begMonth.$begDay."_wsprdat.json" ;
	
		if( processFile($file, "a", "b") == false );
			break ;
	}
	while(1);

}
*/


//
// Main
//

// Cookie-Objekt einlesen
if( isset($_COOKIE["wspr"]))
{
	$str = $_COOKIE["wspr"]; 
	$_cookieObj = json_decode($str, TRUE);
}
else
{
	// Default-Objekt
	$_cookieObj = array();
	$_cookieObj["userName"] = "DF5FH";
	$_cookieObj["setupName"] = "1901_2RedPitaya.001";
	$_cookieObj["bandBitMap"] = "0xffffffff";
	$_cookieObj["setupName"] = "?";
	$_cookieObj["count"] = "50";
	$_cookieObj["unixBegTime"] = 0;
	$_cookieObj["unixEndTime"] = 0xffffffff;
}

//printf("<pre>");
//printf("Debug-Output:\n");
//print_r($_cookieObj);

date_default_timezone_set('UTC');
$jsonfile="./database/".$_cookieObj["userName"]."/".$_cookieObj["setupName"]."/".date("ymd")."_wsprdat.json";
//printf("$jsonfile<br>");

// Wenn json-Datei vorhanden, dann laden
if( file_exists ($jsonfile) == TRUE )
	$jsonstr = file_get_contents($jsonfile);
else
	$jsonstr = "";

// Gibt es was zum verarbeiten ?
if( $jsonstr != "" )
{
	// json-Daten in Array dekodieren
	$_wsprData = json_decode($jsonstr, TRUE);

	// Array fuer AusgabeDaten erzeugen
	$_browserOut = array();
	
	// Report-Zaehler erzeugen
	$setup_num = count($_wsprData["repcnt"]);
	for( $i=0 ; $i<$setup_num ; $i++ )
		$_browserOut["repcnt"][$i] = 0;

	// Setup-Array kopieren
	$_browserOut["setup"] = $_wsprData["setup"];
	
	// Slot-Array erzeugen
	$_browserOut["slot"] = array();

	// Index der "neuen" Slots
	$new_slot_idx = 0 ;
	$new_slot_flag = false ;
	
	// Alle Slots bearbeiten
	$limit = 0;
	$slot_num = count($_wsprData["slot"]);
	for( $slot_idx=0; $slot_idx < $slot_num; $slot_idx++ )
	{
		// Ausgabe-Begrenzung
		if( $limit >= $_cookieObj["count"] )
			break; 
		
		// FILTER

		// Index der neuen Reports
		$new_rep_idx = 0 ;
		
		// Alle Reports "filtern"
		$repnum = count($_wsprData["slot"][$slot_idx]["report"]);
		for( $rep_idx=0; $rep_idx < $repnum ; $rep_idx++ )
		{
			// FILTER ANWENDEN

			// band-Filter
			$band = $_wsprData["slot"][$slot_idx]["report"][$rep_idx]["band"];
			$band_bit = $_bandBits[$band];
			if(( $_cookieObj["bandBitMap"] & $band_bit ) == 0 )
				continue;
		
			// FILTERERGEBNIS UEBERNEHMEN

			// Pruefen, ob neues Slot-Element noch nicht erzeugt wurde
			if( !isset($_browserOut["slot"][$new_slot_idx]))
			{
				// Neues Slot-Element erzeugen
				$_browserOut["slot"][$new_slot_idx] = array();
				$_browserOut["slot"][$new_slot_idx]["unixtime"] = $_wsprData["slot"][$slot_idx]["unixtime"];
				$_browserOut["slot"][$new_slot_idx]["report"] = array();
			}
			
			// vollstaendigen Report uebernehmen
			$_browserOut["slot"][$new_slot_idx]["report"][$new_rep_idx] = $_wsprData["slot"][$slot_idx]["report"][$rep_idx]; 

			// Report-Zaehler ueber alle Sourcen updaten
			$src_arr = $_browserOut["slot"][$new_slot_idx]["report"][$new_rep_idx]["srcArray"];
			$src_num = count($src_arr);
			for( $src_idx=0; $src_idx < $src_num ; $src_idx++ )
				if( isset($src_arr[$src_idx]))	// Achtung: hier wird auch "NO RECEIVE" mitgezaehlt
					$_browserOut["repcnt"][$src_idx] ++;

			// neuen Index asynchron zu $rep_idx erhoehen
			$new_rep_idx++ ;

			// Flag fuer neuen Slot-Index
			$new_slot_flag = true;
			
			// Ausgabe-Begrenzung
			$limit ++ ;
			if( $limit >= $_cookieObj["count"] )
				break; 
		}

		// Pruefen, ob neuer Slot-Index 
		if( $new_slot_flag == true )
		{
			$new_slot_flag = false ;
			$new_slot_idx ++ ;
		}
	}

	// json-Daten aus aktuellem Array erzeugen
	$jsonstr = json_encode($_browserOut,  JSON_PRETTY_PRINT);
}
//printf("</pre>");

// Ausgabe Inhalt
header("Content-type: application/json");
printf("$jsonstr");

?>