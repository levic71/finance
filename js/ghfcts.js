var map = null;

// Zoom direct dans une zone prédéfinie
function zoom(zone)
{
	if (zone == 'paris')
		map.setCenter(new GLatLng(48.854325, 2.349014), 11);

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

// Combobox access
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

// Fonction de création de point avec légende et utilisation des icones
function createSimpleMarker(point, legende, icon)
{
	var marker = new GMarker(point, icon);
	GEvent.addListener(marker, 'mouseover', function() {
		marker.openInfoWindowHtml(legende);
	});
	GEvent.addListener(marker, 'mouseout', function() {
		map.closeInfoWindow();
	});
	GEvent.addListener(marker, 'click', function() {
		map.setCenter(point, 15);
	});
	GEvent.addListener(marker, 'dblclick', function() {
		map.setCenter(point, 11);
	});
	
	return marker;
}
		
function loadmap()
{
	if (GBrowserIsCompatible())
	{
		map = new GMap2(document.getElementById("map"));
	
		// Ajout des control
		map.addControl(new GMapTypeControl());
		map.addControl(new GScaleControl());
		map.addControl(new GLargeMapControl());
		
		// Positionnement initial de la carte sur PARIS
		map.setCenter(new GLatLng(48.854325, 2.349014), 11);

		// Lecture des données
		GDownloadUrl("partenaires_get.php", function(data) {
			var xml = GXml.parse(data);
			var markers = xml.documentElement.getElementsByTagName("marker");
			for (var i = 0; i < markers.length; i++)
			{
				var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),parseFloat(markers[i].getAttribute("lng")));
				var nom = markers[i].getAttribute("nom");
				var ville = markers[i].getAttribute("ville");
				var cp = markers[i].getAttribute("cp");
				var adresse = markers[i].getAttribute("adresse");
				var tel = markers[i].getAttribute("tel");
				var email = markers[i].getAttribute("email");
				var web = markers[i].getAttribute("web");
				var my_icon = icon_msn_viollet;
				if (markers[i].getAttribute("icon") == "icon_ballon") my_icon = icon_ballon;
				if (markers[i].getAttribute("icon") == "icon_person_red") my_icon = icon_person_red;
				if (markers[i].getAttribute("icon") == "icon_person_blue") my_icon = icon_person_blue;
				if (markers[i].getAttribute("icon") == "icon_person_green") my_icon = icon_person_green;
				if (markers[i].getAttribute("icon") == "icon_person_yellow") my_icon = icon_person_yellow;
				if (markers[i].getAttribute("icon") == "icon_person_white") my_icon = icon_person_white;
				if (markers[i].getAttribute("icon") == "icon_person_orig") my_icon = icon_person_orig;
				map.addOverlay(createSimpleMarker(point, '<div id="map"><b>'+nom+'</b><br />'+adresse+'<br />'+cp+' '+ville+'<br />'+tel+'<br />'+email+'<br />'+web+'</div>', my_icon));
				SBox_Ajout_Item(document.getElementById("speedaccess"), markers[i].getAttribute("nom"), markers[i].getAttribute("lat")+'|'+markers[i].getAttribute("lng"), false);
				window.setTimeout(function() {
  					map.panTo(point);
				}, 1000);			}
		});
	}
}
