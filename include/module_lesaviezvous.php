<div id="suggest" class="home_left_div">
	<div class="fond">
		<div id="titre2" class="titre"><span>Le saviez-vous</span></div>
	</div>
	<div class="corps">
<?
    $lesaviezvous = JKCache::getCache("../cache/lesaviezvous_home.txt", 900, "_FLUX_LE_SAVIEZ_VOUS_");
	foreach($lesaviezvous as $item) echo $item;
?>
	</div>
	<div class="allaccess"><a href="../www/forum.php?general=0&amp;dual=3">Toutes les actuces</a></div>
</div>
