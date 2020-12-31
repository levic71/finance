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

<link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:700,300,300italic' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="widgets/jmpress/demo.css" />
<link rel="stylesheet" type="text/css" href="widgets/jmpress/style.css" />
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="widgets/jmpress/style_ie.css" />
<![endif]-->
<!-- jQuery -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="widgets/jmpress/jmpress.min.js"></script>
<script type="text/javascript" src="widgets/jmpress/jquery.jmslideshow.js"></script>
<script type="text/javascript" src="widgets/jmpress/modernizr.custom.48780.js"></script>
<noscript>
	<style>
	.step {
		width: 100%;
		position: relative;
	}
	.step:not(.active) {
		opacity: 1;
		filter: alpha(opacity=99);
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(opacity=99)";
	}
	.step:not(.active) a.jms-link{
		opacity: 1;
		margin-top: 40px;
	}
	</style>
</noscript>

</head>
<body>

<? if ($i > 0) {
	$xml_file = $sas->getXMLFilename();
	$xml_cache = JKCache::getCache($xml_file, -1, "_FLUX_XML_ALBUM_");

	if (count($photos) == 0) echo "Aucune photo";
	else { ?>

<br />

<div class="container">
<section id="jms-slideshow" class="jms-slideshow">
<?
$j = 0;
foreach($photos as $item) {
	$i = ($j%5)+1;
?>
	<div class="step" data-color="color-<?= $i ?>" <?= $i==1 ? 'data-y="500" data-scale="0.4" data-rotate-x="30"' : ($i==2 ? 'data-x="2000" data-z="3000" data-rotate="170"' : ($i==3 ? 'data-x="3000"' : ($i==4 ? 'data-x="4500" data-z="1000" data-rotate-y="45"' : ''))) ?>>
		<div class="jms-content">
			<h3><?= "" ?></h3>
			<p><?= $item['commentaire'] ?></p>
			<a class="jms-link" href="#">Read more</a>
		</div>
		<img src="<?= $item['photo'] ?>" />
	</div>
<?
	$j++;
}
?>
</section>
</div>

<script type="text/javascript">
$(function() { $( '#jms-slideshow' ).jmslideshow(); });
</script>

<?
	}
}
?>

</body>
</html>