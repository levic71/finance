<map id="geo_map" name="geo_map">
	<area shape="rect" coords="0,0,42,45"   alt="Carte de france" href="../www/carte_france.php" />
	<area shape="rect" coords="42,0,118,45" alt="Carte du monde"  href="../www/carte_monde.php" />
</map>

<div class="pave">
	<div class="titre"> <?= ToolBox::nls("LEFT_MENU_xxx", "Géolocalisation") ?> </div>
	<div class="corps">

		<a href="../www/carte_france.php">
			<img usemap="#geo_map" src="../images/map.gif" alt="Géolocalisation" />
		</a>

	</div>
</div>
