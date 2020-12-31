<?

class menu
{
	var $style, $table, $href;
	var $left_boxes;
	var $right_boxes;
	var $display_header;
	var $display_footer;
	var $display_championnat_info;
	var $display_toolbar_combo;
	var $display_header_icon_menu;
	var $display_forum_global;
	var $display_calendar_global;
	var $display_left_menu;
	var $display_right_menu;

	function menu($style)
	{
		global $HTTP_SESSION_VARS;

		$this->display_header              = true;
		$this->display_footer              = true;
		$this->display_championnat_info    = true;
		$this->display_toolbar_combo       = true;
		$this->display_header_icon_menu    = false;
		$this->display_forum_global        = false;
		$this->display_calendar_global     = false;
		$this->display_left_menu           = true;
		$this->display_right_menu          = true;

		$this->style = $style;
		$this->left_boxes   = array();
		$this->right_boxes  = array();

		// while(list($variable, $valeur) = each($HTTP_SESSION_VARS)) { ToolBox::alert($variable." = ".$valeur); }

	}

	function isAdmin()
	{
		return ( $this->style == "admin" ? true : false );
	}

	function initOptions()
	{
		global $sess_context, $projet_version;

		$i = 0;
		if ($this->style == "home.old" )
		{
			$this->left_boxes[$i++] = "../include/module_edito.php";
			$this->left_boxes[$i++] = "../include/module_chronique.php";
			$this->left_boxes[$i++] = "../include/module_spotlight.php";
//			if (sess_context::isHomePhotosSet())  $this->left_boxes[$i++] = "../include/module_photo.php";
			$this->left_boxes[$i++] = "../include/module_lesaviezvous.php";
//			$this->left_boxes[$i++] = "../include/module_adsense.php";

			$this->right_boxes[$i++] = "../include/module_actifs.php";
			if (sess_context::isHomeSondageSet())  $this->right_boxes[$i++] = "../include/module_sondage.php";
			if (sess_context::isHomePartenariat()) $this->right_boxes[$i++] = "../include/module_partenariat.php";
			if (sess_context::isHomeZoneLibre())   $this->right_boxes[$i++] = "../include/module_zone_libre.php";
			$this->right_boxes[$i++] = "../include/module_tdb.php";
			$this->right_boxes[$i++] = "../include/module_forumgeneral.php";

			$this->display_championnat_info    = false;
			$this->display_forum_global        = true;
			$this->display_calendar_global     = true;
		}
		if ($this->style == "home.2009")
		{
			$this->left_boxes[$i++] = "../include/module_edito.php";
			$this->left_boxes[$i++] = "../include/module_forumgeneral.php";

			$this->right_boxes[$i++] = "../include/module_actifs.php";
			if (sess_context::isHomeSondageSet())  $this->right_boxes[$i++] = "../include/module_sondage.php";
			if (sess_context::isHomePartenariat()) $this->right_boxes[$i++] = "../include/module_partenariat.php";
			if (sess_context::isHomeZoneLibre())   $this->right_boxes[$i++] = "../include/module_zone_libre.php";

			$this->display_championnat_info    = false;
			$this->display_forum_global        = true;
			$this->display_calendar_global     = true;
		}
		else if ($this->style == "home")
		{
			$this->left_boxes[$i++] = "../include/module_edito.php";
			$this->left_boxes[$i++] = "../include/include_actifs.php";

			$this->right_boxes[$i++] = "../include/module_adsense.php";
			$this->right_boxes[$i++] = "../include/module_forumgeneral.php";

			$this->display_championnat_info    = false;
			$this->display_forum_global        = true;
			$this->display_calendar_global     = true;
		}
		else if ($this->style == "forum_access")
		{
			$this->left_boxes[$i++] = "../include/module_visuel.php";
			$this->left_boxes[$i++] = "../include/module_menu.php";

			$this->right_boxes[$i++] = "../include/module_calendar.php";
			$this->right_boxes[$i++] = "../include/module_france.php";
			$this->right_boxes[$i++] = "../include/module_newsletter.php";

			$this->display_championnat_info    = false;
			$this->display_forum_global        = false;
			$this->display_calendar_global     = true;
		}
		else if ($this->style == "championnat_home")
		{
			$this->left_boxes[$i++] = "../include/module_menu.php";
			$this->left_boxes[$i++] = "../include/module_forum.php";
			$this->left_boxes[$i++] = "../include/module_admin.php";
			$this->left_boxes[$i++] = "../include/module_newsletter.php";

			$this->right_boxes[$i++] = "../include/module_visuel.php";
			$this->right_boxes[$i++] = "../include/module_calendar.php";
			$this->right_boxes[$i++] = "../include/module_infoschamp.php";
			$this->right_boxes[$i++] = "../include/module_france.php";
//			$this->right_boxes[$i++] = "../include/module_pub.php";
		}
		else
		{
			$this->left_boxes[$i++] = "../include/module_menu.php";
			$this->left_boxes[$i++] = "../include/module_forum.php";
			$this->left_boxes[$i++] = "../include/module_admin.php";
			$this->left_boxes[$i++] = "../include/module_newsletter.php";

			$this->right_boxes[$i++] = "../include/module_adsense2.php";
		}

		if ($this->style == "planning")
		{
			$this->display_header = false;
		}
		if ($this->style == "planning" || $this->style == "slide_view_mode")
		{
			$this->display_left_menu  = false;
			$this->display_right_menu = false;
		}
		if ($this->style != "home" && $this->style != "slide_view_mode" && ($this->style == "full_access" || $sess_context->isChampionnatValide()))
		{
			$this->display_header_icon_menu = true;
		}
		if ($this->style == "slide_view_mode" || $this->style == "home" || $this->style == "forum_access" || $this->style == "accueil")
		{
			$this->display_toolbar_combo = false;
		}
	}

	function debut($nom_championnat="", $code_page = "-1", $onload = "")
	{
		global $sess_context, $projet_version;

		$this->initOptions();

		if ($this->style == "home")
		{
			TemplateBox::htmlBeginWithKeyPressedAction($code_page, $onload);
			$nom_championnat = "";
		}
		else
			TemplateBox::htmlBeginWithCodePage($code_page, $onload);

?>
<? if ($onload == "loadmap()") { ?>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAxSR6tl3WAafWf0ejSwIWqBQC-WnudriH7EPj_GGA9JFl0uvjGBT3OxrkgPvHGJPY1QSvqIL-jWvWAA" type="text/javascript"></script>
<script src="../js/gicons.js" type="text/javascript"></script>
<script src="../js/ghfcts.js" type="text/javascript"></script>
<? } ?>

<? if ($onload == "initEditor()" || $onload == "preload()") { ?>

<!--
<script type="text/javascript">
_editor_url = "../include/htmlarea/";
_editor_lang = "fr";
</script>
<script type="text/javascript" src="../include/htmlarea/htmlarea.js"></script>
<script type="text/javascript">
var editor = null;
function initEditor()
{
  editor = new HTMLArea("ta");

  // comment the following two lines to see how customization works
  editor.generate();
}
</script>
-->
<!-- tinyMCE -->
<script type="text/javascript" src="../include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
// Notice: The simple theme does not use all options some of them are limited to the advanced theme
tinyMCE.init({
		mode : "textareas",
		debug : false,
		content_css : "../include/tinymce/jscripts/tiny_mce/themes/advanced/css/mycontent5.css",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		plugins : "paste",
		plugins : "searchreplace",
		theme_advanced_buttons1 : "newdocument,cut,copy,paste,pastetext,pasteword,selectall,undo,redo,separator,bold,italic,underline,strikethrough,sub,sup,charmap,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent",
		theme_advanced_buttons2 : "fontselect,fontsizeselect,forecolor,backcolor,separator,hr,link,unlink,anchor,separator,search,replace,separator,cleanup,help",
		theme_advanced_buttons3 : "",
		theme : "advanced"
	});
</script>
<!-- /tinyMCE -->

<? } ?>

<div id="conteneur-r">
<div id="conteneur">

<?	if ($this->display_header) { ?>


	<div id="header">

<?	if ($this->style != "home" ) { ?>
<div id="accessible">
<ul>
	<li><a href="../www/politique_accessibilite.php" accesskey="3" tabindex="9">Politique d'accessibilité</a> </li>
	<li><a href="#centre" accesskey="6" tabindex="10">Aller au texte</a></li>
	<li class="dernier"><a href="#menugauche" accesskey="8" tabindex="11">Aller au menu</a></li>
</ul>
</div>
<? } ?>

		<? include ("../include/module_header.php"); ?>

<?		if (!(isset($no_ads468x60) && $no_ads468x60 == 1) && $this->style == "home")
		{
			echo "<div class=\"pub468x60\">";
			JKAds::getAds468x60header();
			echo "</div>";
		}
?>
	</div>

	<div id="toolbar">
		<? include ("../include/module_toolbar.php"); ?>
	</div>


<? } ?>


<?	if ($this->display_left_menu) { ?>

	<div id="gauche">
	<? $this->debut_left_box(); ?>
	</div>

<? } ?>


<?	if ($this->display_right_menu) { ?>

	<div id="droite">
	<? $this->debut_right_box(); ?>
	</div>

<? } ?>


	<div id="centre">

<!-- FENETRE AU CENTRE -->
<?
	}



	function end($option = "")
	{
		global $sess_context, $projet_version, $no_ads468x60;

		echo "<!-- FIN FENETRE AU CENTRE -->";

		if ($option == "signature" )
		{
			echo "<div class=\"signature\" style=\"margin: 10px 0px 0px 0px;\"><a href=\"http://www.jorkers.com/www/championnat_redirect.php?champ=".$sess_context->getRealChampionnatId()."\"><img src=\"http://www.jorkers.com/images/jorkers_signature.jpg\" /></a></div>";
		}

		if (!(isset($no_ads468x60) && $no_ads468x60 == 1) && $this->style != "home" )
		{
			echo "<div class=\"pub468x60\" style=\"background: transparent !important;\">";
			JKAds::getAds468x60footer();
			echo "</div>";
		}

		echo "</div>";

		if ($this->display_footer)
		{
			echo "<div id=\"footer\">";
			include ("../include/module_footer.php");
			echo "</div>";
		}
?>
		</div>
	</div>


<?	if ($this->style == "home" ) { ?>
<!-- AddToAny BEGIN -->
<a class="a2a_dd" href="http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Fwww.jorkers.com&amp;linkname="><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" border="0" alt="Share"/></a>
<script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.show_title = 1;
a2a_config.linkurl = "http://www.jorkers.com";
</script>
<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
<!-- AddToAny END -->
<? } ?>

	<div id="standardButtons">
		<a href="http://validator.w3.org/check?uri=referer" rel="external" title="Valid XHTML 1.0 Strict">
			<img src="../images/jorkers/images/button_xhtml.png" alt="Valid XHTML 1.0 Strict" />
		</a>
		<a href="http://jigsaw.w3.org/css-validator/check/referer" rel="external" title="Valid CSS" style="display: none;">
			<img src="../images/jorkers/images/button_css.png" alt="Valid CSS" />
		</a>
		<a href="http://www.contentquality.com/mynewtester/cynthia.exe?url1=http://www.jorkers.com/www/home.php" rel="external" title="Section 508">
			<img src="../images/jorkers/images/button_508.png" alt="Section 508" />
		</a>
	</div>

<?
		TemplateBox::htmlEnd();
	}


	function debut_left_box()
	{
		global $sess_context, $projet_version, $icon_type;

		foreach($this->left_boxes as $item)
		{
			echo "\n<div class=\"menu_gauche\">\n";
			include ($item);
			echo "\n</div>\n";
		}
	}


	function debut_right_box()
	{
		global $sess_context, $projet_version, $icon_type;

		foreach($this->right_boxes as $item)
		{
			echo "\n<div class=\"menu_droit\">\n";
			include ($item);
			echo "\n</div>\n";
		}
	}


}

?>
