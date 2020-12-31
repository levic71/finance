<? $flux = JKCache::getCache("../cache/home_zone_libre.txt", -1, "_FLUX_ZONE_LIBRE_"); ?>
<div id="zonelibre" class="home_right_div">
	<div class="fond">
		<div id="titre12" class="titre"><span>Zone libre</span></div>
	</div>
	<div class="corps">
<?
	foreach($flux as $line)
		echo $line;
?>
	</div>
</div>
