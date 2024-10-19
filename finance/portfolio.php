<?

require_once "sess_context.php";

session_start();

include "common.php";
include "googlesheet/sheet.php";

foreach ([''] as $key)
	$$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri d?croissant des perf DM des stocks
arsort($data2["perfs"]);

// On recup?re le nb de portefeuille de l'utilisateur
$req = "SELECT count(*) total FROM portfolios WHERE user_id=" . $sess_context->getUserId();
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);
$nb_portfolios = $row['total'];

// On r?cup?re les portefeuilles de l'utilisateur
$lst_portfolios = array();
$req = "SELECT * FROM portfolios WHERE user_id=" . $sess_context->getUserId();
$res = dbc::execSql($req);
while ($row = mysqli_fetch_array($res)) $lst_portfolios[] = $row;

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$trend_following = [];
$aggregate_ptf   = calc::getAggregatePortfoliosByUser($sess_context->getUserId());
if (isset($aggregate_ptf['trend_following'])) $trend_following = $aggregate_ptf['trend_following'];

?>

<div class="ui container inverted">

	<div class="ui stackable grid container" id="portfolio_market" style="display: flex; justify-content: center; padding: 5px 0px;">
		<?
		foreach (['INDEXEURO:PX1', 'INDEXSP:.INX', 'INDEXDJX:.DJI', 'INDEXNASDAQ:.IXIC', 'INDEXCBOE:VIX'] as $key => $val) {
			if (isset($data2['stocks'][$val])) {
				$stock = $data2['stocks'][$val];
				$seuils = isset($trend_following[$val]['seuils']) ? $trend_following[$val]['seuils'] : "";
				echo '<div class="ui buttons"><button class="mini ui grey button">' . $stock['name'] . '</button><button class="mini ui button ' . ($stock['percent'] >= 0 ? "green" : "red") . '">' . sprintf("%.2f", $stock['percent']) . '%</button></div>';
			}
		}
		?>
	</div>

	<h2 class="ui left floated">
		<? if ($sess_context->isUserConnected()) { ?>
			<i class="inverted briefcase icon"></i>Mes Portefeuilles

			<button id="nav_menu_bt" class="dropbtn ui right floated button icon_action"><i class="inverted black sliders horizontal icon"></i></button>

			<div class="ui vertical menu nav" id="nav_menu" data-right="20">
				<a class="item" id="portfolio_add1_bt"><span>Ajouter un portefeuille</span></a>
				<a class="item" id="portfolio_add2_bt"><span>Ajouter une Synth?se</span></a>
			</div>

		<? } ?>

	</h2>

	<div class="ui stackable grid container" id="portfolio_box">
		<?
		foreach ($lst_portfolios as $key => $val) {

			// Calcul synthese portefeuille
			$portfolio_data = calc::aggregatePortfolioById($val['id']);

			// Du mardi au vendredi (PHP different de MySQL pour les n° de jour de semaine)
			$req = "SELECT * FROM portfolio_valo WHERE portfolio_id = " . $val['id'] . " AND DATE(date) <> CURDATE() AND DAYOFWEEK(date) > 1 AND DAYOFWEEK(date) < 7 order by date desc LIMIT 1";
			// Le weekend
			if (date('N') >= 6) $req = "SELECT * FROM portfolio_valo WHERE portfolio_id = " . $val['id'] . " AND DATE(date) <> CURDATE() AND DAYOFWEEK(date) = 5 order by date desc LIMIT 1";
			// Le lundi
			if (date('N') == 1) $req = "SELECT * FROM portfolio_valo WHERE portfolio_id = " . $val['id'] . " AND DATE(date) <> CURDATE() AND DAYOFWEEK(date) = 6 order by date desc LIMIT 1";
			$res = dbc::execSql($req);

			$daily_perf = 0;
			if ($row = mysqli_fetch_array($res)) {

				$data = json_decode($row['data']);
				$daily_perf = $data->valo_ptf == 0 ? 0 : Round((($portfolio_data['valo_ptf'] - $data->valo_ptf) / $data->valo_ptf) * 100, 2);
			}

			uimx::portfolioCard($val, $portfolio_data, $daily_perf);
		}
		?>
	</div>

</div>


<script>
	<? foreach ($lst_portfolios as $key => $val) { ?>

		Dom.addListener(Dom.id('portfolio_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) {
			go({
				action: 'portfolio',
				id: 'main',
				url: 'portfolio_detail.php?action=upt<?= $val['synthese'] == 1 ? "_synthese" : "" ?>&portfolio_id=<?= $val['id'] ?>',
				loading_area: 'main'
			});
		});
		Dom.addListener(Dom.id('portfolio_dashboard_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) {
			go({
				action: 'portfolio',
				id: 'main',
				url: 'portfolio_dashboard.php?portfolio_id=<?= $val['id'] ?>',
				loading_area: 'main'
			});
		});

	<? } ?>

	<? if ($sess_context->isUserConnected()) { ?>

		Dom.addListener(Dom.id('portfolio_add1_bt'), Dom.Event.ON_CLICK, function(event) {
			go({
				action: 'portfolio',
				id: 'main',
				url: 'portfolio_detail.php?action=new',
				loading_area: 'main'
			});
		});
		Dom.addListener(Dom.id('portfolio_add2_bt'), Dom.Event.ON_CLICK, function(event) {
			go({
				action: 'portfolio',
				id: 'main',
				url: 'portfolio_detail.php?action=new_synthese',
				loading_area: 'main'
			});
		});
		Dom.addListener(Dom.id('nav_menu_bt'), Dom.Event.ON_CLICK, function(event) {
			toogleCN('nav_menu', 'on');
		});

		Dom.find('.zone_bts button:nth-child(2)').forEach(function(item) {
			let id = item.id.split('_')[2];
			Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
				overlay.load('portfolio_balance.php', {
					'portfolio_id': id
				});
			});
		});

		Dom.find('.zone_bts button:nth-child(1)').forEach(function(item) {
			let id = item.id.split('_')[2];
			Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
				overlay.load('portfolio_graph.php', {
					'portfolio_id': id
				});
			});
		});

	<? } ?>
</script>