<?

class TemplateBox
{

var $home;

function htmlBegin($onkeypressed = false, $no_cache = false, $code_page = "-1", $onload = "")
{
	global $championnat_home, $adminstration_page, $actualite_page, $refresh_page_forum;

	if (!isset($this->home)) $this->home = false;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta name="author"         content="Jorkers" />
<meta name="keywords"       content="Jorkers,gratuit,gestion,championnat,TOURNOI,Tournoi,Forum,jorkers,jorker,Foot 2x2,Jorky,online,en ligne,web,footris,Gratuit,Gestion,Championnat,Championship,classement,tournoi,statistique,joueur,équipe,journée,photo,forum,football,sport,divertissement,compétition,ami,pote,fun,futsal,Futsal Tournaments,management" />
<meta name="description"    content="Gestion de Championnats/tournois de Foot 2x2 - Tout est gratuit - Saisissez vos joueurs/équipes/matchs et automatiquement les classements et les statistiques sont calculés. Affichage et personnalisation de ces informations sur votre site grâce à la syndication des classements." />
<meta name="robots"         content="index, follow" />
<meta name="rating"         content="General" />
<meta name="distribution"   content="Global" />
<meta name="author"         content="contact@jorkers.com" />
<meta name="reply-to"       content="contact@jorkers.com" />
<meta name="owner"          content="contact@jorkers.com" />
<meta name="copyright"      content="&copy;Copyright : jorkers.com" />
<meta name="identifier-url" content="http://www.jorkers.com/" />
<meta name="category"       content="Sport, Football, Soccer, Foot 2x2, Futsal, Sport de balles et ballon, loisirs" />
<meta name="publisher"      content="Jorkers.com" />
<meta name="location"       content="Région parisienne" />
<meta name="revisit-after"  content="7 days" />
<meta http-equiv="Content-Language" content="fr-FX" />
<meta http-equiv="Content-Type"     content="text/html; charset=<?= sess_context::charset ?>" />
<meta http-equiv="pragma"           content="no-cache" />


<? if ($no_cache) { ?>
<meta http-equiv="Pragma" content="no-cache" />
<? } ?>

<? if (isset($refresh_page_forum) && $refresh_page_forum == 1)
	echo "<meta http-equiv=\"refresh\" content=\"300; URL=forum.php\" />"; ?>

<link rel="stylesheet" href="../css/HTMLTable.css" type="text/css" />
<link rel="stylesheet" href="../css/submenu.css" type="text/css" />
<link rel="stylesheet" href="../css/stylesv300.css" type="text/css" />
<link rel="stylesheet" href="../css/printv300.css" type="text/css" media="print" />

<link rel="stylesheet" href="../css/templatev400.css" type="text/css" />
<? if ($this->home) { ?>
<link rel="stylesheet" href="../css/homev400.css" type="text/css" />
<? } ?>
<link rel="stylesheet" href="../css/forumv300.css" type="text/css" />
<? if (isset($championnat_home) && $championnat_home == 1) { ?>
<link rel="stylesheet" href="../css/championnat_homev400.css" type="text/css" />
<? } ?>

<? if (isset($adminstration_page) && $adminstration_page == 1) { ?>
<style type="text/css">
.nospace, body, form {
	background   : #F1F1F1;
	padding: 2px;
}
</style><? } ?>

<? if (isset($actualite_page) && $actualite_page == 1) { ?>
<link rel="stylesheet" href="../css/actualitesv300.css" type="text/css" />
<? } ?>

<? if ($onload == "redirect") { ?>
<link rel="stylesheet" href="../css/redirectv300.css" type="text/css" />
<? } ?>

<link href="../images/H.ico" rel="shortcut icon" />
<title>Jorkers - Gestion de tournois/championnats de Foot 2x2, Futsal, Football</title>


<style type="text/css" media="all">
@import "../ajax3/thickbox/css/global.css";
</style>

<script type="text/javascript" src="../js/MochiKit/MochiKit.js"></script>
<script type="text/javascript" src="../js/MochiKit/sortable_tables.js"></script>

<script src="../ajax3/thickbox/js/jquery.js" type="text/javascript"></script>
<script src="../ajax3/thickbox/js/thickbox.js" type="text/javascript"></script>

<script src="../js/scriptsv300.js"  type="text/javascript"></script>
<script src="../js/submenu.js"      type="text/javascript"></script>

</head>

<body class="nospace" <?= ($onkeypressed) ? " onkeypress=\"enter_home(event)\"" : "" ?><?= $onload == "" || $onload == "redirect" ? "" : " onload=\"".$onload."\"" ?>>

<!-- FENETRE GLOBALE -->

<div id="div_info" class="info"></div>
<div id="popmenu" class="menuskin" onmouseover="clearhidemenu();highlightmenu(event,'on')" onmouseout="highlightmenu(event,'off');dynamichide(event)"></div>

<?
}

function htmlBeginWithKeyPressedAction($code_page = "-1", $onload = "")
{
	$this->home = true;
	TemplateBox::htmlBegin(true, false, $code_page, $onload);
}

function htmlBeginWithCodePage($code_page, $onload = "")
{
	$this->home = false;
	TemplateBox::htmlBegin(false, false, $code_page, $onload);
}

function htmlEnd($stats = true)
{
	global $sess_context;

?>

<!-- FIN FENETRE GLOBALE -->

<? if (!$sess_context->isSuperUser()) { ?>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-1509984-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>

<? } ?>

</body>
</html>
<?
}


}

?>
