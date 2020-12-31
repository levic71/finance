<?
global $libelle_type;
$lib_championnat = $this->display_championnat_info ? strtoupper($nom_championnat)." <span>[".$libelle_type[$sess_context->getChampionnatType()]."]</span>" : "";

$select_saison = "";
$select_amis   = "";

if ($this->display_toolbar_combo)
{
	$select_saison = "<select class=\"header_select\" name=\"choix_saisons\" onchange=\"javascript:csaison(this.value, '".$_SERVER["PHP_SELF"]."');\">";
	foreach($sess_context->saisons as $saison)
		$select_saison .= "<option value=\"".$saison['id']."\" ".($saison['active'] == 1 ? "selected=\"selected\"": "").">".$saison['nom']."</option>";
	$select_saison .= "</select>";
	$select_amis = "<select class=\"header_select\" name=\"choix_amis\" onchange=\"javascript:cchamp(this.value);\"><option value=\"0\" selected=\"selected\"> Championnats amis</option>";
	while(list($key, $value) = each($sess_context->friends))
		$select_amis .= "<option value=\"".$key."\">".$value."</option>";
	$select_amis .= "</select>";
}

?>

<div class="header_toolbar">

<ul class="header_left">
	<li>
		<? if ($lib_championnat == "") { ?>
			&nbsp;
		<? } else { ?>
			<a href="../www/championnat_home.php" class="tb_nom"><?= $lib_championnat ?></a>
		<? } ?>
	</li>
</ul>

<ul class="header_right">
<? if ($this->style == "slide_view_mode") { ?>
	<td class="slide_bouton"><a href="javascript:location.reload();"><img src="../images/slide_refresh.gif" alt="Refresh"   title="Refresh"   /></a></td>
	<td class="slide_bouton"><a href="javascript:stop_slide();">     <img src="../images/slide_stop.gif"    alt="Stop"      title="Stop"      /></a></td>
	<td class="slide_bouton"><a href="javascript:start_slide();">    <img src="../images/slide_start.gif"   alt="Start"     title="Start"     /></a></td>
	<td class="slide_bouton"><a href="javascript:change_slide(-1);"> <img src="../images/slide_prev.gif"    alt="Précédent" title="Précédent" /></a></td>
	<td class="slide_bouton"><a href="javascript:change_slide(1);">  <img src="../images/slide_next.gif"    alt="Suivant"   title="Suivant"   /></a></td>
	<td><font color="white">Temporisation : </font>
		<select name="slide_delai" onchange="javascript:change_delai(this.value);" class="header_select">
			<option value="1000">1 sec</option>
			<option value="5000" selected="selected">5 Sec</option>
			<option value="10000">10 sec</option>
			<option value="20000">20 sec</option>
		</select>
	</td>
	<td align="right"><font color="white">[Slide View Mode]</font></TD>
<? }

else

{ ?>
	<? if ($this->style == "home") { ?>
		<li class="header_create"><a href="../www/championnat_details.php?inscription=1" class="header_link">Créer un championnat</a></li>
		<!-- <li class="header_timeline"><a href="../www/timeline.php" class="header_link">Timeline</a></li> -->
		<!-- <li class="header_person"><a href="../www/partenaires.php" class="header_link">Partenaires</a></li> -->
	<? } ?>
	<li class="header_contact"><a href="../www/contacter.php" accesskey="7" tabindex="2" class="header_link">Contact</a></li>
	<li class="header_aide"><a class="header_link" accesskey="5" tabindex="3" href="../www/decouvrir.php" onclick="javascript:window.open('../www/decouvrir.php', 'faq', 'resizable=yes, scrollbars=yes, width=750, height=500, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=yes, status=no, menuBar=no');return false;">Aide</a></li>
	<li class="header_links"><a href="../www/links.php" class="header_link">Liens</a></li>
<? } ?>

<!--
	<p align="right" style="display:none;" class="header_select2">
		<div class="nls_flags" style="display:none; width:130px;">
			<div onclick="javascript:clang('fr');" class="nls_flag1"></div>
			<div onclick="javascript:clang('fr');" class="nls_flag2"></div>
			<div onclick="javascript:clang('fr');" class="nls_flag3"></div>
			<div onclick="javascript:clang('fr');" class="nls_flag4"></div>
			<div onclick="javascript:clang('fr');" class="nls_flag5"></div>
			<div onclick="javascript:clang('fr');" class="nls_flag6"></div>
		</div>
	</p>
-->
<? if ($this->display_header_icon_menu) { ?>
	<li class="header_select2"><?= $select_amis ?></li>
	<li class="header_select2"><?= $select_saison ?></li>
<? } ?>


	<li style="width: 30px;"><a href="../www/rss.php" class="rsslink"><span>Flux rss</span></a></li>
</ul>

</div>
