<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!isset($valid)) $valid = 0;

// On récupère l'id du joueur à supprimer
$item = explode('=', urldecode($pkeys_where));
$id_j = $item[1];

$db = dbc::connect();

if ($valid == 0)
{
    $select = "SELECT COUNT(*) total FROM jb_equipes WHERE nb_joueurs=2 AND (joueurs LIKE '%|".$id_j."' OR joueurs LIKE '".$id_j."|%') AND id_champ=".$sess_context->getRealChampionnatId();
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_equipes = $row['total'];

    $select = "SELECT COUNT(*) total FROM jb_equipes WHERE nb_joueurs > 2 AND (joueurs LIKE '%|".$id_j."' OR joueurs LIKE '".$id_j."|%' OR joueurs LIKE '%|".$id_j."|%') AND id_champ=".$sess_context->getRealChampionnatId();
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $nb_equipes2 = $row['total'];
    
    $menu = new menu("full_access");
    $menu->debut($sess_context->getChampionnatNom());
?>

<div style="text-align: left; width: 400px;">
<B>Impact de la suppression de ce joueur: </B>
<ul>
<? if ($sess_context->isFreeXDisplay()) { ?>
    <li> Nombre d'equipes supprimées: <?= $nb_equipes ?>
<? } ?>
    <li> Nombre d'equipes modifiées: <?= $nb_equipes2 ?>
</ul>
<B>Les statistiques globales du championnat/saisons vont être impactées.</B>
</div>

<div style="width: 460px; margin: 30px 0px 30px 0px;">
<table border=0>
<FORM ACTION=joueurs.php METHOD=POST>
    <tr><td><INPUT TYPE=SUBMIT VALUE="Annuler"></td>
</FORM>
<FORM ACTION=joueurs_supprimer_do.php METHOD=POST>
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
$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
$joueur = $sjs->getJoueur($id_j);
if ($joueur['photo'] != "" && file_exists($joueur['photo'])) unlink($joueur['photo']);

// On détruit les équipes de 2 dont ce joueur fait parti pour les championnats libres seulement
if ($sess_context->isFreeXDisplay())
{
	$delete = "DELETE FROM jb_equipes WHERE nb_joueurs=2 AND (joueurs LIKE '%|".$id_j."' OR joueurs LIKE '".$id_j."|%') AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($delete);
}

// On met à jour les équipes de + de 2 qui contiennent ce joueurs
$req = "SELECT * FROM jb_equipes WHERE nb_joueurs > ".($sess_context->isFreeXDisplay() ? 2 : 0)." AND (joueurs LIKE '%|".$id_j."' OR joueurs LIKE '".$id_j."|%' OR joueurs LIKE '%|".$id_j."|%') AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res))
{
	$tab = explode('|', $row['joueurs']);
	$selection = "";
	foreach($tab as $item)
	{
		if ($item != $id_j)
		{
			$selection .= ($selection == "" ? "" : "|").$item;
		}
	}
	$update = "UPDATE jb_equipes SET joueurs='".$selection."', nb_joueurs=".($row['nb_joueurs']-1)." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$row['id'];
	$res2 = dbc::execSQL($update);
}

// Suppression du joueur
$delete = "DELETE FROM jb_joueurs ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($delete);

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("joueurs.php");

?>
