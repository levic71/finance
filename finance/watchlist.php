<?

use GuzzleHttp\Promise\Is;

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach([] as $key)
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

// On récupère les infos du portefeuille + les positions et les ordres
$lst_positions = $sc->getPositions();

// Recupération de tous les actifs suivi
$lst_trendfollowing = $sc->getTrendFollowing();

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted bullseye icon"></i>Actifs
		<button id="ptf_pos_sync_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black sync icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
					<thead><tr>
						<th class="center aligned"></th>
						<th class="center aligned">Actif</th>
						<th class="center aligned" data-sortable="false">PRU<br />Qté</th>
						<th class="center aligned">Cotation<br />%</th>
						<th class="center aligned">MM200<br />%</th>
						<th class="center aligned" data-sortable="false">Alertes</th>
						<th class="center aligned">DM</th>
						<th class="center aligned">Tendance</th>
						<th class="center aligned">Poids</th>
						<th class="center aligned">Valorisation (&euro;)</th>
						<th class="center aligned">Performance</th>
						<th class="center aligned">Rendement<br /><small>PRU/Cours</small></th>
					</tr></thead>
					<tbody>
	<?
						$watchlist_selection = [];

						foreach($lst_positions as $key => $val)
							$watchlist_selection[$key] = $key;

						foreach($lst_trendfollowing as $key => $val) {
							$qc = new QuoteComputing($sc, $key);
							if (!$qc->isTypeIndice() && $qc->isAlerteActive()) $watchlist_selection[$key] = $key;
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

// Tri sur tableau
Sortable.initTable(el("lst_position"));

// On cache les fitres de selection de la liste des ordres passes
hide("filters");

</script>