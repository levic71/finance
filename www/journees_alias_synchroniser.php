<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Récupéraction de l'id de la journee
$id_journee = $id;

$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $id_journee);
$j  = $sjs->getJournee();
$aj = $sjs->getAllAliasJournee();

// Mise à jour de la journée mère
if (count($aj) > 0)
{
	$match_a_retirer = array();
	$match_a_reaffecter = "";

	foreach($aj as $x)
	{
		$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), $x['id']);
		$j = $sjs2->getJournee();

		$tmp = explode('|', $j['id_matchs']);
		if (count($tmp) > 1)
		{
			$lst = explode(',', $tmp[1]);
			if (count($lst) > 0)
				foreach($lst as $item)
				{
					if ($item != "")
						$match_a_reaffecter .= ($match_a_reaffecter == "" ? "" : ",").$item;
				}
		}
	}
	if ($match_a_reaffecter != "") $match_a_reaffecter = "-|".$match_a_reaffecter;

	$update = "UPDATE jb_journees SET id_matchs='".$match_a_reaffecter."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$id_journee;
	$res = dbc::execSQL($update);
}

mysql_close ($db);

ToolBox::do_redirect("matchs_tournoi.php?pkeys_where_jb_journee+id=".$id);

?>
