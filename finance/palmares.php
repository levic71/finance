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

<div class="ui container inverted segment">

	<h2><i class="inverted black diamond icon"></i>&nbsp;&nbsp;Meilleures stratégies Dual Momemtum (1Y)</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
			$req = "SELECT * FROM strategies WHERE defaut=1 AND methode=1";

			$i = 1;
			$tab_strat = array();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
				$tab_strat[] = $row['id'];
				$ribbon_color = $i == 1 ? "ribbon--orange" : ($i == 2 ? "ribbon--green" : ($i == 3 ? "ribbon--blue" : "ribbon--gray"));
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::perfCard("home_card", $row['id'], $row['title'], $data2["day"], $data2["perfs"], $row['data'], $row['methode']) ?>
				<div class="ribbon <?= $ribbon_color ?>">N°<?= $i ?><br /><small>15%</small></div>
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

<div class="ui container inverted segment">

	<h2><i class="inverted black cubes icon"></i>&nbsp;&nbsp;Meilleures stratégies DCA (1Y)</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
			$req = "SELECT * FROM strategies WHERE defaut=1 AND methode=2";

			$i = 1;
			$tab_strat = array();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
				$tab_strat[] = $row['id'];
				$ribbon_color = $i == 1 ? "ribbon--orange" : ($i == 2 ? "ribbon--green" : ($i == 3 ? "ribbon--blue" : "ribbon--gray"));
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::perfCard("home_card", $row['id'], $row['title'], $data2["day"], $data2["perfs"], $row['data'], $row['methode']) ?>
				<div class="ribbon <?= $ribbon_color ?>">N°<?= $i ?><br /><small>15%</small></div>
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
				spaceBetween: 5
			}
		},
		pagination: {
          el: ".swiper-pagination",
          clickable: true,
        }
    });

<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val ?>', loading_area: 'home_sim_bt_<?= $val ?>' }); });
<? } ?>


</script>