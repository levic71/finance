<?

use GuzzleHttp\Promise\Is;

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;
$strat_ptf    = isset($_COOKIE["strat_ptf"]) ? $_COOKIE["strat_ptf"] :  1;

foreach([ 'strat_ptf' ] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) { ?>
	<script>
	go({ action: 'login', id: 'main', url: 'login.php?redirect=1&goto=portfolio' });
	</script>
	<?
	exit(0);
}

// Reuperation des devises
$devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolioByUser($sess_context->getUserId());

$sc = new StockComputing($quotes, $portfolio_data, $devises);
$sc->setStratPtf($strat_ptf);

// On récupère les infos du portefeuille + les positions et les ordres
$lst_positions = $sc->getPositions();

// Recupération de tous les actifs suivi
$lst_trendfollowing = $sc->getTrendFollowing();

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted bullseye icon"></i>Watchlist
		<div class="ui right floated buttons" id="strat_bts">
			<button data-value="1" class="mini ui <?= $strat_ptf == 1 ? "primary" : "grey" ?> button">Défensive</button>
			<button data-value="2" class="mini ui <?= $strat_ptf == 2 ? "primary" : "grey" ?> button">Passive</button>
			<button data-value="3" class="mini ui <?= $strat_ptf == 3 ? "primary" : "grey" ?> button">Offensive</button>
			<button data-value="4" class="mini ui <?= $strat_ptf == 4 ? "primary" : "grey" ?> button">Aggressive</button>
		</div>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
					<thead><? echo QuoteComputing::getHtmlTableHeader(); ?></thead>
					<tbody>
	<?
						$watchlist_selection = [];

						foreach($lst_positions as $key => $val)
							$watchlist_selection[$key] = $key;

						foreach($lst_trendfollowing as $key => $val) {
							$qc = new QuoteComputing($sc, $key);
							if ($sc->isInQuotes($key) && !$qc->isTypeIndice() && $qc->isAlerteActive()) $watchlist_selection[$key] = $key;
						}

						$i = 1;
						ksort($watchlist_selection);
						foreach($watchlist_selection as $key => $val) {

							$qc = new QuoteComputing($sc, $key);
							echo $qc->getHtmlTableLine($i++);

						}
?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>


<script>

updateDataPage = function(opt) {
	// On parcours les lignes du tableau positions pour calculer valo, perf, gain, atio et des tooltip du tableau des positions
	trendfollowing_ui.computePositionsTable('lst_position', -1);
}('init');

// Listener sur boutons tags
let i = 0;
let lib_buttons = [ 'Déf', 'Pass', 'Off', 'Agg'];
Dom.find('#strat_bts button').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		let strat = Dom.attribute(item, 'data-value');
		setCookie('strat_ptf', strat, 10000);
        go({ action: 'watchlist', id: 'main', url: 'watchlist.php?strat_ptf=' + strat, loading_area: 'main' });
	});

	if (window.innerWidth < 600) item.innerText = lib_buttons[i++];
});

// Tri sur tableau
Sortable.initTable(el("lst_position"));

// On cache les fitres de selection de la liste des ordres passes
hide("filters");


</script>