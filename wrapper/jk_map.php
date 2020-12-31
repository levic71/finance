<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$iframe = Wrapper::getRequest('iframe', 'yes');
$_action_ = "leagues";
$opt_edit = "";
$new_tip  = "Nouveau Championnat";

if ($iframe == "yes") {
?>
	<ul class="sidebar">
		<li><a href="#" onclick="go({action: '<?= $_action_ ?>', id:'main', url:'edit_<?= $_action_ ?>.php<?= $opt_edit ?>'});" id="new" class="ToolText" onmouseover="showtip('new');"><span><?= $new_tip ?></span></a></li>
		<li id="swap1"><a href="#" onclick="mm({action: 'leagues'});" class="swap ToolText" onmouseover="showtip('swap1');"><span>Annuaire</span></a></li>
	</ul>

	<iframe src="jk_map.php?iframe=no" height="740" width="700" frameborder="0" border="0" framespacing="0" scrolling="no"></iframe>
<?
	exit(0);
}

?>

<!DOCTYPE>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?= sess_context::charset ?>" />
<title>MarkerClustererPlus V3 Example</title>

<style type="text/css">
body { padding: 0px; margin: 0px; }
#map-container {
	padding: 6px;
	border-width: 1px;
	border-style: solid;
	border-color: #ccc #ccc #999 #ccc;
	-webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
	-moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
	box-shadow: rgba(64, 64, 64, 0.1) 0 2px 5px;
	width: 686px;
}

#map {
	width: 684px;
	height: 686px;
}

.winmap {
	font: 12px "Lucida Grande", Lucida, Verdana, sans-serif;
}
.winmap div { line-height: 20px; padding-left: 15px; }
.winmap .name { margin-bottom: 5px; font-size: 16px; font-weight: bold; background: url('img/icon_champ.gif') no-repeat 2px 6px; border-bottom: 1px solid #ccc; }
.winmap .type_0 { background: url('img/icon_libre.gif') no-repeat 2px 6px; }
.winmap .type_2 { background: url('img/icon_tournoi.gif') no-repeat 2px 6px; }
.winmap .access { padding-left: 0px; margin-top: 5px; border-top: 1px solid #ccc; }
</style>

<link rel="stylesheet" type="text/css" media="screen" href="css/components.css" />

<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
<script type="text/javascript" src="data/json_championnats.json?<?= date("YmdH00") ?>"></script>
<script type="text/javascript" src="js/markerclusterer_packed.js"></script>

<script type="text/javascript">

map = null;
infoWindow = null;
function initialize() {

	var libelle_genre = [];
	<? while (list($cle, $val) = each($libelle_genre)) { echo "libelle_genre[".$cle."]='".$val."';\n"; } ?>

	var center = new google.maps.LatLng(48.856614, 2.3522219);
	map = new google.maps.Map(document.getElementById('map'), { zoom: 3, center: center, mapTypeId: google.maps.MapTypeId.ROADMAP });

	var markerImage = [];
	markerImage[0] = new google.maps.MarkerImage("img/google/orange/star-3.png", new google.maps.Size(32, 37));
	markerImage[1] = new google.maps.MarkerImage("img/google/blue/star-3.png", new google.maps.Size(32, 37));
	markerImage[2] = new google.maps.MarkerImage("img/google/green/star-3.png", new google.maps.Size(32, 37));
	markerImage[10] = new google.maps.MarkerImage("img/google/orange/soccer.png", new google.maps.Size(32, 37));
	markerImage[11] = new google.maps.MarkerImage("img/google/blue/soccer.png", new google.maps.Size(32, 37));
	markerImage[12] = new google.maps.MarkerImage("img/google/green/soccer.png", new google.maps.Size(32, 37));
	markerImage[20] = new google.maps.MarkerImage("img/google/orange/stadium.png", new google.maps.Size(32, 37));
	markerImage[21] = new google.maps.MarkerImage("img/google/blue/stadium.png", new google.maps.Size(32, 37));
	markerImage[22] = new google.maps.MarkerImage("img/google/green/stadium.png", new google.maps.Size(32, 37));
	markerImage[30] = new google.maps.MarkerImage("img/google/orange/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[31] = new google.maps.MarkerImage("img/google/blue/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[32] = new google.maps.MarkerImage("img/google/green/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[40] = new google.maps.MarkerImage("img/google/orange/basketball.png", new google.maps.Size(32, 37));
	markerImage[41] = new google.maps.MarkerImage("img/google/blue/basketball.png", new google.maps.Size(32, 37));
	markerImage[42] = new google.maps.MarkerImage("img/google/green/basketball.png", new google.maps.Size(32, 37));
	markerImage[50] = new google.maps.MarkerImage("img/google/orange/handball.png", new google.maps.Size(32, 37));
	markerImage[51] = new google.maps.MarkerImage("img/google/blue/handball.png", new google.maps.Size(32, 37));
	markerImage[52] = new google.maps.MarkerImage("img/google/green/handball.png", new google.maps.Size(32, 37));
	markerImage[60] = new google.maps.MarkerImage("img/google/orange/volleyball.png", new google.maps.Size(32, 37));
	markerImage[61] = new google.maps.MarkerImage("img/google/blue/volleyball.png", new google.maps.Size(32, 37));
	markerImage[62] = new google.maps.MarkerImage("img/google/green/volleyball.png", new google.maps.Size(32, 37));
	markerImage[70] = new google.maps.MarkerImage("img/google/orange/rugbyfield.png", new google.maps.Size(32, 37));
	markerImage[71] = new google.maps.MarkerImage("img/google/blue/rugbyfield.png", new google.maps.Size(32, 37));
	markerImage[72] = new google.maps.MarkerImage("img/google/green/rugbyfield.png", new google.maps.Size(32, 37));
	markerImage[80] = new google.maps.MarkerImage("img/google/orange/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[81] = new google.maps.MarkerImage("img/google/blue/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[82] = new google.maps.MarkerImage("img/google/green/soccerfield.png", new google.maps.Size(32, 37));
	markerImage[90] = new google.maps.MarkerImage("img/google/orange/tennis.png", new google.maps.Size(32, 37));
	markerImage[91] = new google.maps.MarkerImage("img/google/blue/tennis.png", new google.maps.Size(32, 37));
	markerImage[92] = new google.maps.MarkerImage("img/google/green/tennis.png", new google.maps.Size(32, 37));
	markerImage[100] = new google.maps.MarkerImage("img/google/orange/tebletennis.png", new google.maps.Size(32, 37));
	markerImage[101] = new google.maps.MarkerImage("img/google/blue/tebletennis.png", new google.maps.Size(32, 37));
	markerImage[102] = new google.maps.MarkerImage("img/google/green/tebletennis.png", new google.maps.Size(32, 37));
	markerImage[110] = new google.maps.MarkerImage("img/google/orange/petanque.png", new google.maps.Size(32, 37));
	markerImage[111] = new google.maps.MarkerImage("img/google/blue/petanque.png", new google.maps.Size(32, 37));
	markerImage[112] = new google.maps.MarkerImage("img/google/green/petanque.png", new google.maps.Size(32, 37));
	markerImage[120] = new google.maps.MarkerImage("img/google/orange/videogames.png", new google.maps.Size(32, 37));
	markerImage[121] = new google.maps.MarkerImage("img/google/blue/videogames.png", new google.maps.Size(32, 37));
	markerImage[122] = new google.maps.MarkerImage("img/google/green/videogames.png", new google.maps.Size(32, 37));

	var shadow = new google.maps.MarkerImage('img/google/shadow2.png');

	var markers = [];
	for (var i = 0; i < data.count; i++) {
		var item = data.items[i];
		var latLng = new google.maps.LatLng(item.lat, item.lng);
		var marker = new google.maps.Marker({
			position: latLng,
			icon: markerImage[(parseInt(item.sport)*10)+parseInt(item.type)],
			title: item.name,
			shadow: shadow
		});

		var fn = markerClick(
			'<div class="winmap">'+
				'<div class="name type_'+item.type+'">'+item.name+'</div>'+
				'<div class="sport">'+libelle_genre[item.sport]+'</div>'+
				'<div class="owner">'+item.manager+'</div>'+
				'<div class="addr">'+item.address+'</div>'+
				'<div class="access">» <a href="#" onclick="parent.window.location.href=\'<?= $sess_context->isSuperUser() ? "http://localhost:8088/jorkyball/" : "http://'+item.dns+'.jorkers.com/wrapper/" ?>jk.php?idc='+item.id+'\';">Accès</a></div>'+
			'</div>', latLng);
		google.maps.event.addListener(marker, 'click', fn);

		markers.push(marker);
	}
	var markerCluster = new MarkerClusterer(map, markers, {
          maxZoom: 16,
          gridSize: 50
        });

	infoWindow = new google.maps.InfoWindow();
}

google.maps.event.addDomListener(window, 'load', initialize);

markerClick = function(infoHtml, latlng) {
	return function(e) {
		e.cancelBubble = true;
		e.returnValue = false;
		if (e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		}

		infoWindow.setContent(infoHtml);
		infoWindow.setPosition(latlng);
		infoWindow.open(map);
	};
}

zoom_map = function(zone)
{
	if (zone == 'paris')	{ map.setCenter(new google.maps.LatLng(48.854325, 2.349014)); map.setZoom(11); }
	if (zone == 'france')	{ map.setCenter(new google.maps.LatLng(46.66451741754235, 2.548828125)); map.setZoom(6); }
	if (zone == 'portugal')	{ map.setCenter(new google.maps.LatLng(40.212440718286466, -5.25146484375)); map.setZoom(6); }
	if (zone == 'europe')	{ map.setCenter(new google.maps.LatLng(45.521743896993634, 7.470703125)); map.setZoom(4); }
	if (zone == 'monde')	{ map.setCenter(new google.maps.LatLng(45.336701909968106, 7.3828125)); map.setZoom(2); }
 	infoWindow.close();
}
</script>
</head>
<body>
<div id="map-container"><div id="map"></div></div>

<div style="text-align:center; margin-top: 5px;">
<button class="button blue" onclick="javascript:zoom_map('paris');">Zoom Paris</button>
<button class="button blue" onclick="javascript:zoom_map('france');">Zoom France</button>
<button class="button blue" onclick="javascript:zoom_map('europe');">Zoom Europe</button>
<button class="button blue" onclick="javascript:zoom_map('monde');">Zoom Monde</button>
</div>

</body>
</html>
