<? $flux = JKCache::getCache("../cache/forum_champ_".$sess_context->getRealChampionnatId()."_.txt", 600, "_FLUX_FORUM_"); ?>

<div class="pave">
	<div class="titre"> <?= ToolBox::nls("LEFT_MENU_xxx", "Derniers msgs") ?> </div>
	<div class="corps">

	<table cellpadding="0" cellspacing="0" class="menu_droit" summary="Last msgs">

	<tr><td><div class="forum_container">
<?
	$i = 0;
	foreach($flux as $row)
	{
		echo "<div class=\"forum_container_link forum_msg\" onmouseover=\"changeStyle3(this);\" onmouseout=\"changeStyle4(this);\" onclick=\"javascript:launch('forum_message.php?id_msg=".$row['id']."#bottom');\">";
		echo "<div class=\"forum_msg_date\">".Toolbox::mysqldate2smalldatetime($row['date'])."</div>";
		echo "<div class=\"forum_msg_auteur\">".$row['nom']."</div>";
		echo "<div class=\"forum_msg_titre\">".$row['title']."</div>";
		echo "<div class=\"forum_msg_texte\">".$row['message']."</div>";
		echo "</div>";
		$i++;
	}
	if ($i == 0) echo "<div class=\"forum_msg_titre\">&nbsp;Aucun message</div>";
		
	echo "</div></td></tr>";

	if (!(!$this->display_forum_global && $sess_context->isChampionnatValide()))
		echo "<tr class=\"menu2\"><td colspan=\"2\" CLASS=\"menu\"><a href=\"../www/forum.php?option=general\" class=\"menu\"><b>Accès</b></a></td></tr>";
?>

	</table>

	</div>
</div>
