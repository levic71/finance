<?

$ver     = "2.25.1".(sess_context::isLocalHost() ? ".".time() : "");

$wrapper = isset($wrapper) ? $wrapper : false;
$chpwd   = isset($chpwd)   ? $chpwd   : false;
$id_msg  = isset($id_msg)  ? $id_msg  : 0;

$theme   = $chp['theme'] == 1 && $sess_context->getRealChampionnatId() == 8 ? rand(1, count($libelle_theme)) : $chp['theme'];

?>

<meta name="keywords"       content="jorkers,gratuit,gestion,championnat,tournoi,jorker,gestionnaire,multi sport,foot 2x2,jorky,championship,classement,statistique,joueur,équipe,journée,football,sport,compétition,futsal,tournaments,management" />
<meta name="description"    content="Gestionnaire de Championnats et de Tournois multi sports gratuit pour PC, Smartphone et Tablette" />
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
<meta name="location"       content="Paris" />
<meta name="revisit-after"  content="7 days" />
<meta http-equiv="Content-Language" content="fr-FX" />
<meta http-equiv="Content-Type"     content="text/html; charset=<?= sess_context::charset ?>" />
<meta http-equiv="pragma"           content="no-cache" />

<? if (!$wrapper) { ?>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=0.4, maximum-scale=1.0" />
<? } ?>

<link rel="apple-touch-icon" href="img/webclip.png" />
<link rel="stylesheet" type="text/css" media="screen" href="css/fonts/font<?= true ? 5 : $chp['logo_font'] ?>.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/jk.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/grid.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/components.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/calendar.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/dragdealer.css<?= "?ver=".$ver ?>" />
<!-- <link rel="stylesheet" type="text/css" media="screen" href="../mdl/material.min.css<?= "?ver=".$ver ?>" /> -->
<link rel="stylesheet" type="text/css" media="screen" href="../mdl/material.purple-blue.min.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="../mdl/socialglyphs-regular.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="../mdl/styles.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="css/theme<?= $theme ?>.css<?= "?ver=".$ver ?>" />

<? if (!$wrapper) { /* Premiere ligne jamais prise en compte car viewport=900 */ /* -webkit-min-device-pixel-ratio: 2 pour ipad2 */ ?>
<link rel="stylesheet" media="all and (max-device-width: 480px)" href="css/iphone.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" media="all and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" href="css/ipad-portrait.css<?= "?ver=".$ver ?>" />
<link rel="stylesheet" media="all and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" href="css/ipad-landscape.css<?= "?ver=".$ver ?>" />
<? } ?>

<? if ($wrapper) { ?>
<link rel="stylesheet" type="text/css" media="screen" href="css/wrapper.css<?= "?ver=".$ver ?>" />
<? } ?>

<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="yes" />
<link rel="icon" type="image/png" sizes="225x225" href="/images/android-desktop.png" />

<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="apple-mobile-web-app-title" content="Jorker's" />
<link rel="apple-touch-icon-precomposed" href="/images/ios-desktop.png" />

<!-- Tile icon for Win8 (144x144 + tile color) -->
<meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png" />
<meta name="msapplication-TileColor" content="#3372DF" />

<link rel="icon" type="image/png" href="favicon.ico" />
<link rel="apple-touch-icon" href="img/webclip.png" />
<link rel="apple-touch-icon" sizes="72x72" href="img/webclip72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="img/webclip114.png" />
<link rel="apple-touch-icon-precomposed" href="img/webclip.png">
<link rel="apple-touch-startup-image" href="img/webclip.png" />

<script type="text/javascript" src="js/jxs_compressed.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/raphael-min.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/dragdealer.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/trianglify.min.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/popup.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/jk.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/store.min.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/components.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/calendar.js<?= "?ver=".$ver ?>"></script>
<script type="text/javascript" src="js/ftj.js<?= "?ver=".$ver ?>"></script>
<script>
window.onload = function() {
	window.scrollTo(0,0);
	go({action: 'slidebar', id:'slidebar', url:'navslidebar.php'});
	go({ action: 'login_panel', id: 'login_panel', url: 'login_panel.php' });
	<? if ($wrapper) { ?>
	mm({action: 'days'});
	<? } else if ($chpwd) { 
		if (wrapper::isChPwdValid($chpwd)) { ?>
			mm({action: 'chpwd', params: '<?= $chpwd ?>'});
		<? } else { ?> 
			mm({action: 'leagues'}); $aMsg({msg : 'La demande invalide' });
		<? } ?> 
	<? } else if ($id_msg > 0) { ?>
	go({action: 'tchat', id:'main', url:'edit_tchat.php?idp=<?= $id_msg ?>'});
	<? } else if (isset($idp) && is_numeric($idp) && $idp > 0) { ?>
	mm({action: 'stats', idp: '<?= $idp ?>'});
	<? } else if (isset($idt) && is_numeric($idt) && $idt > 0) { ?>
	mm({action: 'stats', idt: '<?= $idt ?>'});
	<? } else if (isset($idj) && is_numeric($idj) && $idj > 0) { ?>
	mm({action: 'matches', idj: '<?= $idj ?>', date: '<?= $date ?>', name: '<?= $name ?>'});
	<? } else if (isset($idc) && is_numeric($idc) && $idc == sess_context::INVALID_CHAMP_ID_PROFIL) { ?>
	mm({action: 'myprofile'});
	<? } else if (isset($idc) && is_numeric($idc) && $idc == sess_context::INVALID_CHAMP_ID_LOGIN) { ?>
	mm({action: 'login'});
	<? } else if (isset($idc) && is_numeric($idc) && $idc == sess_context::INVALID_CHAMP_ID_HOME) { ?>
	mm({action: 'leagues'});
	<? } else { ?>
	mm({action: 'dashboard'});
	<? } ?>
	var pattern = Trianglify({ width: window.innerWidth, height: window.innerHeight });
	document.body.appendChild(pattern.canvas());
}
</script>