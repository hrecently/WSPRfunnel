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


//
// Functions
//


//
// Main
//

// Bitmap fuer Band-Selektion 
if( isset($_COOKIE["band_bitmap"]))
	$_bandBitmap = $_COOKIE["band_bitmap"]; 
else
	$_bandBitmap = 0xffffffff;

date_default_timezone_set("Europe/Berlin");
$jsonfile="./database/".date("Y")."/".date("ymd")."_wsprdat.json";
//$jsonfile="./database/190406_wsprdat.json";

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

//print_r($_wsprData["repcnt"]);
	// Alle Report-Zaehler auf 0 setzen
	$setup_num = count($_wsprData["repcnt"]);
	for( $i=0 ; $i<$setup_num ; $i++ )
		$_wsprData["repcnt"][$i] = 0;
	
	// Alle Slots bearbeiten
	$slot_num = count($_wsprData["slot"]);
	for( $slot_idx=0; $slot_idx < $slot_num; $slot_idx++ )
	{
		// Kopie der Reports des aktuellen Slots
		$reports = $_wsprData["slot"][$slot_idx];
		
		// Weiter, wenn keine Reports vorhanden
		if( $reports == null )
			continue ;

		// Neues Array erzeugen, in das die nach der Filterung verbleibenden Reports umgelagert werden
		$_wsprData["slot"][$slot_idx] = array();
		$idx = 0; // Index fuer das neue Array
		
		// Alle Reports "filtern"
		$repnum = count($reports);
		for( $rep_idx=0; $rep_idx < $repnum ; $rep_idx++ )
		{
			// band-Filter
			$band = $reports[$rep_idx]["band"];
			$band_bit = $_bandBits[$band];
			
			// Wenn Band-Bit nicht gesetzt, dann Report auf null setzen			
			if(( $_bandBitmap & $band_bit ) == 0 )
			{
				$reports[$rep_idx] = null;
				continue;
			}

			// Report "umlagern"
			$_wsprData["slot"][$slot_idx][$idx] = $reports[$rep_idx];
			$idx++;

			// Reportzaehler inkrementieren
			for( $setupidx=0 ; $setupidx < $setup_num ; $setupidx++ )
			{
				// Wenn RXF gesetzt, dann ist ein Report vorhanden (leeres Element = NO RECEIVE)
				if( isset( $reports[$rep_idx]["srcArray"][$setupidx]["RXF"]))
					$_wsprData["repcnt"][$setupidx]++ ;
			}
		}
	}
//print_r($_wsprData["repcnt"]);
	
	// json-Daten aus aktuellem Array erzeugen
	$jsonstr = json_encode($_wsprData,  JSON_PRETTY_PRINT);
}

// Ausgabe Inhalt
header("Content-type: application/json");
printf("$jsonstr");
?>