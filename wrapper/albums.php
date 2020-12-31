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

?>

<div class="dashcounter" id="albums">

<?

$i = 0;
foreach($themes as $item)
{
?>

<div class="box"><button id="b5" class="button <?= $id_theme == $item['id'] ? "blue" : "gray" ?>" onclick="mm({action: 'photos', idg: <?= $item['id'] ?>});"><img src="img/picture_icon&32.png" /><div><?= $item['nb_photos'] ?></div><br /><div class="txt"><?= $item['nom'] ?></div></button></div>

<?
	$i++;
}

?>

</div>

<?

$url = "albums_display.php";
if (isset($_SERVER['HTTP_USER_AGENT']) &&  (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) $url = "albums_display.old.php";

?>

<div>

<? if ($i == 0) echo "<p>Aucun album</p>"; ?>

<iframe src="<?= $url ?>?id_theme=<?= $id_theme ?>" height="740" width="700" frameborder="0" border="0" framespacing="0" scrolling="no"></iframe>

<? if ($sess_context->isAdmin()) { ?>
<ul class="sidebar">
	<li id="sb_addtheme"><a href="#" onclick="go({action: 'photos', id:'main', url:'edit_galeries.php'});" onmouseover="showtip('sb_addtheme');" class="ToolText"><span>Ajouter une galerie</span></a></li>
<? if ($i > 0) { ?>
	<li><a href="#" onclick="go({action: 'photos', id:'main', url:'edit_galeries.php?idg=<?= $id_theme ?>'});" id="sb_upd" class="ToolText" onmouseover="showtip('sb_upd');"><span>Modifier la galerie</span></a></li>
	<li><a href="#" onclick="go({action: 'photos', id:'main', url:'edit_galeries_do.php?del=0&idg=<?= $id_theme ?>', confirmdel:'1'});" id="sb_del" class="ToolText" onmouseover="showtip('sb_del');"><span>Supprimer la galerie</span></a></li>
<? } ?>
</ul>
<? } ?>

</div>