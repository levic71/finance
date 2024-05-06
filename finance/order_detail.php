<?

require_once "sess_context.php";

session_start();

include "common.php";

$order_id = -1;
$portfolio_id = -1;
$is_synthese = false;
$id_synthese = -1;
$from_stock_detail = 0;
$symbol = "";

foreach(['symbol', 'portfolio_id', 'order_id', 'action', 'id_synthese', 'from_stock_detail'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

$devises = calc::getGSDevisesWithNoUpdate();

$libelle_action_bt = tools::getLibelleBtAction($action);

if ($action == "upt") {

    // Recuperation des infos du ptf
    $req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
    $res = dbc::execSql($req);
    if (!$row = mysqli_fetch_assoc($res)) exit(0);

    // Si ptf de synthese alors on v�rifie que tout est coherant et on switche sur le portfolio_id port� par l'ordre tout en concervant une trace du ptf appelant
    if ($row['synthese'] == 1) {
        $tab_sub_ptf = explode(',', $row['all_ids']);
        if (!in_array($portfolio_id, $tab_sub_ptf)) exit(0);
        $is_synthese = true;
        $id_synthese = $portfolio_id;
    }

    // Recherche des infos de l'ordre � modifier
    $req = "SELECT * FROM orders WHERE id=".$order_id." AND portfolio_id=".$portfolio_id;
    $res = dbc::execSql($req);
    if (!$row = mysqli_fetch_assoc($res)) exit(0);

    // Switche sur le portfolio_id de l'ordre si syntheses
    if ($is_synthese) $portfolio_id = $row['portfolio_id'];

} else {
    $row['date']         = date('Y-m-d');
    $row['id']           = 0;
    $row['product_name'] = $symbol != "" ? $symbol : "Cash";
    $row['action']       = 0;
    $row['quantity']     = 0;
    $row['price']        = 0;
    $row['commission']   = 0;
    $row['confirme']     = 1;
    $row['devise']       = 'EUR';
    $row['taux_change']  = 1;
}

// Recuperation liste de mes ptf
$lst_ptfs = array();
$req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId()." AND synthese = 0";
$res = dbc::execSql($req);
while ($myptf = mysqli_fetch_assoc($res)) $lst_ptfs[$myptf['id']] = $myptf;

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

?>

<div class="ui inverted form">

    <input type="hidden" id="order_id" value="<?= $order_id ?>" />

    <div class="ui inverted clearing segment">
		<h2 class="ui inverted left floated header"><i class="inverted briefcase icon"></i>Mon Ordre</h2>

        <? if ($action == "upt") { ?>
            <h3 class="ui right floated header"><i id="order_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
        <? } ?>
    </div>

    <div class="six fields">
        <div class="field" style="width: 150px;">
            <label>Date</label>
            <div class="ui right icon inverted left labeled fluid input">
                <input type="text" size="10" id="f_date" value="<?= $row['date'] ?>" placeholder="0000-00-00">
                <i class="inverted black calendar alternate outline icon"></i>
            </div>
        </div>
        <div class="field">
            <label>Portefeuille</label>
            <select id="portfolio_id" class="ui dropdown">
                <? foreach ($lst_ptfs as $key => $val) { ?>
                    <option value="<?= $key ?>" <?= $portfolio_id == $key ? "selected=\"selected\"" : "" ?>><?= $val['name'] ?></option>
                <? } ?>
            </select>
        </div>
        <div class="field">
            <label>Actif</label>
            <select id="f_product_name" class="ui dropdown">
                <option value="Cash" data-price="0" <?= $row['product_name'] == "Cash" ? "selected=\"selected\"" : "" ?>>Cash</option>
                <? foreach ($quotes["stocks"] as $key => $val) { ?>
                    <? if ($row['product_name'] == $val['symbol']) { $row['price'] = $val['price']; $row['devise'] = $val['currency']; $row['action'] = 1; $row['taux_change'] = calc::getCurrencyRate($val['currency']."EUR", $devises); } ?>
                    <option value="<?= $val['symbol'] ?>" data-price="<?= sprintf("%.2f", $val['price']) ?>" data-currency="<?= $val['currency'] ?>" <?= $row['product_name'] == $val['symbol'] ? "selected=\"selected\"" : "" ?>><?= $val['symbol'] ?></option>
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
    </div>

    <div class="six fields">
        <div class="field">
            <label>Quantit�</label>
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
                    <option value="<?= $val ?>" data-taux="<?= calc::getCurrencyRate($val."EUR", $devises) ?>" <?= $row['devise'] == $val ? "selected=\"selected\"" : "" ?>><?= $val ?></option>
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
            <label>Confirm�</label>
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
	c = Dom.attribute(item.options[item.selectedIndex], 'data-currency');
	n = item.options[item.selectedIndex].value;

    // Si Cash
    if (!c) c = 'EUR';

    // On positionne l'action a Achat si actif choisit
    Dom.id('f_action').selectedIndex = item.selectedIndex > 0 ? 1 : 0;

    // Mise a jour de la devise en fct de l'actif + taux de change idoine
    devises = Dom.id('f_devise');
    for(i=0; i < devises.length; i++) {
        if (devises.options[i].value == c) devises.selectedIndex = i;
    }
    t = Dom.attribute(devises.options[devises.selectedIndex], 'data-taux');
    Dom.id('f_taux_change').value = t;

    Dom.attribute(Dom.id('f_price'), { 'value': v });

    if (n == 'AUTRE') show('f_other_name'); else hide('f_other_name');
});

// Change sur selection devise
Dom.addListener(Dom.id('f_devise'), Dom.Event.ON_CHANGE, function(event) {
    item = Dom.id('f_devise');
    Dom.id('f_taux_change').value = Dom.attribute(item.options[item.selectedIndex], 'data-taux');
});

// Cancel button
Dom.addListener(Dom.id('order_cancel_bt'), Dom.Event.ON_CLICK, function(event) {
    <? if ($from_stock_detail == 1) { ?>    
    go({ action: 'portfolio', id: 'main', url: 'stock_detail.php?symbol=<?= $row['product_name'] ?>&ptf_id=<?= $id_synthese != - 1 ? $id_synthese : $portfolio_id ?>', loading_area: 'main' });
    <? } else { ?>
    go({ action: 'portfolio', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $is_synthese ? $id_synthese : $portfolio_id ?>', loading_area: 'main' });
    <? } ?>
});

// Add/Update button
Dom.addListener(Dom.id('order_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

    // Controle si champ numerique
	if (!format_and_check_num('f_quantity', 'Quantit�', 1, 999999999999))
		return false;

	if (!format_and_check_num('f_price', 'Prix', 0, 999999999999))
		return false;

    if (!format_and_check_num('f_commission', 'Commission', 0, 999999999999))
		return false;

    if (!format_and_check_num('f_taux_change', 'Taux de change', 0, 999999999999))
		return false;

    item = Dom.id('f_product_name');
    n = item.options[item.selectedIndex].value;

    params = '?action=<?= $action ?>&'+attrs(['order_id', 'portfolio_id', 'f_date', 'f_action', 'f_quantity', 'f_price', 'f_commission', 'f_devise', 'f_taux_change' ]) + '&f_confirme='+(valof('f_confirme') == 0 ? 0 : 1);
    params += '&from_stock_detail=' + <?= $from_stock_detail ?> + '&id_synthese=' + <?= $id_synthese ?> + '&f_product_name=' + (n == 'AUTRE' ? 'AUTRE:' + encodeURIComponent(valof('f_other_name')) : encodeURIComponent(valof('f_product_name')));

	go({ action: 'order', id: 'main', url: 'order_action.php'+params, loading_area: 'main' });

});

// Del button
<? if ($action == "upt") { ?>
	Dom.addListener(Dom.id('order_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_action.php?action=del&order_id=<?= $order_id ?>&portfolio_id=<?= $portfolio_id ?>&id_synthese='+<?= $id_synthese ?>, loading_area: 'main', confirmdel: 1 }); });
<? } ?>

</script>

