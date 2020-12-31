<?

global $sondage_sessionid, $display_sondage, $force_display;

if (!(isset($display_sondage) && $display_sondage == 1) && sess_context::isHomeSondageQuestionSet())
{ ?>
<div id="sondage" class="home_left_div">
	<div class="fond">
		<div id="titre11" class="titre"><span>Sondage</span></div>
	</div>
	<div class="corps" style="height: 70px; margin: 5px 10px 10px 10px;">
		<img src="../images/jorkers/images/lightbulb.png" />
		Une idée de sondage, contacter nous par mail en précisant la question et les réponses attendues.
		<div style="display: inline;">
			<div class="allaccess" style="float: right; padding: 5px 5px 0px 0px;"><a href="../www/home.php?display_sondage=<?= $sondage_encours ?>">Contacter nous</a></div>
		</div>
	</div>
</div>
<?	} else {

	$sondage_encours = 7;
	if (isset($force_display) && $force_display > 0) $sondage_encours = $force_display;

	if (!isset($sondage_detail)) $sondage_detail = 0; // Par defaut, on affiche le résultat

	$filecachephp = "../cache/sondage_".$sondage_encours.".php";
	$filecachetxt = "../cache/sondage_".$sondage_encours.".txt";

	$id_sondage = 1;
	$sondage_display[$id_sondage]  = 1;
	$datedeb_sondage[$id_sondage]  = "10/06/2006";
	$datefin_sondage[$id_sondage]  = "17/06/2006";
	$question_sondage[$id_sondage] = "Qui pour succéder à Donetsk <br /> le 17 juin aux Masters d'Alfortville ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "AJAX AMSTERDAM";
	$choix[$id_sondage][$i++] = "ARSENAL";
	$choix[$id_sondage][$i++] = "BARCELONE";
	$choix[$id_sondage][$i++] = "BLACKBURN";
	$choix[$id_sondage][$i++] = "BRATISLAVA";
	$choix[$id_sondage][$i++] = "LA COROGNE";
	$choix[$id_sondage][$i++] = "LAZIO ROME";
	$choix[$id_sondage][$i++] = "LIVERPOOL";
	$choix[$id_sondage][$i++] = "MARSEILLE";
	$choix[$id_sondage][$i++] = "MONACO";
	$choix[$id_sondage][$i++] = "MONTPELLIER";
	$choix[$id_sondage][$i++] = "SEDAN";
	$choix[$id_sondage][$i++] = "SPARTA PRAGUE";
	$choix[$id_sondage][$i++] = "SUNDERLAND";
	$choix[$id_sondage][$i++] = "TOTTENHAM";
	$choix[$id_sondage][$i++] = "VILLAREAL";
	$analyse[$id_sondage] = "Eh oui, les favoris ne sont pas toujours là où on les attend !!!";

	$id_sondage = 2;
	$sondage_display[$id_sondage]  = 1;
	$datedeb_sondage[$id_sondage]  = "11/06/2006";
	$datefin_sondage[$id_sondage]  = "09/07/2006";
	$question_sondage[$id_sondage] = "Qui pour succéder au Brésil <br /> le 09 juillet à Berlin ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Allemagne";
	$choix[$id_sondage][$i++] = "Angleterre";
	$choix[$id_sondage][$i++] = "Angola";
	$choix[$id_sondage][$i++] = "Arabie Saoudite";
	$choix[$id_sondage][$i++] = "Argentine";
	$choix[$id_sondage][$i++] = "Australie";
	$choix[$id_sondage][$i++] = "Brésil";
	$choix[$id_sondage][$i++] = "Corée du Sud";
	$choix[$id_sondage][$i++] = "Costa Rica";
	$choix[$id_sondage][$i++] = "Côte d'Ivoire";
	$choix[$id_sondage][$i++] = "Croatie";
	$choix[$id_sondage][$i++] = "Equateur";
	$choix[$id_sondage][$i++] = "Espagne";
	$choix[$id_sondage][$i++] = "France";
	$choix[$id_sondage][$i++] = "Ghana";
	$choix[$id_sondage][$i++] = "Iran";
	$choix[$id_sondage][$i++] = "Italie";
	$choix[$id_sondage][$i++] = "Japon";
	$choix[$id_sondage][$i++] = "Mexique";
	$choix[$id_sondage][$i++] = "Paraguay";
	$choix[$id_sondage][$i++] = "Pays Bas";
	$choix[$id_sondage][$i++] = "Pologne";
	$choix[$id_sondage][$i++] = "Portugal";
	$choix[$id_sondage][$i++] = "République Tchèque";
	$choix[$id_sondage][$i++] = "Serbie et Monténégro";
	$choix[$id_sondage][$i++] = "Suède";
	$choix[$id_sondage][$i++] = "Suisse";
	$choix[$id_sondage][$i++] = "Togo";
	$choix[$id_sondage][$i++] = "Trinidad & Tobago";
	$choix[$id_sondage][$i++] = "Tunisie";
	$choix[$id_sondage][$i++] = "Ukraine";
	$choix[$id_sondage][$i++] = "USA";
	$analyse[$id_sondage] = "France-Portugal en finale, une prochaine fois j'espère ...";

	$id_sondage = 3;
	$sondage_display[$id_sondage]  = 2;
	$datedeb_sondage[$id_sondage]  = "27/07/2006";
	$datefin_sondage[$id_sondage]  = "17/08/2006";
	$question_sondage[$id_sondage] = "Gizou aura t-il trouvé une équipe pour la saison 2006-2007 du Master d'Alfortville ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Non";
	$choix[$id_sondage][$i++] = "Oui";
	$analyse[$id_sondage] = "Le résultat est marrant, c'est du 50-50, merci Jean-Pierre, mais je pense que Gizou sera belle et bien là !!!";

	$id_sondage = 4;
	$sondage_display[$id_sondage]  = 2;
	$datedeb_sondage[$id_sondage]  = "18/08/2006";
	$datefin_sondage[$id_sondage]  = "31/08/2006";
	$question_sondage[$id_sondage] = "Le relookage du Jorkers.com est-il réussit ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Non";
	$choix[$id_sondage][$i++] = "Oui";
	$analyse[$id_sondage] = "Une grande majorité d'entre vous trouve que c'est mieux, j'en suis content. N'hésitez pas à utiliser le forum général pour me faire part de vos remarques.";

	$id_sondage = 5;
	$sondage_display[$id_sondage]  = 1;
	$datedeb_sondage[$id_sondage]  = "23/09/2006";
	$datefin_sondage[$id_sondage]  = "31/09/2006";
	$question_sondage[$id_sondage] = "Comment avez vous connu le Jorkers.com ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Le webmaster est un ami";
	$choix[$id_sondage][$i++] = "Sur internet";
	$choix[$id_sondage][$i++] = "Dans un club";
	$choix[$id_sondage][$i++] = "Dans la presse";
	$choix[$id_sondage][$i++] = "A la TV/Radio";
	$analyse[$id_sondage] = "Pas encore d'analyse.";

	$id_sondage = 6;
	$sondage_display[$id_sondage]  = 2;
	$datedeb_sondage[$id_sondage]  = "18/10/2007";
	$datefin_sondage[$id_sondage]  = "30/11/2007";
	$question_sondage[$id_sondage] = "<blink>_&#175;_&#175;_&#175;_&#175;_ I NEED YOU _&#175;_&#175;_&#175;_&#175;_&#175;_</blink><br /><br />Pronostiquer sur la Ligue1 du foot avec le jorkers.com, ça vous tente ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Oui, ça serait génial";
	$choix[$id_sondage][$i++] = "Oui, pourquoi pas";
	$choix[$id_sondage][$i++] = "Bof, à voir";
	$choix[$id_sondage][$i++] = "Non, pas vraiment";
	$analyse[$id_sondage] = "Pas encore d'analyse.";

	$id_sondage = 7;
	$sondage_display[$id_sondage]  = 2;
	$datedeb_sondage[$id_sondage]  = "10/10/2009";
	$datefin_sondage[$id_sondage]  = "30/11/2009";
	$question_sondage[$id_sondage] = "<blink>_&#175;_&#175; ATTENTION &#175;_&#175;_</blink><br /><br />Une question  simple, qui utilse un iPhone ?";
	$i = 1;
	$choix[$id_sondage][$i++] = "Oui, c'est génial";
	$choix[$id_sondage][$i++] = "Non, mais ça me tente grave";
	$choix[$id_sondage][$i++] = "Non, c'est trop chère";
	$choix[$id_sondage][$i++] = "Non, j'suis pas un geek !";
	$analyse[$id_sondage] = "Pas encore d'analyse.";

	$msg = JKCache::getCache($filecachetxt, -1, "_FLUX_SONDAGE_");
?>

<? if ($sondage_detail != 1) { ?>
<div id="sondage" class="home_left_div">
	<div class="fond">
		<div id="titre11" class="titre"><span>Sondage</span></div>
	</div>
<? } else { ?>
<div id="sondage" class="home_left_div">
<? } ?>
	<div class="corps" style="<?= $sondage_detail != 1 ? "height:85px;" : "" ?> margin: 5px 10px 10px 10px;">

<?

// Si on a déjà répondu on affiche le résultat
$rep_sondage = array();

if (file_exists($filecachephp))
	include $filecachephp;

if (isset($sondage_sessionid) && isset($rep_sondage[$sondage_sessionid]))
	$display_sondage = 1;

if (isset($display_sondage) && $display_sondage != 0)
{
?>
		<div>
<span style="font-weight: bold; color: black;"><?= $datedeb_sondage[$sondage_encours]." - ".str_replace("<br />", " ", $question_sondage[$sondage_encours]) ?></span>
<br />
<?
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
			echo "<li>".$choix[$sondage_encours][$item["id"]]." : ".$item["valeur"]." % </li>";
			if ($sondage_detail == 0 && $i > 1) break;
			$i++;
		}
	}
	echo "</ul>";

	if ($sondage_detail == 1)
	{
		echo "<br /> Nombre de votants : ".count($rep_sondage)."<br /><br />";

		if (isset($analyse[$sondage_encours]))
			echo "<span style=\"font-weight: bold;\">Analyse:</span><ul><li>".$analyse[$sondage_encours]."</li></ul><br />";

		echo "<span style=\"font-weight: bold;\">Les autres sondages:</span>";
		echo "<ul>";
		krsort($question_sondage);
		while(list($cle, $val) = each($question_sondage))
		{
			if ($cle != $sondage_encours)
				echo "<li><a href=\"sondage_detail.php?display_sondage=1&amp;force_display=".$cle."\">".$datedeb_sondage[$cle]." - ".str_replace("<br />", " ", $question_sondage[$cle])."</a></li>";
		}
		echo "</ul>";
	}
?>
		</div>

<? if ($sondage_detail != 1) { ?>
		<div style="display: inline;">
			<div class="allaccess" style="float: right; padding: 0px 5px 0px 0px;"><a href="../www/sondage_detail.php?display_sondage=2">+ de détail</a></div>
		</div>
<?
	}
}
else
{
?>
		<div>
<span style="font-weight: bold; color: black;"><?= $datedeb_sondage[$sondage_encours]." - ".$question_sondage[$sondage_encours] ?></span>
<br />
<div class="accesbox">
<? if ($sondage_display[$sondage_encours] == 1) { ?>
<select id="reponse1" name="reponse1">
<? while(list($cle, $val) = each($choix[$sondage_encours])) echo "<option value=\"".$cle."\">".$val."</option>"; ?>
</select>
<? } ?>
<? if ($sondage_display[$sondage_encours] == 2) { ?>
<ul>
<?
	$x = 0;
	while(list($cle, $val) = each($choix[$sondage_encours]))
		echo "<li style=\"float:left;width: 160px;list-style-type: none;padding-right: 20px;\"><input style=\"border: 0px dashed black; background: transparent;\" onclick=\"document.forms[0].reponse1.value=".$cle.";\" type=\"radio\" ".($x++ == 0 ? "checked=\"checked\"" : "")." id=\"reponse1\" name=\"reponse1\" value=\"".$cle."\" /><div style=\"padding: 3px 0px 0px 3px;\">".$val."</div></li>";
?>
</ul>
<? } ?>
<button class="ok" onclick="javascript:launchSondage('<?= $sondage_encours ?>', document.getElementById('reponse1'), <?= $sondage_display[$sondage_encours] ?>);"><img src="../images/templates/defaut/bt_votez.gif" alt="" /></button>
</div>
		</div>

		<div style="display: inline;">
			<div class="allaccess" style="float: right; padding: 5px 5px 5px 0px;"><a href="../www/home.php?display_sondage=<?= $sondage_encours ?>">Voir les résultats</a></div>
		</div>
<? } ?>

	</div>
</div>

<? } ?>
