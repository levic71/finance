<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$portfolio_id = 0;

foreach(['action', 'f_nom', 'f_strategie_id', 'portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "del" && isset($portfolio_id) && $portfolio_id != 0) {

    $req = "DELETE FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

if ($action == "new") {

    if (!$sess_context->isSuperAdmin()) {
        $req = "SELECT count(*) total FROM portfolios WHERE user_id=".$sess_context->getUserId();
        $res = dbc::execSql($req);
        $row = mysqli_fetch_array($res);

        if ($row['total'] >= 1) {
            echo '<div class="ui container inverted segment"><h3>Max portefeuille atteint !!!</h3></div>';
            exit(0);
        }
    }

    $req = "INSERT INTO portfolios (name, user_id, strategie_id) VALUES ('".$f_nom."', ".$sess_context->getUserId().", ".$f_strategie_id.")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($portfolio_id) && $portfolio_id != 0) {

    $req = "UPDATE portfolios SET name='".$f_nom."', strategie_id='".$f_strategie_id."' WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

?>

<script>
    go({ action: 'portfolio', id: 'main', url: 'portfolio.php' });
    var p = loadPrompt();
    p.success('Portfeuille <?= $f_nom.($action == "new" || $action == "copy"? " ajout�": ($action == "upt" ? " modifi�" : " supprim�")) ?>');
</script>