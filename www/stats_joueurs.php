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
$menu->debut($sess_context->getChampionnatNom(), "07", $redirect == 1 ? "redirect" : "");

if (!isset($choix_stat)) $choix_stat = 0;

//$sgb = new StatsGlobalBuilder($sess_context->getChampionnatId(), $sess_context->getChampionnatType());
$sgb           = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$best_teams    = $sgb->getBestTeams();
$most_matchs   = $sgb->getMostTeams();

?>

<link rel="stylesheet" href="../css/XList.css" type="text/css">
<form action="stats_joueurs.php" method="post">
<input type="hidden" name="redirect" value="<?= $redirect ?>" />
<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">

<?

$select  = "<SELECT NAME=choix_stat onChange=\"javascript:document.forms[0].submit();\">";
$select .= "<OPTION VALUE=0 ".($choix_stat == 0 ? "SELECTED" : "")."> Statistiques Joueurs";
$select .= "<OPTION VALUE=1 ".($choix_stat == 1 ? "SELECTED" : "")."> Statistiques Equipes";
$select .= "<OPTION VALUE=2 ".($choix_stat == 2 ? "SELECTED" : "")."> Equipes les + sur le terrain";
$select .= "<OPTION VALUE=4 ".($choix_stat == 4 ? "SELECTED" : "")."> Equipes: Meilleures attaques";
$select .= "<OPTION VALUE=5 ".($choix_stat == 5 ? "SELECTED" : "")."> Equipes: Meilleures défenses";
$select .= "<OPTION VALUE=6 ".($choix_stat == 6 ? "SELECTED" : "")."> Joueurs: Meilleurs attaquants";
$select .= "<OPTION VALUE=7 ".($choix_stat == 7 ? "SELECTED" : "")."> Joueurs: Meilleurs défenseurs";
$select .= "<OPTION VALUE=3 ".($choix_stat == 3 ? "SELECTED" : "")."> Fannys";
$select .= "</SELECT>";

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 0)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListStatsJoueurs($sgb);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetFooter($sgb->getNbMatchs()." matchs joués dans le championnat sur ".$sgb->getNbJournees()." journées (".sprintf("%.2f", $sgb->getMoyMatchsJoues())." matchs/journée)");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 1)
{
	echo "<TR><TD>";
	$fxlist = new FXListStatsTeams($best_teams);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=1");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU EQUIPES LES + SUR LE TERRAIN
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 2)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListMostOnGround($most_matchs);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=2");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU FANNYS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 3)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListFannysJoueur($sess_context->getChampionnatId(), "");
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=3");
	$fxlist->FXDisplay();
	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU EQUIPES : MEILLEURES ATTAQUES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 4)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListStatsAttDef($sgb->getBestAttaques(), 1);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=4");
	$fxlist->FXDisplay();

	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU EQUIPES : MEILLEURES DEEFENSES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 5)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListStatsAttDef($sgb->getBestDefenses(), 0);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=5");
	$fxlist->FXDisplay();

	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU JOUEURS : MEILLEURS ATTAQUANTS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 6)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListBestPlayers($sgb->getBestJoueursAttaquants(), 1);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=6");
	$fxlist->FXDisplay();

	echo "</TD>";
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU JOUEURS : MEILLEURS DEFENSEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 7)
{
	echo "<TR VALIGN=TOP><TD>";
	$fxlist = new FXListBestPlayers($sgb->getBestJoueursDefenses(), 0);
	$fxlist->FXSetTitle($select);
	$fxlist->FXSetPagination("stats_joueurs.php?choix_stat=7");
	$fxlist->FXDisplay();

	echo "</TD>";
}

?>

<tr><td><div class="cmdbox">
<div><a class="cmd" href="#" onclick="javascript:window.open('decouvrir.php#stats', 'faq', 'resizable=yes, scrollbars=yes, width=750, height=500, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=yes, status=no, menuBar=no');">Aide</a></div>
<div><a class="cmd" href="#" onclick="javascript:document.getElementById('redirect').style.display='block'; return false;">Code pour afficher ce classement sur un autre site</a></div>
<? $url = "http://www.jorkers.com/www/classement_redirect.php?champ=".$sess_context->getRealChampionnatId()."&view=".$sess_context->getChampionnatType(); ?>
<div id="redirect" style="display:none;">
	<div class="box"><div class="label">Url direct :</div><input type="text" size="80" value="<?= $url ?>" onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
	<br />
	<div class="box"><div class="label">Code embarqué :</div><input type="text" size="80" value='<iframe  scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" height="600" width="740" src="<?= $url ?>"></iframe>' onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
</div>
</div></td>

<? if ($redirect == 1) { ?>
<script type="text/javascript">
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
</script>
<? } ?>

</TABLE>
</FORM>


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

<? $menu->end($redirect == 1 ? "signature" : ""); ?>
