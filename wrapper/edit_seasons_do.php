<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$nom         = Wrapper::getRequest('nom',           '');
$active      = Wrapper::getRequest('active',        '1');
$selection   = Wrapper::getRequest('selecteditems', '');
$del         = Wrapper::getRequest('del',           0);
$ids         = Wrapper::getRequest('ids',           0);
$upd         = Wrapper::getRequest('upd',           0);

if (($upd == 1 || $del == 1) && !is_numeric($ids)) ToolBox::do_redirect("grid.php");

if ($del == 1 && $ids > 0)
{
	$err = true;
	$sql = "SELECT count(*) total FROM jb_saisons WHERE id=".$ids." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	if ($row['total'] == 1) {

		$select = "SELECT count(*) total FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($select);
		$row = mysqli_fetch_array($res);
		if ($row['total'] == 1)
		{
			ToolBox::do_redirect("saisons.php?delete=no");
			exit(0);
		}

		$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $ids);
		$saison = $sss->getSaison();

		$delete = "DELETE FROM jb_matchs WHERE id_champ=".$ids;
		$res = dbc::execSQL($delete);

		$delete = "DELETE FROM jb_journees WHERE id_champ=".$ids;
		$res = dbc::execSQL($delete);

		$delete = "DELETE FROM jb_classement_poules WHERE id_champ=".$ids;
		$res = dbc::execSQL($delete);

		$delete = "DELETE FROM jb_saisons WHERE id = ".$ids." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);

		if ($saison['active'] == 1)
		{
			$select = "SELECT MAX(id) max FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
			$res = dbc::execSQL($select);
			$row = mysqli_fetch_array($res);
			$update = "UPDATE jb_saisons SET active=1 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$row['max'];
			$res = dbc::execSQL($update);
		}

		$sess_context->setSaisons();

		$err = false;
	}
?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'seasons'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Saison <?= $err ? "non" : "" ?> supprimée' });</script><?
	exit(0);
}

$modifier = $upd == 1 ? true : false;


$errno = 0;
if ($modifier)
{
	// Changement status ancienne saison active
	if ($active == 1)
	{
		$update = "UPDATE jb_saisons SET active=0 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND active=1;";
		$res = dbc::execSQL($update);
		$sess_context->setSaisonId($ids);
		$sess_context->setSaisonNom($nom);
	}
	else
	{
		$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $ids);
		$saison = $sss->getSaison();
		if ($saison['active'] == 1) { ?>-1||Désactivation impossible !<? exit(0); }
	}

	// Modification de la saison
	if ($sess_context->isFreeXDisplay())
	{
		$update = "UPDATE jb_saisons SET nom='".$nom."', active=".$active.", joueurs='".SQLServices::cleanIN($selection)."', equipes='' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$ids;
		$res = dbc::execSQL($update);
	}
	else
	{
		$joueurs = "";
		if ($selection != "")
		{
			$req = "SELECT * FROM jb_equipes WHERE ID IN (".SQLServices::cleanIN($selection).")";
			$res = dbc::execSQL($req);
			while($row = mysqli_fetch_array($res))
				$joueurs .= ($joueurs == "" ? "" : ",").$row['joueurs'];
		}

		$update = "UPDATE jb_saisons SET nom='".$nom."', active=".$active.", joueurs='".SQLServices::cleanIN(str_replace('|', ',', $joueurs))."', equipes='".SQLServices::cleanIN($selection)."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$ids;
		$res = dbc::execSQL($update);
	}

	$sess_context->setSaisons();
}
else
{
	// Changement status ancienne saison active
	if ($active == 1)
	{
		$update = "UPDATE jb_saisons SET active=0 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND active=1;";
		$res = dbc::execSQL($update);
	}

	if ($sess_context->isFreeXDisplay())
	{
		$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".date("Y")."-".date("m")."-".date("d")."', '".$active."', '".$selection."', '');";
		$res = dbc::execSQL($insert);
	}
	else
	{
		$joueurs = "";
		if ($selection != "")
		{
			$req = "SELECT * FROM jb_equipes WHERE ID IN (".SQLServices::cleanIN($selection).")";
			$res = dbc::execSQL($req);
			while($row = mysqli_fetch_array($res))
			{
				if ($row['nb_joueurs'] > 0)
					$joueurs .= ($joueurs == "" ? "" : ",").$row['joueurs'];
			}
		}

		$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".date("Y")."-".date("m")."-".date("d")."', '".$active."', '".str_replace('|', ',', $joueurs)."', '".$selection."');";
		$res = dbc::execSQL($insert);
	}

	if ($active == 1)
	{
		$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
		$saison = $scs->getSaisonActive();
		$sess_context->setSaisonId($saison['id']);
		$sess_context->setSaisonNom($saison['nom']);
		$sess_context->setSaisons();
	}
}

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'seasons'}); $cMsg({ msg: 'Saison <?= $modifier ? "modifiée" : "ajoutée" ?>' });</script>