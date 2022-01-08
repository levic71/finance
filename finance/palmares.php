<?

require_once "sess_context.php";

session_start();

include "common.php";
include "simulator_fct.php";

$range = 1;

foreach(['range'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// On cherche a tricher !!!
if ($range > 4 || $range < 0) $range = 1;

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

$tab_strat = array("1" => array(), "2" => array());
$tab_perfs = array("1" => array(), "2" => array());

$req = "SELECT * FROM strategies WHERE defaut=1";
$res = dbc::execSql($req);

if ($range == 4)
	$date_start = date('Y-m-d', strtotime('-6 month'));
else
	$date_start = date('Y-m-d', strtotime('-'.$range.' year'));

$date_end   = date("Y-m-d");

while($row = mysqli_fetch_array($res)) {
		
	$lst_symbols = array();
	$lst_decode_symbols = json_decode($row['data'], true);

	// Recherche de la date min qui contient le max de data pour tous les actifs de la strategie
	foreach($lst_decode_symbols['quotes'] as $key => $val) {
		$lst_symbols[] = $key;
		$d = calc::getMaxDailyHistoryQuoteDate($key);
		if ($d > $date_start) $date_start = $d;
	}
	
	// Initialisation des parametres pour la simulation
	$params = array();
	$params['strategie_data']    = $row['data'];
	$params['strategie_methode'] = $row['methode'];
	$params['compare_to']        = "SPY";
	$params['capital_init']      = 0;
	$params['date_start']        = $date_start;
	$params['date_end']          = $date_end;
	$params['retrait']           = 0;
	$params['montant_retrait']   = 0;
	$params['delai_retrait']     = 0;
	$params['invest']            = $row['cycle'] * 1000;
	$params['cycle_invest']      = $row['cycle'];
	
	// Lancement de la simulation
	$row['sim'] = strategieSimulator($params);
	
	$tab_strat[$row['methode']][$row['id']] = $row;
	$tab_perfs[$row['methode']][$row['id']] = $row['sim']['perf_pf'];
}

arsort($tab_perfs["1"]);
arsort($tab_perfs["2"]);

?>

<div class="ui container inverted">

	<h2 class="ui left floated">
		<i class="inverted diamond icon"></i>Dual Momemtum
		<button id="paramares_3y_bt" class="mini ui right floated button <?= $range == 3 ? "pink" : "gray" ?>">3Y</button>
		<button id="paramares_2y_bt" class="mini ui right floated button <?= $range == 2 ? "pink" : "gray" ?>">2Y</button>
		<button id="paramares_1y_bt" class="mini ui right floated button <?= $range == 1 ? "pink" : "gray" ?>">1Y</button>
		<button id="paramares_6M_bt" class="mini ui right floated button <?= $range == 4 ? "pink" : "gray" ?>">6M</button>
	</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
			$i = 1;
        	foreach($tab_perfs["1"] as $key => $val) {
				$ribbon_color = $i == 1 ? "ribbon--orange" : ($i == 2 ? "ribbon--green" : ($i == 3 ? "ribbon--blue" : "ribbon--gray"));
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::perfCard(0, $tab_strat["1"][$key], $data2["day"], $data2["perfs"]) ?>
				<div class="ribbon <?= $ribbon_color ?>">N°<?= $i ?><br /><small><?= $tab_strat["1"][$key]['sim']['perf_pf'] ?>%</small></div>
			</div>
<?
				$i++;
			}
?>
    			</div>
    			<div class="swiper-pagination"></div>
    		</div>

		</div>
    </div>
</div>

<div class="ui container inverted">

	<h2><i class="inverted cubes icon"></i>DCA</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
			$i = 1;
        	foreach($tab_perfs["2"] as $key => $val) {
				$ribbon_color = $i == 1 ? "ribbon--orange" : ($i == 2 ? "ribbon--green" : ($i == 3 ? "ribbon--blue" : "ribbon--gray"));
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::perfCard(0, $tab_strat["2"][$key], $data2["day"], $data2["perfs"]) ?>
				<div class="ribbon <?= $ribbon_color ?>">N°<?= $i ?><br /><small><?= $tab_strat["2"][$key]['sim']['perf_pf'] ?>%</small></div>
			</div>
<?
				$i++;
			}
?>

    			</div>
    			<div class="swiper-pagination"></div>
    		</div>

		</div>
    </div>
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
				spaceBetween: 15
			}
		},
		pagination: {
          el: ".swiper-pagination",
          clickable: true,
        }
    });

<?	foreach(["1", "2"] as $k => $v)
		foreach($tab_strat[$v] as $key => $val) { ?>
			Dom.addListener(Dom.id('home_sim_bt_<?= $val['id'] ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val['id'] ?>&f_date_start=<?= $date_start ?>&f_date_end=<?= $date_end ?>', loading_area: 'main' }); });
<? } ?>

<? if ($range != 1) { ?>
	Dom.addListener(Dom.id('paramares_1y_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'palmares', id: 'main', url: 'palmares.php?range=1', loading_area: 'main' }); });
<? } ?>

<? if ($range != 2) { ?>
	Dom.addListener(Dom.id('paramares_2y_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'palmares', id: 'main', url: 'palmares.php?range=2', loading_area: 'main' }); });
<? } ?>

<? if ($range != 3) { ?>
	Dom.addListener(Dom.id('paramares_3y_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'palmares', id: 'main', url: 'palmares.php?range=3', loading_area: 'main' }); });
<? } ?>

<? if ($range != 4) { ?>
	Dom.addListener(Dom.id('paramares_6M_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'palmares', id: 'main', url: 'palmares.php?range=4', loading_area: 'main' }); });
<? } ?>

</script>