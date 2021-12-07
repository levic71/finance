<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = -1;

foreach(['portfolio_id', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

$libelle_action_bt = tools::getLibelleBtAction($action);

if ($action == "upt") {
    $req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);
    if (!$strategie = mysqli_fetch_assoc($res)) exit(0);
} else {
    $strategie['name'] = "";
    $strategie['strategie_id'] = 0;
}

// Dans le cadre de la création d'un portefeuille synthese, on recupère tous les portefeuilles de l'utilisateur
$all_portfolios = array();
if ($action == "new_synthese") {
    $req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) $all_portfolios[] = $row;
}

// Recuperation des strategies de l'utilisateur + defaut
$tab_strategies = array();
$req = "SELECT * FROM strategies WHERE defaut=1 OR user_id=".$sess_context->getUserId()." ORDER BY title ASC";
$res = dbc::execSql($req);
while($row = mysqli_fetch_assoc($res)) $tab_strategies[] = $row;

?>

<div class="ui inverted form">

    <input type="hidden" id="portfolio_id" value="<?= $portfolio_id ?>" />

    <div class="ui inverted clearing segment">
		<h2 class="ui inverted left floated header">
            <i class="inverted black briefcase icon"></i>&nbsp;&nbsp;Mon Portefeuille
        </h2>

        <? if ($action == "upt") { ?>
            <h3 class="ui right floated header"><i id="portfolio_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
        <? } ?>
    </h2>

    <div class="two fields">
        <div class="field">
            <label>Titre</label>
            <input type="text" id="f_nom" value="<?= $strategie['name'] ?>" placeholder="Nom du portefeuille">
        </div>
        <div class="field">
            <label>Choix d'une stratégie</label>
            <select id="f_strategie_id" class="ui selection dropdown">
                <option value="0" <?= $strategie['strategie_id'] == 1 ? "selected=\"selected\"" : "" ?>>Aucune</option>
                <? foreach ($tab_strategies as $key => $val) { ?>
                    <option value="<?= $val['id'] ?>" <?= $strategie['strategie_id'] == $val['id'] ? "selected=\"selected\"" : "" ?>><?= $val['title'] ?></option>
                <? } ?>
            </select>
        </div>
    </div>

<? if ($action = "new_synthese") { ?>
    <div class="two fields">
        <div class="field">
            <label>Sélection des portefeuilles</label>

            <? foreach ($all_portfolios as $key => $val) { ?>
                <button id="button_choice_<?= $val['id'] ?>" class="ui blue button strategie_choice" data-value="<?= $val['id'] ?>"><?= $val['name'] ?></button>
            <? } ?>

        </div>
        <div class="field">
        </div>
    </div>
<? } ?>

    <div class="ui grid">
        <div class="wide right aligned column">
            <div id="portfolio_cancel_bt" class="ui grey submit button">Cancel</div>
            <div id="portfolio_<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
        </div>
    </div>

</div>

<script>

Dom.addListener(Dom.id('portfolio_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio.php', loading_area: 'main' }); });

Dom.addListener(Dom.id('portfolio_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

    params = '?action=<?= $action ?>&'+attrs(['portfolio_id', 'f_nom', 'f_strategie_id' ]);

    <? if ($action == 'new_synthese') { ?>

        Dom.find('button.strategie_choice').forEach(function(item) {
        	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) { changeState(item); });
        });

    <? } ?>

	go({ action: 'portfolio', id: 'main', url: 'portfolio_action.php'+params, loading_area: 'main' });

});

<? if ($action == "upt") { ?>
	Dom.addListener(Dom.id('portfolio_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_action.php?action=del&portfolio_id=<?= $portfolio_id ?>', loading_area: 'main', confirmdel: 1 }); });
<? } ?>

changeState = function(item) {
    switchColorElement(item.id, 'blue', 'grey');
}

Dom.find('button.strategie_choice').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) { changeState(item); });
    alert(isCN(item.id, 'blue'));
});

</script>

