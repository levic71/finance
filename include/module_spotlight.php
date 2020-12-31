<? $shc = new JKStaticHTMLCache("../cache/html_module_spotlight.txt", 3600*2); ?>

<? if ($shc->hasExpired) {

$most_active = JKCache::getCache("../cache/most_active_home.txt", -1, "_FLUX_MOST_ACTIVE_");
$rand_value = rand(0, 15);
$z = 0;
foreach($most_active as $c)
{
	if ($z == $rand_value)
	{
		$real_id = $c['id'];
		break;
	}
	$z++;
}
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
	$best_teams_championnat = @$sgb->getBestTeamsByPoints();
else if ($type_champ == _TYPE_LIBRE_)
{
	$id_joueurs    = $sgb->getIdPlayers();
	$nom_joueurs   = $sgb->getPlayersName();
	$stats_joueurs = $sgb->getStatsPlayers();
}

$nb_col = 4;
if ($type_champ == _TYPE_TOURNOI_ || $type_champ == _TYPE_CHAMPIONNAT_) $nb_col = 5;

?>

<div id="spotlight" class="home_left_div">
	<div class="fond">
		<div class="titre"><span lang="en">Spotlight</span></div>
	</div>
	<div class="corps">

		<table border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr class="caption">
				<th colspan="<?= $nb_col ?>">
					SPOTLIGHT on<br /><span><?= $champ_info['championnat_nom'] ?></span><br />géré par <?= $champ_info['gestionnaire'] ?>
				</th>
			</tr>
<?
		if ($type_champ == _TYPE_TOURNOI_)
		{
			echo "<tr><th style=\"width: 10px;\">&nbsp;</th><th align=\"left\" style=\"width: 50%;\">Equipe</th><th align=\"right\">Nb points</th><th align=\"right\">Goal Avg</th><th align=\"right\">Moy class.</th></tr>";
			echo "</thead>";

			$k = 1;
			while(list($cle, $st) = each($best_teams_tournoi))
			{
				echo "<tr".($k % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$k."</td><td>".$st->nom."</td><td align=\"right\">".$st->tournoi_points."</td><td align=\"right\">".($st->diff > 0 ? "+" : "").$st->diff."</td><td align=\"right\">".$st->tournoi_classement_moy."</td></tr>";
				if ($k++ > 4) break;
			}
		}
		else if ($type_champ == _TYPE_CHAMPIONNAT_)
		{
			echo "<tr><th style=\"width: 10px;\">&nbsp;</th><th align=\"left\" style=\"width: 50%;\">Equipe</th><th align=\"right\">Nb points</th><th align=\"right\">Goal Avg</th></tr>";
			echo "</thead>";

			$k = 1;
			while(list($cle, $st) = each($best_teams_championnat))
			{
				echo "<tr".($k % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$k."</td><td>".$st->nom."</td><td align=\"right\">".$st->points."</td><td align=\"right\">".($st->diff > 0 ? "+" : "").$st->diff."</td></tr>";
				if ($k++ > 4) break;
			}
		}
		else
		{
			echo "<tr><th align=\"left\" style=\"width: 50%;\">Joueur</th><th align=\"right\">% matchs gagnés</th><th align=\"center\">Forme</th></tr>";
			echo "</thead>";

			$k = 1;
			shuffle($stats_joueurs);
			while(list($cle, $st) = each($stats_joueurs))
			{
				echo "<tr".($k % 2 == 1 ? " class=\"odd\"" : "" )."><td>".$st->nom." ".$st->prenom."</td><td>".sprintf("%2.2f %%",$st->pourc_gagnes)."</td><td>".$st->forme_indice."</td></tr>";
				if ($k++ > 4) break;
			}
		}
?>
		<tfoot>
			<tr><td colspan="<?= $nb_col ?>"><div class="allaccess" style="float: right;"><a href="championnat_acces.php?ref_champ=<?= $real_id ?>">Accès au championnat</a></div></td></tr>
		</tfoot>
		</table>

	</div>
</div>

<? } ?>

<? $shc->closeStaticHTMLCache(); ?>
