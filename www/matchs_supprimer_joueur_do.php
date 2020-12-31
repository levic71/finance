<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

// On récupère les infos de la journée
$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);
$joueurs = $journee['joueurs'];

for($i=0; $i<30; $i++)
{
	$j = ToolBox::get_global("joueur".$i);
	if ($j != "")
	{
		// On enlève le joueur du champs des joueurs qui ont participé à la journée
		$joueurs = str_replace($j.",", "", $joueurs);

		// On récupère les équipes du joueurs
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId()." AND joueurs LIKE '%".$j."%';";
		$res = dbc::execSQL($select);
		$equipes = "";
		while($e = mysql_fetch_array($res))
		{
			$items = explode('|', $e['joueurs']);
			if (ToolBox::findInArray($j, $tab_jj))
				$equipes .= ($equipes == "" ? "" : ",").$e['id'];
		}

		// On supprime les matchs joués par ce joueur
		$delete = "DELETE FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId()." AND (id_equipe1 IN (".$equipes.") OR id_equipe2 IN (".$equipes."));";
		$res = dbc::execSQL($delete);
	}
}

$update = "UPDATE jb_journees set joueurs='".$joueurs."' WHERE id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

mysql_close ($db);

?>

<SCRIPT>
window.opener.document.forms[0].action='matchs.php';
window.opener.document.forms[0].submit();
window.close();
</SCRIPT>
