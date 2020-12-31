<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example</title>
<link rel="stylesheet" href="../css/stylesv300.css" type="text/css" />
<link rel="stylesheet" href="../css/templatev300.css" type="text/css" />
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAxSR6tl3WAafWf0ejSwIWqBQC-WnudriH7EPj_GGA9JFl0uvjGBT3OxrkgPvHGJPY1QSvqIL-jWvWAA" type="text/javascript"></script>
<script src="../js/gicons.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[

var map = null;
var geocoder = null;

function zoom(zone)
{
	if (zone == 'paris')
		map.setCenter(new GLatLng(48.85, 2.35), 11);

	if (zone == 'france')
		map.setCenter(new GLatLng(46.66451741754235, 2.548828125), 6);

	if (zone == 'portugal')
		map.setCenter(new GLatLng(40.212440718286466, -5.25146484375), 6);

	if (zone == 'europe')
		map.setCenter(new GLatLng(45.521743896993634, 7.470703125), 4);

	if (zone == 'monde')
		map.setCenter(new GLatLng(45.336701909968106, 7.3828125), 2);

	map.closeInfoWindow();
}

// Fonction de création de point avec légende et utilisation des icones
function createMarker(point, legende, icon)
{
	var marker = new GMarker(point, icon);
	GEvent.addListener(marker, 'mouseover', function() {
		marker.openInfoWindowHtml(legende);
		var matchll = /\(([-.\d]*), ([-.\d]*)/.exec(point);
		if ( matchll )
		{
			var lat = parseFloat( matchll[1] );
			var lon = parseFloat( matchll[2] );
			lat = lat.toFixed(6);
			lon = lon.toFixed(6);
			texte = "geotagged geo:lat=" + lat + " geo:lon=" + lon + " ";
		}
		else
			texte = "erreur";
		document.getElementById("tabox").value = texte;
	});
	GEvent.addListener(marker, 'mouseout', function() {
		map.closeInfoWindow();
	});
	return marker;
}

function SBox_Ajout_Item(sbox, item, val, selected)
{
	a=new Option(item, val, false, selected);
	indexD=sbox.options.length;
	sbox.options[indexD]=a;
}
function zoompoint(sboxS)
{
	nb_sel=sboxS.length;
	for(i=0; i < nb_sel; i++)
	{
		if (i > 0 && sboxS.options[i].selected == true)
		{
			var txt = sboxS.options[i].text;
			var val = sboxS.options[i].value;
			var tab = val.split('|');
			map.setCenter(new GLatLng(tab[0], tab[1]), 15);
		}
	}
}
// Création d'un point à partir d'une adresse
function showAddress(address, legende, icon) {
	geocoder.getLatLng(
		address,
		function(point) {
			if (!point) {
				alert(address + " not found");
			} else {
				var marker = createMarker(point, legende, icon);
				map.addOverlay(marker);
				map.panTo(point);
			}
		});
}

function showAddress2(address) {
	showAddress(address, '<div id=map>'+address+'</div>', icon_ballon);
}

function loadmap()
{
	if (GBrowserIsCompatible())
	{
		map = new GMap2(document.getElementById("map"));
		geocoder = new GClientGeocoder();

		// Permet de visualiser les coordonnées du centre de la carte
		GEvent.addListener(map, "moveend", function() {
			var center = map.getCenter();
			var zoom   = map.getZoom();
			document.getElementById("message").innerHTML = center.toString()+'-'+zoom.toString();
		});

		// Ajout des control
		map.addControl(new GMapTypeControl());
		map.addControl(new GScaleControl());
		map.addControl(new GLargeMapControl());

		// Positionnement initial de la carte
		map.setCenter(new GLatLng(48.85, 2.35), 11);

		// Message en ouverture
		//  map.openInfoWindow(map.getCenter(), document.createTextNode("Hello, world"));

		// Ajout dynamique de points
 		GEvent.addListener(map, 'click', function(overlay, point) {
			if (overlay)
			{
				map.removeOverlay(overlay);
				map.closeInfoWindow();
			}
			else if (point)
			{
				// point = Lat/Lon:(4.7021484375, 51.971345808851716)
				var matchll = /\(([-.\d]*), ([-.\d]*)/.exec(point);
				if ( matchll )
				{
					var lat = parseFloat( matchll[1] );
					var lon = parseFloat( matchll[2] );
					lat = lat.toFixed(6);
					lon = lon.toFixed(6);
					var message = "geotagged geo:lat=" + lat + " geo:lon=" + lon + " ";
					var marker = new createMarker(point, message, icon_ballon);
					map.addOverlay(marker);
				}
				else
					alert("<b>Error extracting info from</b>:" + point + "");
			}
		});

		// Lecture des données
		GDownloadUrl("../xml/pos.xml", function(data, responseCode) {
			var xml = GXml.parse(data);
			var markers = xml.documentElement.getElementsByTagName("marker");
			for (var i = 0; i < markers.length; i++)
			{
				var point   = new GLatLng(parseFloat(markers[i].getAttribute("lat")),parseFloat(markers[i].getAttribute("lng")));
				var libelle = '<div>'+markers[i].getAttribute("nom")+'<br />'+markers[i].getAttribute("adresse")+'<br />'+markers[i].getAttribute("cp")+' '+markers[i].getAttribute("ville")+'<br />'+markers[i].getAttribute("tel")+'</div>';
				map.addOverlay(createMarker(point, libelle, icon_ballon));
				SBox_Ajout_Item(document.getElementById("speedaccess"), markers[i].getAttribute("nom"), markers[i].getAttribute("lat")+'|'+markers[i].getAttribute("lng"), false);
			}
		});

		// Tests
		// var eiffel = new GPoint(2.2944259643554688, 48.85817876694892);
		// map.addOverlay(createMarker(eiffel, "La Tour Eiffel", icon_ballon));
		// showAddress('36 rue moliere 78800 HOUILLES', '36 rue moliere 78800 HOUILLES', icon_ballon);

		var polyline = new GPolyline([
		new GLatLng(48.8581, 2.2944),
		new GLatLng(48.8681, 2.2844)
		], "#FF6600", 10);
		map.addOverlay(polyline);

	}
}

//]]>
</script>
  </head>
  <body onload="loadmap()" onunload="GUnload()">

    <center>

    <form action="#" onsubmit="showAddress2(this.address.value); return false">
      <p>
        <input type="text" size="60" name="address" value="1600 Amphitheatre Pky, Mountain View, CA" />
        <input type="submit" value="Go!" />
      </p>

      <div id="map" style="width: 700px; height: 500px"></div>
      <div id="message"></div>

      <p>
      	<textarea name="tabox" id="tabox" cols="80" rows="10">
      	</textarea>
      </p>

      <p>
      	<a href="javascript:zoom('paris');"> zoom paris </a>
      	<a href="javascript:zoom('france');"> zoom france </a>
      	<a href="javascript:zoom('portugal');"> zoom portugal </a>
      	<a href="javascript:zoom('europe');"> zoom europe </a>
      	<a href="javascript:zoom('monde');"> zoom monde </a>
      </p>

      <p>
      	<select id="speedaccess" onchange="javascript:zoompoint(document.getElementById('speedaccess'));">
      	<option />
      	</select>
      </p>

    </form>

    </center>

  </body>
</html>
