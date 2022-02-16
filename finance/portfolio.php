<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) { ?>
<script>
go({ action: 'login', id: 'main', url: 'login.php?redirect=1' });
</script>
<?
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

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

?>

<div class="ui container inverted">

	<h2 class="ui left floated">
	<? if ($sess_context->isUserConnected()) { ?>
		<i class="inverted briefcase icon"></i>Mes Portefeuilles

		<button id="nav_menu_bt" class="dropbtn circular ui right floated grey button icon_action"><i class="inverted white ellipsis vertical icon"></i></button>

		<div class="ui vertical menu nav" id="nav_menu" data-right="20">
			<a class="item" id="portfolio_add1_bt"><span>Ajouter un portefeuille</span></a>
			<a class="item" id="portfolio_add2_bt"><span>Ajouter une Synthèse</span></a>
		</div>

	<? } ?>

	</h2>

	<div class="ui stackable grid container" id="portfolio_box">
<?
			foreach($lst_portfolios as $key => $val) {
				// Calcul synthese portefeuille
				$portfolio_data = calc::aggregatePortfolio($val['id'], $quotes);
				uimx::portfolioCard($val, $portfolio_data);
			}
?>
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

<? if ($sess_context->isUserConnected()) { ?>
	Dom.addListener(Dom.id('portfolio_add1_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=new', loading_area: 'main' }); });
	Dom.addListener(Dom.id('portfolio_add2_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_detail.php?action=new_synthese', loading_area: 'main' }); });
	Dom.addListener(Dom.id('nav_menu_bt'), Dom.Event.ON_CLICK, function(event) { toogleCN('nav_menu', 'on'); });
<? } ?>


</script>