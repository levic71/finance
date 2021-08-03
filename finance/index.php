<?

include_once "include.php";

$ver = tools::isLocalHost() ? rand() : "1.2.50";
$pea = -1;
$admin = 0;

foreach(['pea', 'admin'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

$admin = $admin == 1 ? true : false;

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
		<link rel="stylesheet" href="css/the-datepicker.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/sortable-theme-minimal.css?ver=<?= $ver ?>" />

		<!-- script type="text/javascript" src="js/semantic.min.js?ver=<?= $ver ?>"></script -->
		<script type="text/javascript" src="js/chart.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/sweetalert2.all.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/dom.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/jxs_compressed.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/the-datepicker.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/sortable.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/scripts.js?ver=<?= $ver ?>"></script>

		<script>
		window.onload = function() {

			go({ action: 'home_content', id: 'main', url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' });

			Dom.addListener(Dom.id('m1_sidebar_bt'), Dom.Event.ON_CLICK, function(event) { addCN('sidebar_menu', 'visible'); });
			Dom.addListener(Dom.id('m1_home_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm1_home_bt',   url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' }); });
			Dom.addListener(Dom.id('m2_home_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm2_home_bt',   url: 'home_content.php?pea=<?= $pea ?>&admin=<?= $admin ?>' }); });
			Dom.addListener(Dom.id('m1_login_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm1_login_bt',   url: 'login.php' }); });
			Dom.addListener(Dom.id('m2_login_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'm2_login_bt',   url: 'login.php' }); });
			Dom.addListener(Dom.id('footer_contact_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'footer_contact_bt',   url: 'contact.php' }); });
			Dom.addListener(Dom.id('footer_terms_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'footer_terms_bt',   url: 'terms.php' }); });
			Dom.addListener(Dom.id('footer_faq_bt'),     Dom.Event.ON_CLICK, function(event) { go({ action: 'home',   id: 'main', menu: 'footer_faq_bt',   url: 'faq.php' }); });

<? if ($admin) { ?>
			Dom.addListener(Dom.id('m1_cron_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'cron',   id: 'main', menu: 'm1_cron_bt',   url: 'crontab.php' }); });
			Dom.addListener(Dom.id('m1_log_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'log',    id: 'main', menu: 'm1_log_bt',    url: 'log.php' }); });
			Dom.addListener(Dom.id('m2_cron_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'cron',   id: 'main', menu: 'm2_cron_bt',   url: 'crontab.php' }); });
			Dom.addListener(Dom.id('m2_log_bt'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'log',    id: 'main', menu: 'm2_log_bt',    url: 'log.php' }); });
<? } ?>

		}
		</script>
    </head>

	<body class="ui fluid inverted segment container">

	<!-- Sidebar Menu -->
	<div class="ui vertical inverted sidebar menu" id="sidebar_menu">
		<a class="item" id="m2_home_bt"><i class="ui inverted home icon"></i>Home</a>
<? if ($admin) { ?>
	<a class="item" id="m2_users_bt"><i class="ui inverted users download alternate icon"></i>Users</a>
	<a class="item" id="m2_cron_bt"><i class="ui inverted cloud download alternate icon"></i>Cron</a>
		<a class="item" id="m2_log_bt"><i class="ui inverted sort amount down icon"></i>Log</a>
<? } ?>
		<a class="item" id="m2_login_bt"><i class="ui inverted user icon"></i>Login</a>
	</div>

	<!-- Page Contents -->
	<div class="pusher">
		<div class="ui inverted vertical masthead center aligned segment">

			<div class="ui inverted container">
    			<div class="ui large secondary inverted pointing menu" id="wide_menu">
					<a class="toc inverted item" id="m1_sidebar_bt"><i class="sidebar inverted icon"></i></a>
					<a class="active item" id="m1_home_bt">Home</a>
<? if ($admin) { ?>
					<a class="item" id="m1_users_bt">Users</a>
					<a class="item" id="m1_cron_bt">Cron</a>
					<a class="item" id="m1_log_bt">Log</a>
<? } ?>
					<div class="right item">
						<button id="m1_login_bt" class="ui blue icon button">
  							<i class="user white inverted icon"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="main" class="main"></div>
	
	<div class="ui inverted vertical footer segment">
		<div class="ui center aligned container">
			<div class="ui inverted section divider"></div>
			<div class="ui horizontal inverted small divided link list">
				<a id="footer_contact_bt" class="item" href="#">Nous Contacter</a>
				<a id="footer_terms_bt" class="item" href="#">Conditions d'utilisation</a>
				<a id="footer_faq_bt" class="item" href="#">FAQ</a>
			</div>
		</div>
	</div>

    </body>
</html>