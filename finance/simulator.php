<?

include_once "include.php";

$capital_init = 0;
$invest = 1000;
$date_start = "2019-02-01";
$date_end = date("Y-m-d");

foreach(['invest', 'date_start', 'date_end', 'capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$lst = ["ESE.PAR", "BRE.PAR", "PUST.PAR", "OBLI.PAR"];
$capital = $capital_init;
$nb_mois = 0;
$actifs_achetees_nb = 0;
$actifs_achetees_pu = 0;
$actifs_achetees_symbol = "";

?>

<p><b>
    Simulation DM RP PEA<br />[<?= implode('-', $lst) ?>]<br />
    Capital Initial <input type="text" id="capital_init" value="<?= $capital_init ?>" />&euro;<br />
    Investissement <input type="text" id="invest" value="<?= $invest ?>" />&euro;/mois<br />
    Du <input type="text" id="date_start" value="<?= $date_start ?>" /> au <input type="text" id="date_end" value="<?= $date_end ?>" />
</b></p>

<pre><table id="lst_sim" class="table table-hover table-striped">
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
        <th>Info</th>
    </tr>
<?

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

    echo "<tr><td>".$data["stocks"][$best_quote]["ref_day"]."</td><td>".round($capital, 2)."</td>";

    // Vente anciens actifs si different du nouveau plus performant
    if ($actifs_achetees_nb > 0 && $actifs_achetees_symbol != $best_quote) {

        $pu = floatval(calc::getDailyHistoryQuote($actifs_achetees_symbol, $data["stocks"][$best_quote]["ref_day"]));
        $capital += $actifs_achetees_nb * $pu;

        $perf = round(($pu - $actifs_achetees_pu)*100/$actifs_achetees_pu, 2);

        echo "<td>".$actifs_achetees_symbol."</td><td>".$actifs_achetees_nb."</td><td>".round($pu, 2)."</td><td>".$perf."</td>";

        $actifs_achetees_nb = 0;
    }
    else {
        echo "<td>-</td><td>-</td><td>-</td><td>-</td>";
    }

    // Achat nouveaux actifs
    if ($capital > 0) {

        $actifs_achetees_pu = $data["stocks"][$best_quote]["ref_close"];

        // achat nouveaux actifs
        $x = floor($capital / $actifs_achetees_pu);
        $actifs_achetees_nb = ($actifs_achetees_symbol == $best_quote) ? $actifs_achetees_nb + $x : $x;
        $capital -= $x * $actifs_achetees_pu;
        $actifs_achetees_symbol = $best_quote;

        echo "<td>".$actifs_achetees_symbol."</td><td>".$x."</td><td>".round($actifs_achetees_pu, 2)."</td>";
    }


    $info_title =  "[".$data["stocks"][$best_quote]["ref_day"]."] => ".$best_quote;

    $info_content = "<ul>";
    foreach($data["perfs"] as $key => $val) {
        $info_content .= "<li>".$key." : ".$val."</li>";
    }
    $info_content .= "</ul>";

    if(substr($i, 4, 2) == "12")
        $i = (date("Y", strtotime($i."01")) + 1)."01";
    else
        $i++;

    $nb_mois++;

    $valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
    $invest_sum = $invest * $nb_mois +$capital_init;
    $perf = $invest_sum == 0 ? 0 : round(($valo - $invest_sum)*100/$invest_sum, 2);
    echo "<td>".$valo."</td><td>".$perf."%</td>";
    echo "<td><span class=\"icon info\" onclick=\"	Swal.fire({ title: '".$info_title."', icon: 'info', html: '".$info_content."' });\">An Icon</span></td>";

    echo "</tr>";
}

?></table></pre>

<?

    $valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
    $perf = $invest_sum == 0 ? 0 : round(($valo - $invest_sum)*100/$invest_sum, 2);
    echo "Valorisation portefeuille = ".$valo."&euro;<br />";
    echo "Capital investit = ".$invest_sum."&euro;<br />";
    echo "Performance = ".$perf."%<br />";

?>