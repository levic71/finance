<? $infos = JKCache::getCache("../cache/info_champ_".$sess_context->getRealChampionnatId()."_.txt", 600, "_FLUX_INFO_CHAMP_"); ?>

<div class="pave">
	<div class="titre"> <?= ToolBox::nls("LEFT_MENU_xxx", "Informations") ?> </div>
	<div class="corps">

<table cellspacing="0" cellpadding="0" summary="Infos championnat">
<tr><td class="lib_info">Lieu de pratique :</td></tr>
<tr><td class="libg_info"><?= $sess_context->championnat['lieu'] == "" ? "?" : $sess_context->championnat['lieu'] ?></td></tr>
<tr><td class="lib_info">Gestionnaire :</td></tr>
<tr><td class="libg_info"><?= $sess_context->championnat['gestionnaire'] == "" ? "?" : $sess_context->championnat['gestionnaire'] ?> <a href="../www/contacter.php?option=0" class="menu"><img src="../images/email.gif" alt="" /></a></td></tr>
<tr><td class="lib_info">Date de création :</td></tr>
<tr><td class="libg_info"><?= ToolBox::mysqldate2date($sess_context->championnat['dt_creation']) ?></td></tr>
<tr><td class="lib_info">Chiffres clés : </td></tr>
<tr><td><table width="100%" border="0" cellpadding="0" cellspacing="0" summary="Détails">
	<tr><td align="right" class="libg_info"><?= $infos['nb_saisons']  ?></td><td align="left" class="lib2_info">saisons</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_joueurs']  ?></td><td align="left" class="lib2_info">joueurs</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_equipes']  ?></td><td align="left" class="lib2_info">équipes</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_journees'] ?></td><td align="left" class="lib2_info">journées</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_matchs']   ?></td><td align="left" class="lib2_info">matchs</td></tr>
	<tr><td align="right" class="libg_info"><?= $infos['nb_messages'] ?></td><td align="left" class="lib2_info">messages</td>
<?	if (!$sess_context->isAdmin()) { ?>
	<td style="width: 15px;"><a class="menu" href="../www/championnat_details.php"><img src="../images/plus.gif" alt="Plus de détails" /></a></td>
<? } ?>
	</tr>
</table></td></tr>
<? if ($sess_context->isAdmin())
	echo "<tr class=\"menu\"><td class=\"item2\" colspan=\"2\"><a class=\"cmd\" href=\"../www/championnat_details.php\">Modifier</a></td></tr>";
?>

</table>

	</div>
</div>
