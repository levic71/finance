<div>
<? if ($sess_context->isAdmin()) { ?>
	<div><a href="../admin/superuser_exit.php"><img src="../images/templates/defaut/acces_deconnexion.gif" alt="Déconnexion" /></a></div>
<? } else { ?>
	<div><a href="../www/ajaxLogin.htm?height=130&amp;width=300" title="ajax" class="thickbox"><img src="../images/templates/defaut/acces_admin.gif" alt="Administration" /></a></div>
<? } ?>
</div>

<div>
	<div><a href="../www/championnat_details.php?inscription=1" title="creer un championnat"><img src="../images/templates/defaut/acces_create.gif" alt="Créer un championnat" /></a></div>
</div>

<? if (false) { ?>
<div class="pave">
	<div class="titre"> <?= ToolBox::nls("LEFT_MENU_xxx", "Administration") ?> </div>
	<div class="corps">

<iframe STYLE="margin: 0px;" frameborder="0" SRC="../www/administration.php" WIDTH="115" HEIGHT="60" scrolling="no">
no frame
</iframe>

<a href="../www/administration.php">
admin
</a>
	</div>
</div>
<? } ?>
