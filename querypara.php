<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<title>Specify Query Parameter</title>
<style>
body
{ 
	margin:0px;
	padding:0px;
	border:0px;
	font-family:Arial; 
	font-size:16px;
	background-color:#FFF; 
	white-space: nowrap;
}
</style>

<?PHP
// Setup-Array generieren (aus Verzeichnissen in /database)
function srcGenerateSetupArray()
{
	printf(	"\t// Setup-Array (php-generated)\n".
			"\tvar setupname = [");
		   
	// Verzeichnis, in das die Report-Dateien hochgeladen werden
	$srcdir = "./database/DF5FH/" ;
	if($dh = opendir($srcdir)) 
	{
		$flag = false;
		
		// Alle Setup-Verzeichnisse bearbeiten
		while (($file = readdir($dh)) !== false) 
		{
			if( $file == "." )
				continue ;
			if( $file == ".." )
				continue ;

			if( filetype($srcdir.$file) == "dir" )
			{
				if( $flag == false )
				{
					$flag = true ;
					printf( "\"$file\"" );
				}
				else
				{
					printf( ",\"$file\"" );
				}
			}
		}
		
		closedir($dh);
	}
	printf("];\n");
}
?>	

<script type="text/javascript">

//
// VARIABLEN
//

var _cookieObj;
var _bandBitmap;

//
// FUNCTIONS
//

function setCookie(cname, cvalue, exdays) 
{
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) 
{
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function updCheckBoxes()
{
	var bands = [ "LW", "MW", "160m", "80m", "!80m", "60m", "!60m", "40m", "30m", "20m", "17m", "15m", "12m", "10m", "6m", "4m", "2m", "70cm", "23cm" ];

	for( var i=0 ; i<bands.length ; i++ )
	{
		var el = document.getElementById(bands[i]);
		
		if(( _bandBitmap & (1<<i)) != 0 )
			el.checked = true;
		else
			el.checked = false;
	}
}

function init()
{
	var obj;
	var cookiestr = getCookie("wspr");
	if( typeof cookiestr != "string" || cookiestr.length == 0 )
	{
		// Default-Objekt erzeugen
		_cookieObj = new Object();
		_cookieObj.userName = "DF5FH";
		_cookieObj.setupName = "?";
		_cookieObj.bandBitMap = "0";
		_cookieObj.count = "50";
		_cookieObj.unixBegTime = "0";
		_cookieObj.unixEndTime = "0xffffffff";
		
		setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
	}
	else
	{
		try
		{
			_cookieObj = JSON.parse(cookiestr);
		}
		catch (e)
		{
			alert(cookiestr);
			return;
		}
	}
	
	// Band-Bitmap als Zahl zur Verfuegung stellen
	_bandBitmap = parseInt( _cookieObj.bandBitMap );
	
	
	// BANDS

	updCheckBoxes();
		
	
	// SETUP
	
	var div = document.getElementById("Setup");
	
	// Ueberschrift
	var html = document.createElement("div");
	html.innerHTML ="Setup";
	div.appendChild(html);
	
	var sel = document.createElement("select");
	div.appendChild(sel);

<?PHP
	// Das Setup-Array wird durch Auslesen der Verzeichnisse mit PHP erzeugt
	srcGenerateSetupArray();
?>	

	if( _cookieObj.setupName == "?" )
	{
		_cookieObj.setupName = setupname[0];
		setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
	}
	
	// Das Setup-Array
	for (var i = 0; i < setupname.length; i++ ) 
	{
		var option = document.createElement("option");
		option.value = setupname[i];
		option.text = setupname[i];
		if( option.value == _cookieObj.setupName )
			option.selected = "selected";
		sel.appendChild(option);
	}
	
	sel.onchange = function()
	{
		// Aktuell eingestellten Wert holen
		var elem = (typeof this.selectedIndex === "undefined" ? window.event.srcElement : this);
		var value = elem.value || elem.options[elem.selectedIndex].value;
		
		// ... und uebernehmen
		_cookieObj.setupName = value;
		setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
	}

	
	// COUNT

	div = document.getElementById("Count");
	
	input = document.createElement("input");
	div.appendChild(input);
	
	input.value = _cookieObj.count;
	input.style.width = "100px";
	
	input.onchange = function()
	{
		// Aktuell eingestellten Wert holen
		var elem = (typeof this.selectedIndex === "undefined" ? window.event.srcElement : this);
		var value = elem.value || elem.options[elem.selectedIndex].value;

		// ... und uebernehmen
		_cookieObj.count = value;
		setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
	}
}	


function enterBand(el)
{
	if( el.checked == true )
		_bandBitmap |= parseInt(el.name);
	else
		_bandBitmap &= ~parseInt(el.name);

	_cookieObj.bandBitMap = _bandBitmap.toString();
	setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
}

function selectAll()
{
	_bandBitmap = "524287";
	
	_cookieObj.bandBitMap = _bandBitmap.toString();
	setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );

	updCheckBoxes();
}

function selectNone()
{
	_bandBitmap = "0";
	
	_cookieObj.bandBitMap = _bandBitmap.toString();
	setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );

	updCheckBoxes();
}

function enterTimeRange( mode )
{
	switch(mode)
	{
		case 0: // today
		
		var el = document.getElementById("today");
		
		if( el.checked == true )
		{
			var d = new Date();
			var year = d.getUTCFullYear();
			var month = d.getUTCMonth() + 1;
			var day = d.getUTCDate();
			
			var d2 = new Date(year, month - 1, day, 2, 0, 0, 0);
			var beg = d2.getTime();

			var d3 = new Date(year, month - 1, day+1, 2, 0, 0, 0);
			var end = d2.getTime();
			
			_cookieObj.unixBegTime = beg/1000;
			_cookieObj.unixEndTime = end/1000;
		
			setCookie( "wspr", JSON.stringify(_cookieObj), 1000 );
		}
	}
}

window.onload = init;

</script>
</head>
<body>
<table>
<tr style="vertical-align:top">
<td>
	<div style="margin-top:50px;margin-left:50px">Bands</div>
	<div style="margin-top:0px;margin-left:50px">
		<input id="LW"   name="1"      type="checkbox" onchange="enterBand(this)"> Longwave<br>
		<input id="MW"   name="2"      type="checkbox" onchange="enterBand(this)"> Midwave<br>
		<input id="160m" name="4"      type="checkbox" onchange="enterBand(this)"> 160m<br>  
		<input id="80m"  name="8"      type="checkbox" onchange="enterBand(this)"> 80m<br>  
		<input id="!80m" name="16"     type="checkbox" onchange="enterBand(this)"> 80m (old)<br>  
		<input id="60m"  name="32"     type="checkbox" onchange="enterBand(this)"> 60m<br>  
		<input id="!60m" name="64"     type="checkbox" onchange="enterBand(this)"> 60m (2)<br>  
		<input id="40m"  name="128"    type="checkbox" onchange="enterBand(this)"> 40m<br>  
		<input id="30m"  name="256"    type="checkbox" onchange="enterBand(this)"> 30m<br>  
		<input id="20m"  name="512"    type="checkbox" onchange="enterBand(this)"> 20m<br>  
		<input id="17m"  name="1024"   type="checkbox" onchange="enterBand(this)"> 17m<br>  
		<input id="15m"  name="2048"   type="checkbox" onchange="enterBand(this)"> 15m<br>  
		<input id="12m"  name="4096"   type="checkbox" onchange="enterBand(this)"> 12m<br>  
		<input id="10m"  name="8192"   type="checkbox" onchange="enterBand(this)"> 10m<br>  
		<input id="6m"   name="16384"  type="checkbox" onchange="enterBand(this)"> 6m<br>  
		<input id="4m"   name="32768"  type="checkbox" onchange="enterBand(this)"> 4m<br>  
		<input id="2m"   name="65536"  type="checkbox" onchange="enterBand(this)"> 2m<br>  
		<input id="70cm" name="131072" type="checkbox" onchange="enterBand(this)"> 70cm<br>  
		<input id="23cm" name="262144" type="checkbox" onchange="enterBand(this)"> 23cm<br>  
		<br>
		<span style="cursor:pointer" onClick="selectAll()">all</span>
		<span style="cursor:pointer" onClick="selectNone()">none</span>
		<span><a href="reports.html">update</a></span>
	</div>
</td>
<td>
	<div style="margin-top:50px;margin-left:50px" id="Setup"></div>
	
	<div style="margin-top:30px;margin-left:50px">Time range</div>
	<div style="margin-top:0px;margin-left:50px">
		<input id="today" name="range" type="radio" onchange="enterTimeRange(0)"> today<br>
		<input id="" name="range" type="radio" onchange=""> from begin<br>
	</div>
	
	<div style="margin-top:30px;margin-left:50px">Count</div>
	<div style="margin-top:0px;margin-left:50px">
		<div id="Count"></div>
		<br>
		<span><a href="reports.html">update</a></span>
	<div>
	
</td>
</tr>
</table>
</body>
