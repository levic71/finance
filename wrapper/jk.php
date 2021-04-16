<?

require_once "../include/sess_context.php";

// Permettre le partage de session entre sous domaines
ini_set('session_domain', '.jorkers.com');
ini_set("session.cookie_domain", ".jorkers.com");
// Empecher la lecture des cookies en javascript pour eviter CSS
//ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set("url_rewriter.tags", "input=src");
ini_set('arg_separator.output', '&amp;');

session_cache_expire(60 * 60);
session_start();

if (!session_id()) {
	session_start();
	$currentCookieParams = session_get_cookie_params();
	$sidvalue = session_id();
	setcookie(
		'PHPSESSID',//name
		$sidvalue,//value
		0,//expires at end of session
		$currentCookieParams['path'],//path
		$currentCookieParams['domain'],//domain
		true //secure
	);
}


header('Content-Type: text/html; charset=' . sess_context::charset);

require_once "../include/constantes.php";
require_once "../include/toolbox.php";
require_once "../include/cache_manager.php";
require_once "../include/inc_db.php";
require_once "../include/templatebox.php";
require_once "wrapper_fcts.php";

$_REQUEST['idc'] = isset($_REQUEST['idc']) ? $_REQUEST['idc'] : sess_context::INVALID_CHAMP_ID_HOME;

// Si redirect fb ou mail => Voir dans head.php
$tmp = explode("_", $_REQUEST['idc']);
if (count($tmp) >= 2) {
	$_REQUEST['idc'] = $tmp[0];
	if (substr($tmp[1], 0, 1) == 'p') {
		$idp = substr($tmp[1], 1);
	} else if (substr($tmp[1], 0, 1) == 't') {
		$idt = substr($tmp[1], 1);
	} else {
		$idj = $tmp[1];
		$date = isset($tmp[2]) ? utf8_decode($tmp[2]) : "";
		$name = isset($tmp[3]) ? utf8_decode($tmp[3]) : "";
	}
}

// $idc = is_numeric($_REQUEST['idc']) ? $_REQUEST['idc'] : (isset($sess_context) && $sess_context->isSuperUser() ? 8 : 85);
$idc = is_numeric($_REQUEST['idc']) ? $_REQUEST['idc'] : sess_context::INVALID_CHAMP_ID_HOME;


// /////////////////////////////////////////////////////
// Initialisation sess_context
// => Dans les autres programmes include common.php qui vérifie existance sinon renvoie sur jk.php
// /////////////////////////////////////////////////////
if (isset($_SESSION['sess_context'])) {
	$sess_context = $_SESSION['sess_context'];
	echo "toto"; exit(0);
} else {
	$sess_context = new sess_context();
	$_SESSION["sess_context"] = $sess_context;
	echo "totffff"; exit(0);
}

$db = dbc::connect();

if ($idc > 0) {
	$req = dbc::execSql("SELECT count(*) total FROM jb_championnat WHERE id=" . $idc);

	if ($req) {
		$row = mysqli_fetch_assoc($req);
	}

	if (!$req || $row['total'] == 0) {
		$idc = sess_context::INVALID_CHAMP_ID_HOME;
	}

}

$chp = $idc < 0 ? array("logo_photo" => "", "theme" => 1, "championnat_id" => sess_context::INVALID_CHAMP_ID_HOME, "twitter" => "Jorkers", "logo_font" => "1", "championnat_nom" => "Welcome", "type" => "", "entity" => "", "friends" => "") : Wrapper::getChampionnat($idc);
$chp['login'] = "";
$chp['pwd'] = "";
//$chp['home_list_headcount'] = 2;
$sess_context->setChampionnat($chp);

// Remise à zero Admin
$sess_context->resetAdmin();

// Si on vient d'une création de championnat
if (isset($_SESSION['autologonadmin']) && $_SESSION['autologonadmin'] == 1) {
	unset($_SESSION['autologonadmin']);
	$sess_context->setAdmin();
} else {
	if ($sess_context->isUserConnected()) {

		if ($sess_context->isSuperAdmin()) {
			$sess_context->setAdmin();
		}

		$select = "SELECT * FROM jb_roles WHERE id_champ=" . $sess_context->getRealChampionnatId() . " AND id_user=" . $sess_context->user['id'] . ";";
		$res = dbc::execSQL($select);
		if ($row2 = mysqli_fetch_array($res)) {
			if ($row2['role'] == _ROLE_ADMIN_ || $row2['role'] == _ROLE_DEPUTY_) {
				$sess_context->setAdmin();
			}

			$sess_context->setRole($row2['role']);
		}
	} else {
		$sess_context->resetAdmin();
	}

}

// ////////////////////////////////////////
// TEMPLATE HTML PRINCIPAL DE TOUT LE SITE
// Redirection JS possible dans head.php
///////////////////////////////////////////

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head><? require_once "head.php"; ?></head>

<body>
	<div class="demo-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

		<header class="demo-header mdl-layout__header mdl-color--grey-100 mdl-color-text--grey-600" id="myheader">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title logo<?=$chp['logo_font']?>" id="logo"><?=$chp['championnat_nom']?></span>
				<div class="mdl-layout-spacer"></div>
				<div id="login_panel" class="mdl-grid"></div>
			</div>
		</header>

		<div id="slidebar" class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50"></div>

		<main class="mdl-layout__content" id="main-content">
			<div class="mdl-grid demo-content" id="main"></div>
		</main>

	</div>

	<div id="msgboxes"></div>

	<script src="../mdl/material.min.js"></script>

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