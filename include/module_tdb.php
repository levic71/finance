<? $tdb = JKCache::getCache("../cache/tdb_home.txt", 900, "_FLUX_TDB_HOME_"); ?>

<div id="tdb" class="home_left_div">
	<div class="fond">
		<div class="titre"><a id="titre1" href="../www/home.php" onclick="javascript:divSwapDisplay('box4', 'box3', 'titre1', '../images/templates/defaut/home_tdb_title.jpg', '../images/templates/defaut/home_whatsup_title.jpg'); return false;"><span lang="en">What's up</span></a></div>
	</div>
	<div class="corps">

	<div id="box3">
		<div style="margin: 20px 5px 5px 10px;">
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_tournois']     ?></div><div class="rightlabel" style="background:url('../images/jorkers/images/<?= $icon_type[2] ?>') no-repeat right 3px;">tournois</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_championnats'] ?></div><div class="rightlabel" style="background:url('../images/jorkers/images/<?= $icon_type[1] ?>') no-repeat right 3px;">championnats</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_libres']       ?></div><div class="rightlabel" style="background:url('../images/jorkers/images/<?= $icon_type[0] ?>') no-repeat right 3px;">libres</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_joueurs']      ?></div><div class="rightlabel">joueurs</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_equipes']      ?></div><div class="rightlabel">équipes</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_journees']     ?></div><div class="rightlabel">journées</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_matchs']       ?></div><div class="rightlabel">matchs joués</div></div>
			<div style="width: 250px;"><div class="leftlabel"><?= $tdb['nb_messages']     ?></div><div class="rightlabel">messages</div></div>
		</div>
	</div>

	<div id="box4">
		<div class="actuclip">
<?
	$today = getdate();
	$tstamp = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);

	$sas = new SQLActualitesServices();
	$actus = $sas->getActualitesAlaUne();
	foreach($actus as $item)
	{
		$z["day"]   = substr($item['date'], 8, 2);
		$z["month"] = substr($item['date'], 5, 2);
		$z["year"]  = substr($item['date'], 0, 4);
?>
		<div class="actubox">
			<a href="../www/actualites_jorkers.php">
			<span class="date<?= $today['mon'] == $z["month"] && (($today['mday'] - $z["day"]) < 5) ? " hot" : "" ?>"><?= ToolBox::mysqldate2date($item['date']) ?></span><br />
			<span class="texte"><?= $item['resume'] ?></span>
			</a>
		</div>
<?
	}
?>

		</div>
		<div class="allaccess" style="float: right; padding: 0px 5px 0px 0px;"><a href="../www/actualites_jorkers.php" accesskey="2" tabindex="4">Toutes les actualités</a></div>
	</div>

	</div>
</div>


