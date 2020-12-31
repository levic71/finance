<?
	$id_sondage = 1;

	$choix[1]  = "AJAX AMSTERDAM";
	$choix[2]  = "ARSENAL";
	$choix[3]  = "BARCELONE";
	$choix[4]  = "BLACKBURN";
	$choix[5]  = "BRATISLAVA";
	$choix[6]  = "LA COROGNE";
	$choix[7]  = "LAZIO ROME";
	$choix[8]  = "LIVERPOOL";
	$choix[9]  = "MARSEILLE";
	$choix[10] = "MONACO";
	$choix[11] = "MONTPELLIER";
	$choix[12] = "SEDAN";
	$choix[13] = "SPARTA PRAGUE";
	$choix[14] = "SUNDERLAND";
	$choix[15] = "TOTTENHAM";
	$choix[16] = "VILLAREAL";

	$msg = JKCache::getCache("../cache/sondage_".$id_sondage.".txt", -1, "_FLUX_SONDAGE_");
?>

<div id="sondage" class="home_left_div">
	<div class="fond">
		<div id="titre11" class="titre"><span>Sondage</span></div>
	</div>
	<div class="corps" style="height: 75px; padding: 0px 5px 0px 5px; margin: 10px 10px 10px 25px; border: 1px dotted #CCCCCC;">

<?
if (isset($display_sondage) && $display_sondage != 0)
{
?>
		<div>
<span style="font-weight: bold;">Qui pour succéder à Donetsk le 17 juin <br /> aux Masters d'Alfortville ?</span>
<br />
<?
	$rep_sondage = array();
	@include "../cache/sondage_".$display_sondage.".php";

	$total = count($rep_sondage);

		echo "<ul style=\"padding: 0px; margin: 5px 0px 0px 30px;\">";
	if ($total == 0)
	{
		echo "<li>Pas encore de votes</li>";
	}
	else
	{
		// Cumul des réponses
		$tab = array();
		foreach($rep_sondage as $item)
		{
			if (!isset($tab[$item])) $tab[$item] = 0;
			$tab[$item]++;
		}

		// Calcul des % et tri
		$tri = array();
		$res = array();
		while(list($cle, $val) = each($tab))
		{
			$res[$cle] = array("id" => $cle, "valeur" => round(($val*100)/$total));
			$tri[$cle] = round(($val*100)/$total);
		}
		array_multisort($tri, SORT_DESC, $res);

		$i = 0;
		reset($res);
		foreach($res as $item)
		{
			echo "<li>".$choix[$item["id"]]." : ".$item["valeur"]." % </li>";
			if ($i > 1) break;
			$i++;
		}
	}
	echo "</ul>";
?>		</div> <?
}
else
{
?>
		<div>
<span style="font-weight: bold;">Qui pour succéder à Donetsk le 17 juin <br /> aux Masters d'Alfortville ?</span>
<br />
<div class="accesbox">
<select id="reponse1" name="reponse1">
<? while(list($cle, $val) = each($choix)) echo "<option value=\"".$cle."\">".$val."</option>"; ?>
</select>
<button class="ok" onclick="javascript:launchSondage('1', document.getElementById('reponse1'));"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button>
</div>
		</div>
		<div style="display: inline;">
			<div class="allaccess" style="float: right; padding: 10px 5px 0px 0px;"><a href="../www/home.php?display_sondage=1">Voir les résultats</a></div>
		</div>
<? } ?>

	</div>
</div>
