<?

include_once "include.php";

$ver = tools::isLocalHost() ? rand() : "1.2.34";
$pea = -1;
$admin = 0;

foreach(['pea', 'admin'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

$admin = $admin == 1 ? true : false;

// TODO
//
// Gerer les cotations vides dans le calcul DM
// Courbe historique sur detail
// Courbe evolution capital sur simu
// Courbes evolutions des DM de chaque actif
// Recup data form https://marketstack.com
// alerte si gros volume échangé
// ratio de sharpe
// Gestion des portefeuille en base de donnees

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Homepage Dual Momentum</title>
		<link rel="stylesheet" href="css/semantic.min.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/style.css?ver=<?= $ver ?>" />
		<!-- script type="text/javascript" src="js/semantic.min.js?ver=<?= $ver ?>"></script -->
		<script type="text/javascript" src="js/chart.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/sweetalert2.all.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/dom.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/jxs_compressed.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/scripts.js?ver=<?= $ver ?>"></script>
		<script>
window.onload = function() {

	go({ action: 'home_content', id: 'main', url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' });

	Dom.addListener(Dom.id('m1_sidebar_bt'), Dom.Event.ON_CLICK, function(event) { addCN('sidebar_menu', 'visible'); });
	Dom.addListener(Dom.id('m1_home_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm1_home_bt',   url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' }); });
	Dom.addListener(Dom.id('m1_sim_bt'),     Dom.Event.ON_CLICK, function(event) { go({ action: 'sim',    id: 'main', menu: 'm1_sim_bt',    url: 'simulator.php' }); });
	Dom.addListener(Dom.id('m2_home_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm1_home_bt',   url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' }); });
	Dom.addListener(Dom.id('m2_sim_bt'),     Dom.Event.ON_CLICK, function(event) { go({ action: 'sim',    id: 'main', menu: 'm1_sim_bt',    url: 'simulator.php' }); });

<? if ($admin) { ?>
		Dom.addListener(Dom.id('m1_search_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
		Dom.addListener(Dom.id('m1_cron_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'cron',   id: 'main', menu: 'm1_cron_bt',   url: 'crontab.php' }); });
		Dom.addListener(Dom.id('m1_log_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'log',    id: 'main', menu: 'm1_log_bt',    url: 'log.php' }); });
		Dom.addListener(Dom.id('m2_search_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
		Dom.addListener(Dom.id('m2_cron_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'cron',   id: 'main', menu: 'm1_cron_bt',   url: 'crontab.php' }); });
		Dom.addListener(Dom.id('m2_log_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'log',    id: 'main', menu: 'm1_log_bt',    url: 'log.php' }); });
<? } ?>

}
		</script>
    </head>
    <body class="ui inverted segment container">

	<!-- Sidebar Menu -->
	<div class="ui vertical inverted sidebar menu" id="sidebar_menu">
		<a class="active item" id="m2_home_bt">Home</a>
		<a class="item" id="m2_sim_bt">Simulator</a>
<? if ($admin) { ?>
		<a class="item" id="m2_search_bt">Search</a>
		<a class="item" id="m2_cron_bt">Cron</a>
		<a class="item" id="m2_log_bt">Log</a>
<? } ?>
		<a class="item">Login</a>
		<a class="item">Signup</a>
	</div>

	<!-- Page Contents -->
	<div class="pusher">
		<div class="ui inverted vertical masthead center aligned segment">

			<div class="ui inverted container">
    			<div class="ui large secondary inverted pointing menu" id="wide_menu">
					<a class="toc inverted item" id="m1_sidebar_bt"><i class="sidebar inverted icon"></i></a>
					<a class="active item" id="m1_home_bt">Home</a>
					<a class="item" id="m1_sim_bt">Simulator</a>
<? if ($admin) { ?>
					<a class="item" id="m1_search_bt">Search</a>
					<a class="item" id="m1_cron_bt">Cron</a>
					<a class="item" id="m1_log_bt">Log</a>
<? } ?>
					<div class="right item">
						<div class="item">
							<div class="ui primary button">Sign up</div>
  						</div>
						<div class="item">
							<div class="ui button">Log in</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="main" class="main"></div>
    
    </body>
</html>