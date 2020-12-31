<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

// On récupère les valeurs directement de HTTP_POST_VARS
while(list($cle, $val) = each($HTTP_POST_VARS))
{
	if (strstr($cle, "match"))
	{
		$exp = explode("_", $cle);
		$matchs[$exp[1]] = $exp[1];
		if ($exp[2] == "score1")  $score1[$exp[1]]  = $val;
		if ($exp[2] == "score2")  $score2[$exp[1]]  = $val;
		if ($exp[2] == "score3")  $score3[$exp[1]]  = $val;
		if ($exp[2] == "score4")  $score4[$exp[1]]  = $val;
		if ($exp[2] == "score5")  $score5[$exp[1]]  = $val;
		if ($exp[2] == "score6")  $score6[$exp[1]]  = $val;
		if ($exp[2] == "score7")  $score7[$exp[1]]  = $val;
		if ($exp[2] == "score8")  $score8[$exp[1]]  = $val;
		if ($exp[2] == "score9")  $score9[$exp[1]]  = $val;
		if ($exp[2] == "score10") $score10[$exp[1]] = $val;
	}
}

foreach($matchs as $match)
{
	$resultat = $score1[$match]."/".$score2[$match];
	if (isset($score3[$match])) $resultat .= $score3[$match]."/".$score4[$match];
	if (isset($score5[$match])) $resultat .= $score5[$match]."/".$score6[$match];
	if (isset($score7[$match])) $resultat .= $score7[$match]."/".$score8[$match];
	if (isset($score9[$match])) $resultat .= $score9[$match]."/".$score10[$match];
	
	$fanny = (!isset($score3[$match]) && (($score1[$match] == 0 && $score2[$match] > 0) || ($score1[$match] > 0 && $score2[$match] == 0))) ? 1 : 0;
	
	$update = "UPDATE jb_matchs SET resultat='".$resultat."', fanny=".$fanny." WHERE id=".$match;
	$res = dbc::execSQL($update);
}

$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

ToolBox::do_redirect("matchs.php");

?>