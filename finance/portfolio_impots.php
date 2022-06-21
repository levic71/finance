<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;
$year = date('Y');

foreach (['portfolio_id', 'year'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// Recuperation des infos du portefeuille
$req = "SELECT *, YEAR(creation) year FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!($row = mysqli_fetch_assoc($res))) { echo "Portefeuille inexistant"; exit(0); }

$name = $row['name'];
$year_creation = $row['year'];

function plusoumoinsvalue($order) {

    $ret = "0";

    $nb = 0;
    $valo = 0;

    // echo '<br />'.$order['product_name'].'-'.$order['quantity'].'-'.$order['price']."<br />";

    // On recupere tous les ordres d'achat/vente pass�s sur cet actif qui ont ete passe avant l'ordre de vente
    $req = "SELECT * FROM orders WHERE portfolio_id=".$order['portfolio_id']." AND product_name='".$order['product_name']."' AND (action=-1 OR action=1) AND datetime < '".$order['datetime']."' AND confirme=1 ORDER BY datetime";
    $res = dbc::execSql($req);

    while($row = mysqli_fetch_assoc($res)) {

        // echo $row['product_name'].'-'.$row['quantity'].'-'.$row['price']."<br />";

        if ($row['action'] == 1) {
            $nb += $row['quantity'];
            $valo += $row['quantity'] * $row['price'];
        } else {
            $nb -= $row['quantity'];
            $valo -= $row['quantity'] * $row['price'];
        }

        // echo $nb."-".$valo.'<br />';

    }

    $pru = $nb == 0 ? 0 : $valo / $nb;

    // echo $pru.'<br />';

    $ret = $order['quantity'] * ($order['price'] - $pru);

    // echo $ret.'<br />';

    return $ret;

}

// Recuperation des ordres de vente depuis le d�but de l'ann�e courante
$date_deb = $year."-01-01";
$date_fin = $year."-31-12";
$req = "SELECT * FROM orders WHERE portfolio_id=".$portfolio_id." AND action=-1 AND date >= '".$date_deb."' AND date <= '".$date_fin."' AND confirme=1 ORDER BY datetime ASC ";
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

<h2 class="ui left floated">
    <i class="inverted dollar icon"></i><?= $name ?>
    <select id="year_select_bt">
        <?
            for($i=$year_creation; $i <= date('Y'); $i++) echo '<option value="'.$i.'" selected="'.($i == $year ? 'selected' : '').'">'.$i.'</option>';
        ?>
    </select>
</h2>

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
Dom.addListener(Dom.id('year_select_bt'), Dom.Event.ON_CHANGE, function(event) {
    element = Dom.id('year_select_bt');
    var selection = "";
    for (i=0; i < element.length; i++) if (element[i].selected) selection = element[i].value;
    if (selection != "") overlay.load('portfolio_impots.php', { 'portfolio_id' : <?= $portfolio_id ?>, 'year' : selection });
});
</script>