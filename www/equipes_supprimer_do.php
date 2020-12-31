<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!isset($valid)) $valid = 0;

// On récupère l'id de l'équipe à supprimer
$item = explode('=', urldecode($pkeys_where));
$id_e = $item[1];

$db = dbc::connect();

if ($valid == 0)
{
	$select = "SELECT COUNT(*) total FROM jb_equipes ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_equipes = $row['total'];

	$select = "SELECT COUNT(*) total FROM jb_matchs WHERE(id_equipe1=".$id_e." OR id_equipe2=".$id_e.")";
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_matchs = $row['total'];
    
    $menu = new menu("full_access");
    $menu->debut($sess_context->getChampionnatNom());
?>

<div style="text-align: left; width: 400px;">
<B>Impact de la suppression de cette équipe: </B>
<ul>
    <li> Nombre d'equipes supprimées: <?= $nb_equipes ?>
    <li> Nombre de matchs supprimés: <?= $nb_matchs ?>
</ul>
<B>Les statistiques globales du championnat/saisons vont être impactées.</B>
</div>

<div style="width: 460px; margin: 30px 0px 30px 0px;">
<table border=0>
<FORM ACTION=equipes.php METHOD=POST>
    <tr><td><INPUT TYPE=SUBMIT VALUE="Annuler"></td>
</FORM>
<FORM ACTION=equipes_supprimer_do.php METHOD=POST>
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

// Récupération des infos de l'ancienne image pour la supprimer
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$equipe = $ses->getEquipe($id_e);
if ($equipe['photo'] != "" && file_exists($equipe['photo'])) unlink($equipe['photo']);

// Suppression de l'équipe
$delete = "DELETE FROM jb_equipes ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($delete);

// Suppression des matchs jouer par l'équipe (cohérence pour les stats)
$delete = "DELETE FROM jb_matchs WHERE(id_equipe1=".$id_e." OR id_equipe2=".$id_e.")";
$res = dbc::execSQL($delete);

// On récupère l'id du joueur à supprimer
$item = explode('=', urldecode($pkeys_where));
$id_e = $item[1];

// Pour les tournois et les championnats, supprimer l'équipe à la saison en cours
if (!$sess_context->isFreeXDisplay())
{
   	$select = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
	$res = dbc::execSQL($select);
	if ($row = mysql_fetch_array($res))
   	{
  		$tab = explode(",", $row['equipes']);
  		$equipes = "";
  		foreach($tab as $elt)
		  	if ($elt != $id_e) $equipes .= ($equipes == "" ? "" : ",").$elt;
		$update  = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
		$res = dbc::execSQL($update);
	}
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("equipes.php");

?>
