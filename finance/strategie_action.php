<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

$f_common = 0;

foreach(['action', 'f_common', 'strategie_id', 'f_name', 'f_methode', 'f_nb_symbol_max', 'f_symbol_choice_1', 'f_symbol_choice_pct_1', 'f_symbol_choice_2', 'f_symbol_choice_pct_2', 'f_symbol_choice_3', 'f_symbol_choice_pct_3', 'f_symbol_choice_4', 'f_symbol_choice_pct_4', 'f_symbol_choice_5', 'f_symbol_choice_pct_5', 'f_symbol_choice_6', 'f_symbol_choice_pct_6', 'f_symbol_choice_7', 'f_symbol_choice_pct_7'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

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

if ($action == "new") {

    $req = "INSERT INTO strategies (title, data, methode, defaut, user_id) VALUES ('".$f_name."', '".$data."', ".$f_methode.", ".$f_common.", ".$sess_context->getUserId().")";
    $res = dbc::execSql($req);

}

if ($action == "upt" && isset($strategie_id) && $strategie_id != "") {

    $req = "UPDATE strategies SET title='".$f_name."', data='".$data."', methode='".$f_methode."', defaut=".$f_common." WHERE id=".$strategie_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

}

?>

<script>
    go({ action: 'home_content', id: 'main', url: 'home_content.php' });
    var p = loadPrompt();
    p.success('Stratégie <?= $f_name.($action == "new" ? " ajoutée": ($action == "upt" ? " modifiée" : " supprimée")) ?>');
</script>