<?

require_once "sess_context.php";

session_start();

include "common.php";
include "googlesheet/sheet.php";


foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// SQL SCHEMA UPDATE
// $ret = dbc::delColTable("trend_following", "quotes", "ALTER TABLE `portfolios` DROP `quotes`;");
// $ret = dbc::addColTable("orders", "devise", "ALTER TABLE `orders` ADD `devise` VARCHAR(16) NOT NULL AFTER `price`;");
// $ret = dbc::addColTable("orders", "taux_change", "ALTER TABLE `orders` ADD `taux_change` VARCHAR(16) NOT NULL AFTER `devise`;");
// $ret = dbc::addColTable("stocks", "dividende_annualise", "ALTER TABLE `stocks` ADD `dividende_annualise` FLOAT NOT NULL AFTER `rating`, ADD `date_dividende` DATE NOT NULL AFTER `dividende_annualise`;");

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
	$positions       = $aggregate_ptf['positions'];
	$trend_following = $aggregate_ptf['trend_following'];
}

$gsa = calc::getGSAlertes();

/* echo "Liste des actifs en surveillance : ";
foreach($gsa as $key => $val) echo $val[0].", ";

echo "<br />Liste des actifs en portefeuille : ";
foreach($positions as $key => $val) echo $key.", ";
*/

$indicateurs_a_suivre = [ 'INDEXEURO:PX1', 'INDEXSP:.INX', 'INDEXDJX:.DJI', 'INDEXNASDAQ:.IXIC', 'INDEXRUSSELL:RUT', 'INDEXCBOE:VIX' ];

// Init à 0 des valeurs des indicateurs dont les donnees pas recupérées
foreach($indicateurs_a_suivre as $key => $val) {
	if (!isset($gsa[$val]))
		$gsa[$val] = array($val,"-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-");
}

// Tableau des notifs
$notifs = [];

$file_cache = 'cache/TMP_ALERTES.json';
if (file_exists($file_cache)) $notifs = cacheData::readCacheData($file_cache);

?>

<div id="strategie_container" class="ui container inverted">

	<?	if (count($notifs) > 0) { ?>
		<h2 class="ui left floated">
			<i class="inverted bullhorn icon"></i><span>Alertes</span>
			<? if ($sess_context->isSuperAdmin()) { ?><button id="home_alertes_refresh" class="circular ui right floated button icon_action"><i class="inverted black redo icon"></i></button><? } ?>
			<button id="home_alertes_detail" class="circular ui right floated button icon_action"><i class="inverted black history icon"></i></button>
		</h2>
		<?
			foreach($notifs as $key => $val)
				echo '
					<div id="portfolio_alertes_'.$val['actif'].'_bt" class="ui labeled button portfolio_alerte" tabindex="0">
						<div class="ui '.$val['colr'].' button">
							<i class="'.$val['icon'].' inverted icon"></i>'.$val['actif'].'
						</div>
						<a class="ui basic '.$val['colr'].' left pointing label">'.sprintf(is_numeric($val['seuil']) ? "%.2f " : "%s ", $val['seuil']).'</a>
					</div>';
		?>
	<? } ?>

	<h2 class="ui left floated"><i class="inverted eye icon"></i><span>Market</span></h2>
	<table class="ui striped inverted single line unstackable very compact table sortable-theme-minimal" id="lst_scan" data-sortable>
		<thead>
			<tr>
				<th></th>
				<th class="center aligned">Seuils</th>
				<th>Valeur</th>
				<th>% J</th>
				<th>% YTD</th>
				<th>% W</th>
				<th>% M</th>
				<th>% Y</th>
				<th>% 3Y</th>
				<th>MM200</th>
			</tr>
		</thead>
		<tbody>
			<?
				foreach($indicateurs_a_suivre as $key => $val) {
					echo '<tr>
							<td><button class="mini ui primary button">'.$gsa[$val][0].'</button></td>
							<td class="center aligned"><button class="mini ui secondary button" data-tootik="'.$gsa[$val][3].'" data-tootik-conf="right">'.($gsa[$val][3] == "" ? 0 : count(explode(';', $gsa[$val][3]))).'</button></td>
							<td>'.sprintf("%.2f", $gsa[$val][4]).'</td>
							<td class="'.($gsa[$val][12] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][12]).'</td>
							<td class="'.(str_replace("\%", "", $gsa[$val][13]) >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][13]).'</td>
							<td class="'.(str_replace("\%", "", $gsa[$val][14]) >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][14]).'</td>
							<td class="'.(str_replace("\%", "", $gsa[$val][15]) >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][15]).'</td>
							<td class="'.(str_replace("\%", "", $gsa[$val][16]) >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][16]).'</td>
							<td class="'.(str_replace("\%", "", $gsa[$val][17]) >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $gsa[$val][17]).'</td>
							<td>'.($gsa[$val][22] == 0 ? "-" : Round($gsa[$val][22], 2)).'</td>
						</tr>';
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

		<? if ($sess_context->isUserConnected()) { ?><button id="home_strategie_add" class="ui circular right floated pink button icon_action" data-tootik-conf="left" data-tootik="Nouvelle stratégie"><i class="inverted white add icon"></i></button><? } ?>
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
			<button id="lst_filter9_bt"  class="mini ui grey button"><i class="ui star grey inverted icon"></i></button>
			<button id="lst_filter7_bt"  class="mini ui grey button">ETF</button>
			<button id="lst_filter8_bt"  class="mini ui grey button">Action</button>
			<button id="lst_filter1_bt"  class="mini ui grey button">PEA</button>
			<button id="lst_filter2_bt"  class="mini ui grey button">EUR</button>
			<button id="lst_filter3_bt"  class="mini ui grey button">USD</button>
			<button id="lst_filter4_bt"  class="mini ui grey button">&lt; 0.3%</button>
			<button id="lst_filter5_bt"  class="mini ui grey button">&gt; 150 M</button>
			<button id="lst_filter6_bt"  class="mini ui grey button"><i class="inverted ellipsis horizontal icon"></i></button>
		</div>
		<? if ($sess_context->isSuperAdmin()) { ?><button id="home_symbol_search" class="circular ui right floated pink button icon_action" data-tootik-conf="left" data-tootik="Nouveau actif"><i class="inverted white add icon"></i></button><? } ?>
		
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
			        <div class="item"><button <?= $val['desc'] != "" ? "data-tootik-conf=\"multiline\" data-tootik=\"".$val['desc']."\"" : "" ?> id="bt_filter_<?= strtoupper(substr($lib, 0, 3))."_".$key ?>" class="item mini ui bt_tags grey button"><?= $val['tag'] ?></button></div>
    			<? } ?>
					<button id="bt_filter_<?= strtoupper(substr($lib, 0, 3))."_99999" ?>" class="item mini ui bt_tags grey button hidden">Entreprise</button>
				</div>
	<? } ?>
    </div>

	<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_stock" data-sortable>
		<thead>
			<tr>
				<th>Symbole</th>
				<th></th>
                <th class="four wide">Nom</th>
                <th></th>
				<th>Frais</th>
				<th>Actifs</th>
				<th></th>
				<th data-sortable-type="numeric">Prix</th>
				<th data-sortable-type="numeric">Var</th>
				<th data-sortable-type="numeric">DM</th>
				<th data-sortable="false"></th>
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

	$tags_infos = uimx::getIconTooltipTag($val['tags']);

	$curr  = $val['currency'] == "EUR" ? "&euro;" : "$";
	$class = $val['currency']." ".($val['pea'] == 1 ? "PEA" : "")." ".($val['frais'] <= 0.3 ? "FRAIS" : "")." ".($val['actifs'] >= 150 ? "ACTIFS" : "")." ".($val['type'] == "ETF" ? "ETF" : "EQY")." ".(isset($favoris[$val['symbol']]) ? "FAV" : "");

	echo "<tr class=\"".$class."\" data-ptf=\"".(isset($positions[$val['symbol']]) ? 1 : 0)."\" data-tags=\"".utf8_decode($val['tags'])."\">";

	echo "
		<td><button class=\"mini ui primary button\">".$val['symbol']."</button></td>
		<td data-value=\"".$tags_infos['icon_tag']."\" data-tootik=\"".$tags_infos['tooltip']."\" class=\"collapsing\"><i data-secteur=\"".$tags_infos['icon_tag']."\" class=\"inverted grey ".$tags_infos['icon']." icon\"></i></td>
		<td>".utf8_decode($val['name'])."</td>
	";
	
	echo "
		<td><button data-tootik-conf=\"right  multiline\" data-tootik=\"".($val['type'] == "ETF" ? "ETF" : "Action")."\" class=\"mini ui ".($val['type'] == "ETF" ? "green" : "primary")." button badge\">".($val['type'] == "ETF" ? "E" : "A")."</button></td>
		<td data-value=\"".$val['frais']."\">".sprintf("%.2f", $val['frais'])." %</td>
		<td data-value=\"".$val['actifs']."\">".$val['actifs']." M</td>
		<td>
			<span data-tootik-conf=\"left  multiline\" data-tootik=\"Dernière cotation le ".($val['day'] == NULL ? "N/A" : $val['day'])."\"><a class=\"ui circular\"><i class=\"inverted calendar ".($val['day'] == date("Y-m-d") ? "grey" : "black")." alternate icon\"></i></a></span>
			<span data-tootik-conf=\"right multiline\" data-tootik=\"Alertes\"><a class=\"ui circular\"><i class=\"inverted alarm ".($val['day'] == date("Y-m-d") ? "grey" : "black")." icon\"></i></a></span>
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
	f1_on = Dom.hasClass(Dom.id('lst_filter1_bt'), 'orange');
	f2_on = Dom.hasClass(Dom.id('lst_filter2_bt'), 'orange');
	f3_on = Dom.hasClass(Dom.id('lst_filter3_bt'), 'orange');
	f4_on = Dom.hasClass(Dom.id('lst_filter4_bt'), 'orange');
	f5_on = Dom.hasClass(Dom.id('lst_filter5_bt'), 'orange');
	f7_on = Dom.hasClass(Dom.id('lst_filter7_bt'), 'orange');
	f8_on = Dom.hasClass(Dom.id('lst_filter8_bt'), 'orange');
	f9_on = Dom.hasClass(Dom.id('lst_filter9_bt'), 'orange');
	f10_on = Dom.hasClass(Dom.id('lst_filter10_bt'), 'orange');

	var filter_tags = [];
	Dom.find('#other_tags button.bt_tags').forEach(function(item) {
		if (isCN(item.id, tags_colr)) filter_tags.push(item.innerHTML);
	});

	// On affiche toutes les lignes du tableau
	for (const element of tab_stocks) Dom.css(element, {'display' : 'table-row'});

	// On passe en revue toutes les lignes et on cache celles qui ne correspondent pas aux boutons allumés
	if (!(f1_on == false && f2_on == false && f3_on == false && f4_on == false && f5_on == false && f7_on == false && f8_on == false && f9_on == false && f10_on == false)) {
		for (const element of tab_stocks) {

			let in_ptf = Dom.attribute(element, 'data-ptf');

			if (
				(!f1_on || (f1_on && Dom.hasClass(element, 'PEA')))    &&
				(!f2_on || (f2_on && Dom.hasClass(element, 'EUR')))    &&
				(!f3_on || (f3_on && Dom.hasClass(element, 'USD')))    && 
				(!f4_on || (f4_on && Dom.hasClass(element, 'FRAIS')))  && 
				(!f5_on || (f5_on && Dom.hasClass(element, 'ACTIFS'))) &&
				(!f7_on || (f7_on && Dom.hasClass(element, 'ETF')))    && 
				(!f8_on || (f8_on && Dom.hasClass(element, 'EQY')))    &&
				(!f9_on || (f9_on && Dom.hasClass(element, 'FAV')))    &&
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

alert('hehe');



// Listener sur bouton filtre default strategie
Dom.addListener(Dom.id('strategie_default_bt'),   Dom.Event.ON_CLICK, function(event) { filterLstStrategies('strategie_default_bt'); });


alert('tata');
// Listener sur les boutons de filte tableau assets
Dom.addListener(Dom.id('lst_filter1_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter1_bt'); });
Dom.addListener(Dom.id('lst_filter2_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter3_bt', 'orange')) { switchColorElement('lst_filter3_bt', 'orange', 'grey'); setCookie('lst_filter3_bt', 0 , 1000); }; filterLstAction('lst_filter2_bt'); });
Dom.addListener(Dom.id('lst_filter3_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter2_bt', 'orange')) { switchColorElement('lst_filter2_bt', 'orange', 'grey'); setCookie('lst_filter2_bt', 0 , 1000); }; filterLstAction('lst_filter3_bt'); });
Dom.addListener(Dom.id('lst_filter4_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter4_bt'); });
Dom.addListener(Dom.id('lst_filter5_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter5_bt'); });
Dom.addListener(Dom.id('lst_filter6_bt'),  Dom.Event.ON_CLICK, function(event) { toogle('other_tags'); });
Dom.addListener(Dom.id('lst_filter7_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter8_bt', 'orange')) { switchColorElement('lst_filter8_bt', 'orange', 'grey'); setCookie('lst_filter8_bt', 0 , 1000); }; filterLstAction('lst_filter7_bt'); });
Dom.addListener(Dom.id('lst_filter8_bt'),  Dom.Event.ON_CLICK, function(event) { if (isCN('lst_filter7_bt', 'orange')) { switchColorElement('lst_filter7_bt', 'orange', 'grey'); setCookie('lst_filter7_bt', 0 , 1000); }; filterLstAction('lst_filter8_bt'); });
Dom.addListener(Dom.id('lst_filter9_bt'),  Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter9_bt'); });
Dom.addListener(Dom.id('lst_filter10_bt'), Dom.Event.ON_CLICK, function(event) { filterLstAction('lst_filter10_bt'); });
alert('tior');
// Listener sur bouton ajout strategie
<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('home_strategie_add'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_new', id: 'main', url: 'strategie.php?action=new', loading_area: 'home_strategie_add' }); });
<? } ?>

// Listener sur boutons backtesting + copy strategie
<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val['id'] ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val['id'] ?>', loading_area: 'home_sim_bt_<?= $val['id'] ?>' }); });
	<? if ($sess_context->isUserConnected()) { ?>
		Dom.addListener(Dom.id('home_strategie_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_upt', id: 'main', url: 'strategie.php?action=<?= $val['copy'] ? "copy" : "upt" ?>&strategie_id=<?= $val['id'] ?>', loading_area: 'home_strategie_<?= $val['id'] ?>_bt' }); });
	<? } ?>
<? } ?>
alert('coucou');
// Listener sur recherche nouveau actif
<? if ($sess_context->isSuperAdmin()) { ?>
Dom.addListener(Dom.id('home_symbol_search'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });

Dom.addListener(Dom.id('home_alertes_refresh'), Dom.Event.ON_CLICK, function(event) {
	overlay.load('crontab_alertes.php', { });
});
<? } ?>
alert('hello');

change_wide_menu_state('wide_menu', 'm1_home_bt');

// Init tri tableau
Sortable.initTable(el("lst_stock"));

// Init affichage default strategies + bt screener
addCN("strategie_swiper", "showmine");
<? if ($sess_context->isSuperAdmin() || !$sess_context->isUserConnected()) { ?>
	<? if ($sess_context->isUserConnected()) { ?>
		[ 'lst_filter1_bt', 'lst_filter2_bt', 'lst_filter3_bt', 'lst_filter4_bt', 'lst_filter5_bt', 'lst_filter6_bt', 'lst_filter7_bt', 'lst_filter8_bt', 'lst_filter9_bt' ].forEach(function(elt) {
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

// Listener sur button detail ligne tableau
Dom.find("#lst_stock tbody tr td:nth-child(1) button").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+element.innerHTML, loading_area: 'main' });
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

// Listener sur favoris ligne tableau
Dom.find("#lst_stock tbody tr td:nth-child(12) i").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		switchColorElement(element.id, 'yellow', 'black');
		go({ action: 'user_fav', id: 'main', url: 'user_fav.php?action='+(isCN(element.id, 'yellow') ? 'add' : 'del')+'&symbol='+Dom.attribute(element, 'data-sym'), no_data: 1 });
	});
});

hide('other_tags');

var row_per_page = 20;
<? if ($sess_context->isUserConnected()) { ?>
	if (getCookie('home_stock_row_per_page') != "") row_per_page = getCookie('home_stock_row_per_page');
<? } ?>

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