<map id="menu" name="menu">
	<area shape="rect" coords="10,10,330,100" accesskey="1" alt="Page d'accueil"   href="../www/home.php" />
<? if ($this->display_header_icon_menu) { ?>
	<area shape="rect" coords="625,10,680,70" accesskey="X" alt="Accès joueurs"    href="../www/joueurs.php" />
	<area shape="rect" coords="685,10,740,70" accesskey="E" alt="Accès equipes"    href="../www/equipes.php" />
	<area shape="rect" coords="745,10,795,70" accesskey="J" alt="Accès journées"   href="<?= $sess_context->championnat['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "../www/calendar.php" : "../www/journees.php" ?>" />
	<area shape="rect" coords="805,10,855,70" accesskey="C" alt="Accès classement" href="../www/stats_<?= $sess_context->isFreeXDisplay() ? "joueurs" : "equipes" ?>.php" />
	<area shape="rect" coords="865,10,915,70" accesskey="A" alt="Accès albums"     href="<?= $sess_context->isAdmin() ? "../www/albums_themes.php" : "../www/albums.php" ?>" />
	<area shape="rect" coords="925,10,975,70" accesskey="F" alt="Accès forum"      href="../www/forum.php" />
<? } ?>
</map>

<div>
	<div class="left">
			<img usemap="#menu" id="logo" src="../images/templates/defaut/fond_header.gif" alt="logo" />
	</div>
</div>
