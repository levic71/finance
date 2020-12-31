<DIV ID=nouveaux CLASS=home_left_div>
	<DIV CLASS=fond >
		<A onMouseOver="this.style.cursor='pointer'" HREF="../www/lesnouveaux.php">
			<DIV ID=titre3 CLASS=titre></DIV>
		</A>
	</DIV>
	<DIV CLASS=corps>
<?
    $last_created = JKCache::getCache("../cache/last_created_home.txt", 300, "_FLUX_LAST_CREATED_");
    $k = 0;
	$infos = "<TABLE BORDER=0 SUMMARY=\"Les nouveaux\" CELLPADDING=0 CELLSPACING=0>";
	foreach($last_created as $c)
	{
		$infos .= "<TR onmouseover=\"this.style.background='#CCCCCC';\" onmouseout=\"this.style.background='';\"><TD CLASS=date>".$c['dt_creation']."</TD><TD CLASS=type><IMG SRC=../images/jorkers/images/".$icon_type[$c['type']]." ALT=\"icon\"></TD><TD CLASS=nom><A HREF=\"championnat_acces.php?ref_champ=".$c['id']."\">".$c['nom']."</A></TD></TR>";
		if ($k++ > 12) break;
	}
	$infos .= "</TABLE>";
	echo $infos;
?>
	</DIV>
</DIV>
