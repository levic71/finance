<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;
$year = date('Y');

foreach (['portfolio_id', 'year'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

// Recuperation des infos du portefeuille
$req = "SELECT *, YEAR(creation) year FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!($row = mysqli_fetch_assoc($res))) { echo "Portefeuille inexistant"; exit(0); }

$name = $row['name'];
$year_creation = $row['year'];

// Recalcul de l'annee de reference si ptf synthese
if ($row['synthese'] == 1) {

    $req2 = "SELECT min(YEAR(creation)) year FROM portfolios WHERE id IN (".$row['all_ids'].") AND user_id=".$sess_context->getUserId();
    $res2 = dbc::execSql($req2);
    if (!($row2 = mysqli_fetch_assoc($res2))) { $year_creation = min($year_creation, $row2['year']); }

}

function plusoumoinsvalue($order) {

    $ret = "0";

    $nb = 0;
    $valo = 0;

    // echo '<br />'.$order['product_name'].'-'.$order['quantity'].'-'.$order['price']."<br />";

    // On recupere tous les ordres d'achat/vente passés sur cet actif qui ont ete passe avant l'ordre de vente
    $req = "SELECT * FROM orders WHERE portfolio_id=".$order['portfolio_id']." AND product_name='".$order['product_name']."' AND (action=-1 OR action=1) AND datetime < '".$order['datetime']."' AND confirme=1 ORDER BY datetime";
    $res = dbc::execSql($req);

    while($row = mysqli_fetch_assoc($res)) {

        // echo $row['product_name'].'-'.$row['quantity'].'-'.$row['price']."<br />";

        $nb   += ($row['action'] == 1 ? 1 : -1) * $row['quantity'];
        $valo += ($row['action'] == 1 ? 1 : -1) * $row['quantity'] * $row['price'] * $row['taux_change'];

        // echo $nb."-".$valo.'<br />';

    }

    $pru = $nb == 0 ? 0 : $valo / $nb;

    // echo $pru.'<br />';

    $ret = $order['quantity'] * (($order['price'] * $order['taux_change']) - $pru);

    // echo $ret.'<br />';

    return $ret;

}

// Recuperation des ordres de vente depuis le début de l'année courante
$date_deb = $year."-01-01";
$date_fin = ($year == "1900" ? date("Y") : $year)."-31-12";

// Selection du/des portefeuille
$select_where_ptf = $row['synthese'] == 1 ? "portfolio_id IN (".$row['all_ids'].")" : "portfolio_id=".$portfolio_id;

// Selection des ordres de vente
$req = "SELECT * FROM orders WHERE ".$select_where_ptf." AND action=-1 AND date >= '".$date_deb."' AND date <= '".$date_fin."' AND confirme=1 ORDER BY datetime ASC ";
$res = dbc::execSql($req);

$plusoumoinsvalue = [];

// Calcul du PRU pour chaque vente et on regarde si +/- value sur la vente
while($row = mysqli_fetch_assoc($res)) {

    if (isset($plusoumoinsvalue[$row['product_name']]))
        $plusoumoinsvalue[$row['product_name']]['gain'] += plusoumoinsvalue($row);
    else
        $plusoumoinsvalue[$row['product_name']]['gain'] = plusoumoinsvalue($row);

    $plusoumoinsvalue[$row['product_name']]['devise']      = $row['devise'];
    $plusoumoinsvalue[$row['product_name']]['taux_change'] = $row['taux_change'];
}

?>

<h2 class="ui left floated">
    <i class="inverted balance icon"></i><?= $name ?> <button id="balance_sum">0&euro;</button>
    <select id="year_select_bt" style="float: right">
        <option value="1900" <?= $year == "1900" ? 'selected="selected"' : '' ?>>All</option>';
        <?
            for($i=$year_creation; $i <= date('Y'); $i++) echo '<option value="'.$i.'" '.($i == $year ? 'selected="selected"' : '').'>'.$i.'</option>';
        ?>
    </select>
</h2>

<div class="ui stackable column grid">
    <div class="row">
		<div class="column">
	        <table id="tab_balance" class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal">
<?

$balance_sum = 0;
ksort($plusoumoinsvalue);
foreach($plusoumoinsvalue as $key => $val) {

    echo "<tr><td>".QuoteComputing::getQuoteNameWithoutExtension(calc::getPName($key))."</td><td class=\"right aligned ".($val['gain'] >=0 ? "aaf-positive" : "aaf-negative")."\">".($val['gain'] >=0 ? "+" : "").sprintf("%.2f", $val['gain'])."&euro;</td></tr>";
    $balance_sum += $val['gain'] * $val['taux_change'];
}

?>
            </table>
            <div id="pagination_box"></div>

        </div>
    </div>
</div>

<script>


// Maj Balance dans header
Dom.id('balance_sum').innerHTML = '<?= ($balance_sum >=0 ? '+' : '').sprintf("%.2f", $balance_sum) ?>' + '&euro;';
Dom.attribute(Dom.id('balance_sum'), { 'class': 'ui button <?= $balance_sum >=0 ? "aaf-positive" : "aaf-negative" ?>' } );

// Selection scope (ALL/2023/2022/...)
Dom.addListener(Dom.id('year_select_bt'), Dom.Event.ON_CHANGE, function(event) {
    element = Dom.id('year_select_bt');
    var selection = "";
    for (i=0; i < element.length; i++) if (element[i].selected) selection = element[i].value;
    if (selection != "") overlay.load('portfolio_balance.php', { 'portfolio_id' : <?= $portfolio_id ?>, 'year' : selection });
});

// Pagination
paginator({
	table: document.getElementById("tab_balance"),
    rows_per_page: 10,
	box: document.getElementById("pagination_box")
});

// Tri sur tableau
Sortable.initTable(el("tab_balance"));

</script>