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
$stats_teams   = $sgb->getStatsTeams();
$xt            = $stats_teams[$id_detail];

$tri_id  = array();
$tri_nom = array();
foreach($stats_teams as $stat)
{
	$tri_id[]  = $stat->id;
	$tri_nom[] = $stat->nom;
}
array_multisort($tri_nom, SORT_ASC, $tri_id);

?>

<FORM ACTION=stats_detail_equipe.php METHOD=POST>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

// Récupération de la liste des équipes
$select_equipes = "<SELECT NAME=id_detail onChange=\"javascript:document.forms[0].submit();\">";
while(list($id, $val) = each($tri_nom))
	$select_equipes .= "<OPTION VALUE=".$tri_id[$id]." ".($tri_id[$id] == $id_detail ? "SELECTED" : "").">".$val;
$select_equipes .= "</SELECT>";

// Tableau qui contient la seule ligne à afficher
$t = array("0" => $xt);
echo "<TR><TD>";
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	$fxlist = new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $t);
else
	$fxlist = new FXListStatsTeams($t);
$fxlist->FXSetTitle("Statistiques de ".$select_equipes);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD HEIGHT=5></TD>

<?

// Récupération des infos de l'équipe
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$equipe = $ses->getEquipe($id_detail);

if ($equipe['commentaire'] or $equipe['photo'])
{
	$tab = array();

	$Info_Equipe  = "<TABLE BORDER=0><TR ALIGN=CENTER>";
	$Info_Equipe .= "<TD WIDTH=150>".ToolBox::ombre($equipe['photo'] != "" ? $equipe['photo'] : "../uploads/linconnu.gif", 100, 100)."</TD>";	$Info_Equipe .= "<TD VALIGN=TOP>".$equipe['commentaire']."</TD>";
	$Info_Equipe .= "</TR></TABLE>";

	$tab[] = array($Info_Equipe);

	echo "<TR><TD>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle($equipe['nom']);
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD></TR>";

	echo "<TR><TD HEIGHT=5></TD>";
}

$options = explode('|', $sess_context->getChampionnatOptions());

// Affichage des joueurs de l'équipe
if ($equipe['nb_joueurs'] > 0)
{
	$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());

	$i = 0;
	$tab = array();
	
	$id_joueurs = explode('|', $equipe['joueurs']);
	foreach($id_joueurs as $id)
	{
		$joueur = $sjs->getJoueur($id);
		$age	= ToolBox::mysqldate2date($joueur['dt_naissance']);
		
		$ligne[$i] = "<TABLE BORDER=0>";
		if (isset($options[6]) && $options[6] == 1)
			$ligne[$i] .= "<TR ALIGN=CENTER><TD WIDTH=150><A HREF=stats_detail_joueur.php?id_detail=".$joueur['id'].">".ToolBox::ombre($joueur['photo'] != "" ? $joueur['photo'] : "../uploads/linconnu.gif", 100, 100)."</A></TD>";
		else
			$ligne[$i] .= "<TR ALIGN=CENTER><TD WIDTH=150>".ToolBox::ombre($joueur['photo'] != "" ? $joueur['photo'] : "../uploads/linconnu.gif", 100, 100)."</TD>";
		$ligne[$i] .= "<TD><TABLE BORDER=0>";
		$ligne[$i] .= "<TR VALIGN=TOP ALIGN=CENTER><TD>".$joueur['pseudo']." (".ToolBox::date2age($joueur['dt_naissance'])." ans)</TD>";
		$ligne[$i] .= "<TR VALIGN=TOP ALIGN=CENTER><TD> Né(e) le ".ToolBox::mysqldate2date($joueur['dt_naissance'])."</TD>";
		$ligne[$i] .= "</TABLE></TD>";
		$ligne[$i] .= "</TABLE>";
		
		$i++;
		
		if ($i == 2)
		{
			$tab[] = array($ligne[0], $ligne[1]);
			$i = 0;
			unset($ligne[0]);
			unset($ligne[1]);
		}
	}
	if ($i == 1) $tab[] = array($ligne[0], null);

	echo "<TR><TD>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Liste des Joueurs");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

}
?>

<TR><TD HEIGHT=5></TD>

<!-- STATS MATCHS GAGNES + STATS REPARTITION EQUIPES -->
<TR><TD ALIGN=CENTER><TABLE BORDER=0 WIDTH=100% CELLSPACING=0 CELLPADDING=0>
<?
	// % Matchs gagnés
	$pourc_sets_gagnes = ($xt->sets_joues > 0) ? ($xt->sets_gagnes / $xt->sets_joues)*100 : 0;
	$pourc_sets_perdus = ($xt->sets_joues > 0) ? 100 - $pourc_sets_gagnes : 0;
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=Palmares&datas=".$xt->pourc_gagnes."|".(100-$xt->pourc_gagnes)."&legendes=Matchs+gagnes|Matchs+perdus\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TR><TD ALIGN=LEFT>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("% Matchs gagnés");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

	// % Sets gagnés
	$datas = ($xt->sets_joues == 0) ? "0|100" : $pourc_sets_gagnes."|".$pourc_sets_perdus;
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=Repartition+equipe&datas=".$datas."&legendes=Sets gagnes|Sets perdus\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TD WIDTH=5></TD>";
	echo "<TD ALIGN=RIGHT CLASS=special>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("% Sets gagnés");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";
?>
	</TABLE></TD>

<TR><TD HEIGHT=5></TD>

<?
	if (count($xt->evol_classement) > 0)
	{
		reset($xt->evol_classement);
		$q1 = "";
		$q2 = "";
		while(list($cle, $val) = each($xt->evol_classement))
		{
			$tmp = $val == "" ? "@" : -$val;
			$q1 .= $q1 == "" ? $tmp : "|".$tmp;
			$q2 .= ($q2 == "") ? $cle : "|".$cle;
		}
		$q1 = str_replace('@', '', $q1);

		// On met une ligne droite si une seule donnée doit être affichée
		if (!strstr($q1, "|") && $q1 != "")
		{
			$q1 = $q1."|".$q1;
			$q2 = $q2."|".$q2;
		}

		// Evolution classement
		$datas = ($xt->sets_joues == 0) ? "0|100" : $pourc_sets_gagnes."|".$pourc_sets_perdus;
		$tab = array();
		$tab[] = array("<IMG SRC=\"filledgridex1.php?reverse=1&scale1=".(-count($stats_teams))."&scale2=0&datas1=".$q1."&datas2=".$q2."&legendes=Evolution+classement|Moyenne\" BORDER=0 WIDTH=680 HEIGHT=350>");

		echo "<TR><TD>";
		$fxlist = new FXListPresentation($tab);
		$fxlist->FXSetTitle("Classement");
		$fxlist->FXSetMouseOverEffect(false);
		$fxlist->FXDisplay();
		echo "</TD>";
	}
?>

<TR><TD HEIGHT=5></TD>

<!-- STATS FANNYS + STATS MATCHS SERRES -->
<TR><TD ALIGN=CENTER><TABLE BORDER=0 WIDTH=100% CELLSPACING=0 CELLPADDING=0>
<?
	$req = "SELECT COUNT(*) total FROM jb_matchs WHERE fanny=1 AND id_champ=".$sess_context->getChampionnatId();
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);
	$fanny_total = $row['total'];

	$pourc_fanny_in  = $fanny_total == 0 ? 0 : ($xt->fanny_in  * 100) / $fanny_total;
	$pourc_fanny_out = $fanny_total == 0 ? 0 : ($xt->fanny_out * 100) / $fanny_total;
	$pourc_fanny_xxx = 100 - $pourc_fanny_in - $pourc_fanny_out;
	$pourc_justesse_gagnes = $xt->matchs_joues == 0 ? 0 : ($xt->justesse_gagnes * 100) / $xt->matchs_joues;
	$pourc_justesse_perdus = $xt->matchs_joues == 0 ? 0 : ($xt->justesse_perdus * 100) / $xt->matchs_joues;
	$pourc_justesse_xxx    = 100 - $pourc_justesse_gagnes - $pourc_justesse_perdus;


	// % Matchs gagnés
	$pourc_sets_gagnes = ($xt->sets_joues > 0) ? ($xt->sets_gagnes / $xt->sets_joues)*100 : 0;
	$pourc_sets_perdus = ($xt->sets_joues > 0) ? 100 - $pourc_sets_gagnes : 0;
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=fannys&datas=".$pourc_fanny_in."|".$pourc_fanny_out."|".$pourc_fanny_xxx."&legendes=Fannys+pris|Fannys+donnes|Autres\" BORDER=0 WIDTH=320 HEIGHT=140>");

	echo "<TR><TD ALIGN=LEFT>";
	$fxlist = new FXListPresentation($tab);
	$fxlist->FXSetTitle("Fannys");
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</TD>";

	// % Sets gagnés
	$datas = ($xt->sets_joues == 0) ? "0|100" : $pourc_sets_gagnes."|".$pourc_sets_perdus;
	$tab = array();
	$tab[] = array("<IMG SRC=\"pie3dex3.php?titre=fannys&datas=".$pourc_justesse_gagnes."|".$pourc_justesse_perdus."|".$pourc_justesse_xxx."&legendes=gagnes+serres|perdus+serres|Autres\" BORDER=0 WIDTH=320 HEIGHT=140");

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

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU STATS CONTRONTATIONS
// /////////////////////////////////////////////////////////////////////////////////////////////

echo "<TR><TD>";
$fxlist = new FXListStatsConfrontations($sess_context->getChampionnatId(), $id_detail, 10);
$fxlist->FXSetTitle("Statistiques Confrontations");
$fxlist->FXDisplay();
echo "</TD>";
?>
<tr><td><div class="cmdbox">
<div><a class="cmd" href="stats_equipes_confrontations.php?id_detail=<?= $id_detail ?>" >Accès à la liste complète</a></div>
</div></td>

<TR><TD HEIGHT=5></TD>

<?

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU FANNYS
// /////////////////////////////////////////////////////////////////////////////////////////////

echo "<TR><TD>";
$fxlist = new FXListFannysEquipe($sess_context->getChampionnatId(), $id_detail, 10);
$fxlist->FXSetTitle("Fannys");
$fxlist->FXDisplay();
echo "</TD>";
?>
<tr><td><div class="cmdbox">
<div><a class="cmd" href="stats_joueurs_fannys.php?joueur_ou_equipe=2&id_detail=<?= $id_detail ?>" >Accès à la liste complète</a></div>
</div></td>

</TABLE>
</FORM>

<? $menu->end(); ?>
