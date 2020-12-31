<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$iframe  = Wrapper::getRequest('iframe',  'yes');
$street  = Wrapper::getRequest('street',  '');
$city    = Wrapper::getRequest('city',    '');
$zip     = Wrapper::getRequest('zip',     '');
$state   = Wrapper::getRequest('state',   '');
$country = Wrapper::getRequest('country', '');
$lat     = Wrapper::getRequest('lat',     '');
$lng     = Wrapper::getRequest('lng',     '');
$zoom    = Wrapper::getRequest('zoom',    '10');


if ($iframe == "yes") {
?>
	<iframe src="admin_address_chooser.php?iframe=no&zoom=<?= $zoom ?>&lat=<?= $lat ?>&lng=<?= $lng ?>&street=<?= utf8_encode($street) ?>&city=<?= utf8_encode($city) ?>&zip=<?= $zip ?>&state=<?= utf8_encode($state) ?>&country=<?= utf8_encode($country) ?>" height="800" width="800" frameborder="0" border="0" framespacing="0" scrolling="no"></iframe>
<?
	exit(0);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" media="screen" href="css/basic.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/components.css" />
<script src="http://www.google.com/jsapi?key=ABQIAAAAxSR6tl3WAafWf0ejSwIWqBQC-WnudriH7EPj_GGA9JFl0uvjGBT3OxrkgPvHGJPY1QSvqIL-jWvWAA" type="text/javascript"></script>
<script src="js/googlemap.js" type="text/javascript"></script>
<script src="js/addresschooser.js" type="text/javascript"></script>
<script src="js/raphael-min.js" type="text/javascript"></script>
<script src="js/jk.js" type="text/javascript"></script>
<script src="js/components.js" type="text/javascript"></script>
</head>
<body>

<div id="address_chooser">

  <div id='map_container'>
    <div id='map_tooltip'>Déplacer le marker pour définir au plus près votre emplacement</div>
    <div id="big_spinner" style="display:none"></div>
    <div id='map'></div>
  </div>

  <div class="clear"></div><br />

  <form id='form' onsubmit='displayAddress(); return false;'>

	<div style="float: right;">
		<button class="button gray" onclick="closegooglemap();" style="float: left; width: 80px;">Annuler</button>
		<button class="button green" onclick="displayAddress();" style="clear: both; float: left; width: 80px; margin-top: 5px;">Valider</button>
   	</div>

    <label for='street'>Adresse</label>
    <input type='text' name='street' id='street' class='text street' value='<?= $street ?>' />

    <label for='zip'>Code postal</label>
    <input type='text' name='zip' id='zip' class='text zip' value='<?= $zip ?>' />

    <label for='city'>Ville</label>
    <input type='text' name='city' id='city' class='text city' value='<?= $city ?>' />

    <label for='state'>Etat</label>
    <input type='text' name='state' id='state' class='text state' value='<?= $state ?>' />

    <label for='country'>Pays</label>
    <input type='text' name='country' id='country' class='text country' value='<?= $country ?>' />

    <input type='hidden' name='lat' id='lat' value='<?= $lat ?>' />
    <input type='hidden' name='lng' id='lng' value='<?= $lng ?>' />
    <input type='hidden' name='zoom' id='zoom' value='<?= $zoom ?>' />

  </form>

</div>

<script>
var marker;
var widget = new Maptimize.AddressChooser.Widget(
	{
		onInitialized: function(widget) {
			// Add small map control (zoom and pan)
			widget.getMap().setUIToDefault();

			// Change default icon
			var icon = new GIcon({
				image:            "img/google/orange/star-3.png",
				iconSize:         new GSize(32, 37),
				iconAnchor:       new GPoint(15, 36),
				infoWindowAnchor: new GPoint(9, 2),
				infoShadowAnchor: new GPoint(18, 25),
				shadow:           "img/google/shadow2.png"});
			widget.setIcon(icon);

			// Center map on selected address or on user location
			widget.initMap();
			widget.getMap().setZoom(<?= $zoom ?>);

			// Focus street field
			document.getElementById('street').focus();
		},
		spinner: 'big_spinner'
	}
);

displayAddress = function() {
	if (widget.lat.value != '')
	{
		window.parent.document.getElementById('zoom').value = widget.getMap().getZoom();
		window.parent.document.getElementById('lat').value = widget.lat.value;
		window.parent.document.getElementById('lng').value = widget.lng.value;
	}
	closegooglemap();
}
</script>


</body>
</html>