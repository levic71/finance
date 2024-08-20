<?

require_once "sess_context.php"; // background-image: linear-gradient(to right, rgba(0, 255,0,1) 25%, rgba(0,255,0,0.1) 25%, rgba(255,0,0,0)) !important;

session_start();

include "common.php";
include "googlesheet/sheet.php";

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// SQL SCHEMA UPDATE
// $ret = dbc::addColTable("stocks", "dividende_annualise", "ALTER TABLE `stocks` ADD `dividende_annualise` FLOAT NOT NULL AFTER `rating`, ADD `date_dividende` DATE NOT NULL AFTER `dividende_annualise`;");
$ret = dbc::addColTable("indicators", "last_upt", "ALTER TABLE `indicators` ADD `last_upt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `3y`;");

//UPDATE `orders` SET devise='EUR', taux_change='1'

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

// On récupère les actifs favoris si connecté
$favoris = array_flip(explode("|", $sess_context->isUserConnected() ? $sess_context->getUser()['favoris'] : ""));

$positions       = [];
$trend_following = [];

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
if ($sess_context->isUserConnected()) {
	$aggregate_ptf   = calc::getAggregatePortfoliosByUser($sess_context->getUserId());
	if (isset($aggregate_ptf['positions']))       $positions       = $aggregate_ptf['positions'];
	if (isset($aggregate_ptf['trend_following'])) $trend_following = $aggregate_ptf['trend_following'];
}

$indicateurs_a_suivre = [ 'INDEXEURO:PX1', 'INDEXSP:.INX', 'INDEXDJX:.DJI', 'INDEXNASDAQ:.IXIC', 'INDEXRUSSELL:RUT', 'INDEXCBOE:VIX' ];

// Tableau des notifs
$notifs = [];

// Recuperation des alertes non lues
$req = "SELECT * FROM alertes WHERE (user_id=".$sess_context->getUserId()." OR user_id=0) AND lue=0 AND date=CURDATE()";
$res = dbc::execSql($req);
while ($row = mysqli_fetch_assoc($res)) $notifs[] = $row;

?>

<div id="strategie_container" class="ui container inverted">

	<? if ($sess_context->isUserConnected() && count($notifs) > 0) { ?>
	<h2 class="ui left floated">
		<i class="inverted bullhorn icon"></i><span>Alertes</span>
		<? if ($sess_context->isSuperAdmin()) { ?><button id="home_alertes_refresh" class="circular ui right floated button icon_action"><i class="inverted black redo icon"></i></button><? } ?>
		<button id="home_alertes_list" class="circular ui right floated button icon_action"><i class="inverted black history icon"></i></button>
		<button id="home_alertes_markall" class="circular ui right floated button icon_action"><i class="inverted black low vision icon"></i></button>
	</h2>
	<div id="lst_alertes">
	<?
		foreach($notifs as $key => $val)
			if ($val['user_id'] == 0 || ($sess_context->isUserConnected() && $val['user_id'] == $sess_context->getUserId())) {
				echo '
					<div id="portfolio_alertes_'.$val['actif'].'_bt" data-tootik="'.mb_convert_encoding($data2['stocks'][$val['actif']]['name'], 'ISO-8859-1', 'UTF-8').'" class="ui labeled button portfolio_alerte" tabindex="0">
						<div class="ui '.($val['sens'] == -1 && $val['couleur'] == "green" ? "positive " : "").($val['sens'] == 1 && $val['couleur'] == "red" ? "negative " : "").$val['couleur'].' button" data-symbol="'.$val['actif'].'">
							<i class="'.$val['icone'].' inverted icon"></i>'.$val['actif'].'
						</div>
						<a class="ui basic '.$val['couleur'].' left pointing label">'.sprintf("%s <br/>%.2f", ucfirst($val['type']), $val['seuil']).'</a>
					</div>';
			}
	?>
	<? } ?>
	</div>

	<h2 class="ui left floated">
		<i class="inverted eye icon"></i><span>Market</span>
		<? if ($sess_context->isUserConnected() && count($notifs) == 0) { ?>
			<button id="home_alertes_list" class="ui right floated button icon_action"><i class="inverted black history icon"></i></button>
		<? } ?>
	</h2>
	<table class="ui striped inverted single line unstackable very compact table sortable-theme-minimal" id="lst_scan" data-sortable>
		<? uimx::displayHeadTable([ ["l" => "", "c" => "" ], ["l" => "Seuils", "c" => "center aligned" ], ["l" => "Valeur", "c" => "" ], ["l" => "%J", "c" => "" ], ["l" => "YTD", "c" => "" ], ["l" => "1W", "c" => "" ], ["l" => "1M", "c" => "" ], ["l" => "1Y", "c" => "" ], ["l" => "3Y", "c" => "" ], ["l" => "MM200", "c" => "" ]  ]); ?>
		<tbody>
			<?
				foreach($indicateurs_a_suivre as $key => $val) {
					$x = str_replace(':', '.', $val);
					if (isset($data2['stocks'][$x])) {
						$stock = $data2['stocks'][$x];
						$seuils = isset($trend_following[$x]['seuils']) ? $trend_following[$x]['seuils'] : "";
						echo '<tr>
								<td><button class="mini ui primary button" data-symbol="'.$stock['symbol'].'">'.$stock['name'].'</button></td>
								<td class="center aligned"><button class="mini ui secondary button" data-tootik="'.$seuils.'" data-tootik-conf="right">'.($seuils == "" ? 0 : count(explode(';', $seuils))).'</button></td>
								<td>'.sprintf("%.2f", $stock['price']).'</td>
								<td class="'.($stock['percent'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['percent']).'%</td>
								<td class="'.($stock['ytd'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['ytd'] * 100).'%</td>
								<td class="'.($stock['1w']  >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['1w']  * 100).'%</td>
								<td class="'.($stock['1m']  >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['1m']  * 100).'%</td>
								<td class="'.($stock['1y']  >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['1y']  * 100).'%</td>
								<td class="'.($stock['3y']  >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $stock['3y']  * 100).'%</td>
								<td>'.($stock['MM200'] == 0 ? "-" : Round($stock['MM200'], 2)).'</td>
							</tr>';
					}
				}

			?>				
		</tbody>
	</table>



	<h2 class="ui left floated">
		<i class="inverted chess icon"></i>
		<span>
			<?= $sess_context->isUserConnected() ? "Mes Stratégies" : "Stratégies" ?>
			<button id="strategie_default_bt" class="mini ui grey button <?= $sess_context->isUserConnected() ? "" : "hidden" ?>"><i class="ui inverted thumbtack icon"></i></button>
		</span>

		<? if ($sess_context->isUserConnected()) { ?><button id="home_strategie_add" class="ui right floated button icon_action" data-tootik-conf="left" data-tootik="Nouvelle stratégie"><i class="inverted black add icon"></i></button><? } ?>
	</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper" id="strategie_swiper">
<?
/* 			if ($sess_context->isUserConnected() && $sess_context->isSuperAdmin())
				$req = "SELECT * FROM strategies WHERE defaut= 1 OR user_id=".$sess_context->getUserId();
			else
			if ($sess_context->isUserConnected())
				$req = "SELECT * FROM strategies WHERE user_id=".$sess_context->getUserId();
			else 
				$req = "SELECT * FROM strategies WHERE defaut=1";
 */
			if ($sess_context->isUserConnected())
				$req = "SELECT * FROM strategies WHERE defaut= 1 OR user_id=".$sess_context->getUserId()." ORDER BY ordre, id, defaut ASC";
			else
				$req = "SELECT * FROM strategies WHERE defaut=1 ORDER BY ordre, id ASC";

			$tab_strat = array();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
				$tab_strat[] = [
					"id" => $row['id'],
					"copy" => !($row['user_id'] == $sess_context->getUserId())
				];
?>
					<div class="four wide column swiper-slide <?= $row['defaut'] == 1 ? "defaut" : "" ?>">
						<?= uimx::perfCard($sess_context->getUserId(), $row, $data2) ?>
					</div>
<? } ?>

    			</div>
    			<div class="swiper-pagination"></div>
    		</div>

		</div>
    </div>
</div>

	
<div id="stocks_box" class="ui container inverted segment">

	<h2 class="ui left floated">
		<span><i class="inverted podcast icon"></i>Screener</span>
		<div>
			<button id="lst_filter10_bt" class="mini ui grey button"><i class="ui briefcase grey inverted icon"></i></button>
			<button id="lst_filter12_bt" class="mini ui grey button"><i class="ui alarm grey inverted icon"></i></button>
			<button id="lst_filter9_bt"  class="mini ui grey button"><i class="ui star grey inverted icon"></i></button>
			<button id="lst_filter7_bt"  class="mini ui grey button">ETF</button>
			<button id="lst_filter8_bt"  class="mini ui grey button">Action</button>
			<button id="lst_filter11_bt" class="mini ui grey button">Indice</button>
			<button id="lst_filter1_bt"  class="mini ui grey button">PEA</button>
			<button id="lst_filter2_bt"  class="mini ui grey button">EUR</button>
			<button id="lst_filter3_bt"  class="mini ui grey button">USD</button>
			<button id="lst_filter4_bt"  class="mini ui grey button">&lt; 0.3%</button>
			<button id="lst_filter5_bt"  class="mini ui grey button">&gt; 150 M</button>
			<button id="lst_filter6_bt"  class="mini ui grey button"><i class="inverted ellipsis horizontal icon"></i></button>
		</div>
		<? if ($sess_context->isSuperAdmin()) { ?><button id="home_symbol_search" class="ui right floated button icon_action" data-tootik-conf="left" data-tootik="Nouveau actif"><i class="inverted black add icon"></i></button><? } ?>
		
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

	<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_stock" data-sortable>

		<? uimx::displayHeadTable([ ["l" => "Symbole", "c" => "" ], ["l" => "", "c" => "" ], ["l" => "Nom", "c" => "four wide" ], ["l" => "", "c" => "" ], ["l" => "Frais", "c" => "" ], ["l" => "Actif", "c" => "" ], ["l" => "", "c" => "" ], ["l" => "Prix", "c" => "", "o" => "data-sortable-type=\"numeric\"" ], ["l" => "Var", "c" => "", "o" => "data-sortable-type=\"numeric\"" ], ["l" => "DM", "c" => "", "o" => "data-sortable-type=\"numeric\"" ], ["l" => "", "c" => "", "o" => "data-sortable=\"false\"" ], ["l" => "", "c" => "", "o" => "data-sortable=\"false\"" ], ["l" => "", "c" => "", "o" => "data-sortable=\"false\"" ]  ]); ?>


		<tbody id="lst_stock_body">
<?

$x = 0;

// GET MAX HISTO FOR ALL SYMBOL && METTRE EN CACHE
$max_histo_tab = calc::getAllMaxHistoryDate();

foreach($data2["stocks"] as $key => $val) {

	$symbol = $key;

	$isAlerteActive = isset($trend_following[$key]['active']) && $trend_following[$key]['active'] == 1 ? true : false;
	$isWatchlist    = isset($trend_following[$key]['watchlist']) && $trend_following[$key]['watchlist'] == 1 ? true : false;
	$stopprofit = isset($trend_following[$key]['stop_profit']) ? $trend_following[$key]['stop_profit'] : 0;
	$stoploss   = isset($trend_following[$key]['stop_loss'])   ? $trend_following[$key]['stop_loss']   : 0;
	$objectif   = isset($trend_following[$key]['objectif'])    ? $trend_following[$key]['objectif']    : 0;
	$seuils     = isset($trend_following[$key]['seuils'])      ? $trend_following[$key]['seuils']      : '';
	$options    = isset($trend_following[$key]['options'])     ? $trend_following[$key]['options']     : 0;
	$strat_type = isset($trend_following[$key]['strategie_type'])    ? $trend_following[$key]['strategie_type']    : 1;
	$reg_type   = isset($trend_following[$key]['regression_type'])   ? $trend_following[$key]['regression_type']   : 1;
	$reg_period = isset($trend_following[$key]['regression_period']) ? $trend_following[$key]['regression_period'] : 0;

//	$max_histo = calc::getMaxHistoryDate($symbol);
	$max_histo      = isset($max_histo_tab[$symbol]) ? $max_histo_tab[$symbol] : "0000-00-00";
	$perf_indicator = calc::getPerfIndicator($val);

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";

	$tags_infos = uimx::getIconTooltipTag($val['tags']);

	$curr  = $val['type'] == 'INDICE' ? "" : ($val['currency'] == "EUR" ? "&euro;" : "$");
	$class = $val['currency']." ".($val['pea'] == 1 ? "PEA" : "")." ".($val['frais'] <= 0.3 ? "FRAIS" : "")." ".($val['actifs'] >= 150 ? "ACTIFS" : "")." ".($val['type'] == "ETF" ? "ETF" : ($val['type'] == "INDICE" ? "IND" : "EQY"))." ".(isset($favoris[$val['symbol']]) ? "FAV" : "");

	$now = time();
	$your_date = strtotime($val['day']);
	$datediff = $now - $your_date;
	$diff_days = round($datediff / (60 * 60 * 24));
	$symbol_new_name = 1;

	echo "<tr class=\"".$class."\" data-alerte=\"".($isAlerteActive ? 1 : 0)."\" data-ptf=\"".(isset($positions[$val['symbol']]) ? 1 : 0)."\" data-tags=\"".mb_convert_encoding($val['tags'], 'ISO-8859-1', 'UTF-8')."\">";

	echo "
		<td><button class=\"mini ui primary button\" data-symbol=\"".$val['symbol']."\">".QuoteComputing::getQuoteNameWithoutExtension($val['symbol'])."</button></td>
		<td data-value=\"".$tags_infos['icon_tag']."\" data-tootik=\"".$tags_infos['tooltip']."\" class=\"collapsing\"><i data-secteur=\"".$tags_infos['icon_tag']."\" class=\"inverted grey ".$tags_infos['icon']." icon\"></i></td>
		<td>".mb_convert_encoding($val['name'], 'ISO-8859-1', 'UTF-8')."</td>
	";
	
	echo "
		<td><button data-tootik-conf=\"right  multiline\" data-tootik=\"".$val['type']."\" class=\"mini ui ".strtolower($val['type'])." button badge\">".(substr($val['type'] == 'Equity' ? 'Action' : $val['type'], 0, 1))."</button></td>
		<td data-value=\"".$val['frais']."\">".sprintf("%.2f", $val['frais'])." %</td>
		<td data-value=\"".$val['actifs']."\">".$val['actifs']." M</td>
		<td>
			<span data-tootik-conf=\"left  multiline\" data-tootik=\"Dernière cotation le ".($val['date_update'] == NULL ? "N/A" : $val['date_update'])."\"><a class=\"ui circular\"><i class=\"inverted calendar ".($val['date_update'] == date("Y-m-d") ? "grey" : ($diff_days > 3 ? "red" : "orange"))." alternate icon\"></i></a></span>
			<span data-tootik-conf=\"right multiline\" data-tootik=\"Alertes\"><a class=\"ui circular\"><i data-pname=\"".$symbol."\" data-value=\"".$val['price']."\" data-active=\"".($isAlerteActive ? 1 : 0)."\" data-watchlist=\"".($isWatchlist ? 1 : 0)."\" data-stoploss=\"".$stoploss."\" data-objectif=\"".$objectif."\" data-stopprofit=\"".$stopprofit."\" data-seuils=\"".$seuils."\" data-options=\"".$options."\" data-strat-type=\"".$strat_type."\" data-reg-type=\"".$reg_type."\" data-reg-period=\"".$reg_period."\" class=\"inverted alarm ".($isAlerteActive ? "blue" : "black")." icon\"></i></a></span>
		</td>
		<td data-value=\"".$val['price']."\">".($val['price'] == NULL ? "N/A" : sprintf("%.2f", $val['price']).$curr)."</td>
		<td data-value=\"".$val['percent']."\" class=\"".($val['percent'] >= 0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $val['percent'])." %</td>
		<td data-value=\"".$val['DM']."\"      class=\"".($val['DM'] >= 0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $val['DM'])." %</td>
	";

	echo "<td><span data-tootik-conf=\"left multiline\" data-tootik=\"".uimx::$perf_indicator_libs[$perf_indicator]."\"><a class=\"ui empty ".uimx::$perf_indicator_colrs[$perf_indicator]." circular label\"></a></span></td>";

	echo "<td class=\"collapsing\"><i id=\"fav_".$val['symbol']."\" data-sym=\"".$val['symbol']."\" class=\"inverted ".(isset($favoris[$val['symbol']]) ? "yellow" : "black")." star icon\"></i></td>";

	echo "<td></td>";
	echo "</tr>";

	$x++;
}

?>
		</tbody>
	</table>
	<div id="lst_stock_box"></div>
</div>

<script>

var tags_colr = 'teal';

// On récupère toutes les lignes du tabeau
var tab_stocks = Dom.find("#lst_stock tbody tr");

var swiper = new Swiper(".mySwiper", {
	loop: false,
	loopFillGroupWithBlank: true,
	breakpoints: {
		320: {
			slidesPerView: 1,
			slidesPerGroup: 1,
			spaceBetween: 0
		},
		640: {
			slidesPerView: 2,
			slidesPerGroup: 2,
			spaceBetween: 5
		},
		720: {
			slidesPerView: 3,
			slidesPerGroup: 3,
			spaceBetween: 5
		},
		1024: {
			slidesPerView: 4,
			slidesPerGroup: 4,
			spaceBetween: 15
		}
	},
	pagination: {
		el: ".swiper-pagination",
		clickable: true,
	}
});

match_tags = function(tags, filters) {

	ret = false;

	var t = tags.split('|');

	filters.forEach(function(item) {
		t.forEach(function(item2) {
			if (item == item2) ret=true;
		});
	});

	return ret;
}

filterLstStocks = function() {
	f1_on  = Dom.hasClass(Dom.id('lst_filter1_bt'), 'orange');
	f2_on  = Dom.hasClass(Dom.id('lst_filter2_bt'), 'orange');
	f3_on  = Dom.hasClass(Dom.id('lst_filter3_bt'), 'orange');
	f4_on  = Dom.hasClass(Dom.id('lst_filter4_bt'), 'orange');
	f5_on  = Dom.hasClass(Dom.id('lst_filter5_bt'), 'orange');
	f7_on  = Dom.hasClass(Dom.id('lst_filter7_bt'), 'orange');
	f8_on  = Dom.hasClass(Dom.id('lst_filter8_bt'), 'orange');
	f9_on  = Dom.hasClass(Dom.id('lst_filter9_bt'), 'orange');
	f10_on = Dom.hasClass(Dom.id('lst_filter10_bt'), 'orange');
	f11_on = Dom.hasClass(Dom.id('lst_filter11_bt'), 'orange');
	f12_on = Dom.hasClass(Dom.id('lst_filter12_bt'), 'orange');

	var filter_tags = [];
	Dom.find('#other_tags button.bt_tags').forEach(function(item) {
		if (isCN(item.id, tags_colr)) filter_tags.push(item.innerHTML);
	});

	// On affiche toutes les lignes du tableau
	for (const element of tab_stocks) Dom.css(element, {'display' : 'table-row'});

	// On passe en revue toutes les lignes et on cache celles qui ne correspondent pas aux boutons allumés
	if (!(f1_on == false && f2_on == false && f3_on == false && f4_on == false && f5_on == false && f7_on == false && f8_on == false && f9_on == false && f10_on == false && f11_on == false && f12_on == false)) {
		for (const element of tab_stocks) {

			let in_ptf = Dom.attribute(element, 'data-ptf');
			let in_alerte = Dom.attribute(element, 'data-alerte');

			if (
				(!f1_on  || (f1_on && Dom.hasClass(element, 'PEA')))    &&
				(!f2_on  || (f2_on && Dom.hasClass(element, 'EUR')))    &&
				(!f3_on  || (f3_on && Dom.hasClass(element, 'USD')))    && 
				(!f4_on  || (f4_on && Dom.hasClass(element, 'FRAIS')))  && 
				(!f5_on  || (f5_on && Dom.hasClass(element, 'ACTIFS'))) &&
				(!f7_on  || (f7_on && Dom.hasClass(element, 'ETF')))    && 
				(!f8_on  || (f8_on && Dom.hasClass(element, 'EQY')))    &&
				(!f9_on  || (f9_on && Dom.hasClass(element, 'FAV')))    &&
				(!f11_on || (f11_on && Dom.hasClass(element, 'IND')))   &&
				(!f12_on || (f12_on && in_alerte == 1))                 &&
				(!f10_on || (f10_on && in_ptf == 1))
			) continue;

			Dom.css(element, {'display' : 'none'});
		}
	}

	// Seconde passe de filtrage sur les tags s'il y en a au moins allumé
	if (filter_tags.length > 0) {
		for (const element of tab_stocks) {
			var stock_tags = Dom.attribute(element, 'data-tags');
			if (stock_tags && match_tags(stock_tags, filter_tags)) continue;
			Dom.css(element, {'display' : 'none'});
		}
	}
}

filterLstStrategies = function(elt) {
	switchColorElement(elt, 'grey', 'yellow');
	toogleCN("strategie_swiper", "showall", "showmine");
	setCookie(elt, isCN(elt, 'grey') ? 0 : 1, 1000);
}

filterLstAction = function(elt) {
	if (elt != 'lst_filter6_bt') switchColorElement(elt, 'grey', 'orange');
	filterLstStocks();
	setCookie(elt, isCN(elt, 'grey') ? 0 : 1, 1000);
}

onlyOneActiveButton = function(lst_buttons, bt_clicked) {

	// Si on clique sur le bt deja actif alors on le deactive sans activer un autre bt
	if (isCN(bt_clicked, 'orange')) {
		switchColorElement(bt_clicked, 'orange', 'grey');
		filterLstStocks();
		setCookie(bt_clicked, 0, 1000);
	} else {
		lst_buttons.forEach(function(elt) {
			if (isCN(elt, 'orange')) {
				switchColorElement(elt, 'orange', 'grey');
				setCookie(elt,  0, 1000);
			}
		});

		filterLstAction(bt_clicked);
	}
}

// Listener sur bouton filtre default strategie
Dom.addListener(Dom.id('strategie_default_bt'),   Dom.Event.ON_CLICK, function(event) { filterLstStrategies('strategie_default_bt'); });

// Listener sur les boutons de filte tableau assets
Dom.addListener(Dom.id('lst_filter1_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter1_bt'); });
Dom.addListener(Dom.id('lst_filter2_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter3_bt', 'orange')) { switchColorElement('lst_filter3_bt', 'orange', 'grey'); setCookie('lst_filter3_bt', 0 , 1000); }; filterLstAction('lst_filter2_bt'); });
Dom.addListener(Dom.id('lst_filter3_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter2_bt', 'orange')) { switchColorElement('lst_filter2_bt', 'orange', 'grey'); setCookie('lst_filter2_bt', 0 , 1000); }; filterLstAction('lst_filter3_bt'); });
Dom.addListener(Dom.id('lst_filter4_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter4_bt'); });
Dom.addListener(Dom.id('lst_filter5_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter5_bt'); });
Dom.addListener(Dom.id('lst_filter6_bt'),  Dom.Event.ON_CLICK, function(event) { toogle('other_tags'); });

// ETF/Equity/INDICE
[ 'lst_filter7_bt', 'lst_filter8_bt', 'lst_filter11_bt' ].forEach(function(elt) { Dom.addListener(Dom.id(elt),  Dom.Event.ON_CLICK, function(event) { onlyOneActiveButton([ 'lst_filter7_bt', 'lst_filter8_bt', 'lst_filter11_bt' ], elt);  }); });

Dom.addListener(Dom.id('lst_filter9_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter9_bt'); });
Dom.addListener(Dom.id('lst_filter10_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter10_bt'); });
Dom.addListener(Dom.id('lst_filter12_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter12_bt'); });

// Listener sur bouton ajout strategie et liste des alertes historiques
<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_add'),    Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_new', id: 'main', url: 'strategie.php?action=new', loading_area: 'home_strategie_add' }); });
	Dom.addListener(Dom.id('home_alertes_list'),     Dom.Event.ON_CLICK, function(event) { overlay.load('portfolio_alertes.php', { }); });
	<? if (count($notifs) > 0) { ?>Dom.addListener(Dom.id('home_alertes_markall'),  Dom.Event.ON_CLICK, function(event) { if (confirm('Etes-vous sur de masquer toutes les alertes ?')) go({ action: 'alert_viewed', id: 'main', url: 'portfolio_alerte_viewed.php?alerte=-1', no_data: 1 });}); <? } ?>
<? } ?>

// Listener sur boutons backtesting + copy strategie
<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val['id'] ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val['id'] ?>', loading_area: 'home_sim_bt_<?= $val['id'] ?>' }); });
	<? if ($sess_context->isUserConnected()) { ?>
		Dom.addListener(Dom.id('home_strategie_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_upt', id: 'main', url: 'strategie.php?action=<?= $val['copy'] ? "copy" : "upt" ?>&strategie_id=<?= $val['id'] ?>', loading_area: 'home_strategie_<?= $val['id'] ?>_bt' }); });
	<? } ?>
<? } ?>

// Listener sur recherche nouveau actif
<? if ($sess_context->isSuperAdmin()) { ?>
Dom.addListener(Dom.id('home_symbol_search'),   Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
<? if (count($notifs) > 0) { ?>Dom.addListener(Dom.id('home_alertes_refresh'), Dom.Event.ON_CLICK, function(event) { overlay.load('crontab_alertes.php', { }); });<? } ?>
<? } ?>

change_wide_menu_state('wide_menu', 'm1_home_bt');

// Init tri tableau
Sortable.initTable(el("lst_scan"));
Sortable.initTable(el("lst_stock"));

// Init affichage default strategies + bt screener
addCN("strategie_swiper", "showmine");
<? if ($sess_context->isSuperAdmin() || !$sess_context->isUserConnected()) { ?>
	<? if ($sess_context->isUserConnected()) { ?>
		[ 'lst_filter1_bt', 'lst_filter2_bt', 'lst_filter3_bt', 'lst_filter4_bt', 'lst_filter5_bt', 'lst_filter6_bt', 'lst_filter7_bt', 'lst_filter8_bt', 'lst_filter9_bt', 'lst_filter10_bt', 'lst_filter11_bt', 'lst_filter12_bt' ].forEach(function(elt) {
			if (getCookie(elt) == 1) switchColorElement(elt, 'orange', 'grey');
		});
		if (getCookie('strategie_default_bt') != 1) filterLstStrategies('strategie_default_bt');
	<? } ?>
	filterLstStrategies('strategie_default_bt');
	filterLstStocks();
<? } ?>

// Changement etat bouttons tags
changeState = function(item) {
	switchColorElement(item.id, tags_colr, 'grey');
}

// Listener sur boutons tags other
Dom.find('button.bt_tags').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		changeState(item);
		filterLstStocks('');
	});
});

// Listener sur button detail stock tableau Market
Dom.find("#lst_scan tbody tr td:nth-child(1) button").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+Dom.attribute(element, 'data-symbol'), loading_area: 'main' });
	});
});

// Listener sur button detail stock tableau Screener
Dom.find("#lst_stock tbody tr td:nth-child(1) button").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+Dom.attribute(element, 'data-symbol'), loading_area: 'main' });
	});
});

// Listener sur icon secteur ligne tableau
Dom.find("#lst_stock tbody tr td:nth-child(2) i").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		Dom.find('#other_tags #'+Dom.attribute(element, 'data-secteur')).forEach(function(item) {
			changeState(item);
			filterLstStocks('');
		});
	});
});

// Listener sur icon alerte ligne tableau
Dom.find("#lst_stock tbody tr td:nth-child(7) span:nth-child(2) i").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

<? if ($sess_context->isUserConnected()) { ?>

		// On récupère les valeurs dans la cellule du tableau - Pas tres beau !!!
		var pname      = Dom.attribute(element, 'data-pname');
		var price      = Dom.attribute(element, 'data-value');
		var active     = Dom.attribute(element, 'data-active');
		var watchlist  = Dom.attribute(element, 'data-watchlist');
		var stoploss   = Dom.attribute(element, 'data-stoploss');
		var objectif   = Dom.attribute(element, 'data-objectif');
		var stopprofit = Dom.attribute(element, 'data-stopprofit');
		var seuils     = Dom.attribute(element, 'data-seuils') ? Dom.attribute(element, 'data-seuils') : '';
		var options    = parseInt(Dom.attribute(element, 'data-options'));
		var strat_type = parseInt(Dom.attribute(element, 'data-strat-type'));
		var reg_type   = parseInt(Dom.attribute(element, 'data-reg-type'));
		var reg_period = parseInt(Dom.attribute(element, 'data-reg-period'));

		console.log(pname+':'+price+':'+watchlist+':'+active+':'+stoploss+':'+objectif+':'+stopprofit+':'+seuils+':'+options+':'+strat_type+':'+reg_type+':'+reg_period);
		tf_ui_html = trendfollowing_ui.getHtml(pname, price, watchlist, active, stoploss, objectif, stopprofit, seuils, options, strat_type, reg_type, reg_period);

		Swal.fire({
				title: '',
				html: tf_ui_html,
				showCancelButton: true,
				confirmButtonText: 'Valider',
				cancelButtonText: 'Annuler',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed) {

					if (!trendfollowing_ui.checkForm()) return false;

					Dom.attribute(element, { 'data-stoploss'   : valof('f_stoploss') });
					Dom.attribute(element, { 'data-stopprofit' : valof('f_stopprofit') });
					Dom.attribute(element, { 'data-objectif'   : valof('f_objectif') });
					Dom.attribute(element, { 'data-seuils'     : valof('f_seuils') });
					Dom.attribute(element, { 'data-strat-type' : valof('f_strat_type') });
					Dom.attribute(element, { 'data-reg-type'   : valof('f_reg_type') });
					Dom.attribute(element, { 'data-reg-period' : valof('f_reg_period') });
					Dom.attribute(element, { 'data-seuils'     : valof('f_seuils') });
					Dom.attribute(element, { 'data-options'    : trendfollowing_ui.getOptionsValue() });
					Dom.attribute(element, { 'data-active'     : valof('f_active') == 0 ? 0 : 1 });
					Dom.attribute(element, { 'data-watchlist'  : valof('f_watchlist') == 0 ? 0 : 1 });
					Dom.attribute(element, { 'class': 'inverted alarm '+(valof('f_active') == 0 ? 'black' : 'blue')+' icon' });

					go({ action: 'main', id: 'main', url: trendfollowing_ui.getUrlRedirect(pname), no_data: 1, no_scroll: 1 });

					Swal.fire('Données modifiées');
				}
			});
	<? } else { ?>
		alert('Vous devez être connecté')
	<? } ?>

	});
});

// Listener sur favoris ligne tableau
Dom.find("#lst_stock tbody tr td:nth-child(12) i").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		switchColorElement(element.id, 'yellow', 'black');
		go({ action: 'user_fav', id: 'main', url: 'user_fav.php?action='+(isCN(element.id, 'yellow') ? 'add' : 'del')+'&symbol='+Dom.attribute(element, 'data-sym'), no_data: 1 });
	});
});

hide('other_tags');

// Pagination
row_per_page = <? if ($sess_context->isUserConnected()) { ?> getCookie('home_stock_row_per_page', 20)<? } else { ?>20<? } ?>;

memRowPerPage = function() {
	Dom.find("#lst_stock_box select").forEach(function(element) {
		Dom.addListener(element, Dom.Event.ON_CHANGE, function(event) {
			var nb = 20;
			for (i=0; i < element.length; i++) if (element[i].selected) nb = element[i].value;
			setCookie('home_stock_row_per_page', nb, 10000);
		});
	});
}

paginator({
	table: document.getElementById("lst_stock"),
	box: document.getElementById("lst_stock_box"),
	rows_per_page: row_per_page,
	tail_call: memRowPerPage
});


</script>