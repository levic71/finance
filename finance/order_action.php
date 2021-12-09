<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$order_id = 0;
$portfolio_id = 0;

foreach(['action', 'order_id', 'portfolio_id', 'f_date', 'f_product_name', 'f_action', 'f_quantity', 'f_price', 'f_commission', 'quotes'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "del" && isset($order_id) && $order_id != 0) {

    $req = "DELETE FROM orders WHERE id=".$order_id." AND portfolio_id=".$portfolio_id;
    $res = dbc::execSql($req);

}

if ($action == "new") {

    $req = "INSERT INTO orders (portfolio_id, date, product_name, action, quantity, price, commission) VALUES (".$portfolio_id.", '".$f_date."', '".$f_product_name."', ".$f_action.", ".$f_quantity.", ".$f_price.", ".$f_commission.")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($order_id) && $order_id != 0) {

    $req = "UPDATE orders SET date='".$f_date."', product_name='".$f_product_name."', action=".$f_action.", quantity=".$f_quantity.", price=".$f_price.", commission=".$f_commission." WHERE id=".$order_id." AND portfolio_id=".$portfolio_id;
    $res = dbc::execSql($req);

}

// Save assets prices not folloxed and filled manually by user in portfolio data
if ($action == "save" && isset($portfolio_id) && $portfolio_id != 0) {

    $req = "UPDATE portfolios SET quotes='".$quotes."' WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}


?>

<script>
<? if ($action != "save") { ?>
    go({ action: 'order', id: 'main', url: 'order.php?portfolio_id=<?= $portfolio_id ?>' });
<? } ?>
    var p = loadPrompt();
    p.success('Ordre <?= ($action == "new" ? " ajouté": ($action == "upt" || $action == "save" ? " modifié" : " supprimé")) ?>');
</script>