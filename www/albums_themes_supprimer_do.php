<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// On récupère l'id du theme
$item = explode('=', urldecode($pkeys_where));
$id_theme = $item[1];

// Suppression des photos du theme
$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$photos = $sas->getPhotos();
foreach($photos as $p)
{
	if ($p['photo'] != ""  && file_exists($p['photo'])) unlink($p['photo']);
	$delete = "DELETE FROM jb_albums WHERE id=".$p['id']." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($delete);
}

// Suppression du theme
$delete = "DELETE FROM jb_albums_themes ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($delete);

mysql_close ($db);

ToolBox::do_redirect("albums_themes.php");

?>