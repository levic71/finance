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
foreach($themes as $item) $i++;

?>

<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8 />
<title>Flux Slider Demo</title>
<!--[if lte IE 8]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<link rel="stylesheet" href="slider/css/demo.css" type="text/css" media="screen" title="no title" charset="utf-8">

<? if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== FALSE ) { ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<? } else { ?>
<script src="slider/js/zepto/zepto.js" type="text/javascript" charset="utf-8"></script>
<? } ?>

<script src="slider/js/flux.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
$(function(){
	if(false && !flux.browser.supportsTransitions)
		alert("Flux Slider requires a browser that supports CSS3 transitions");

	window.f = new flux.slider('#slider', {
		pagination: true
	});
});
</script>
</head>
<body>

<?

if ($i > 0) {

$xml_file = $sas->getXMLFilename();
$xml_cache = JKCache::getCache($xml_file, -1, "_FLUX_XML_ALBUM_");

if (count($photos) == 0) echo "Aucune photo";
else { ?>

<section class="container">
<div id="slider">

<?
foreach($photos as $item)
	echo "<img src=\"".$item['photo']."\" alt=\"\" />";
?>

</div>
</section>

<?
}

}
?>

</body>
</html>
