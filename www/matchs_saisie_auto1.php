<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";
include "ManagerFXList.php";

$db = dbc::connect();

// On récupère le nom des équipes
$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId();
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) $eq[$row['id']] = $row;

// On parcours les matchs à modifier
$req = "SELECT * FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId()." ORDER BY id";
$res = dbc::execSQL($req);

if (mysql_num_rows($res) == 0) ToolBox::do_redirect("matchs.php?errno=2");

function getPlayersName($championnat)
{
	$req = "SELECT id, nom, prenom, pseudo FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysql_fetch_array($res))
		{
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
		}
	}

	mysql_free_result($res);

	return $players_name;
}

function getIdsPlayers($championnat, $equipe, $names)
{
	$ret = array();

	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$championnat." AND id=".$equipe;
	$res = dbc::execSQL($req);

	if ($res)
	{
		$row = mysql_fetch_array($res);
		$items = explode('|', $row['joueurs']);
		foreach($items as $j) array_push($ret, $names[$j]);
	}

	mysql_free_result($res);

	return $ret;
}

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<FORM ACTION=matchs_saisie_auto2.php METHOD=POST>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$players_name = getPlayersName($sess_context->getChampionnatId());

function buildSELECT($name, $val)
{
	$input = "<SELECT NAME=".$name.">";
	for($i=0; $i < 16; $i++) $input .= "<OPTION ".($val == $i ? "SELECTED" : "")."> $i";
	$input .= "</SELECT>";

	return $input;
}

// Collecte des matchs
$tab = array();

while($row = mysql_fetch_array($res))
{
	$eq1 = getIdsPlayers($sess_context->getChampionnatId(), $row['id_equipe1'], $players_name);
	$eq2 = getIdsPlayers($sess_context->getChampionnatId(), $row['id_equipe2'], $players_name);

	$sm = new StatMatch($row['resultat'], $row['nbset']);
	$score = $sm->getScore();

	$input1  = buildSELECT("match_".$row['id']."_score1",  $score[0][0]);
	$input2  = buildSELECT("match_".$row['id']."_score2",  $score[0][1]);
	$input3  = buildSELECT("match_".$row['id']."_score3",  $row['nbset'] >= 2 ? $score[1][0] : 0);
	$input4  = buildSELECT("match_".$row['id']."_score4",  $row['nbset'] >= 2 ? $score[1][1] : 0);
	$input5  = buildSELECT("match_".$row['id']."_score5",  $row['nbset'] >= 3 ? $score[2][0] : 0);
	$input6  = buildSELECT("match_".$row['id']."_score6",  $row['nbset'] >= 3 ? $score[2][1] : 0);
	$input7  = buildSELECT("match_".$row['id']."_score7",  $row['nbset'] >= 4 ? $score[3][0] : 0);
	$input8  = buildSELECT("match_".$row['id']."_score8",  $row['nbset'] >= 4 ? $score[3][1] : 0);
	$input9  = buildSELECT("match_".$row['id']."_score9",  $row['nbset'] == 5 ? $score[4][0] : 0);
	$input10 = buildSELECT("match_".$row['id']."_score10", $row['nbset'] == 5 ? $score[4][1] : 0);

    $tab[] = array($eq[$row['id_equipe1']]['nom'], $input1."/".$input2, $eq[$row['id_equipe2']]['nom']);

	if ($row['nbset'] >= 2)
	    $tab[] = array("", $input3."/".$input4, "");

	if ($row['nbset'] >= 3)
	    $tab[] = array("", $input5."/".$input6, "");

	if ($row['nbset'] >= 4)
	    $tab[] = array("", $input7."/".$input8, "");
		
	if ($row['nbset'] == 5)
	    $tab[] = array("", $input9."/".$input10, "");
}

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("[Matchs] Saisie manuelle", "CENTER");
$fxlist->FXSetColumnsName(array("Equipe 1<BR>(Défenseur/Attaquant)", "Score", "Equipe 2<BR>(Défenseur/Attaquant)"));
$fxlist->FXSetColumnsAlign(array("CENTER", "CENTER", "CENTER"));
$fxlist->FXSetColumnsColor(array("", "#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("", "15%", ""));
$fxlist->FXDisplay();
echo "</TD>";

mysql_free_result($res);

?>

<SCRIPT>
function annuler()
{
	document.forms[0].action='matchs.php';

	return true;
}
</SCRIPT>

<TR><TD ALIGN=CENTER><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Valider"></INPUT></TD>
</TABLE></TD>


</TABLE>

</FORM>

<? $menu->end(); ?>