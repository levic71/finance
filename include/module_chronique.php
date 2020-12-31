<? $msg = JKCache::getCache("../cache/chronique_home.txt", 900, "_FLUX_CHRONIQUE_HOME_"); ?>

<div id="chronique" class="home_left_div hotc">
	<div class="fond">
		<div class="titre"><span lang="en">La chronique !!!</span></div>
	</div>
	<div class="corps">

		<div class="texte">
		<a onmouseover="this.style.cursor='pointer'" href="../www/forum_message.php?id_msg=<?= $msg['id'] ?>&dual=6">

			<span class="title"><?= substr($msg['title'], 11) ?></span>
			<br />
			<span class="nom">Par <?= $msg['nom'] ?>, </span>
			<span class="date">le <?= Toolbox::mysqldate2date($msg['date']) ?></span>
			<br />
			<div class="message">&#171; <?= substr(strip_tags(str_replace('</p>', ' ', $msg['message'])), 0, 380) ?> ...&#187;</div>
		</a>
		</div>

		<div class="box">
			<div id="comment" class="allaccess" style="float: left;"><a href="../www/forum_message.php?id_msg=<?= $msg['id'] ?>&dual=6"><?= $msg['nb_reponses'] ?> commentaire(s)</a></div>
			<div class="allaccess" style="float: right;"><a href="../www/forum_message.php?id_msg=<?= $msg['id'] ?>&dual=6">Lire la suite</a></div>
		</div>

	</div>
</div>

