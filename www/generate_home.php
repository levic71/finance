<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;
$jb_langue = "fr";

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

$infos = JKCache::getCache("../cache/info_champ_".$championnat_id."_.txt", 600, "_FLUX_INFO_CHAMP_");

$scs = new SQLChampionnatsServices($championnat_id);
$champ_info = $scs->getChampionnat();

$menu = new menu("forum_access");
$menu->debut("");

?>

<div id="pageint" style="margin-bottom: 0px">

<h2>Détail du championnat</h2>


<table border="0">

<tr valign="top"><td><table border="0" summary="Infos championnat">
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">Lieu de pratique :</td></tr>
<tr><td class="libg_info"><?= $champ_info == "" ? "?" : $champ_info['lieu'] ?></td></tr>
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">Gestionnaire :</td></tr>
<tr><td class="libg_info"><?= $champ_info == "" ? "?" : $champ_info['gestionnaire'] ?> <a href="../www/contacter.php?option=0" class="menu"><img src="../images/email.gif" alt="" /></a></td></tr>
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">Date de création :</td></tr>
<tr><td class="libg_info"><?= ToolBox::mysqldate2date($champ_info['dt_creation']) ?></td></tr>
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">Description :</td></tr>
<tr><td class="libg_info"><?= $champ_info['description'] ?></td></tr>
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">Chiffres clés : </td></tr>
<tr><td><table width="100%" border="0" cellpadding="0" cellspacing="0" summary="Détails">
	<tr><td align="right" class="libg_info"><?= $infos['nb_saisons']  ?></td><td align="left" class="lib2_info">saisons</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_joueurs']  ?></td><td align="left" class="lib2_info">joueurs</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_equipes']  ?></td><td align="left" class="lib2_info">équipes</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_journees'] ?></td><td align="left" class="lib2_info">journées</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_matchs']   ?></td><td align="left" class="lib2_info">matchs</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_messages'] ?></td><td align="left" class="lib2_info">messages</td></tr>
</table></td></tr>
<tr><td class="lib_info" style="border-bottom: 1px solid #CCC">News :</td></tr>
<tr><td class="libg_info"><?= $champ_info['news'] ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td style="border-top: 1px solid #CCC"><a href="championnat_acces.php?ref_champ=<?= $championnat_id ?>">Accès au championnat</a></td></tr>
</table></td>


<?

$real_id = $championnat_id;

$scs = new SQLChampionnatsServices($real_id);
$champ_info = $scs->getChampionnat();
$type_champ = $champ_info['type'];
$sais_id = $champ_info['saison_id'];

$sess_context->championnat['saison_id'] = $sais_id;
$sess_context->championnat['type'] = $type_champ;
$sgb = JKCache::getCache("../cache/stats_champ_".$real_id."_".$sais_id.".txt", -1, "_FLUX_STATS_CHAMP_");

if ($type_champ == _TYPE_TOURNOI_)
	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
else if ($type_champ == _TYPE_CHAMPIONNAT_)
	$best_teams_championnat = $sgb->getBestTeamsByPoints();
else if ($type_champ == _TYPE_LIBRE_)
{
	$id_joueurs    = $sgb->getIdPlayers();
	$nom_joueurs   = $sgb->getPlayersName();
	$stats_joueurs = $sgb->getStatsPlayers();
}

?>

<style>
#spotlight table {
	font-family: arial;
	font-size: 11px;
	color:#FFF;
	background:#C00 url('../images/templates/defaut/full.png') repeat-x top left;
	border:5px solid #900;
	border-collapse:collapse;
}
#spotlight thead th {
	font-weight: bold;
 	border-bottom:1px dotted #FFF;
	padding: 3px;
}
#spotlight td, #spotlight th {
	background:transparent;
	padding: 5px 3px;
}
#spotlight tbody tr.odd td {
	background:transparent url('../images/templates/defaut/tr_bg.png') repeat top left;
}
* html #spotlight tr.odd td {
	background:none;
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../images/templates/defaut/tr_bg.png', sizingMethod='scale');
}
</style>

<td><table border="0" style="margin: 0px 15px 0px 10px; border-left: 1px solid #CCC;">
<?
		if ($type_champ == _TYPE_TOURNOI_)
		{
			echo "<thead><tr><th style=\"width: 10px;\">&nbsp;</th><th align=\"left\" style=\"width: 50%;\">Equipe</th><th align=\"right\">Nb points</th><th align=\"right\">Goal Avg</th><th align=\"right\">Moy class.</th></tr></thead>";

			$k = 0;
			while(list($cle, $st) = each($best_teams_tournoi))
			{
				echo "<tr".($k++ % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$k."&nbsp;</td><td>".$st->nom."</td><td align=\"right\">".$st->tournoi_points."</td><td align=\"right\">".($st->diff > 0 ? "+" : "").$st->diff."</td><td align=\"right\">".$st->tournoi_classement_moy."</td></tr>";
			}
		}
		else if ($type_champ == _TYPE_CHAMPIONNAT_)
		{
			echo "<thead><tr><th style=\"width: 10px;\">&nbsp;</th><th align=\"left\" style=\"width: 50%;\">Equipe</th><th align=\"right\">Nb points</th><th align=\"right\">Goal Avg</th></tr></thead>";

			$k = 0;
			while(list($cle, $st) = each($best_teams_championnat))
			{
				echo "<tr".($k++ % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$k."&nbsp;</td><td>".$st->nom."</td><td align=\"right\">".$st->points."</td><td align=\"right\">".($st->diff > 0 ? "+" : "").$st->diff."</td></tr>";
			}
		}
		else
		{
			echo "<thead><tr><th align=\"left\" style=\"width: 50%;\">Joueur</th><th align=\"right\">% matchs gagnés</th><th align=\"center\">Forme</th></tr></thead>";

			$k = 0;
			shuffle($stats_joueurs);
			while(list($cle, $st) = each($stats_joueurs))
			{
				echo "<tr".($k++ % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$st->nom." ".$st->prenom."</td><td>".sprintf("%2.2f %%",$st->pourc_gagnes)."</td><td>".$st->forme_indice."</td></tr>";
			}
		}
?>
</table></td></tr>

</table>

</div>

<? $menu->end(); ?>

