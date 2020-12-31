<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$slide_view_mode = isset($options_type_matchs) && $options_type_matchs == "SLIDE" ? true : false;

// On récupère le pkeys_where de la page de journee pour avoir l'id de la journee
$pkeys  = ToolBox::get_global("pkeys_where_jb_journees");
if ($pkeys != "") $sess_context->setJourneeId(str_replace(" WHERE id=", "", $pkeys));

$db = dbc::connect();

// Si on vient de page suivante/précédente, alors on recherche la journée à afficher
if ((isset($journee_prev) && $journee_prev == 1) || (isset($journee_next) && $journee_next == 1))
{
    $select = "select id, virtuelle from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date ASC";
    $res = dbc::execSQL($select);
    while($row = mysql_fetch_array($res)) $id_journee[] = $row;

    $index_selected = -1;
    if (count($id_journee) > 0)
    {
        while(list($cle, $valeur) = each($id_journee))
        {
            if ($valeur['id'] == $sess_context->getJourneeId())
            {
                $index_selected = $cle;
                break;
            }
        }
    }

	$new_index = -1;
    if ($index_selected != -1 && isset($journee_prev) && $journee_prev == 1)
        if ($index_selected != 0) $new_index = --$index_selected;

    if ($index_selected != -1 && isset($journee_next) && $journee_next == 1)
        if ($index_selected != (count($id_journee) - 1)) $new_index = ++$index_selected;

	if ($new_index != -1)
	{
		$sess_context->setJourneeId($id_journee[$new_index]['id']);

		// Si la journée à afficher est virtuelle, il faut rediriger sur journees_virtuelles_ajouter.php
		if ($id_journee[$new_index]['virtuelle'] == 1)
		{
			ToolBox::do_redirect("journees_virtuelles_ajouter.php?pkeys_where_jb_journees=+WHERE+id%3D".$id_journee[$new_index]['id']);
			exit();
		}
	}
}

// Quel est le type de données que l'on va afficher : matchs de poules, de phase final ?
// P pour poule, F pour phase finale, SP pour syntèse poules
if ($slide_view_mode) $options_type_matchs = "SP|0";
$options_type_matchs = isset($options_type_matchs) ? $options_type_matchs : ($sess_context->championnat['option_display_all_matchs'] == 1 ? "AM|0" : "P|1");
$items = explode('|', $options_type_matchs);
$type_matchs = $items[0];
$niveau_type = $items[1];

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = $sjs->isJourneeAlias($row);

// Si ce n'est pas une journee alias, on regarde si cette journee possède des alias
$all_alias = $sjs->getAllAliasJournee($is_journee_alias ? $row['id_journee_mere'] : "");

$date_journee = $row['date'];
$exclude_matchs_journee = "";

if ($is_journee_alias)
{
	$tmp = explode('|', $row['id_matchs']);
	$matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$journee_mere = $sjs->getJournee($row['id_journee_mere']);
	$id_journee_mere = $row['id_journee_mere'];
	$nb_poules    = $journee_mere['tournoi_nb_poules'];
	$phase_finale = $journee_mere['tournoi_phase_finale'];
	$consolante   = $journee_mere['tournoi_consolante'];
	$equipes_journee = $journee_mere['equipes'];
	$liste_poules = explode('|', $journee_mere['equipes']);
}
else
{
	$tmp = explode('|', $row['id_matchs']);
	$exclude_matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$nb_poules    = $row['tournoi_nb_poules'];
	$phase_finale = $row['tournoi_phase_finale'];
	$consolante   = $row['tournoi_consolante'];
	$equipes_journee = $row['equipes'];
	$liste_poules = explode('|', $row['equipes']);
}

// /////////////////////////// MENU //////////////////////////////////////////////////////////////////
if ($slide_view_mode)
{
	$menu = new menu("slide_view_mode");
	$menu->debut($sess_context->getChampionnatNom()." - Journée du ".ToolBox::mysqldate2date($date_journee)." - ");
?>
<LINK REL="stylesheet" HREF="../css/slide_show.css" TYPE="text/css">
<?
}
else
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}

if (isset($errno) && $errno == 1)
{
	Toolbox::alert("Une journée à cette date est déjà prévue.");
}
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Formatage du champs équipes pour prendre en compte les poules et les phases finales
$all_equipes = "";
$nb_equipes  = 0;

if ($type_matchs == "P")
{
	// Recherche de la poule à afficher
	$all_equipes = isset($liste_poules[$niveau_type-1]) ? $liste_poules[$niveau_type-1] : "";
}
else
{
	// Mise à plat du champ 'equipes' pour récupérer toutes les équipes sans distinction de poules
	$tmp = str_replace('|', ',', $equipes_journee);
	$items = explode(',', $tmp);
	foreach($items as $item)
		if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;
}

$classement_equipes = array();
$equipes = array();

// Recherche des équipes et création de stats vierge
if ($type_matchs == "SP")
{
	reset($liste_poules);
	while(list($cle, $equipes_poules) = each($liste_poules))
	{
		// On récupères les infos des equipes (avec init classement vierge si besoin)
		if ($equipes_poules != "")
		{
			$num_poule = $cle + 1;
			$classement_equipes[$num_poule] = "";
			$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$equipes_poules.") ORDER BY nom ASC";
			$res = dbc::execSql($req);
			while($eq = mysql_fetch_array($res))
			{
				if ($classement_equipes[$num_poule] != "") $classement_equipes[$num_poule] .= "|";
				$equipes[$num_poule][$eq['id']] = $eq['nom'];
				$classement_equipes[$num_poule] .= $eq['id']."@".StatJourneeTeam::vierge();
				$nb_equipes++;
			}
		}
	}
} else if ($all_equipes != "") {
  	// On récupères les infos des equipes (avec init classement vierge si besoin)
	$classement_equipes[$niveau_type] = "";
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$all_equipes.") ORDER BY nom ASC";
	$res = dbc::execSql($req);
	while($eq = mysql_fetch_array($res))
	{
		if ($classement_equipes[$niveau_type] != "") $classement_equipes[$niveau_type] .= "|";
		$equipes[$niveau_type][$eq['id']] = $eq['nom'];
		$classement_equipes[$niveau_type] .= $eq['id']."@".StatJourneeTeam::vierge();
		$nb_equipes++;
	}
}

// On essaie de trouver le classement des poules
$req = "SELECT * FROM jb_classement_poules WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".($is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
$res = dbc::execSql($req);
while($stat_poule = mysql_fetch_array($res))
{
	$niveau_poule = explode('|', $stat_poule['poule']);
	$classement_equipes[$niveau_poule[1]] = $stat_poule['classement_equipes'];
}

// COMBO pour la sélection du type de matchs à voir (poules/phase finale/matchs de classement)
$select_niveau = "<SELECT NAME=options_type_matchs onChange=\"javascript:document.forms[0].submit();\">";
for($i=1; $i <= $nb_poules; $i++)
	$select_niveau .= "<OPTION VALUE=\"P|".$i."\" ".($type_matchs == "P" && $niveau_type == $i ? "SELECTED" : "")."> Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$i-1) : $i);
reset($libelle_phase_finale);
$select_niveau .= "<OPTION VALUE=\"AM|0\" ".($type_matchs == "AM" ? "SELECTED" : "")."> [Tous les matchs de la journée]";
if ($nb_poules > 1) $select_niveau .= "<OPTION VALUE=\"SP|0\" ".($type_matchs == "SP" ? "SELECTED" : "")."> [Synthèse Poules]";
$select_niveau .= "<OPTION VALUE=\"F|"._PHASE_PLAYOFF_."\" ".($type_matchs == "F" ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_PLAYOFF_];
if ($consolante > 0) $select_niveau .= "<OPTION VALUE=\"Y|"._PHASE_CONSOLANTE2_."\" ".($type_matchs == "Y" ? "SELECTED" : "")."> [".$libelle_phase_finale[_PHASE_CONSOLANTE2_]."]";
$select_niveau .= "<OPTION VALUE=\"C|"._PHASE_CONSOLANTE1_."\" ".($type_matchs == "C" ? "SELECTED" : "")."> [".$libelle_phase_finale[_PHASE_CONSOLANTE1_]."]";
$select_niveau .= "<OPTION VALUE=\"X|0\" ".($type_matchs == "X" ? "SELECTED" : "")."> [Classement Tournoi]";
$select_niveau .= "</SELECT>";

?>

<SCRIPT type="text/javascript">
function delAllMatchs()
{
	if (confirm("Etes-vous sur de vouloir supprimer tout les matchs enregistrés ?"))
	{
	    document.forms[0].action = 'matchs_suppression.php';
		document.forms[0].submit();

		return true;
	}

	return false;
}
function delJournee()
{
	if (confirm("Etes-vous sûr de vouloir supprimer cette journée ?"))
	{
	    document.forms[0].action = '<?= $is_journee_alias ? "journees_alias_supprimer_do.php" : "journees_supprimer_do2.php" ?>';
		document.forms[0].submit();

		return true;
	}

	return false;
}
function ajouter_match()
{
<? if ($nb_equipes < 2) { ?>
	alert('Vous devez ajouter au moins 2 équipes !');
	return false;
<? } ?>
    document.forms[0].action = 'matchs_ajouter.php';
}
function gotoPoule(poule)
{
	document.forms[0].options_type_matchs.value=poule;
    document.forms[0].action = 'matchs_tournoi.php';
	document.forms[0].submit();
}
function ajouter_match2(poule)
{
	document.forms[0].niveau.value='SP|'+poule;
    document.forms[0].action = 'matchs_ajouter.php';
}
function ajouter_match_barrage()
{
	document.forms[0].niveau.value='C|-1';
    document.forms[0].action = 'matchs_ajouter.php';
}
function gestion_bonus()
{
	document.forms[0].niveau.value='C|-1';
    document.forms[0].action = 'matchs_tournoi_bonus.php';
}
function modifier_match(pkeys, action, niveau)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'matchs_ajouter.php';
    document.forms[0].niveau.value=niveau;

	document.forms[0].submit();
}
function supprimer_match(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer ce match ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'matchs_supprimer_do.php';

	document.forms[0].submit();
}
function launch_slide()
{
    document.forms[0].action = 'matchs_tournoi_slideshow.php';
	document.forms[0].submit();

//    window.open('matchs_tournoi.php?options_type_matchs=SLIDE', 'slide_show', 'width=900, height=600, resizable=yes, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no');
}
function launch_planning()
{
    window.open('matchs_tournoi_planning.php', 'Planning', 'scrollbars=1, width=1000, height=700, resizable=yes, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no');
}
</SCRIPT>

<? if ($slide_view_mode) { ?>
<SCRIPT SRC="../js/ticker.js" type="text/javascript"></SCRIPT>
<STYLE type="text/css">
.hticker {
	position: relative;
	top: 0px;
	left:0px;
	width: 100%;
	z-index: 100;
	font-size: 18px;
	padding: 5px 0px 5px 0px;
	margin: 0px 0px 0px 0px;
}
</STYLE>

<DIV CLASS=hticker>
<script type="text/javascript" language="JavaScript">
Article = new Array;
i=0;
<?
	$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), -1);
	$matchs = $sms->getLastMatchsForTicker();
	foreach($matchs as $m)
	{
		$vainqueur = StatsJourneeBuilder::kikiGagne($m);
		$equipe1 = $vainqueur == 1 ? "<B>".$m['nom1']."</B>" : $m['nom1'];
		$equipe2 = $vainqueur == 2 ? "<B>".$m['nom2']."</B>" : $m['nom2'];
		echo "Article[i++] = new Array (\"&nbsp;&nbsp;<IMG SRC=../images/ballon.gif BORDER=0>[".$m['niveau']."]:".$equipe1." [".$m['resultat']."] ".$equipe2."&nbsp;&nbsp;\", \"none\", \"none\");";
	}
?>
buildScroller(Article);
setTimeout("runScroller()", 5000);
</script>
</DIV>

<? } ?>

<FORM ACTION=matchs_tournoi.php METHOD=post ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=type_action VALUE="" />
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="" />
<INPUT TYPE=HIDDEN NAME=niveau VALUE="<?= $options_type_matchs ?>" />

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central" STYLE="padding-bottom:5px;">

<?

// ///////////////////////////////////////////////////////////
// MATCHS DE CLASSEMENT + BARRAGE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "C")
{
	echo"<TR VALIGN=top><TD>";
	$fxlist = new FXListMatchsClassementTournoi($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), isset($equipes[$niveau_type]) ? count($equipes[$niveau_type]) : 0, $sess_context->isAdmin(), "AND niveau like 'C|%'");
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\"><a href=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif ALT=\"Journée précédente\" /></A></div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee)." : ".$select_niveau."</div>";
	$lib .= "<div class=\"box3\"><a href=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A></div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</TD>";
}

// ///////////////////////////////////////////////////////////
// MATCHS PHASE FINALE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "F" || $slide_view_mode)
{
	echo "<STYLE type=\"text/css\">.match1, .match2, .match3, .match4 { width:100px; overflow: hidden; }</STYLE>";
	echo "<TR VALIGN=top><TD id=\"slide0\" class=\"slide\"><DIV STYLE=\"witdh: 700px; overflow: auto;\">";
	if (isset($affichage_liste))
		$fxlist = new FXListMatchsPlayOff($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $sess_context->isAdmin(), $type_matchs);
	else
		$fxlist = new FXListMatchsPlayOffII($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $slide_view_mode ? false : $sess_context->isAdmin(), $slide_view_mode ? "F" : $type_matchs);
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs)."><IMG SRC=../images/journee_prv.gif ALT=\"Journée précédente\" /></A></div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).": ".($slide_view_mode ? $libelle_phase_finale[_PHASE_PLAYOFF_] : $select_niveau)."</div>";
	$lib .= "<div class=\"box3\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs)."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A></div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</DIV></TD>";
	if (!$slide_view_mode)
	{
		echo "<tr><td><div class=\"cmdbox\">";
		echo "<div><a class=\"cmd\" href=\"matchs_tournoi.php?options_type_matchs=F|"._PHASE_PLAYOFF_.(isset($affichage_liste) ? "" : "&affichage_liste=1")."\">Mode liste</a></div>";
		echo "</div></td></tr>";
	}
}

// ///////////////////////////////////////////////////////////
// MATCHS CONSOLANTE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "Y" || ($consolante > 0 && $slide_view_mode))
{
	echo "<TR VALIGN=top><TD id=\"slide1\" class=\"slide\">";
	if (isset($affichage_liste))
		$fxlist = new FXListMatchsPlayOff($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $sess_context->isAdmin(), $type_matchs);
	else
		$fxlist = new FXListMatchsPlayOffII($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $slide_view_mode ? false : $sess_context->isAdmin(), $type_matchs);
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif ALT=\"Journée précédente\" /></A>")."</div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).":".($slide_view_mode ? $libelle_phase_finale[_PHASE_CONSOLANTE2_] : $select_niveau)."</div>";
	$lib .= "<div class=\"box3\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A>")."</div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</TD>";
}

// ///////////////////////////////////////////////////////////
// TOUS LES MATCHS POULES
// ///////////////////////////////////////////////////////////
if ($type_matchs == "AM")
{
	echo "<TR VALIGN=top><TD>";
	$filtre_niveau     = " ";
	$filtre_matchs_in  = ($is_journee_alias && $matchs_journee != "" ? " AND m.id IN (".$matchs_journee.") " : "");
	if ($is_journee_alias && $filtre_matchs_in == "") $filtre_matchs_in = "AND m.id IN (-1) ";
	$filtre_matchs_out = ($exclude_matchs_journee == "" ? "" : " AND m.id NOT IN (".$exclude_matchs_journee.")");
	$filtre = $filtre_niveau.$filtre_matchs_in.$filtre_matchs_out;
	$fxlist = new FXListMatchsPoules($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), $sess_context->isAdmin(), $filtre);
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\"><A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif ALT=\"Journée précédente\" /></A></div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).": ".$select_niveau."</div>";
	$lib .= "<div class=\"box3\"><A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A></div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</TD>";
}

// ///////////////////////////////////////////////////////////
// MATCHS POULES
// ///////////////////////////////////////////////////////////
if ($type_matchs == "P")
{
	echo "<TR VALIGN=top><TD>";
	$filtre_niveau     = " AND niveau='".$options_type_matchs."'";
//	$filtre_matchs_in  = ($is_journee_alias && $matchs_journee != "" ? " AND m.id IN (".$matchs_journee.") " : "");
	$filtre_matchs_in  = "";
//	if ($is_journee_alias && $filtre_matchs_in == "") $filtre_matchs_in = "AND m.id IN (-1) ";
//	$filtre_matchs_out = ($exclude_matchs_journee == "" ? "" : " AND m.id NOT IN (".$exclude_matchs_journee.")");
	$filtre_matchs_out  = "";
	$filtre = $filtre_niveau.$filtre_matchs_in.$filtre_matchs_out;
	$fxlist = new FXListMatchsPoules($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), $sess_context->isAdmin(), $filtre);
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\"><A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"Journée précédente\"></A></div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).": ".$select_niveau."</div>";
	$lib .= "<div class=\"box3\"><A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A></div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</TD>";
}

?>

<? if ($sess_context->isAdmin() && $type_matchs != "X" && $type_matchs != "SP") { ?>
<TR><TD ALIGN=center>
	<TABLE BORDER=0 WIDTH=100% SUMMARY="">
		<TR>
		    <? if (!$is_journee_alias && $type_matchs == "P") { ?><TD ALIGN=right><INPUT TYPE=SUBMIT onClick="return ajouter_match();" VALUE="Ajouter un match"/></TD><? } ?>
			<? if ($sess_context->isAdmin() && $type_matchs == "C") { ?>
				<TD ALIGN=right><TABLE BORDER=0><TR>
					<TD><INPUT TYPE=SUBMIT onClick="return ajouter_match_barrage();" VALUE="Ajouter un match de barrage" /></TD>
				    <TD><INPUT TYPE=SUBMIT onClick="return gestion_bonus();" VALUE="Gestion des bonus/malus" /></TD>
				</TABLE></TD>
			<? } ?>
	</TABLE>
</TD>
<? } else echo "<TR><TD HEIGHT=10> </TD>";

if ($type_matchs == "P" && isset($classement_equipes[$niveau_type]) && $classement_equipes[$niveau_type] != "")
{
	echo "<TR VALIGN=top><TD>";
	$fxlist = new FXListMatchsStatsEquipes($classement_equipes[$niveau_type], $equipes[$niveau_type]);
	$fxlist->FXDisplay();
	echo "</TD>";
}

// ///////////////////////////////////////////////////////////
// SYNTHESE POULES
// ///////////////////////////////////////////////////////////
if ($type_matchs == "SP")
{
    if (!$slide_view_mode)
    {
		echo "<TR VALIGN=top><TD><table width=\"700px\"><caption class=\"FXList_CAPTION\">";
		$lib  = "<div class=\"tc_box\">";
		$lib .= "<div class=\"box1\"><A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"Journée précédente\"></A></div>";
		$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).": ".$select_niveau."</div>";
		$lib .= "<div class=\"box3\"><A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A></div>";
		$lib .= "</div>";
		echo $lib;
		echo "</caption></table></TD>";
		echo "<TR><TD HEIGHT=5></TD>";
	}

	echo "<TR><TD><TABLE BORDER=0 WIDTH=100% CELLSPACING=2 CELLPADDING=0 SUMMARY=\"\">";
	$i = 0;
	$nb_poules = 3;
	reset($classement_equipes);
	while(list($cle, $classement) = each($classement_equipes))
	{
		if (($i % 2) == 0) echo "<TR VALIGN=top>";
		echo "<TD id=\"slide".$nb_poules++."\" class=\"slide\" WIDTH=50%>";
		echo "<P STYLE=\"margin: 0px; padding: 2px 0px 2px 5px; background: #525252; font-weight: bold; color: white; border: 1px black solid;\"><FONT>Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$cle-1) : $cle)."</FONT></P>";
		$fxlist = new FXListMatchsStatsEquipesLight($classement, $equipes[$cle]);
		$fxlist->FXDisplay();
		echo "</TD>";
		$i++;
	}
	echo "</TABLE></TD>";

}

// ///////////////////////////////////////////////////////////
// CLASSEMENT TOURNOI
// ///////////////////////////////////////////////////////////
if ($type_matchs == "X" || $slide_view_mode)
{
	echo "<TR VALIGN=top VALIGN=top><TD id=\"slide2\" class=\"slide\">";
	$fxlist = new FXListClassementJourneeTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_prev=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_prv.gif ALT=\"Journée précédente\" /></A>")."</div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($date_journee).":".($slide_view_mode ? $libelle_phase_finale[_PHASE_CONSOLANTE2_] : $select_niveau)."</div>";
	$lib .= "<div class=\"box3\">".($slide_view_mode ? "" : "<A HREF=matchs_tournoi.php?journee_next=1&options_type_matchs=".$options_type_matchs."><IMG SRC=../images/journee_nxt.gif ALT=\"Journée suivante\"   /></A>")."</div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	$fxlist->FXDisplay();
	echo "</TD>";
}

?>

</TABLE>


<? if (!$slide_view_mode && $sess_context->isAdmin()) { ?>
<DIV CLASS=cmdbox>
<div><a CLASS=cmd href="<?= $is_journee_alias ? "journees_alias_ajouter.php" : "journees_ajouter_tournoi.php" ?>?modifier_journee=1">Modification de la journée <?= $is_journee_alias ? "alias" : "" ?></a></div>
<? if ($is_journee_alias) { ?>
<div><a CLASS=cmd href="matchs_tournoi.php?pkeys_where_jb_journees=+WHERE+id=<?= $journee_mere['id'] ?>">Accès à la journée mère</a></div>
<div><a CLASS=cmd href="journees_alias_synchroniser.php?id=<?= $journee_mere['id'] ?>">Synchroniser journée mère</a></div>
<? } ?>
<hr>
<? if (!$is_journee_alias) { ?>
<div><a CLASS=cmd href="matchs_sync_joueurs.php?nb_poules=<?= $nb_poules ?>">Synchronisation joueurs/equipes avec matchs</a></div>
<div><a CLASS=cmd href="#" onClick="javascript:delAllMatchs();">Suppression de tous les matchs</a></div>
<? } ?>
<div><a CLASS=cmd href="#" onClick="javascript:delJournee();">Suppression de la journée entière</a></div>
<hr>
<? if (!$is_journee_alias) { ?>
<div><A CLASS=cmd HREF="../www/journees_alias_ajouter.php?pkeys_where_jb_journees=<?= $pkeys_where_jb_journees ?>" CLASS=menu>Création d'un alias de la journée</A></DIV>
<? } ?>
<div><A CLASS=cmd HREF="javascript:launch_slide();" CLASS=menu>Slide show</A></DIV>
<div><A CLASS=cmd HREF="javascript:launch_planning();" CLASS=menu>Planning déroulement journée</A></DIV>
</DIV>
<? } ?>

</FORM>

<? if ($slide_view_mode) { ?>

<SCRIPT SRC="../js/slide_showv2.js" type="text/javascript"></SCRIPT>
<SCRIPT>
var slide_delai = 5000;
function boucle()
{
	changeSlide(1);
}
function change_delai(val)
{
	clearInterval(slide_interval);
	slide_delai = val;
	slide_interval = setInterval("boucle()", slide_delai);
}
function change_slide(sens)
{
	clearInterval(slide_interval);
	changeSlide(sens);
	slide_interval = setInterval("boucle()", slide_delai);
}
function stop_slide(sens)
{
	clearInterval(slide_interval);
}
function start_slide(sens)
{
	clearInterval(slide_interval);
	changeSlide(1);
	slide_interval = setInterval("boucle()", slide_delai);
}
var mes_slides = new Array(<?= ($consolante > 0 && $slide_view_mode) ? "'slide1', " : "" ?>'slide2', <? for($i = 3; $i < $nb_poules; $i++) echo "'slide".$i."',"; ?> 'slide0');
init_layers(mes_slides);
slide_interval = setInterval("boucle()", slide_delai);
</SCRIPT>

<META HTTP-EQUIV="refresh" CONTENT="180; URL=matchs_tournoi.php?options_type_matchs=SLIDE">

<? } ?>

<? $menu->end(); ?>
