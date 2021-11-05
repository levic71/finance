<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$f_common = 0;

foreach(['action', 'f_common', 'strategie_id', 'f_name', 'f_methode', 'f_cycle', 'f_nb_symbol_max', 'f_symbol_choice_1', 'f_symbol_choice_pct_1', 'f_symbol_choice_2', 'f_symbol_choice_pct_2', 'f_symbol_choice_3', 'f_symbol_choice_pct_3', 'f_symbol_choice_4', 'f_symbol_choice_pct_4', 'f_symbol_choice_5', 'f_symbol_choice_pct_5', 'f_symbol_choice_6', 'f_symbol_choice_pct_6', 'f_symbol_choice_7', 'f_symbol_choice_pct_7'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// On force à 0 pour les petits malins qui essaierai de forcer
if (!$sess_context->isSuperAdmin()) $f_common = 0;

if ($action != "del") {
    $tab_sym = array();
    foreach(range(1, $f_nb_symbol_max) as $number) {
        $v1 = "f_symbol_choice_".$number;
        $v2 = "f_symbol_choice_pct_".$number;
        $tab_sym[] = '"'.$$v1.'" : '.($f_methode == 1 ? 1 : $$v2);
    }
    $data = '{ "quotes" : { '.implode(', ', $tab_sym).' } }';
}

$db = dbc::connect();

if ($action == "del" && isset($strategie_id) && $strategie_id != "") {

    $req = "DELETE FROM strategies WHERE id=".$strategie_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

if ($action == "new" || $action == "copy") {

    if (!$sess_context->isSuperAdmin()) {
        $req = "SELECT count(*) total FROM strategies WHERE user_id=".$sess_context->getUserId();
        $res = dbc::execSql($req);
        $row = mysqli_fetch_array($res);

        if ($row['total'] >= 3) {
            echo '<div class="ui container inverted segment"><h3>Max stratégie atteint !!!</h3></div>';
            exit(0);
        }
    }

    $req = "INSERT INTO strategies (title, data, methode, cycle, defaut, user_id) VALUES ('".$f_name."', '".$data."', ".$f_methode.", ".$f_cycle.", ".$f_common.", ".$sess_context->getUserId().")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($strategie_id) && $strategie_id != "") {


    $req = "UPDATE strategies SET title='".$f_name."', data='".$data."', methode='".$f_methode."', cycle=".$f_cycle.", defaut=".$f_common." WHERE id=".$strategie_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

?>

<script>
    go({ action: 'home_content', id: 'main', url: 'home_content.php' });
    var p = loadPrompt();
    p.success('Stratégie <?= $f_name.($action == "new" || $action == "copy"? " ajoutée": ($action == "upt" ? " modifiée" : " supprimée")) ?>');
</script>