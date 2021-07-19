<?

include_once "include.php";

$ver = "1.2.9";
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
		<script type="text/javascript" src="js/sweetalert2.all.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/dom.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/jxs_compressed.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/scripts.js?ver=<?= $ver ?>"></script>

<style>
    .hidden.menu {
      display: none;
    }

    .ui.vertical.stripe {
      padding: 8em 0em;
    }
    .ui.vertical.stripe h3 {
      font-size: 2em;
    }
    .ui.vertical.stripe .button + h3,
    .ui.vertical.stripe p + h3 {
      margin-top: 3em;
    }
    .ui.vertical.stripe .floated.image {
      clear: both;
    }
    .ui.vertical.stripe p {
      font-size: 1.33em;
    }
    .ui.vertical.stripe .horizontal.divider {
      margin: 3em 0em;
    }

    .quote.stripe.segment {
      padding: 0em;
    }
    .quote.stripe.segment .grid .column {
      padding-top: 5em;
      padding-bottom: 5em;
    }

    .footer.segment {
      padding: 5em 0em;
    }

    .secondary.pointing.menu .toc.item {
      display: none;
    }

    @media only screen and (max-width: 700px) {
      .ui.fixed.menu {
        display: none !important;
      }
      .secondary.pointing.menu .item,
      .secondary.pointing.menu .menu {
        display: none;
      }
      .secondary.pointing.menu .toc.item {
        display: block;
      }
    }
</style>

<script>
window.onload = function() {

	go({ action: 'home_content', id: 'main', url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' });

	Dom.addListener(Dom.id('m1_home_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm1_home_bt',   url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' }); });
	Dom.addListener(Dom.id('m1_sim_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'sim',    id: 'main', menu: 'm1_sim_bt',    url: 'simulator.php' }); });

	if ($admin) {
		Dom.addListener(Dom.id('m1_search_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
		Dom.addListener(Dom.id('m1_cron_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'cron',   id: 'main', menu: 'm1_cron_bt',   url: 'crontab.php' }); });
		Dom.addListener(Dom.id('m1_log_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'log',    id: 'main', menu: 'm1_log_bt',    url: 'log.php' }); });
	}

}
</script>

    </head>
    <body class="ui inverted segment container">

	<div class="ui large top fixed hidden menu">
		<div class="ui container">
			<a class="active item" onclick="window.location='index.php<?= $admin ? "?admin=1" : "" ?>'">Home</a>
			<a class="item" onclick="window.location='simulator.php'">Simulator</a>
<? if ($admin) { ?>
			<a class="item" onclick="window.location='search.php'">Search</a>
			<a class="item" onclick="window.location='crontab.php'">Cron</a>
			<a class="item" onclick="window.location='log.php'">Log</a>
<? } ?>
			<div class="right menu">
				<div class="item">
					<a class="ui button">Log in</a>
				</div>
				<div class="item">
					<a class="ui primary button">Sign Up</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Sidebar Menu -->
	<div class="ui vertical inverted sidebar menu">
		<a class="active item" onclick="window.location='index.php<?= $admin ? "?admin=1" : "" ?>'">Home</a>
		<a class="item" onclick="window.location='simulator.php'">Simulator</a>
<? if ($admin) { ?>
		<a class="item" onclick="window.location='search.php'">Search</a>
		<a class="item" onclick="window.location='crontab.php'">Cron</a>
		<a class="item" onclick="window.location='log.php'">Log</a>
<? } ?>
		<a class="item">Login</a>
		<a class="item">Signup</a>
	</div>

	<!-- Page Contents -->
	<div class="pusher">
		<div class="ui inverted vertical masthead center aligned segment">

			<div class="ui container">
    			<div class="ui large secondary inverted pointing menu" id="wide_menu">
					<a class="toc item"><i class="sidebar icon"></i></a>
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

	<div id="main" clas="main"></div>
    
    </body>
</html>