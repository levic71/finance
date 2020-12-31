<?

$rss = JKCache::getCache("../cache/rss_news.txt", 900, "_FLUX_NEWSFOOT_");

if (!isset($full_desc)) $full_desc = 0;
if (!isset($id_actu)) $id_actu = -1;

?>
<div id="news" class="home_center_div">
<? if ($full_desc == 0) { ?>
	<div class="fond">
		<div id="titre9" class="titre"><span>Actualités football</span></div>
	</div>
<? } ?>
	<div class="corps">
<?
	
	if ($full_desc != 0)
	{ ?>
<script type="text/javascript">
var id_actu = <?= $id_actu ?>;
</script>
<?	}
	
	$i = 0;
	$rss = JKCache::getCache("../cache/rss_news.txt", 900, "_FLUX_NEWSFOOT_");
	if (isset($rss->items) && count($rss->items) > 0)
	{
		foreach($rss->items as $item)
		{
			if ($full_desc != 1 && $i > 18) break;
			$title = $item['title'];
			$desc  = isset($item['description']) ? $item['description'] : "";
			$url   = isset($item['link']) ? $item['link'] : "#";
			echo "<div class=\"rss_info".($i%2 == 0 ? " pair" : "")."\">";
			echo "<div class=\"date\">".date("Y-m-d H:i", $item['date_timestamp'])."</div>";
	
			if ($full_desc == 0)
				echo "<div class=\"title\"><a href=\"../www/actualites_foot.php?full_desc=1&amp;id_actu=".$i."\">".$title."</a></div>";
			else
			{
				echo "<div class=\"title\"><a href=\"#\" onclick=\"changeActu(".$i.");\">".$title."</a></div>";
				echo "<div id=\"actu".$i."\" class=\"desc ".($i == $id_actu ? "" : "actu_none")."\"><div>".($desc == "" ? "&nbsp;" : ".".$desc)."</div>";
				echo "<div class=\"detail\"><a href=\"#\" onclick=\"javascript:window.open('".htmlspecialchars($url)."', '');\">Détail</a></div></div>";
			}
	
			echo "</div>";
			$i++;
		}
	}
	if ($full_desc != 1) {
?>
	<div class="allaccess"><a href="../www/actualites_foot.php">Toutes les news foot</a></div>
<? } ?>
	</div>
</div>
