<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);
	
?>

<div id="strategie_container" class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted chess icon"></i><span><?= $sess_context->isUserConnected() ? "Mes Stratégies" : "Stratégies" ?> <button id="strategie_bt" class="mini ui grey button"><i class="ui inverted star icon"></i></button></span>

		<? if ($sess_context->isUserConnected()) { ?><button id="home_strategie_add" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Stratégie</button><? } ?>
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
				<?= uimx::perfCard($sess_context->getUserId(), $row, $data2["day"], $data2["perfs"]) ?>
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
		<span><i class="inverted podcast icon"></i>Actifs suivis</span>
		<div>
			<button id="lst_filter1_bt" class="mini ui grey button">PEA</button>
			<button id="lst_filter2_bt" class="mini ui grey button">EUR</button>
			<button id="lst_filter3_bt" class="mini ui grey button">USD</button>
			<button id="lst_filter4_bt" class="mini ui grey button">&lt; 0.3%</button>
			<button id="lst_filter5_bt" class="mini ui grey button">&gt; 150 M</button>
			<button id="lst_filter6_bt" class="mini ui grey button"><i class="inverted ellipsis horizontal icon"></i></button>
		</div>
		<? if ($sess_context->isSuperAdmin()) { ?><button id="home_symbol_search" class="circular ui icon very small right floated pink button"><i class="inverted white add icon"></i> Actif</button><? } ?>
		
	</h2>

	<div id="other_tags">
    <? foreach( [
                "Classe d'actif"      => uimx::$invest_classe,
                "Secteur"             => uimx::$invest_secteur,
                "Zone géographique"   => uimx::$invest_zone_geo,
                "Critère factoriel"   => uimx::$invest_factorielle,
                "Taille"              => uimx::$invest_taille,
                "Thème"               => uimx::$invest_theme
            ] as $lib => $tab) { ?>
				<div class="ui horizontal list">
                <? foreach ($tab as $key => $val) { ?>
			        <div class="item"><button id="bt_filter_<?= strtoupper(substr($lib, 0, 3))."_".$key ?>" class="item mini ui bt_tags grey button"><?= $val['tag'] ?></button></div>
    			<? } ?>
				</div>
	<? } ?>
    </div>

	<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_stock" data-sortable>
		<thead>
			<tr>
				<th></th>
				<th>Symbole</th>
                <th class="four wide">Nom</th>
                <th>Type</th>
				<th>Frais</th>
				<th>Actifs</th>
				<th>Cotation</th>
				<th data-sortable-type="numeric">Prix</th>
				<th data-sortable-type="numeric">Var</th>
				<th data-sortable-type="numeric">DM</th>
				<th data-sortable="false"></th>
				<th data-sortable="false"></th>
			</tr>
		</thead>
        <tbody id="lst_stock_body">
<?

$x = 0;

// GET MAX HISTO FOR ALL SYMBOL && METTRE EN CACHE
$max_histo_tab = calc::getAllMaxHistoryDate();

foreach($data2["stocks"] as $key => $val) {

	$symbol = $key;

//	$max_histo = calc::getMaxHistoryDate($symbol);
	$max_histo      = isset($max_histo_tab[$symbol]) ? $max_histo_tab[$symbol] : "0000-00-00";
	$perf_indicator = calc::getPerfIndicator($val);

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";

	$curr = $val['currency'] == "EUR" ? "&euro;" : "$";

	echo "<tr onclick=\"gotoStockDetail('".$val['symbol']."');\" class=\"".$val['currency']." ".($val['pea'] == 1 ? "PEA" : "")." ".($val['frais'] <= 0.3 ? "FRAIS" : "")." ".($val['actifs'] >= 150 ? "ACTIFS" : "")."\" data-tags=\"".utf8_decode($val['tags'])."\">";

	echo "<td class=\"collapsing\"><i class=\"inverted grey chevron right icon\"></i></td>";

	echo "
		<td><button class=\"mini ui primary button\">".$val['symbol']."</button></td>
		<td>".utf8_decode($val['name'])."</td>
	";
	
	echo "
		<td>".$val['type']."</td>
		<td>".sprintf("%.2f", $val['frais'])." %</td>
		<td>".$val['actifs']." M</td>
		<td><span data-tootik-conf=\"left\" data-tootik=\"".($val['day'] == NULL ? "N/A" : $val['day'])."\"><a class=\"ui circular\"><i class=\"inverted calendar alternate icon\"></i></a></span></td>
		<td data-value=\"".$val['price']."\">".($val['price'] == NULL ? "N/A" : sprintf("%.2f", $val['price']).$curr)."</td>
		<td class=\"".($val['percent'] >= 0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $val['percent'])." %</td>
		<td class=\"".($val['DM'] >= 0 ? "aaf-positive" : "aaf-negative")."\" data-value=\"".$val['DM']."\">".sprintf("%.2f", $val['DM'])." %</td>
	";

	echo "<td><span data-tootik-conf=\"left multiline\" data-tootik=\"".uimx::$perf_indicator_libs[$perf_indicator]."\"><a class=\"ui empty ".uimx::$perf_indicator_colrs[$perf_indicator]." circular label\"></a></span></td>";

	echo "<td></td>";
	echo "</tr>";

	$x++;
}

?>
		</tbody>
	</table>
</div>

<script>

	var tags_colr = 'teal';

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

	gotoStockDetail = function(sym) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+sym, loading_area: 'main' });
	}

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
		f1_on = Dom.hasClass(Dom.id('lst_filter1_bt'), 'orange');
		f2_on = Dom.hasClass(Dom.id('lst_filter2_bt'), 'orange');
		f3_on = Dom.hasClass(Dom.id('lst_filter3_bt'), 'orange');
		f4_on = Dom.hasClass(Dom.id('lst_filter4_bt'), 'orange');
		f5_on = Dom.hasClass(Dom.id('lst_filter5_bt'), 'orange');

		var filter_tags = [];
		Dom.find('button.bt_tags').forEach(function(item) {
			if (isCN(item.id, tags_colr)) filter_tags.push(item.innerHTML);
		});

		// On récupère toutes les lignes  du tabeau et on les affiche toutes
		tab = Dom.find("#lst_stock tbody tr");
		for (const element of tab) Dom.css(element, {'display' : 'table-row'});

		// On passe en revue toutes les lignes et on cache celles qui ne correspondent pas aux boutons allumés
		if (!(f1_on == false && f2_on == false && f3_on == false && f4_on == false && f5_on == false)) {
			for (const element of tab) {

				if (
					(!f1_on || (f1_on && Dom.hasClass(element, 'PEA')))    &&
					(!f2_on || (f2_on && Dom.hasClass(element, 'EUR')))    &&
					(!f3_on || (f3_on && Dom.hasClass(element, 'USD')))    && 
					(!f4_on || (f4_on && Dom.hasClass(element, 'FRAIS')))  && 
					(!f5_on || (f5_on && Dom.hasClass(element, 'ACTIFS')))
				) continue;

				Dom.css(element, {'display' : 'none'});
			}
		}

		// Seconde passe de filtrage sur les tags s'il y en a au moins allumé
		if (filter_tags.length > 0) {
			for (const element of tab) {

				var stock_tags = Dom.attribute(element, 'data-tags');
	
				if (stock_tags && match_tags(stock_tags, filter_tags)) continue;

				Dom.css(element, {'display' : 'none'});
			}
		}
	}

	filterLstAction = function(elt) {
		if (elt != 'lst_filter6_bt') switchColorElement(elt, 'grey', 'orange');
		filterLstStocks();
	}

	filterLstStrategies = function(elt) {
		switchColorElement(elt, 'grey', 'yellow');
		toogleCN("strategie_swiper", "showall", "showmine");
	}

	Dom.addListener(Dom.id('strategie_bt'),   Dom.Event.ON_CLICK, function(event) { filterLstStrategies('strategie_bt'); });
	Dom.addListener(Dom.id('lst_filter1_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter1_bt'); });
	Dom.addListener(Dom.id('lst_filter2_bt'), Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter3_bt', 'orange')) switchColorElement('lst_filter3_bt', 'orange', 'grey'); filterLstAction('lst_filter2_bt'); });
	Dom.addListener(Dom.id('lst_filter3_bt'), Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter2_bt', 'orange')) switchColorElement('lst_filter2_bt', 'orange', 'grey'); filterLstAction('lst_filter3_bt'); });
	Dom.addListener(Dom.id('lst_filter4_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter4_bt'); });
	Dom.addListener(Dom.id('lst_filter5_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter5_bt'); });
	Dom.addListener(Dom.id('lst_filter6_bt'), Dom.Event.ON_CLICK, function(event) { toogle('other_tags'); });

	<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_add'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_new', id: 'main', url: 'strategie.php?action=new', loading_area: 'home_strategie_add' }); });
<? } ?>
<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val['id'] ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val['id'] ?>', loading_area: 'home_sim_bt_<?= $val['id'] ?>' }); });
<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_upt', id: 'main', url: 'strategie.php?action=<?= $val['copy'] ? "copy" : "upt" ?>&strategie_id=<?= $val['id'] ?>', loading_area: 'home_strategie_<?= $val['id'] ?>_bt' }); });
<? } ?>
<? } ?>
<? if ($sess_context->isSuperAdmin()) { ?>
	Dom.addListener(Dom.id('home_symbol_search'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
<? } ?>

	change_wide_menu_state('wide_menu', 'm1_home_bt');

	Sortable.initTable(el("lst_stock"));

	addCN("strategie_swiper", "showmine");
<? if ($sess_context->isSuperAdmin() || !$sess_context->isUserConnected()) { ?>
	filterLstStrategies('strategie_bt');
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

hide('other_tags');

</script>