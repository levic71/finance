<?

include_once "include.php";

$capital_init = 0;
$invest = 1000;
$date_start = "2019-02-01";
$date_end = date("Y-m-d");

foreach(['invest', 'date_start', 'date_end', 'capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT * FROM strategies WHERE id=1" ;
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$lst = array();
$t = json_decode($row['data'], true);
foreach($t['quotes'] as $key => $val)  $lst[] = $key;

$capital = $capital_init;
$nb_mois = 0;
$actifs_achetees_nb = 0;
$actifs_achetees_pu = 0;
$actifs_achetees_symbol = "";
$maxdd = 0;

$infos = '
    <table>
        <tr><td>Capital Initial</td><td><input type="text" id="capital_init" value="'.$capital_init.'" /> &euro;</td><td></td></tr>
        <tr><td>Investissement</td><td><input type="text" id="invest" value="'.$invest.'" /> &euro; par mois</td><td></td></tr>
        <tr><td>Du</td><td><input type="text" id="date_start" value="'.$date_start.'"></td><td></td></tr>
        <tr><td>Au</td><td><input type="text" id="date_end" value="'.$date_end.'"></td><td><button id="sim_go_bt" class="ui green float right small button">Go</button></td></tr>
    </table>
';

?>

<div class="ui stripe inverted segment">
    <h2>Informations</h2>
	<div class="ui stackable grid container">
      	<div class="row">
            <div class="eight wide column">
                <?= uimx::genCard($row['title'], '', $infos); ?>
			</div>

<?

$tab = '
<table id="lst_sim" class="ui selectable inverted single line compact table"><thead>
    <tr>
        <th>Date</th>
        <th>Cash</th>
        <th>Vente</th>
        <th>Nb</th>
        <th>PU</th>
        <th>Perf</th>
        <th>Achat</th>
        <th>Nb</th>
        <th>PU</th>
        <th>Valorisation</th>
        <th>Perf</th>
    </tr></thead><tbody>
';

$i = date("Ym", strtotime($date_start));
while($i <= date("Ym", strtotime($date_end))) {

    // On investit !!!
    $capital += $invest;

    // Recuperation du dernier jour du mois 
    $day = date("Y-m-t", strtotime(substr($i, 0, 4)."-".substr($i, 4, 2)."-01"));

    // Calcul du DM sur les valeurs selectionnees
    $data = calc::getDualMomentum("'".implode("', '", $lst)."'", $day);


    // Tri par performance decroissante en gardant l'index dui contient le symbol
    arsort($data["perfs"]);

    // Recuperation de l'actif le plus performant
    $best_quote = array_keys($data["perfs"])[0];

    $curr = $data["stocks"][$best_quote]['currency'] == "EUR" ? "&euro;" : "$";

    $info_title =  "[".$data["stocks"][$best_quote]["ref_day"]."] => ".$best_quote;

    $info_content = "<ul>";
    foreach($data["perfs"] as $key => $val) {
        $info_content .= "<li>".$key." : ".($val == -9999 ? "N/A" : $val)."</li>";
        // On retire l'actif qui n'a pas de DM faute de profondeur de data
        if ($val == -9999) unset($data["perfs"][$key]);
    }
    $info_content .= "</ul>";

    $auMoinsUnActif = count($data["perfs"]) == 0 ? false : true;

    $tab .= "<tr onclick=\"Swal.fire({ title: '".$info_title."', icon: 'info', html: '".$info_content."' });\">";
    $tab .= "<td>".($auMoinsUnActif ? $data["stocks"][$best_quote]["ref_day"] : $day)."</td><td>".round($capital, 2).$curr."</td>";

    // Vente anciens actifs si different du nouveau plus performant
    if ($auMoinsUnActif && $actifs_achetees_nb > 0 && $actifs_achetees_symbol != $best_quote) {

        $pu = calc::getDailyHistoryQuote($actifs_achetees_symbol, $data["stocks"][$best_quote]["ref_day"]);
        $capital += $actifs_achetees_nb * $pu;

        $perf = round(($pu - $actifs_achetees_pu)*100/$actifs_achetees_pu, 2);

        // Calcul max drawdown
        $maxdd = min($maxdd, $perf);

        $tab .= "<td>".$actifs_achetees_symbol."</td><td>".$actifs_achetees_nb."</td><td>".sprintf("%.2f", round($pu, 2)).$curr."</td><td style=\"color: ".($perf >=0 ? "green" : "red")."\">".sprintf("%.2f", $perf)."%</td>";

        $actifs_achetees_nb = 0;
    }
    else {
        $tab .= "<td>-</td><td>-</td><td>-</td><td>-</td>";
    }

    // Achat nouveaux actifs
    if ($auMoinsUnActif && $capital > 0) {

        $actifs_achetees_pu = $data["stocks"][$best_quote]["ref_close"];

        // achat nouveaux actifs
        $x = floor($capital / $actifs_achetees_pu);
        $actifs_achetees_nb = ($actifs_achetees_symbol == $best_quote) ? $actifs_achetees_nb + $x : $x;
        $capital -= $x * $actifs_achetees_pu;
        $actifs_achetees_symbol = $best_quote;

        $tab .= "<td>".$actifs_achetees_symbol."</td><td>".$x."</td><td>".sprintf("%.2f", round($actifs_achetees_pu, 2)).$curr."</td>";
    }
    else {
        $tab .= "<td>-</td><td>-</td><td>-</td>";
    }

    if(substr($i, 4, 2) == "12")
        $i = (date("Y", strtotime($i."01")) + 1)."01";
    else
        $i++;

    $nb_mois++;

    $valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
    $invest_sum = $invest * $nb_mois +$capital_init;
    $perf = $invest_sum == 0 ? 0 : round(($valo - $invest_sum)*100/$invest_sum, 2);
    $tab .= "<td>".sprintf("%.2f", $valo).$curr."</td><td style=\"color: ".($perf >=0 ? "green" : "red")."\">".sprintf("%.2f", $perf)."%</td>";

    $tab .= "</tr>";
}
$tab .= "</tbody></table>";

$valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
$perf = $invest_sum == 0 ? 0 : round(($valo - $invest_sum)*100/$invest_sum, 2);
$final_info = "<table id=\"sim_final_info\">";
$final_info .= "<tr><td>Valorisation portefeuille</td><td>".sprintf("%.2f", $valo)." &euro;</td></tr>";
$final_info .= "<tr><td>Capital investit</td><td>".sprintf("%.2f", $invest_sum)." &euro;</td></tr>";
$final_info .= "<tr><td>Performance</td><td style=\"color: green\">".sprintf("%.2f", $perf)." %</td></tr>";
$final_info .= "<tr><td>Max DD</td><td style=\"color: red\">".sprintf("%.2f", $maxdd)." %</td></tr>";
$final_info .= "<tr><td>Duree</td><td>".sprintf("%.2f", $perf)." %</td></tr>";
$final_info .= "</table>";

?>
            <div class="eight wide column">
                <?= uimx::genCard('Result', implode(', ', $lst), $final_info); ?>
            </div>

        </div>
    </div>
</div>

<div class="ui container inverted segment">
	<h2>Detail</h2>
    <?= $tab ?>
</div>

<script>
	Dom.addListener(Dom.id('sim_go_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?capital_init='+valof('capital_init')+'&invest='+valof('invest')+'&date_start='+valof('date_start')+'&date_end='+valof('date_end'), loading_area: 'sim_go_bt' }); });
</script>