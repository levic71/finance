<?

require_once("inc_db.php");
include "cache_manager.php";
include "../www/SQLServices.php";

$db = dbc::connect();


ob_start();

?>

<? $most_active = JKCache::getCache("../cache/most_active_home.txt", -1, "_FLUX_MOST_ACTIVE_"); ?>

<div id="actifs" class="home_left_div">
	<div class="fond">
		<div class="titre"><a id="titre4" href="../www/home.php" onclick="javascript:divSwapDisplay('box1', 'box2', 'titre4', '../images/templates/defaut/home_nouveaux_title.jpg', '../images/templates/defaut/home_actifs_title.jpg'); return false;"><span>Les plus actifs</span></a></div>
	</div>
	<div class="corps">

	<div id="box1">
<?
	$k = 0;
	echo "<div>";
	foreach($most_active as $c)
	{
		if ($c['special'] == 0)
		{
			echo "<div class=\"icon_".$c['type']."\" onmouseover=\"this.style.backgroundColor='#333';\" onmouseout=\"this.style.backgroundColor='';\">";
			echo "<div class=\"nom\"><a href=\"championnat_acces.php?ref_champ=".$c['id']."\">".htmlspecialchars($c['nom'])."</a></div>";
			echo "<div class=\"points\"><b>".$c['points']."</b>&nbsp;&nbsp;points</div>";
			echo "</div>";
			if ($k++ > 8) break;
		}
	}
	reset($most_active);
	echo "<hr />";
	$k = 0;
	foreach($most_active as $c)
	{
		if ($c['special'] == 1)
		{
			echo "<div class=\"icon_".$c['type']."\" onmouseover=\"this.style.backgroundColor='#333';\" onmouseout=\"this.style.backgroundColor='';\">";
			echo "<div class=\"nom\"><a href=\"championnat_acces.php?ref_champ=".$c['id']."\">".htmlspecialchars($c['nom'])."</a></div>";
			echo "<div class=\"points\"><b>".$c['points']."</b>&nbsp;&nbsp;points</div>";
			echo "</div>";
			if ($k++ > 3) break;
		}
	}
	echo "</div>";
?>
	<div class="allaccess"><a href="../www/lesplusactifs.php">Tous les + actifs</a></div>
	</div>

	<div id="box2">
<?
    $last_created = JKCache::getCache("../cache/last_created_home.txt", -1, "_FLUX_LAST_CREATED_");
    $k = 0;
	echo "<div>";
	foreach($last_created as $c)
	{
		echo "<div onmouseover=\"this.style.backgroundColor='#333';\" onmouseout=\"this.style.backgroundColor='';\">";
		echo "<div class=\"date\">".$c['dt_creation']."</div>";
		echo "<div style=\"white-space:nowrap;\" class=\"nom icon_".$c['type']."\"><a href=\"championnat_acces.php?ref_champ=".$c['id']."\">".htmlspecialchars($c['nom'])."</a></div>";
		echo "<div class=\"points\"><span>".$c['points']."</span>&nbsp;points</div>";
		echo "</div>";
		if ($k++ > 12) break;
	}
	echo "</div>";
?>
	<div class="allaccess"><a href="../www/lesnouveaux.php">Tous les nouveaux</a></div>
	</div>

	</div>
</div>


<?

$contenuCache = ob_get_contents();
ob_end_clean();

$fd = fopen("include_actifs.php", "w");
if ($fd)
{
	fwrite($fd, $contenuCache);
	fclose($fd);
}

?>