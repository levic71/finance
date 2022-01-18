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

// Tri d�croissant des perf DM des stocks
arsort($data2["perfs"]);

// On recup�re le nb de portefeuille de l'utilisateur
$req = "SELECT count(*) total FROM portfolios WHERE user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);
$nb_portfolios = $row['total'];

// On r�cup�re les portefeuilles de l'utilisateur
$lst_portfolios = array();
$req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) $lst_portfolios[] = $row;

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted briefcase icon"></i>Mes Portefeuilles
		<? if ($sess_context->isUserConnected()) { ?><button id="portfolio_add2_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Synth�se</button><? } ?>
		<? if ($sess_context->isUserConnected()) { ?><button id="portfolio_add1_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Portefeuille</button><? } ?>
	</h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">
			<div class="ui centered cards">
<?
				foreach($lst_portfolios as $key => $val) {

					// Calcul synthese portefeuille
					$portfolio_data = calc::aggregatePortfolio($val['id'], $quotes);
?>
					<div class="four wide column">
						<?= uimx::portfolioCard($val, $portfolio_data) ?>
					</div>
<?
				}
?>
    		</div>

		</div>
    </div>
</div>


<script>

<?
	foreach($lst_portfolios as $key => $val) { ?>
		Dom.addListener(Dom.id('portfolio_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=upt<?= $val['synthese'] == 1 ? "_synthese" : "" ?>&portfolio_id=<?= $val['id'] ?>', loading_area: 'main' }); });
		Dom.addListener(Dom.id('portfolio_dashboard_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $val['id'] ?>', loading_area: 'main' }); });
<?	
	}
?>

	Dom.addListener(Dom.id('portfolio_add1_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=new', loading_area: 'main' }); });
	Dom.addListener(Dom.id('portfolio_add2_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=new_synthese', loading_area: 'main' }); });

</script>