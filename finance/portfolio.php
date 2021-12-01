<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

// On recupère le nb de portefeuille de l'utilisateur
$req = "SELECT count(*) total FROM portfolios WHERE user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);
$nb_portfolios = $row['total'];

// On récupère les portefeuilles de l'utilisateur
$lst_portfolios = array();
$req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) $lst_portfolios[] = $row;

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted black briefcase icon"></i>&nbsp;&nbsp;Mes Portefeuilles
		<? if ($sess_context->isUserConnected()) { ?><button id="portfolio_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Ajouter</button><? } ?>
	</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="swiper-container mySwiper">
    			<div class="swiper-wrapper">
<?
        	foreach($lst_portfolios as $key => $val) {
?>
        	<div class="four wide column swiper-slide">
				<?= uimx::portfolioCard($val) ?>
			</div>
<?
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

<?
	foreach($lst_portfolios as $key => $val) { ?>
		Dom.addListener(Dom.id('portfolio_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=upt&portfolio_id=<?= $val['id'] ?>', loading_area: 'main' }); });
		Dom.addListener(Dom.id('portfolio_orders_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order.php?portfolio_id=<?= $val['id'] ?>', loading_area: 'main' }); });
<?	
	}
?>

	Dom.addListener(Dom.id('portfolio_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=new', loading_area: 'main' }); });

</script>