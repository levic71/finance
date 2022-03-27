<?

require_once "sess_context.php";

session_start();

include "common.php";

$order_id = -1;
$portfolio_id = -1;

foreach(['portfolio_id', 'order_id', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

$libelle_action_bt = tools::getLibelleBtAction($action);

if ($action == "upt") {
    $req = "SELECT * FROM orders WHERE id=".$order_id." AND portfolio_id=".$portfolio_id;
    $res = dbc::execSql($req);
    if (!$row = mysqli_fetch_assoc($res)) exit(0);
} else {
    $row['date']         = date('Y-m-d');
    $row['id']           = 0;
    $row['product_name'] = "Cash";
    $row['action']       = 0;
    $row['quantity']     = 0;
    $row['price']        = 0;
    $row['commission']   = 0;
    $row['confirme']     = 1;
    $row['devise']       = 'EUR';
    $row['taux_change']  = 1;
}

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

?>

<div class="ui inverted form">

    <input type="hidden" id="order_id" value="<?= $order_id ?>" />
    <input type="hidden" id="portfolio_id" value="<?= $portfolio_id ?>" />

    <div class="ui inverted clearing segment">
		<h2 class="ui inverted left floated header"><i class="inverted briefcase icon"></i>Mon Ordre</h2>

        <? if ($action == "upt") { ?>
            <h3 class="ui right floated header"><i id="order_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
        <? } ?>
    </div>

    <div class="six fields">
        <div class="field">
            <label>Date</label>
            <div class="ui right icon inverted left labeled fluid input">
                <input type="text" size="10" id="f_date" value="<?= $row['date'] ?>" placeholder="0000-00-00">
                <i class="inverted black calendar alternate outline icon"></i>
            </div>
        </div>
        <div class="field">
            <label>Actif</label>
            <select id="f_product_name" class="ui dropdown">
                <option value="Cash" data-price="0" <?= $row['product_name'] == "Cash" ? "selected=\"selected\"" : "" ?>>Cash</option>
                <? foreach ($quotes["stocks"] as $key => $val) { ?>
                    <option value="<?= $val['symbol'] ?>" data-price="<?= sprintf("%.2f", $val['price']) ?>" <?= $row['product_name'] == $val['symbol'] ? "selected=\"selected\"" : "" ?>><?= $val['symbol'] ?></option>
                <? } ?>
                <option value="AUTRE" data-price="0" <?= substr($row['product_name'], 0, 5) == "AUTRE" ? "selected=\"selected\"" : "" ?>>Autre</option>
            </select>
            <input type="text" id="f_other_name" value="<?= substr($row['product_name'], 0, 5) == "AUTRE" ? substr($row['product_name'], 6) : "" ?>" style="<?= substr($row['product_name'], 0, 5) == "AUTRE" ? "" : "display: none;" ?> margin-top: 5px;" />
        </div>
        <div class="field">
            <label>Action</label>
            <select id="f_action" class="ui dropdown">
                <? foreach (uimx::$order_actions as $key => $val) { ?>
                    <option value="<?= $key ?>" <?= $row['action'] == $key ? "selected=\"selected\"" : "" ?>><?= $val ?></option>
                <? } ?>
            </select>
        </div>
        <div class="field">
            <label>Quantité</label>
            <input type="text" size="10" id="f_quantity" value="<?= $row['quantity'] ?>" placeholder="0">
        </div>
        <div class="field">
            <label>Prix</label>
            <div class="ui inverted left fluid input">
                <input type="text" size="10" id="f_price" value="<?= $row['price'] ?>" placeholder="0">
            </div>
        </div>
        <div class="field">
            <label>Devise</label>
            <select id="f_devise" class="ui dropdown">
                <? foreach ([ 'EUR', 'USD'] as $key => $val) { ?>
                    <option value="<?= $key ?>" <?= $row['devise'] == $key ? "selected=\"selected\"" : "" ?>><?= $val ?></option>
                <? } ?>
            </select>
        </div>
        <div class="field">
            <label>Taux de change</label>
            <div class="ui inverted fluid input">
                <input type="text" size="10" id="f_taux_change" value="<?= $row['taux_change'] ?>" placeholder="0">
            </div>
        </div>
        <div class="field">
            <label>Commission</label>
            <div class="ui right icon inverted left labeled fluid input">
                <input type="text" size="10" id="f_commission" value="<?= $row['commission'] ?>" placeholder="0">
                <i class="inverted black euro icon"></i>
            </div>
        </div>
        <div class="field">
            <label>Confirmé</label>
            <div class="ui toggle inverted checkbox" onclick="toogleCheckBox('f_confirme');">
                <input type="checkbox" id="f_confirme" <?= $row['confirme'] == 1 ? 'checked="checked' : '' ?> tabindex="0" class="hidden">
                <label></label>
            </div>
        </div>
    </div>

    <div class="ui grid">
        <div class="wide right aligned column">
            <div id="order_cancel_bt" class="ui grey submit button">Cancel</div>
            <div id="order_<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
        </div>
    </div>

</div>

<script>

const datepicker1 = new TheDatepicker.Datepicker(el('f_date'));
datepicker1.options.setInputFormat("Y-m-d")
datepicker1.render();

// Change sur selection produit
Dom.addListener(Dom.id('f_product_name'), Dom.Event.ON_CHANGE, function(event) {
	item = Dom.id('f_product_name');
	v = Dom.attribute(item.options[item.selectedIndex], 'data-price');
	n = item.options[item.selectedIndex].value;
	Dom.attribute(Dom.id('f_price'), { 'value': v });
    if (n == 'AUTRE') show('f_other_name'); else hide('f_other_name');
});

// Cancel button
Dom.addListener(Dom.id('order_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' }); });

// Add/Update button
Dom.addListener(Dom.id('order_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

	// Controle si champ numerique
	if (!check_num(valof('f_quantity'), 'Quantité', 1, 999999999999))
		return false;

	if (!check_num(valof('f_price'), 'Prix', 0, 999999999999))
		return false;

	if (!check_num(valof('f_commission'), 'Commission', 0, 999999999999))
		return false;

    item = Dom.id('f_product_name');
    n = item.options[item.selectedIndex].value;

    params = '?action=<?= $action ?>&'+attrs(['order_id', 'portfolio_id', 'f_date', 'f_action', 'f_quantity', 'f_price', 'f_commission', 'f_devise', 'f_taux_change' ]) + '&f_confirme='+(valof('f_confirme') == 0 ? 0 : 1);
    params += '&f_product_name=' + (n == 'AUTRE' ? 'AUTRE:' + encodeURIComponent(valof('f_other_name')) : encodeURIComponent(valof('f_product_name')));

	go({ action: 'order', id: 'main', url: 'order_action.php'+params, loading_area: 'main' });

});

// Del button
<? if ($action == "upt") { ?>
	Dom.addListener(Dom.id('order_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_action.php?action=del&order_id=<?= $order_id ?>&portfolio_id=<?= $portfolio_id ?>', loading_area: 'main', confirmdel: 1 }); });
<? } ?>

</script>

