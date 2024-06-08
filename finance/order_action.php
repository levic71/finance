<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$order_id = 0;
$portfolio_id = 0;
$id_synthese = -1;
$from_stock_detail = 0;

foreach(['action', 'from_stock_detail', 'order_id', 'portfolio_id', 'id_synthese', 'f_date', 'f_product_name', 'f_action', 'f_quantity', 'f_price', 'f_commission', 'f_confirme', 'quotes', 'f_devise', 'f_taux_change'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "del" && isset($order_id) && $order_id != 0) {

    $req = "DELETE FROM orders WHERE id=".$order_id." AND portfolio_id=".$portfolio_id;
    $res = dbc::execSql($req);

}

if ($action == "new") {

    $pru = 0;
    if ($f_action == 1 || $f_action == -1) {

        $devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

        // Recuperation de tous les actifs
        $quotes = calc::getIndicatorsLastQuote();

        // Calcul synthese portefeuille
        $portfolio_data = calc::aggregatePortfolioById($portfolio_id);

        $sc = new StockComputing($quotes, $portfolio_data, $devises);

        $lst_positions = $sc->getPositions();

        // Prise en compte des actifs suivis manuellement
        $pname = calc::getPName($f_product_name);

        // Prise en compte du PRU au moment de l'insertion, pas de maj si update post insertion
        if (isset(($lst_positions[$pname]['pru']))) $pru = $lst_positions[$pname]['pru'];
    }

    $req = "INSERT INTO orders (portfolio_id, date, product_name, action, quantity, price, pru, commission, confirme, devise, taux_change) VALUES (".$portfolio_id.", '".$f_date."', '".$f_product_name."', ".$f_action.", ".$f_quantity.", ".$f_price.", ".$pru.", ".$f_commission.", ".$f_confirme.", '".$f_devise."', ".$f_taux_change.")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($order_id) && $order_id != 0) {

    $req = "UPDATE orders SET date='".$f_date."', product_name='".$f_product_name."', action=".$f_action.", quantity=".$f_quantity.", price=".$f_price.", commission=".$f_commission.", confirme=".$f_confirme.", devise='".$f_devise."', taux_change=".$f_taux_change.", portfolio_id=".$portfolio_id." WHERE id=".$order_id;
    $res = dbc::execSql($req);

}

// Save assets prices not folloxed and filled manually by user in portfolio data
if ($action == "save" && isset($portfolio_id) && $portfolio_id != 0) {

    $req = "UPDATE portfolios SET quotes='".$quotes."' WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

calc::resetCacheUserPortfolio($sess_context->getUserId());

?>

<script>
<? if ($action != "save") { ?>
    <? if ($from_stock_detail == 1) { ?>    
        go({ action: 'portfolio', id: 'main', url: 'stock_detail.php?symbol=<?= $f_product_name ?>&ptf_id=<?= $id_synthese != - 1 ? $id_synthese : $portfolio_id ?>' });
    <? } else {?>
        go({ action: 'portfolio', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $id_synthese != - 1 ? $id_synthese : $portfolio_id ?>' });
    <? } ?>
<? } ?>
    var p = loadPrompt();
    p.success('Ordre <?= ($action == "new" ? " ajouté": ($action == "upt" || $action == "save" ? " modifié" : " supprimé")) ?>');
</script>