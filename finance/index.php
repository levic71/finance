<?

require_once "sess_context.php";

// Empecher la lecture des cookies en javascript pour eviter CSS
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set("url_rewriter.tags", "input=src");
ini_set('arg_separator.output', '&amp;');

// Creation d'une nouvelle session
session_cache_expire(12 * 60);
session_start();

// Initialisation session
if (isset($_SESSION['sess_context'])) {
	$sess_context = $_SESSION['sess_context'];
} else {
	$sess_context = new sess_context();
	$_SESSION["sess_context"] = $sess_context;
}

include_once "include.php";

$ver = tools::isLocalHost() ? rand() : "1.5.17";

foreach(['action', 'goto', 'debug'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Homepage Dual Momentum</title>

		<link rel="stylesheet" href="css/tootik.min.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/mysemantic.min.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/the-datepicker.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/swiper-bundle.min.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/sortable-theme-minimal.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/prompt.css?ver=<?= $ver ?>" />
		<link rel="stylesheet" href="css/style.css?ver=<?= $ver ?>" />

		<script type="text/javascript" src="js/trendyways.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/regression.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/kwheel.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/paginator.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/chart_options.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/chart.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/sweetalert2.all.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/prompt.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/dom.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/jxs_compressed.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/the-datepicker.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/sortable.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/swiper-bundle.min.js?ver=<?= $ver ?>"></script>
		<script type="text/javascript" src="js/scripts.js?ver=<?= $ver ?>"></script>

		<script>
		window.onload = function() {

			go({ action: 'home', id: 'main', url: '<?= $goto == "" ? "home_content.php" : $goto.".php" ?>', loading_area: '' <?= $goto == "" ? "" : ", menu: '".($goto == "portfolio" ? "m1_portfolio_bt" : $goto)."'" ?> });

			let user_connected = <?= $sess_context->isUserConnected() ? 'true' : 'false' ?>;
			let user_admin     = <?= $sess_context->isSuperAdmin()    ? 'true' : 'false' ?>;

			var tab_menu = {
				home:       { url: "home_content.php", title: 'Home', icon: 'home' },
				palmares:   { url: "palmares.php", title: 'Palmarès', icon: 'trophy' },
				portfolio:  { url: "portfolio.php", title: 'Portefeuilles', icon: 'briefcase' },
				watchlist:  { url: "watchlist.php", title: 'Watchlist', icon: 'bullseye' },
				predict:    { url: "prediction_list.php", title: 'Prédiction', icon: 'magic' },
				tools:      { url: "tools_dca_calculator.php", title: 'Outils', icon: 'tools' },
				users:      { url: user_connected ? "user_list.php" : "", title: 'Users', icon: 'users download alternate' },
				admin:      { url: user_connected ? "admin.php" : "", title: 'Admin', icon: 'sort amount down' },
				login:      { url: !user_connected ? "login.php" : "", title: 'Login', icon: 'user' },
				logout:     { url: user_connected ? "login_action.php?action=logout" : "", title: 'Logout', icon: 'user' }
			};

			Object.keys(tab_menu).forEach(function(key) {

				if (tab_menu[key].url == "") return;

				if (key != 'login' && key != 'logout') {
					let a1 = document.createElement('a');
					a1.text = tab_menu[key].title;
					a1.className = 'item';
					a1.id = 'm1_'+key+'_bt';
					let wide_menu = el('wide_menu');
					wide_menu.insertBefore(a1, wide_menu.children.item(wide_menu.children.length - 2).nextSibling); // Insertion apres le premier item

					let i2 = document.createElement('i');
					i2.className = 'ui inverted '+tab_menu[key].icon+' icon';
					let a2 = document.createElement('a');
					a2.text = tab_menu[key].title;
					a2.appendChild(i2);
					a2.className = 'item';
					a2.id = 'm2_'+key+'_bt';
					let sidebar_menu = el('sidebar_menu');
					sidebar_menu.insertBefore(a2, sidebar_menu.children.item(sidebar_menu.children.length - 1)); // Insertion apres le premier item

				}

				Dom.addListener(Dom.id('m1_'+key+'_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: key, id: 'main', menu: 'm1_'+key+'_bt', url: tab_menu[key].url, loading_area: 'main' }); });
				Dom.addListener(Dom.id('m2_'+key+'_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: key, id: 'main', menu: 'm2_'+key+'_bt', url: tab_menu[key].url, loading_area: 'main' }); });
			});

			Dom.addListener(Dom.id('m1_sidebar_bt'),  Dom.Event.ON_CLICK, function(event) { addCN('sidebar_menu', 'visible'); });
			Dom.addListener(Dom.id('m2_sidebar_bt'),  Dom.Event.ON_CLICK, function(event) { rmCN('sidebar_menu', 'visible'); });

			Dom.addListener(Dom.id('footer_contact_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', menu: 'footer_contact_bt', url: 'contact.php' }); });
			Dom.addListener(Dom.id('footer_terms_bt'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', menu: 'footer_terms_bt',   url: 'terms.php' }); });
			Dom.addListener(Dom.id('footer_faq_bt'),     Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', menu: 'footer_faq_bt',     url: 'faq.php' }); });

<? if ($action == "status") { ?>
			Swal.fire({ title: '', icon: 'success', html: "Utilisateur déactivé" });
<? } ?>
<? if ($action == "confirm") { ?>
			Swal.fire({ title: '', icon: 'success', html: "Email confirmé" });
<? } ?>

		}
		</script>
    </head>

	<body class="ui fluid inverted segment container" id="mybody">

	<!-- Sidebar Menu -->
	<div class="ui vertical inverted sidebar menu" id="sidebar_menu">

		<a class="item" id="m2_sidebar_bt"><i style="float: left; margin: 0px;" class="ui inverted arrow left icon"></i>&nbsp;</a>
<? if ($sess_context->isUserConnected()) { ?>
		<a class="item" id="m2_logout_bt"><i class="ui inverted user icon"></i>Logout</a>
<? } else { ?>
		<a class="item" id="m2_login_bt"><i class="ui inverted user icon"></i>Login</a>
<? } ?>

	</div>

	<!-- Page Contents -->
	<div class="pusher">
		<div class="ui inverted vertical masthead center aligned segment">

			<div class="ui inverted container">
    			<div class="ui large secondary inverted pointing menu" id="wide_menu">
					<a class="toc inverted item" id="m1_sidebar_bt"><i class="sidebar inverted icon"></i></a>
					<div class="right item">
<? if ($sess_context->isUserConnected()) { ?>
						Hello !
						<button id="m1_logout_bt" class="ui black icon button">
  							<i class="sign out alternate white inverted icon"></i>
						</button>
<? } else { ?>
						<button id="m1_login_bt" class="ui blue icon button">
  							<i class="user white inverted icon"></i>
						</button>
<? } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="main" class="ui container inverted segment main"></div>
	
	<div class="ui inverted vertical footer segment">
		<div class="ui center aligned container">
			<div class="ui inverted section divider"></div>
			<div class="ui horizontal inverted small divided link list">
				<a id="footer_contact_bt" class="item" href="#">Contact</a>
				<a id="footer_terms_bt"   class="item" href="#">Conditions d'utilisation</a>
				<a id="footer_faq_bt"     class="item" href="#">FAQ</a>
			</div>
			<div class="disclaimer">
				<div class="ui inverted section"><small>Le site ne couvre pas le cours de toutes les actions de toutes les places boursières, le choix est à la discrétion de l'administrateur du site.</small></div>
				<div class="ui inverted section"><small>Les chiffres fournis fournis sont à titre d'information uniquement, et non à des fins commerciales ou de conseils.</small></div>
				<div class="ui inverted section"><small>Historical data from Alphavantage.com <? if (tools::useGoogleFinanceService()) { ?> - Daily Data from Google Finance (20min delay)<? } ?></small></div>
			</div>
		</div>
	</div>

    </body>
</html>