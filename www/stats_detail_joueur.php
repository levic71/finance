<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Si on vient de la liste des joueurs
if (isset($pkeys_where_jb_joueurs))
{
	$item = explode('=', urldecode($pkeys_where_jb_joueurs));
	$id_detail = $item[1];
}

//$sgb           = new StatsGlobalBuilder($sess_context->getChampionnatId(), $sess_context->getChampionnatType());
$sgb           = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$ids           = $sgb->getIdPlayers();
$nom_joueurs   = $sgb->getPlayersName();
$xj            = $sgb->getStatsPlayer($id_detail);
$stats_teams   = $sgb->getStatsTeams();
$best_teams    = $sgb->getBestTeams($id_detail);
$most_matchs   = $sgb->getMostTeams($id_detail);

?>

<LINK REL="stylesheet" HREF="../css/XList.css" TYPE="text/css">
<FORM ACTION=stats_detail_joueur.php METHOD=POST>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

// Récupération de la liste des joueurs
$select_joueurs = "<SELECT NAME=id_detail onChange=\"javascript:document.forms[0].submit();\">";
ksort($ids);
while(list($id, $val) = each($ids)) $select_joueurs .= "<OPTION VALUE=".$val." ".($val == $id_detail ? "SELECTED" : "").">".$id;
$select_joueurs .= "</SELECT>";

echo "<TR VALIGN=TOP><TD COLSPAN=13>";
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	$fxlist = new FXListStatsTournoiJoueurs($sgb, $id_detail);
else
	$fxlist = new FXListStatsJoueurs($sgb, $id_detail);
$fxlist->FXSetTitle("Statistiques de ".$select_joueurs);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD HEIGHT=5></TD>

<!-- PHOTO + STATS REPARTITION EQUIPES -->
<TR><TD ALIGN=CENTER><TABLE BORDER=0 WIDTH=100% CELLSPACING=0 CELLPADDING=0>
<?
	$tab = array();
	$tab[] = array("<TABLE BORDER=0><TR ALIGN=CENTER><TD WIDTH=150>".ToolBox::ombre($xj->photo != "" ? $xj->photo : "../images/linconnu.gif", 110, 110)."</TD><TR VALIGN=TOP ALIGN=CENTER><TD> Né(e) le ".ToolBox::mysqldate2date($xj->dt_naissance)." (".ToolBox::date2age($xj->dt_naissance)." ans)</TD></TABLE>");

	echo "<TR VALIGN=TOP><TD ALIGN=LEFT>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle($xj->pseudo);
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

	$datas = ($xj->jouesA == 0 && $xj->jouesD == 0) ? "0|100" : $xj->jouesA."|".$xj->jouesD;
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=Repartition+equipe&datas=".$datas."&legendes=Attaquant|Defenseur\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TD WIDTH=5></TD>";
	echo "<TD ALIGN=RIGHT CLASS=special>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Répartition équipe");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";
?>
	</TABLE></TD>

<TR><TD HEIGHT=5></TD>

<!-- STATS MATCHS GAGNES + STATS SETS GAGNES -->
<TR><TD ALIGN=CENTER><TABLE BORDER=0 WIDTH=100% CELLSPACING=0 CELLPADDING=0>
<?
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=Palmares&datas=".$xj->pourc_gagnes."|".(100-$xj->pourc_gagnes)."&legendes=Matchs+gagnes|Matchs+perdus\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TR><TD ALIGN=LEFT>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("% matchs gagnés");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

	$datas = ($xj->sets_joues == 0) ? "0|100" : (($xj->sets_gagnes / $xj->sets_joues)*100)."|".(100 - ($xj->sets_gagnes / $xj->sets_joues)*100);
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=Palmares&datas=".$datas."&legendes=Sets+gagnes|Sets+perdus\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TD WIDTH=5></TD>";
	echo "<TD ALIGN=RIGHT CLASS=special>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("% sets gagnés");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";
?>
	</TABLE></TD>

<TR><TD HEIGHT=5></TD>

<?
	reset($xj->evol_pourc_gagne);
	$q1 = "";
	$q2 = "";
	while(list($cle, $val) = each($xj->evol_pourc_gagne))
	{
		$q1 .= ($q1 == "") ? $val : "|".$val;
		$q2 .= ($q2 == "") ? $cle : "|".$cle;
	}
	// Si le joueur n'a joué aucun match pour l'instant
	if ($q1 == "")
	{
		$q1 = "0|0";
		$q2 = "0|1";
	}
	// Si le joueur n'a joué qu'un match pour l'instant
	if (!strstr($q1, "|"))
	{
		$q1 = "0|".$q1;
		$q2 = "0|".$q2;
	}

	// Evolution classement
	$tab = array();
	$tab[] = array("<IMG SRC=\"filledgridex1.php?scale1=0&scale2=100&datas1=".$q1."&datas2=".$q2."&legendes=Pourcentage+victoires|Moyenne\" BORDER=0 WIDTH=680 HEIGHT=350>");

	echo "<TR><TD>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Performance");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";
?>

<TR><TD HEIGHT=5></TD>

<!-- STATS FANNYS + STATS MATCHS SERRES -->
<TR><TD ALIGN=CENTER><TABLE BORDER=0 WIDTH=100% CELLSPACING=0 CELLPADDING=0>
<?
	$req = "SELECT COUNT(*) total FROM jb_matchs WHERE fanny=1 AND id_champ=".$sess_context->getChampionnatId();
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);
	$fanny_total = $row['total'];

	$pourc_fanny_in  = $fanny_total == 0 ? 0 : ($xj->fanny_in  * 100) / $fanny_total;
	$pourc_fanny_out = $fanny_total == 0 ? 0 : ($xj->fanny_out * 100) / $fanny_total;
	$pourc_fanny_xxx = 100 - $pourc_fanny_in - $pourc_fanny_out;
	$pourc_justesse_gagnes = $xj->joues == 0 ? 0 : ($xj->justesse_gagnes * 100) / $xj->joues;
	$pourc_justesse_perdus = $xj->joues == 0 ? 0 : ($xj->justesse_perdus * 100) / $xj->joues;
	$pourc_justesse_xxx    = 100 - $pourc_justesse_gagnes - $pourc_justesse_perdus;

	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=fannys&datas=".$pourc_fanny_in."|".$pourc_fanny_out."|".$pourc_fanny_xxx."&legendes=Fannys+pris|Fannys+donnes|Autres\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TR><TD ALIGN=LEFT>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Fannys");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=fannys&datas=".$pourc_justesse_gagnes."|".$pourc_justesse_perdus."|".$pourc_justesse_xxx."&legendes=gagnes+serres|perdus+serres|Autres\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TD WIDTH=5></TD>";
	echo "<TD ALIGN=RIGHT CLASS=special>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Matchs serrés");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";
?>
	</TABLE></TD>

<TR><TD HEIGHT=5></TD>

<?
if ($sess_context->getChampionnatType() != _TYPE_TOURNOI_)
{
echo "<TR><TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>";

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU meilleures equipes
// /////////////////////////////////////////////////////////////////////////////////////////////
echo "<TR VALIGN=TOP><TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%><TR>";
echo "<TR><TD>";
$fxlist = new FXListBestTeams($best_teams, 10);
$fxlist->FXSetTitle("Equipes les + performantes");
$fxlist->FXSetFooter("<I> (Seules les équipes ayant joué un nombre de matchs significatifs apparaissent) </I>");
$fxlist->FXDisplay();
echo "</TD>";
?>
<tr><td><div class="cmdbox" style="width:300px;">
<div><a class="cmd" href="stats_joueurs_bestteams.php?id_joueur=<?= $id_detail ?>">Accès à la liste complète</a></div>
</div></td>
<?
echo "</TABLE></TD>";

echo "<TD WIDTH=5> </TD>";

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU + sur le terrain
// /////////////////////////////////////////////////////////////////////////////////////////////
echo "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%><TR>";
echo "<TR><TD CLASS=special>";
$fxlist = new FXListMostOnGround($most_matchs, 10);
$fxlist->FXSetTitle("Equipes les + sur le terrain");
$fxlist->FXDisplay();
echo "</TD>";
?>
<tr><td><div class="cmdbox" style="width:300px;">
<div><a class="cmd" href="stats_joueurs_mostteams.php?id_joueur=<?= $id_detail ?>">Accès à la liste complète</a></div>
</div></td>
<?
echo "</TABLE></TD>";

echo "</TABLE></TD>";

echo "<TR><TD HEIGHT=5></TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU FANNYS
// /////////////////////////////////////////////////////////////////////////////////////////////

echo "<TR><TD>";
$fxlist = new FXListFannysJoueur($sess_context->getChampionnatId(), $id_detail, 10);
$fxlist->FXSetTitle("Fannys");
$fxlist->FXDisplay();
echo "</TD>";
?>
<tr><td><div class="cmdbox">
<div><a class="cmd" href="stats_joueurs_fannys.php?joueur_ou_equipe=1&id_detail&id_detail=<?= $id_detail ?>">Accès à la liste complète</a></div>
</div></td>

</TABLE>
</FORM>

<? $menu->end(); ?>
