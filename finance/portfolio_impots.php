<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach (['portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// Recuperation des infos du portefeuille
$req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!($row = mysqli_fetch_assoc($res))) { echo "Portefeuille inexistant"; exit(0); }

$name = $row['name'];

function plusoumoinsvalue($order) {

    $ret = "0";

    $nb = 0;
    $valo = 0;

    // On recupere tous les ordres d'achat/vente passés sur cet actif qui ont ete passe avant l'ordre de vente
    $req = "SELECT * FROM orders WHERE portfolio_id=".$order['portfolio_id']." AND product_name='".$order['product_name']."' AND (action=-1 OR action=1) AND date < '".$order['date']."' AND confirme=1 ";
    $res = dbc::execSql($req);

    while($row = mysqli_fetch_assoc($res)) {

        if ($row['action'] == 1) {
            $nb += $row['quantity'];
            $valo += $row['quantity'] * $row['price'];
        } else {
            $nb -= $row['quantity'];
            $valo -= $row['quantity'] * $row['price'];
        }

    }

    $pru = $nb == 0 ? 0 : $valo / $nb;

    $ret = $order['quantity'] * ($order['price'] - $pru);

    return $ret;

}

// Recuperation des ordres de vente depuis le début de l'année courante
//$date_ref = "2021-01-01";
$date_ref = date('Y')."-01-01";
$req = "SELECT * FROM orders WHERE portfolio_id=".$portfolio_id." AND action=-1 AND date >= '".$date_ref."' AND confirme=1 ORDER BY datetime ASC ";
$res = dbc::execSql($req);

$plusoumoinsvalue = [];

// Calcul du PRU pour chaque vente et on regarde si +/- value sur la vente
while($row = mysqli_fetch_assoc($res)) {

    if (isset($plusoumoinsvalue[$row['product_name']]))
        $plusoumoinsvalue[$row['product_name']]['gain'] += plusoumoinsvalue($row);
    else
        $plusoumoinsvalue[$row['product_name']]['gain'] = plusoumoinsvalue($row);

    $plusoumoinsvalue[$row['product_name']]['devise'] = $row['devise'];
    $plusoumoinsvalue[$row['product_name']]['taux_change'] = $row['taux_change'];
}

?>

<h2 class="ui left floated"><i class="inverted dollar icon"></i><?= $name ?> [ <?= date('Y') ?> ]</h2>

<div class="ui stackable column grid">
    <div class="row">
		<div class="column">
	        <table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal">
<?

$total = 0;
foreach($plusoumoinsvalue as $key => $val) {
    echo "<tr><td>".$key."</td><td>".sprintf("%.2f", $val['gain']).uimx::getCurrencySign($val['devise'])."</td></tr>";
    $total += $val['gain'] * $val['taux_change'];
}

echo "<tr><td>Total</td><td>".sprintf("%.2f", $total)."&euro;</td></tr>";

?>
            </table>
        </div>
    </div>
</div>

<script>

</script>