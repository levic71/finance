<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$prediction_id = 0;

foreach(['action', 'prediction_id', 'f_date', 'f_actif', 'f_cours', 'f_objectif', 'f_stoploss', 'f_conseiller', 'f_status'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "del" && isset($prediction_id) && $prediction_id != 0) {

    $req = "DELETE FROM prediction WHERE id=".$prediction_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

if ($action == "new") {

    $req = "INSERT INTO prediction (date_avis, symbol, user_id, cours, objectif, stoploss, conseiller, date_status, status) VALUES ('".$f_date."', '".$f_actif."', ".$sess_context->getUserId().", '".$f_cours."', '".$f_objectif."', '".$f_stoploss."', '".$f_conseiller."', '".$f_date."', ".$f_status.")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($prediction_id) && $prediction_id != 0) {

    $req = "UPDATE prediction SET date_avis='".$f_date."', symbol='".$f_actif."', cours='".$f_cours."', objectif='".$f_objectif."', stoploss='".$f_stoploss."', conseiller='".$f_conseiller."', status=".$f_status." WHERE id=".$prediction_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

?>

<script>
<? if ($action != "save") { ?>
    go({ action: 'prediction', id: 'main', url: 'prediction.php' });
<? } ?>
    var p = loadPrompt();
    p.success('Prédiction <?= ($action == "new" ? " ajoutée": ($action == "upt" || $action == "save" ? " modifiée" : " supprimée")) ?>');
</script>