<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

// ////////////////////////////////////////////////////////////////////////////////
// Si on vient d'un redirect pour n'afficher que la liste ...
if (!isset($redirect)) $redirect = 0;
// ////////////////////////////////////////////////////////////////////////////////

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom(), "08", $redirect == 1 ? "redirect" : "");

if (!isset($choix_stat)) $choix_stat = 0;

// Pas besoin, fait lors des operations de maj matchs (insert, update, delete)
// JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");
$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
$best_teams_championnat = $sgb->getBestTeamsByPoints();

?>

<link rel="stylesheet" href="../css/XList.css" type="text/css">
<form action="stats_equipes.php" method="post">
<input type="hidden" name="redirect" value="<?= $redirect ?>" />
<a href="../pdf/pdf_classement.php?champ=<?= $sess_context->getRealChampionnatId() ?>&format=A4" target="_blank"><img style="margin: 0px 10px 0px 0px;" src="../images/templates/defaut/exportA4.gif" alt="" /></a><a href="../pdf/pdf_classement.php?champ=<?= $sess_context->getRealChampionnatId() ?>&format=A3" target="_blank"><img src="../images/templates/defaut/exportA3.gif" alt="" /></a>
<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">

<?
// /////////////////////////////////////////////////////////////////////////////
// ECRAN QUE POUR LES CHAMPIONNATS ET LES TOURNOIS (PAS POUR LIBRE)
// /////////////////////////////////////////////////////////////////////////////
$options = explode('|', $sess_context->getChampionnatOptions());
$select  = "<SELECT NAME=choix_stat onChange=\"javascript:document.forms[0].submit();\">";
$select .= "<OPTION VALUE=0 ".($choix_stat == 0 ? "SELECTED" : "")."> ".($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "Classement Equipes" : "Statistiques Equipes");
if (isset($options[6]) && $options[6] == 1) $select .= "<OPTION VALUE=1 ".($choix_stat == 1 ? "SELECTED" : "")."> Statistiques Joueurs";
$select .= "<OPTION VALUE=2 ".($choix_stat == 2 ? "SELECTED" : "")."> Meilleures Attaques";
$select .= "<OPTION VALUE=3 ".($choix_stat == 3 ? "SELECTED" : "")."> Meilleures Défenses";
$select .= "</SELECT>";

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 0)
{
	echo "<TR><TD>";
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		$fxlist = new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $best_teams_tournoi);
	else
		$fxlist = new FXListStatsTeamsII($best_teams_championnat);

	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_equipes.php?choix_stat=0");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 1)
{
	echo "<TR VALIGN=TOP><TD>";
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		$fxlist = new FXListStatsTournoiJoueurs($sgb);
	else
		$fxlist = new FXListStatsJoueurs($sgb);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetFooter($sgb->getNbMatchs()." matchs joués dans le championnat sur ".$sgb->getNbJournees()." journées (".sprintf("%.2f", $sgb->getMoyMatchsJoues())." matchs/journée)");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE ATTAQUES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 2)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListStatsAttDef($sgb->getBestAttaques(), 1);
	$fxlist->FXSetTitle($select);
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE DEFENDES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 3)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListStatsAttDef($sgb->getBestDefenses(), 0);
	$fxlist->FXSetTitle($select);
	$fxlist->FXDisplay();
	echo "</TD>";
}

?>
<style>
#redirect {
/*	display: none; */
}
</style>

<a name=cmdbox>
<tr><td><div class="cmdbox">
<div><a class="cmd" href="#cmdbox" onclick="javascript:window.open('decouvrir.php#stats', 'faq', 'resizable=yes, scrollbars=yes, width=750, height=500, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=yes, status=no, menuBar=no');">Aide</a></div>
<div><a class="cmd" href="#cmdbox" onclick="javascript:showcode('redirect');">Code pour afficher ce classement sur un autre site</a></div>
<? $url = "http://www.jorkers.com/www/classement_redirect.php?champ=".$sess_context->getRealChampionnatId()."&view=".$sess_context->getChampionnatType(); ?>
<div id="redirect">
	<div class="box"><div class="label">Url direct :</div><input type="text" size="80" value="<?= $url ?>" onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
	<br />
	<div class="box"><div class="label">Code embarqué :</div><input type="text" size="80" value='<iframe  scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" height="600" width="740" src="<?= $url ?>"></iframe>' onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
</div>
</div></td>

</table>
</form>

<br />

<!-- AddToAny BEGIN -->
<a class="a2a_dd" href="http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Fwww.jorkers.com&amp;linkname="><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" border="0" alt="Share"/></a>
<script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = "Jorkers - <?= $sess_context->getChampionnatNom() ?> - Statistiques championnats";
a2a_config.show_title = 1;
a2a_config.linkurl = "http://www.jorkers.com/www/classement_redirect.php?champ=<?= $sess_context->getRealChampionnatId() ?>&view=<?= $sess_context->getChampionnatType() ?>";
</script>
<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
<!-- AddToAny END -->


<script type="text/javascript">
function showcode(box)
{
	document.getElementById(box).style.display='block';

	return false;
}
<? if ($redirect == 1) { ?>
<!--
for(var i=0; i < document.links.length; ++i)
{
	if (document.links[i].href.indexOf('stats') >= 0)
	{
		document.links[i].href = '#';
		document.links[i].style.textDecoration = 'none';
	}
}
//-->
<? } ?>
</script>

<? $menu->end($redirect == 1 ? "signature" : ""); ?>
