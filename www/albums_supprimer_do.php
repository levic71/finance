<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// On récupère l'id de l'équipe à supprimer
$item = explode('=', urldecode($pkeys_where));
$id_photo = $item[1];

// Suppression de la photo
$sas = new SQLAlbumsServices($sess_context->getRealChampionnatId());
$photo = $sas->getPhoto($id_photo);
if ($photo['photo'] != "" && file_exists($photo['photo'])) unlink($photo['photo']);
if ($photo['photo'] != "" && file_exists(str_replace('uploads', 'thumbs', $photo['photo']))) unlink(str_replace('uploads', 'thumbs', $photo['photo']));

// Suppression de la photo
$delete = "DELETE FROM jb_albums ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($delete);

// Décrémentation du compteur de photos dans le theme parent
$sats = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$theme = $sats->getAlbumTheme();

// On force la recréation du XML
$xml_file = $sats->getXMLFilename();
JKCache::delCache($xml_file, "_FLUX_XML_ALBUM_");

$update = "UPDATE jb_albums_themes SET last_modif=SYSDATE(), nb_photos=".($theme['nb_photos']-1)." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_theme;
$res = dbc::execSql($update);

mysql_close ($db);

ToolBox::do_redirect("albums.php?id_theme=".$id_theme);

?>
