<? $msg = JKCache::getCache("../cache/photo_home.txt", 900, "_FLUX_PHOTO_HOME_"); ?>

<div id="photo_sem" class="home_left_div">
	<div class="fond">
		<div id="titre5" class="titre"><span>Photo de la semaine</span></div>
	</div>
	<div class="corps">
		<a onmouseover="this.style.cursor='pointer'" href="../www/forum.php?general=0&amp;dual=2">
			<img src="<?= str_replace('FORUM', 'xFORUM', $msg['image']) ?>" height="120" width="120" style="float: left; padding: 0px 0px 5px 0px;" alt="" />
		</a>
		<div>
			<h1>
			 Envoyez-moi vos photos, vidéos, animations flash de sport par mail pour constituer une galerie fun multimédia, soyez créatif ... 
			</h1>
		</div>
		<div style="display: inline;">
			<div class="allaccess" style="float: right; padding: 10px 0px 0px 0px;"><a href="../www/forum.php?dual=2">Galerie</a></div>
			<div class="allaccess" style="float: right; padding: 10px 5px 0px 0px;"><a href="mailto:contact@jorkers.com">Participer</a></div>
		</div>
	</div>
</div>
