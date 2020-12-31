<?

class JKAds
{
	function onlyChars($string)
	{
		$ret = "";
		$strlength = strlen($string);

		for($i = 0; $i < $strlength; $i++)
		{
			if ((ord($string[$i]) >= 48 && ord($string[$i]) <= 57) ||
				(ord($string[$i]) >= 65 && ord($string[$i]) <= 90) ||
				(ord($string[$i]) >= 97 && ord($string[$i]) <= 122))
			{
				$ret .= "%".base_convert(ord($string[$i]), 10, 16);
			}
			else
				$ret .= "%".base_convert(ord($string[$i]), 10, 16);
		}

		return $ret;
	}




	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//					250 x 250
	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//
	function getAds250x250()
	{
		global $sess_context;

		$ret = "";
		if ($sess_context->isSuperUser()) return;
		$tirage = 0;

		if ($tirage == 0) { ?>

<?		} else if ($tirage > 70) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4460&type=b72&bnb=72" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4460&b=72" border="0" alt="Spreadshirt Designer" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<?		} else if ($tirage > 55) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4469&type=b8&bnb=8" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4469&b=8" border="0" alt="Photoweb" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<?		} else if ($tirage > 40) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4519&type=b77&bnb=77" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4519&b=77" border="0" alt="Snapfish par HP" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<script type="text/javascript" src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&js=1&site=4525&b=68&target=_blank&title=Adam+et+Eve" ></script><noscript><a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4525&type=b68&bnb=68" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4525&b=68" border="0" alt="Adam et Eve" width="250" height="250" /></a><br /></noscript>
<!-- END PARTNER PROGRAM -->

<?
		}

	}





	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//					250 x 250 HOME
	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//
	function getAds250x250_home()
	{
		global $sess_context;

		$ret = "";
		if ($sess_context->isSuperUser()) return;
		$tirage = rand(0, 100);
		$tirage = 0;

		if ($tirage == 0) { ?>

<?		} else if ($tirage > 70) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4460&type=b72&bnb=72" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4460&b=72" border="0" alt="Spreadshirt Designer" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<?		} else if ($tirage > 55) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4469&type=b8&bnb=8" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4469&b=8" border="0" alt="Photoweb" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<?		} else if ($tirage > 40) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4519&type=b77&bnb=77" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4519&b=77" border="0" alt="Snapfish par HP" width="250" height="250" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<script type="text/javascript" src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&js=1&site=4525&b=68&target=_blank&title=Adam+et+Eve" ></script><noscript><a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4525&type=b68&bnb=68" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4525&b=68" border="0" alt="Adam et Eve" width="250" height="250" /></a><br /></noscript>
<!-- END PARTNER PROGRAM -->

<?
		}

	}




	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//					468 x 60 FOOTER
	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//
	function getAds468x60footer()
	{
		global $sess_context;

		$ret = "";
		if ($sess_context->isSuperUser()) return;
		$tirage = rand(0, 100);
		$tirage = 0;

		if ($tirage == 0) { ?>

<? } else if ($tirage > 70) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4565&type=b125&bnb=125" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4565&b=125" border="0" alt="Meetic" width="468" height="60" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else if ($tirage > 55) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4469&type=b6&bnb=6" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4469&b=6" border="0" alt="Photoweb" width="468" height="60" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else if ($tirage > 40) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4519&type=b88&bnb=88" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4519&b=88" border="0" alt="Snapfish par HP" width="468" height="60" /></a><br />
<!-- END PARTNER PROGRAM -->

<?	} else  { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<script type="text/javascript" src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&js=1&site=4525&b=84&target=_blank&title=Adam+et+Eve" ></script><noscript><a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4525&type=b84&bnb=84" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4525&b=84" border="0" alt="Adam et Eve" width="468" height="60" /></a><br /></noscript>
<!-- END PARTNER PROGRAM -->

<?
		}

		echo $ret;
	}




	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//					120 x 600
	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//
	function getAds120x600()
	{
		global $sess_context;

		$ret = "";
		if ($sess_context->isSuperUser()) return;
		$tirage = rand(0, 100);
		$tirage = 0;

		if ($tirage == 0) { ?>

<? } else if ($tirage > 70) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4565&type=b134&bnb=134" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4565&b=134" border="0" alt="Meetic" width="120" height="600" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else if ($tirage > 55) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4469&type=b2&bnb=2" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4469&b=2" border="0" alt="Photoweb" width="120" height="600" /></a><br />
<!-- END PARTNER PROGRAM -->

<? } else if ($tirage > 40) { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4519&type=b13&bnb=13" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4519&b=13" border="0" alt="Snapfish par HP" width="120" height="600" /></a><br />
<!-- END PARTNER PROGRAM -->

<?	} else  { ?>

<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<script type="text/javascript" src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&js=1&site=4525&b=80&target=_blank&title=Adam+et+Eve" ></script><noscript><a href="http://clic.reussissonsensemble.fr/click.asp?ref=356294&site=4525&type=b80&bnb=80" target="_blank">
<img src="http://banniere.reussissonsensemble.fr/view.asp?ref=356294&site=4525&b=80" border="0" alt="Adam et Eve" width="120" height="600" /></a><br /></noscript>
<!-- END PARTNER PROGRAM -->

<?
		}

		echo $ret;
	}






	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//					468 x 60 HEADER
	// /////////////////////////////////////////////////////////////////////////////////////////////////
	//
	function getAds468x60header()
	{
		global $sess_context;

		$ret = "";

//		if ($sess_context->isSuperUser())
//			return;

		$tirage = rand(99, 99);
		$tirage = 9;

		if ($tirage == 0)
		{
?>
<!--Code à insérer CibleClick : Spreadshirt Designer --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=609576961&amp;friend_id=375258298&amp;banniere_id=17431" onclick="window.open(this.href); return false;"><img src="http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=609576961&amp;friend_id=375258298&amp;banniere_id=17431" alt="" /></a><!--Code à insérer CibleClick : Spreadshirt Designer -->
<? } else if ($tirage == 1) { ?>
<!--Code à insérer CibleClick : Meetic --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=1017608593&friend_id=375258298&banniere_id=3840" target="_blank"><img src=http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=1017608593&friend_id=375258298&banniere_id=3840  border=0 alt=></a><!--Code à insérer CibleClick : Meetic -->
<? } else if ($tirage == 2) { ?>
<!--Code à insérer CibleClick : Monsieur Casinos Bonus --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=66999920&friend_id=375258298&banniere_id=24295" target="_blank"><img src=http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=66999920&friend_id=375258298&banniere_id=24295  border=0 alt=></a><!--Code à insérer CibleClick : Monsieur Casinos Bonus -->
<? } else if ($tirage == 3) { ?>
<!--Code à insérer CibleClick : Best Offres --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=913296962&friend_id=375258298&banniere_id=27605" target="_blank"><img src=http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=913296962&friend_id=375258298&banniere_id=27605  border=0 alt=></a><!--Code à insérer CibleClick : Best Offres -->
<? } else if ($tirage == 4) { ?>
<!--Code à insérer CibleClick : aunomdelarose.fr --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=75007320&friend_id=375258298&banniere_id=22886" target="_blank"><img src=http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=75007320&friend_id=375258298&banniere_id=22886  border=0 alt=></a><!--Code à insérer CibleClick : aunomdelarose.fr -->
<? } else if ($tirage == 5) { ?>
<!--Code à insérer CibleClick : Thebestofsearch.com --><a href="http://www.cibleclick.com/cibles/clicks/symp.cfm?site_id=881849394&friend_id=375258298&banniere_id=27495" target="_blank"><img src=http://ad.cibleclick.com/cibles/banniere/symp.cfm?site_id=881849394&friend_id=375258298&banniere_id=27495  border=0 alt=></a><!--Code à insérer CibleClick : Thebestofsearch.com -->
<?
		}
		else if ($tirage == 6)
		{

?>
<!-- ValueClick Media 468x60 Banner CODE v1.0c for jorkers.com -->
<iframe src="http://media.fastclick.net/w/get.media?sid=29417&m=1&tp=1&d=f&v=1.0c&t=n&pageid=1" width=468 height=60 hspace=0 vspace=0 frameborder=0 marginheight=0 marginwidth=0 scrolling=no>
<a href="http://media.fastclick.net/w/click.here?sid=29417&m=1&pageid=1" target="_blank"><img width=468 height=60 src="http://media.fastclick.net/w/get.media?sid=29417&m=1&tp=1&d=s&v=1.0c&pageid=1" border=0></a>
</iframe>
<!-- ValueClick Media 468x60 Banner CODE v1.0c for jorkers.com -->
<?
		}
		else if ($tirage == 7)
		{ ?>
	<div id="headerswf">
<script src="../js/flashobject.js" type="text/javascript"></script>
<script type="text/javascript">
			// <![CDATA[
			var headerswf = new FlashObject("../swf/partenaire468x60.swf", "headerswf", "468", "60", "0", "#000000");
			headerswf.addParam("quality", "best");
			headerswf.addParam("salign", "t");
			headerswf.addParam("scale", "noscale");
			headerswf.write("headerswf");
			// ]]>
</script>
	</div>
<?		}
		else if ($tirage == 8)
		{ ?>
	<div id="headerswf" style="background: black;padding: 15px 0px 10px 0px;">
<script src="../js/flashobject.js" type="text/javascript"></script>
<script type="text/javascript">
			// <![CDATA[
var ufo = new FlashObject("../swf/ticker.swf", "headerswf", "468", "35", "0", "#000000");
ufo.addParam("quality", "best");
ufo.addParam("wmode", "transparent");
ufo.addParam("salign", "t");
ufo.addParam("scale", "noscale");
var vars = "";
vars += "baseURL=.&";
vars += "clickURL=http://www.jorkers.com/&";
vars += "clickLABEL=Get Your Led&";
vars += "delaySpeed=30&";
vars += "type=0&";
vars += "transID=0&"; // 0: left; 1: right; 2: up; 3: down
vars += "str=<?= JKAds::onlyChars("*** NOUVEAU *** TOI AUSSI *** PERSONNALISE TON TICKER *** SUR TON CHAMPIONNAT ***") ?>&";
vars += "bgColor=0&";
vars += "symbolX=30&"; // Nb lettres
vars += "w=3&"; // Dot width
vars += "h=3&"; // Dot height
vars += "pointType=star&"; // point, star, rect
vars += "glowColor=39372&";
vars += "designNum=5&"; // 1: green, 2: red, 3:blue, 4: white, 5: aqua, 6:fushia
ufo.addVariable("xmlDataPath", vars);
ufo.write("headerswf");
// ]]>
			// ]]>
</script>
	</div>
<?	}
		else if ($tirage == 9)
		{ ?>
	<div id="headerswf" style="background: black;padding: 4px 0px 4px 0px;">
		<? include ("../include/module_sms.php"); ?>
	</div>
<?	}
		else
		{ ?>
<!-- Begin Ad42_SellerTag -->
<style type="text/css">
.adHeadline {font: bold 10pt Arial; text-decoration: underline; color: darkblue;}
.adText {font: normal 10pt Arial; text-decoration: none; color: black;}
</style>
<div style="height: 68px; width: 468px; background: #DDDDDD;">
<script type="text/javascript" src="http://adserver.ad42.com/printZone.aspx?idz=1047&amp;newwin=1">
</script>
<div><a class="adHeadline" href="http://www.ad42.com/zone.aspx?idz=1047&ida=-1" target="_blank">
Votre publicit&eacute; ici ?</a></div>
</div>
<!-- End Ad42_SellerTag -->
<?		}

		echo $ret;
	}




}

?>
