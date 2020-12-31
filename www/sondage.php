<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

if (!isset($id_sondage)) ToolBox::do_redirect("home.php");

if (!isset($sondage_sessionid))
{
	$sondage_sessionid = Toolbox::sessionId();
	setcookie("sondage_sessionid", $sondage_sessionid, time()+(3600*24*30*6));
}

$filename = "../cache/sondage_".$id_sondage.".php";

Toolbox::trackUser(isset($row['championnat_id']) ? $row['championnat_id'] : 0, _TRACK_SONDAGE_);

$rep_sondage = array();
@include $filename;

$fichier = fopen($filename, "a");
if (flock($fichier, LOCK_EX))
{
	// On ne vote pas 2 fois mais on trace quand pour les stats !!!!
	if (!isset($rep_sondage[$sondage_sessionid]))
		$flux = "<? \$rep_sondage['".$sondage_sessionid."'] = ".$reponse1."; ?>\n";
	else
		$flux = "<? \$rep_sondage_tentative['".$sondage_sessionid."'] = ".$reponse1."; ?>\n";
	fputs($fichier, $flux);

	$flux = "<? \$rep_sondage_date['".$sondage_sessionid."'] = \"".date("Y-m-d")."\"; ?>\n";
	fputs($fichier, $flux);

	flock($fichier, LOCK_UN);
}
fclose($fichier);

JKCache::delCache("../cache/sondage_".$id_sondage.".txt", "_FLUX_SONDAGE_");

ToolBox::do_redirect("../www/home.php?display_sondage=".$id_sondage.(isset($rep_sondage[$sondage_sessionid]) ? "&once_sondage=1" : ""));

?>
