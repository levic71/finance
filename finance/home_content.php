<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// récupération des DM
$data = calc::getDualMomentum("ALL", date("Y-m-d"));

// Tri décroissant des perf DM des stocks
arsort($data["perfs"]);
	
?>

<div class="ui container inverted segment">

	<h2>Stratégies <? if ($sess_context->isUserConnected()) { ?><button id="home_strategie_add" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Ajouter</button><? } ?></h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
			if ($sess_context->isUserConnected() && $sess_context->isSuperAdmin())
				$req = "SELECT * FROM strategies WHERE defaut= 1 OR user_id=".$sess_context->getUserId();
			else if ($sess_context->isUserConnected())
	        	$req = "SELECT * FROM strategies WHERE user_id=".$sess_context->getUserId();
			else 
				$req = "SELECT * FROM strategies WHERE defaut=1";

			$tab_strat = array();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
				$tab_strat[] = $row['id'];
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::perfCard("home_card", $row['id'], $row['title'], $data["day"], $data["perfs"], $row['data']) ?>
			</div>
<? } ?>

    			</div>
    			<div class="swiper-pagination"></div>
    		</div>

		</div>
    </div>
</div>

	
<div id="stocks_box" class="ui container inverted segment">

<h2 class="ui left floated">Actifs suivis <button id="lst_filter1_bt" class="mini ui grey button">PEA</button><button id="lst_filter2_bt" class="mini ui grey button">EUR</button><button id="lst_filter3_bt" class="mini ui grey button">USD</button><? if ($sess_context->isSuperAdmin()) { ?><button id="home_symbol_search" class="circular ui icon very small right floated pink button"><i class="inverted white add icon"></i> Ajouter</button><? } ?></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_stock" data-sortable>
		<thead>
			<tr>
				<th></th>
				<th>Symbole</th>
                <th class="four wide">Nom</th>
                <th>Type</th>
				<th>Dernière cotation</th>
				<th data-sortable-type="numeric">Prix</th>
				<th data-sortable-type="numeric">%</th>
				<th data-sortable-type="numeric">DM</th>
				<th data-sortable-type="numeric">MM200</th>
				<th data-sortable-type="numeric">MM7</th>
<? if ($sess_context->isSuperAdmin()) { ?>
    		    <th></th>
<? } ?>
			</tr>
		</thead>
        <tbody id="lst_stock_body">
<?

$x = 0;

// GET MAX HISTO FOR ALL SYMBOL && METTRE EN CACHE
$max_histo_tab = calc::getAllMaxHistoryDate();

foreach($data["stocks"] as $key => $val) {

	$symbol = $key;

//	$max_histo = calc::getMaxHistoryDate($symbol);
	$max_histo = isset($max_histo_tab[$symbol]) ? $max_histo_tab[$symbol] : "0000-00-00";

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";

	$curr = $val['currency'] == "EUR" ? "&euro;" : "$";

if ($sess_context->isSuperAdmin()) {
	echo "<tr class=\"".$val['currency']." ".($val['pea'] == 1 ? "PEA" : "")."\">";
} else {
	echo "<tr onclick=\"gotoStockDetail('".$val['symbol']."');\" class=\"".$val['currency']." ".($val['pea'] == 1 ? "PEA" : "")."\">";
}
	echo "<td class=\"collapsing\"><i class=\"inverted grey chevron right icon\"></i></td>";

if ($sess_context->isSuperAdmin()) {
	echo "
		<td onclick=\"gotoStockDetail('".$val['symbol']."');\">".$val['symbol']."</td>
		<td onclick=\"gotoStockDetail('".$val['symbol']."');\">".utf8_decode($val['name'])."</td>
	";
} else {
	echo "
		<td>".$val['symbol']."</td>
		<td>".utf8_decode($val['name'])."</td>
	";
}

	echo "
		<td>".$val['type']."</td>
		<td>".($val['day'] == NULL ? "N/A" : $val['day'])."</td>
		<td data-value=\"".$val['price']."\">".($val['price'] == NULL ? "N/A" : sprintf("%.2f", $val['price']).$curr)."</td>
		<td class=\"".($val['ref_pct'] >= 0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $val['ref_pct'])." %</td>
		<td data-value=\"".$val['MMZDM']."\">".sprintf("%.2f", $val['MMZDM'])." %</td>
		<td data-value=\"".$val['MM200']."\">".sprintf("%.2f", $val['MM200']).$curr."</td>
		<td data-value=\"".$val['MM7']."\">".sprintf("%.2f", $val['MM7']).$curr."</td>
	";

if ($sess_context->isSuperAdmin()) {
	echo "<td class=\"collapsing\">
			<div class=\"ui inverted checkbox\">
				<input type=\"radio\" name=\"row_symbol\" id=\"row_symbol\" value=\"".$val['symbol']."\" /> <label></label>
			</div>
	</td>";
}
	echo "</tr>";

	$tabi = [ $val['region'] => $val['currency'], "Marché" => $val['marketopen'].'-'.$val['marketclose'], "TZ" => $val['timezone'], "Max Histo" => $max_histo, "Cache" => $cache_timestamp ];

if (false) {
	echo '<tr class="row-detail"><td></td><td colspan="10" class="ui fluid">';
	foreach($tabi as $keyi => $vali)
		echo '<div class="ui labeled button" tabindex="0">
				<div class="ui teal button">'.$keyi.'</div>
				<a class="ui basic teal left pointing label">'.$vali.'</a>
			</div>';
	echo '</td></tr>';
}

	$x++;
}

?>
		</tbody>
<? 	if ($sess_context->isSuperAdmin()) { ?>
		<tfoot class="full-width">
			<tr>
				<th></th>
				<th colspan="16">
					<div class="ui right floated buttons">
						<div class="ui small black button" id="delete_bt">Supprimer</div>
					</div>
				</th>
			</tr>
		</tfoot>
<? } ?>
	</table>
</div>

<script>

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
				spaceBetween: 5
			}
		},
		pagination: {
          el: ".swiper-pagination",
          clickable: true,
        }
    });


	hideSubDetail = function() {
		tab = Dom.find("#lst_stock tbody tr.row-detail");
		for (const element of tab) Dom.css(element, {'display' : 'none'});
	}

	gotoStockDetail = function(sym) {
		// hideSubDetail();
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+sym, loading_area: 'main' });
	}

	filterLstStocks = function() {
		f1_on = Dom.hasClass(Dom.id('lst_filter1_bt'), 'blue');
		f2_on = Dom.hasClass(Dom.id('lst_filter2_bt'), 'blue');
		f3_on = Dom.hasClass(Dom.id('lst_filter3_bt'), 'blue');

		tab = Dom.find("#lst_stock tbody tr");
		for (const element of tab) Dom.css(element, {'display' : 'table-row'});

		if (!(f1_on == false && f2_on == false && f3_on == false)) {
			for (const element of tab) {
				if ((f1_on && Dom.hasClass(element, 'PEA')) || (f2_on && Dom.hasClass(element, 'EUR')) || (f3_on && Dom.hasClass(element, 'USD')))
					continue;
				Dom.css(element, {'display' : 'none'});
			}
		}
		// hideSubDetail();
	}

	filterLstAction = function(elt, fct) {
		switchColorElement(elt, 'grey', 'blue');
		filterLstStocks();
	}

	Dom.addListener(Dom.id('lst_filter1_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter1_bt'); });
	Dom.addListener(Dom.id('lst_filter2_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter2_bt'); });
	Dom.addListener(Dom.id('lst_filter3_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter3_bt'); });

	<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_add'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_new', id: 'main', url: 'strategie.php?action=new', loading_area: 'home_strategie_add' }); });
<? } ?>
<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val ?>', loading_area: 'home_sim_bt_<?= $val ?>' }); });
<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_<?= $val ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_upt', id: 'main', url: 'strategie.php?action=upt&strategie_id=<?= $val ?>', loading_area: 'home_strategie_<?= $val ?>_bt' }); });
<? } ?>
<? } ?>
<? if ($sess_context->isSuperAdmin()) { ?>
	Dom.addListener(Dom.id('delete_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'delete', id: 'main', url: 'stock_action.php?action=del&symbol='+valof('row_symbol'), loading_area: 'delete_bt', confirmdel: 1 }); });
	Dom.addListener(Dom.id('home_symbol_search'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
<? } ?>

	change_wide_menu_state('wide_menu', 'm1_home_bt');

	Sortable.initTable(el("lst_stock"));

</script>