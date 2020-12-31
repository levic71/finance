<? if (1 == 0) { ?>

<iframe src="http://www.facebook.com/plugins/live_stream_box.php?app_id=107452429322746&amp;width=290&amp;height=250&amp;via_url&amp;always_post_to_friends=true&amp;locale=en_US" scrolling="no" frameborder="0" style="margin-top: 40px; border:none; overflow:hidden; width:290px; height:250px;" allowTransparency="true"></iframe>

<? } else { ?>

<div id="forum" class="home_right_div">
	<div class="fond">
		<div id="titre6" class="titre"><span>Forum général</span></div>
	</div>
	<div class="corps">
<?
	$today = getdate();
	$tstamp = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);

    $lstmsgs = JKCache::getCache("../cache/forum_home.txt", 900, "_FLUX_FORUM_HOME_");
	$i = 0;
	foreach($lstmsgs as $row)
	{
		$z["day"]   = substr($row['date'], 8, 2);
		$z["month"] = substr($row['date'], 6, 2);
		$z["year"]  = substr($row['date'], 0, 4);

		echo "<div class=\"link\" id=\"forum_".$i."\">";
		echo "<a class=\"glink\" href=\"javascript:launch('forum_message.php?dual=5&amp;id_msg=".$row['id']."#bottom');\">";
		echo "<span class=\"date\" >".Toolbox::mysqldate2smalldatetime($row['date'])."</span>";
		echo "<span class=\"auteur ".( $today['mon'] == $z["month"] && (($today['mday'] - $z["day"]) < 5) ? "hot" : "")."\">".$row['nom']."</span>";
		echo "</a>";
		echo "<br />";
		echo "<a class=\"glink\" href=\"javascript:launch('forum_message.php?dual=5&amp;id_msg=".$row['id']."#bottom');\">";
		echo "<span class=\"title\">".$row['title']."</span>";
		echo "</a>";
		echo "<br />";
		echo "<span class=\"texte\">".$row['message']."</span>";
		echo "</div>";
		$i++;
	}
	if ($i == 0) echo "<div class=\"forum_msg_titre\">&nbsp;Aucun message</div>";
?>
	</div>
	<div class="allaccess"><a href="../www/forum.php?dual=5">Accès au forum</a></div>
</div>

<? } ?>