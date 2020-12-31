<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "journeebuilder.php";
include "ManagerFXList.php";

$db = dbc::connect();

// Si on vient d'un sous menu (et non de journees_ajouter.php)
if (!isset($selection))
{
    $select = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
	$selection = $row['joueurs'];
}

if (!isset($max_matchs)) $max_matchs = 12;

// Si on revient sur cette page une 2ième fois (recalcule des matchs), il ne faut pas insérer de nouveau la journée
if (!(isset($again) && $again == 1))
{
    $new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

	$js = "";
	$eq = "";

	// $type_participation == 0 : Sélection des joueurs qui sont présents
	// $type_participation == 1 : Sélection des équipes qui sont présentes

	if ($type_participant == 0) $js = $selection;

	// Dans le cadre de la gestion par équipe, on va quand même initialiser les joueurs de la journée
	if ($type_participant == 1)
	{
		$j_selected = array();
		$eq = $selection;
		$select = "SELECT * FROM jb_equipes WHERE id IN (".$selection.")";
	    $res = dbc::execSQL($select);
	    while($row = mysql_fetch_array($res))
		{
			if ($row['nb_joueurs'] >= 2)
			{
				$item = explode('|', $row['joueurs']);
				foreach($item as $j) $j_selected[$j] = $j;
			}
		}
		foreach($j_selected as $j) $js .= ($js == "" ? "" : ",").$j;
	}

    $insert = "INSERT INTO jb_journees (id_champ, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", '".$nom.":".$nom_journee."', '".$new_date."', '".$heure."', ".$duree.", '".$js."', '".$eq."', ".$type_participant.");";
    $res = dbc::execSQL($insert);
    $select = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND date='".$new_date."' ORDER BY id DESC;";
    $res = dbc::execSQL($select);
    $row = mysql_fetch_array($res);
    $sess_context->setJourneeId($row['id']);

	JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

    if (isset($create_auto) && $create_auto == 0) ToolBox::do_redirect("matchs.php");
}

$menu = new menu("full_access");
$menu -> debut($sess_context->getChampionnatNom());

?>

<FORM ACTION=journees_ajouter_do2.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=again     VALUE=1>
<INPUT TYPE=HIDDEN NAME=selection VALUE=<?= $selection ?>>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 >

<TR><TD ALIGN=CENTER>

<?php

// Création des matchs de la journée
$jb = new JourneeBuilder($sess_context->getRealChampionnatId(), ereg_replace(",$", "", $selection));

if (isset($max_matchs) && $max_matchs != "") $jb -> setMaxMatchs($max_matchs);

$matchs_affected     = $jb -> getMatchs();
$match_par_joueur    = $jb -> getMatchsParJoueur();
$match_par_attaquant = $jb -> getMatchsParAttaquant();
$match_par_defenseur = $jb -> getMatchsParDefenseur();
// //////////////////////////////////////////////////////////////////////
// Tableau pour les stats
?>
<TR><TD ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 BACKGROUND=../images/table_fond.jpg>
<?php

echo "<TR>";
HTMLTable::printCellWithColSpan("<FONT CLASS=white SIZE=5 COLOR=white> [Journée] Matchs proposés </FONT>", "#4863A0", "100%", "CENTER", _CELLBORDER_ALL_, 9);

echo "<TR>";
HTMLTable::printCell("Insérer",     "#BCC5EA", "",   "CENTER", _CELLBORDER_U_);
HTMLTable::printCell("Défenseur",   "#BCC5EA", "",   "RIGHT",  _CELLBORDER_BOTTOM_);
HTMLTable::printCell("-",           "#BCC5EA", "1%", "CENTER", _CELLBORDER_BOTTOM_);
HTMLTable::printCell("Attaquant",   "#BCC5EA", "",   "LEFT",   _CELLBORDER_SE_);
HTMLTable::printCell("/",           "#BCC5EA", "",   "CENTER", _CELLBORDER_SE_);
HTMLTable::printCell("Défenseur",   "#BCC5EA", "",   "RIGHT",  _CELLBORDER_BOTTOM_);
HTMLTable::printCell("-",           "#BCC5EA", "1%", "CENTER", _CELLBORDER_BOTTOM_);
HTMLTable::printCell("Attaquant",   "#BCC5EA", "",   "LEFT",   _CELLBORDER_BOTTOM_);
HTMLTable::printCell("Joueurs Out", "#BCC5EA", "",   "CENTER", _CELLBORDER_U_);

$ind = 0;
foreach($matchs_affected as $match) {
    $joueurs = $jb -> getPseudosInMatch($match);
    $coche = "<INPUT TYPE=CHECKBOX NAME=match_sel" . ($ind++) . " VALUE=\"" . $match . "\" CHECKED></INPUT>";

    echo "<TR onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";
    HTMLTable::printCell($coche,      "", "",   "CENTER", _CELLBORDER_U_);
    HTMLTable::printCell($joueurs[0], "", "",   "RIGHT",  _CELLBORDER_BOTTOM_);
    HTMLTable::printCell("-",         "", "1%", "CENTER", _CELLBORDER_BOTTOM_);
    HTMLTable::printCell($joueurs[1], "", "",   "LEFT",   _CELLBORDER_SE_);
    HTMLTable::printCell("/",         "", "",   "CENTER", _CELLBORDER_SE_);
    HTMLTable::printCell($joueurs[2], "", "",   "RIGHT",  _CELLBORDER_BOTTOM_);
    HTMLTable::printCell("-",         "", "1%", "CENTER", _CELLBORDER_BOTTOM_);
    HTMLTable::printCell($joueurs[3], "", "",   "LEFT",   _CELLBORDER_BOTTOM_);
    HTMLTable::printCell($jb->getJoueursOut($match), "", "", "CENTER", _CELLBORDER_U_);
}

echo "<TR>";
HTMLTable::printCellWithColSpan("Nombre de matchs = <INPUT TYPE=TEXT NAME=max_matchs VALUE=".$max_matchs." SIZE=2 MAXLENGTH=2>", "#BCC5EA", "", "RIGHT", _CELLBORDER_SW_, 4);
HTMLTable::printCellWithColSpan("Temps de jeu estimé = " . $jb -> getEstimedTime() . " &nbsp;&nbsp; Indice satisfaction = " . $jb -> getIndiceSatisfaction(), "#BCC5EA", "", "LEFT", _CELLBORDER_SE_, 5);

reset($match_par_joueur);
while (list($j, $nb) = each($match_par_joueur)) {
    echo "<TR>";
    HTMLTable::printCellWithColSpan($jb -> getNomJoueur($j) . " => ", "#BCC5EA", "", "RIGHT", _CELLBORDER_SW_, 4);
    HTMLTable::printCellWithColSpan($nb . " matchs [ Défenseur = " . $match_par_defenseur[$j] . ", Attaquant = " . $match_par_attaquant[$j] . " ]", "#BCC5EA", "", "LEFT", _CELLBORDER_SE_, 5);
}

?>
</TABLE>

<SCRIPT>
function annuler()
{
	document.forms[0].action='calendar.php';

	return true;
}
function recalculer()
{
	document.forms[0].action='journees_ajouter_do.php';

	return true;
}
</SCRIPT>
<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler"    onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Recalculer" onclick="return recalculer();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Ajouter"></INPUT></TD>
</TABLE></TD>

</TD>
</TABLE>
</FORM>

<? $menu -> end(); ?>
