<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

if (!isset($_REQUEST['id_theme']) || $_REQUEST['id_theme'] == 0)
{
	$tmp = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), -1);
	$id_theme = $tmp->getFirstTheme();
}
else $id_theme = $_REQUEST['id_theme'];

$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$themes = $sas->getAllThemes();
$photos = $sas->getPhotos();

$i = 0;
foreach($themes as $item)
	$i++;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<link rel="stylesheet" type="text/css" href="css/slicebox.css" />
<link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow&v1' rel='stylesheet' type='text/css' />
<link href='http://fonts.googleapis.com/css?family=Volkhov:400italic,700' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="js/modernizr.custom.13303.js"></script>

</head>
<body>

<div>

<?

if ($i > 0) {
	$xml_file = $sas->getXMLFilename();
	$xml_cache = JKCache::getCache($xml_file, -1, "_FLUX_XML_ALBUM_");

	if (count($photos) == 0) echo "Aucune photo";
	else {
?>

<div id="sb-slider" class="sb-slider">
<?
foreach($photos as $item)
	echo "<img src=\"".$item['photo']."\" title=\"".$item['commentaire']."\" />";
?>
</div>



<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.slicebox.min.js"></script>
<script type="text/javascript">
	$(function() {

		$('#sb-slider').slicebox({
			slicesCount			: 9,
			disperseFactor		: 50,
			sequentialRotation	: true,
			sequentialFactor	: 20
		});

		if( !Modernizr.csstransforms3d ) {
			$('#sb-note').show();

			$('body').append(
				$('script').attr( 'type', 'text/javascript' ).attr( 'src', 'js/jquery.easing.1.3.js' )
			);
		}
	});
</script>


<?
	}
}
?>

</div>


</body>
</html>


