<div class="pave">
	<div class="titre"> <?= ToolBox::nls("LEFT_MENU_000", "Menu") ?> </div>
	<div class="corps" id="menugauche">
		<ul>
			<li class="alt2"><a class="cmd" accesskey="1" tabindex="300" href="../www/home.php"> <?= ToolBox::nls("LEFT_MENU_001", "Accueil") ?> </a></li>
			<li class="alt1"><a class="cmd" accesskey="I" tabindex="301" href="../www/championnat_details.php?inscription=1"> <?= ToolBox::nls("LEFT_MENU_002", "Inscription") ?> </a></li>
			<!-- <li class="alt2"><a class="cmd" accesskey="P" tabindex="302" href="../www/partenaires.php"> <?= ToolBox::nls("LEFT_MENU_003", "Partenaires") ?> </a></li> -->
			<li class="alt1"><a class="cmd" accesskey="G" tabindex="303" href="../www/forum.php?dual=5"> <?= ToolBox::nls("LEFT_MENU_006", "Forum général") ?> </a></li>
			<li class="alt2"><a class="cmd" accesskey="2" tabindex="304" href="../www/actualites_jorkers.php"> <?= ToolBox::nls("LEFT_MENU_000000", "What's up") ?> </a></li>
			<li class="alt1"><a class="cmd" accesskey="S" tabindex="305" href="../www/forum.php?dual=3"> <?= ToolBox::nls("LEFT_MENU_000000", "Le saviez-vous") ?> </a></li>
			<li class="alt2"><a class="cmd" accesskey="N" tabindex="306" href="../www/actualites_foot.php"> <?= ToolBox::nls("LEFT_MENU_004", "Actualité Foot") ?> </a></li>
			<li class="alt1"><a class="cmd" accesskey="K" tabindex="307" href="../www/sondage_detail.php?display_sondage=1"> <?= ToolBox::nls("LEFT_MENU_00000000000", "Sondages") ?> </a></li>
			<li class="alt1"><a class="cmd" accesskey="M" tabindex="308" href="../www/affiches.php"> <?= ToolBox::nls("LEFT_MENU_00000000000", "Affiches") ?> </a></li>
<? if ($sess_context->isAdmin()) { ?>
			<li class="alt1" style="background: orange"><a class="cmd" accesskey="X" tabindex="309" href="../admin/superuser_fcts.php"> <?= ToolBox::nls("LEFT_MENU_000000", "Admin console") ?> </a></li>
<? } ?>
		</ul>
	</div>
</div>