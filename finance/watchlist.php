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
		<button id="filter_bt" class="mini ui icon very small right floated grey labelled button" style="margin-top: 5px;"><i class="inverted white sliders horizontal icon"></i></button>
		<div class="ui right floated buttons" id="strat_bts" style="margin-top: 6px;">
			<button data-value="1" class="mini ui <?= $strat_ptf == 1 ? "primary" : "grey" ?> button">Défensive</button>
			<button data-value="2" class="mini ui <?= $strat_ptf == 2 ? "primary" : "grey" ?> button">Passive</button>
			<button data-value="3" class="mini ui <?= $strat_ptf == 3 ? "primary" : "grey" ?> button">Offensive</button>
			<button data-value="4" class="mini ui <?= $strat_ptf == 4 ? "primary" : "grey" ?> button">Aggressive</button>
		</div>
	</h2>

	<div id="other_tags">
    <? foreach( [
                "Secteur"             => uimx::$invest_secteur,
                "Zone géographique"   => uimx::$invest_zone_geo,
                "Critère factoriel"   => uimx::$invest_factorielle,
                "Taille"              => uimx::$invest_taille,
                "Thème"               => uimx::$invest_theme,
				"Conseillée par"      => uimx::$tags_conseillers
            ] as $lib => $tab) { ?>
				<div class="ui horizontal list">
                <? foreach ($tab as $key => $val) { ?>
			        <div class="item"><button <?= $val['desc'] != "" ? "data-tootik-conf=\"multiline\" data-tootik=\"".$val['desc']."\"" : "" ?> id="bt_filter_<?= strtoupper(substr($lib, 0, 3))."_".$key ?>" class="item mini ui bt_tags grey button"><?= $val['tag'] ?></button></div>
    			<? } ?>
					<button id="bt_filter_<?= strtoupper(substr($lib, 0, 3))."_99999" ?>" class="item mini ui bt_tags grey button hidden">Entreprise</button>
				</div>
	<? } ?>
    </div>

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

// On récupère toutes les lignes du tabeau
var tab_stocks = Dom.find("#lst_position tbody tr");

filterLstStocks = function() {

	var filter_tags = [];
	Dom.find('#other_tags button.bt_tags').forEach(function(item) {
		if (isCN(item.id, tags_colr)) filter_tags.push(item.innerHTML);
	});

	// On affiche toutes les lignes du tableau
	for (const element of tab_stocks) Dom.css(element, {'display' : 'table-row'});

	// Seconde passe de filtrage sur les tags s'il y en a au moins allumé
	if (filter_tags.length > 0) {
		for (const element of tab_stocks) {
			var stock_tags = Dom.attribute(element, 'data-tags');
			if (stock_tags && match_tags(stock_tags, filter_tags)) continue;
			Dom.css(element, {'display' : 'none'});
		}
	}
}

updateDataPage = function(opt) {
	// On parcours les lignes du tableau positions pour calculer valo, perf, gain, atio et des tooltip du tableau des positions
	trendfollowing_ui.computePositionsTable('lst_position', -1);
}('init');

// Listener sur le bouton filter
Dom.addListener(Dom.id('filter_bt'),  Dom.Event.ON_CLICK, function(event) { toogle('other_tags'); });

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

// Listener sur boutons tags other
Dom.find('button.bt_tags').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		changeState(item);
		filterLstStocks('');
	});
});

// Tri sur tableau
Sortable.initTable(el("lst_position"));

// On cache les fitres de selection de la liste des ordres passes
hide("other_tags");


</script>