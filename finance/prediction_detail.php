<?

require_once "sess_context.php";

session_start();

include "common.php";

$prediction_id = -1;

foreach(['prediction_id', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('prediction');

$libelle_action_bt = tools::getLibelleBtAction($action);

// Récupération des devises
$devises = calc::getGSDevisesWithNoUpdate();

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

if ($action == "upt") {
    $req = "SELECT * FROM prediction WHERE id=".$prediction_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);
    if (!$row = mysqli_fetch_assoc($res)) exit(0);
} else {
    $row['date_avis']    = date('Y-m-d');
    $row['symbol']       = "";
    $row['cours']        = 0;
    $row['objectif']     = 0;
    $row['stoploss']     = 0;
    $row['conseiller']   = "";
    $row['status']       = 0;
}

?>

<div class="ui inverted form">

    <input type="hidden" id="prediction_id" value="<?= $prediction_id ?>" />

    <div class="ui inverted clearing segment">
		<h2 class="ui inverted left floated header"><i class="inverted magic icon"></i>Prédiction</h2>

        <? if ($action == "upt") { ?>
            <h3 class="ui right floated header"><i id="delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
        <? } ?>
    </div>

    <div class="seven fields">
        <div class="field" style="width: 150px;">
            <label>Date</label>
            <div class="ui right icon inverted left labeled fluid input">
                <input type="text" size="10" id="f_date" value="<?= $row['date_avis'] ?>" placeholder="0000-00-00">
                <i class="inverted black calendar alternate outline icon"></i>
            </div>
        </div>
        <div class="field">
            <label>Actif</label>
            <select id="f_actif" class="ui dropdown">
				<option value=""></option>
                <? foreach ($quotes["lst_actifs"] as $key => $val) { $q = $quotes["stocks"][$key]; ?>
                    <option value="<?= $key ?>" data-price="<?= sprintf("%.2f", $q['price']) ?>" data-currency="<?= $q['currency'] ?>" <?= $row['symbol'] == $key ? "selected=\"selected\"" : "" ?>><?= QuoteComputing::getQuoteNameWithoutExtension($key) ?></option>
                <? } ?>
            </select>
        </div>
        <div class="field">
            <label>Cours</label>
            <input type="text" size="10" id="f_cours" value="<?= $row['cours'] ?>" placeholder="0">
        </div>
        <div class="field">
            <label>Objectif</label>
            <input type="text" size="10" id="f_objectif" value="<?= $row['objectif'] ?>" placeholder="0">
        </div>
        <div class="field">
            <label>Stoploss</label>
            <input type="text" size="10" id="f_stoploss" value="<?= $row['stoploss'] ?>" placeholder="0">
        </div>
        <div class="field">
            <label>Conseiller</label>
            <select id="f_conseiller" class="ui dropdown">
                <? foreach (uimx::$conseillers as $key => $val) { ?>
                    <option value="<?= $key ?>" <?= $row['conseiller'] == $key ? "selected=\"selected\"" : "" ?>><?= $val ?></option>
                <? } ?>
            </select>
        </div>
        <div class="field">
            <label>Status</label>
            <select id="f_status" class="ui dropdown">
				<option value="0"  <?= $row['status'] == 0  ? "selected=\"selected\"" : "" ?>>En cours</option>
				<option value="1"  <?= $row['status'] == 1  ? "selected=\"selected\"" : "" ?>>Validée</option>
				<option value="-1" <?= $row['status'] == -1 ? "selected=\"selected\"" : "" ?>>Invalidée</option>
				<option value="-2" <?= $row['status'] == -2 ? "selected=\"selected\"" : "" ?>>Expirée</option>
            </select>
        </div>
    </div>

    <div class="ui grid">
        <div class="wide right aligned column">
            <div id="cancel_bt" class="ui grey submit button">Cancel</div>
            <div id="<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
        </div>
    </div>

</div>

<script>

const datepicker1 = new TheDatepicker.Datepicker(el('f_date'));
datepicker1.options.setInputFormat("Y-m-d")
datepicker1.render();

// Change sur selection produit
Dom.addListener(Dom.id('f_actif'), Dom.Event.ON_CHANGE, function(event) {

    item = Dom.id('f_actif');
	v = Dom.attribute(item.options[item.selectedIndex], 'data-price');

    // On positionne l'action a Achat si actif choisit
    Dom.attribute(Dom.id('f_cours'), { 'value': v });
});

// Cancel button
Dom.addListener(Dom.id('cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction.php', loading_area: 'main' }); });

// Add/Update button
Dom.addListener(Dom.id('<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

	// Controle si champ numerique
	if (!format_and_check_num('f_cours', 'Cours', 0, 999999999999))
		return false;

	if (!format_and_check_num('f_objectif', 'Objectif', 0, 999999999999))
		return false;

    if (!format_and_check_num('f_stoploss', 'Stoploss', 0, 999999999999))
		return false;

    item = Dom.id('f_actif');
    n = item.options[item.selectedIndex].value;

    params = '?action=<?= $action ?>'+attrs(['prediction_id', 'f_date', 'f_actif', 'f_cours', 'f_objectif', 'f_stoploss', 'f_conseiller', 'f_status' ]) + '&f_confirme='+(valof('f_confirme') == 0 ? 0 : 1);
 
	go({ action: 'prediction', id: 'main', url: 'prediction_action.php'+params, loading_area: 'main' });

});

// Del button
<? if ($action == "upt") { ?>
	Dom.addListener(Dom.id('delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction_action.php?action=del&prediction_id=<?= $prediction_id ?>', loading_area: 'main', confirmdel: 1 }); });
<? } ?>

</script>

