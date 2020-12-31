<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$nom         = Wrapper::getRequest('nom',         '');
$photo       = Wrapper::getRequest('photo',       '');
$commentaire = Wrapper::getRequest('commentaire', '');
$nb_joueurs  = Wrapper::getRequest('nb_joueurs',  0);
$joueurs     = Wrapper::getRequest('joueurs',     '');
$capitaine   = Wrapper::getRequest('capitaine',   0);
$adjoint     = Wrapper::getRequest('adjoint',     0);
$del         = Wrapper::getRequest('del',         0);
$idt         = Wrapper::getRequest('idt',         0);
$upd         = Wrapper::getRequest('upd',         0);

if (($upd == 1 || $del == 1) && !is_numeric($idt)) ToolBox::do_redirect("grid.php");

if ($del == 1 && $idt > 0)
{
	$err = true;
	$sql = "SELECT count(*) total FROM jb_equipes WHERE id=".$idt." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	if ($row['total'] == 1) {

		// Récupération des infos de l'ancienne image pour la supprimer
		$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
		$equipe = $ses->getEquipe($idt);
		if ($equipe['photo'] != "" && file_exists($equipe['photo'])) unlink($equipe['photo']);

		// Suppression de l'équipe
		$delete = "DELETE FROM jb_equipes WHERE id=".$idt." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);

		// Suppression des matchs jouer par l'équipe (cohérence pour les stats)
		$delete = "DELETE FROM jb_matchs WHERE(id_equipe1=".$idt." OR id_equipe2=".$idt.")";
		$res = dbc::execSQL($delete);

		// Pour les tournois et les championnats, supprimer l'équipe à la saison en cours
		if (!$sess_context->isFreeXDisplay())
		{
			$select = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
			$res = dbc::execSQL($select);
			if ($row = mysqli_fetch_array($res))
			{
				$tab = explode(",", $row['equipes']);
				$equipes = "";
				foreach($tab as $elt)
					if ($elt != $idt) $equipes .= ($equipes == "" ? "" : ",").$elt;
				$update  = "UPDATE jb_saisons SET equipes='".SQLServices::cleanIN($equipes)."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
				$res = dbc::execSQL($update);
			}
		}
		$err = false;
	}
?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'teams'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Equipe <?= $err ? "non" : "" ?> supprimée' });</script><?
	exit(0);
}



function getPlayersNameForInsert($championnat, $regulier)
{
	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat.($regulier == 1 ? " AND presence=1;" : "");
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysqli_fetch_array($res))
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
	}

	mysqli_free_result($res);

	return $players_name;
}


function getPlayersNameForUpdate($championnat, &$last_id_insert)
{
    global $nom, $prenom, $pseudo, $presence;

	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysqli_fetch_array($res))
		{
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
            if ($row['nom'] == $nom && $row['prenom'] == $prenom && $row['pseudo'] == $pseudo && $row['presence'] == $presence)
               $last_id_insert = $row['id'];
		}
	}

	mysqli_free_result($res);

	return $players_name;
}


$modifier = $upd == 1 ? true : false;


if ($modifier)
{
	// Vérification nom équipe unique
	$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND nom='".$nom."' AND id <> ".$idt;
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Nom d'équipe déjà existante déjà existante"; exit(0); }

	if ($selection == "")
		$nb_joueurs = 0;
	else
	{
		$tab = explode('|', $selection);
		$nb_joueurs = count($tab);
	}

	// Modification du joueur
	$update = "UPDATE jb_equipes SET capitaine=".$capitaine.", adjoint=".$adjoint.", nom='".$nom."', joueurs='".$selection."', nb_joueurs=".$nb_joueurs.", photo='".$photo."', commentaire='".$commentaire."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$idt;
	$res = dbc::execSQL($update);
}
else
{
	// Vérification nom équipe unique
	$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND nom LIKE '%".$nom."%'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Nom équipe existante"; exit(0); }

	// Vérification équipe pas déjà créée
	if ($selection != "")
	{
		$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND joueurs='".$selection."'";
		$res = dbc::execSQL($select);
		$row = mysqli_fetch_array($res);
		if ($row['total'] > 0) { echo "-1||Equipe avec ces mêmes joueurs déjà existante"; exit(0); }
	}

	$insert = "INSERT INTO jb_equipes (id_champ, capitaine, adjoint, nom, photo, commentaire, joueurs, nb_joueurs) VALUES (".$sess_context->getRealChampionnatId().", ".$capitaine.", ".$adjoint.", '".$nom."', '".$photo."', '".$commentaire."', '".$selection."', ".$nb_selection.");";
	$res = dbc::execSQL($insert);

	// Pour les tournois et les championnats, ajouter l'équipe à la saison en cours
	if ($sess_context->getChampionnatType() != _TYPE_LIBRE_)
	{
		$select = "SELECT * FROM jb_equipes WHERE nom='".$nom."' AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($select);
		if ($eq = mysqli_fetch_array($res))
		{
			$select = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
			$res = dbc::execSQL($select);
			if ($row = mysqli_fetch_array($res))
			{
				$equipes = $row['equipes'].($row['equipes'] == "" ? "" : ",").$eq['id'];
				$update  = "UPDATE jb_saisons SET equipes='".SQLServices::cleanIN($equipes)."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
				$res = dbc::execSQL($update);
			}
		}
	}
}

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'teams'}); $cMsg({ msg: 'Equipe <?= $modifier ? "modifiée" : "ajoutée" ?>' });</script>