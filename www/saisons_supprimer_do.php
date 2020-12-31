<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!isset($valid)) $valid = 0;

$id_saison = str_replace(" WHERE id=", "", urldecode($pkeys_where));

$db = dbc::connect();

if ($valid == 0)
{
	$select = "SELECT COUNT(*) total FROM jb_journees WHERE id_champ=".$id_saison;
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_journees = $row['total'];

	$select = "SELECT COUNT(*) total FROM jb_matchs WHERE id_champ=".$id_saison;
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_matchs = $row['total'];
    
    $menu = new menu("full_access");
    $menu->debut($sess_context->getChampionnatNom());
?>

<div style="text-align: left; width: 400px;">
<B>Impact de la suppression de cette saison: </B>
<ul>
    <li> Nombre de journées supprimées: <?= $nb_journees ?>
    <li> Nombre de matchs supprimés: <?= $nb_matchs ?>
</ul>
<B>Les statistiques globales du championnat/saisons vont être impactées.</B>
</div>

<div style="width: 460px; margin: 30px 0px 30px 0px;">
<table border=0>
<FORM ACTION=saisons.php METHOD=POST>
    <tr><td><INPUT TYPE=SUBMIT VALUE="Annuler"></td>
</FORM>
<FORM ACTION=saisons_supprimer_do.php METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=pkeys_where VALUE=<?= $pkeys_where?>>
	<INPUT TYPE=HIDDEN NAME=valid VALUE=1>
    <td><INPUT TYPE=SUBMIT VALUE="Confirmer la suppression"></td>
</FORM>
</table>
</div>

<?

    $menu->end();
    
    exit(0);
}

$select = "SELECT count(*) total FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($select);
$row = mysql_fetch_array($res);
if ($row['total'] == 1)
{
	ToolBox::do_redirect("saisons.php?delete=no");
	exit(0);
}


$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $id_saison);
$saison = $sss->getSaison();

$delete = "DELETE FROM jb_matchs WHERE id_champ=".$id_saison;
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_journees WHERE id_champ=".$id_saison;
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_classement_poules WHERE id_champ=".$id_saison;
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_saisons ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($delete);

if ($saison['active'] == 1)
{
	$select = "SELECT MAX(id) max FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);
	$update = "UPDATE jb_saisons SET active=1 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$row['max'];
	$res = dbc::execSQL($update);
}

$sess_context->setSaisons();

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("saisons.php");

?>
