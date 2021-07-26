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
                <?= uimx::genCard('sim_card2', $row['title'], '', $infos); ?>
			</div>

<?

$tab = '
<table id="lst_sim" class="ui selectable inverted single line very compact unstackable table"><thead>
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

$tab_date = array();
$tab_valo = array();
$tab_invt = array();
$tab_perf = array();

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
        // tableau des perfs par symbol
        $tab_perf[$key][$day] = ($val == -9999 ? 0 : $val);
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

        $tab .= "<td>".$actifs_achetees_symbol."</td><td>".$actifs_achetees_nb."</td><td>".sprintf("%.2f", round($pu, 2)).$curr."</td><td class=\"".($perf >=0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $perf)."%</td>";

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
    $tab .= "<td>".sprintf("%.2f", $valo).$curr."</td><td class=\"".($perf >=0 ? "aaf-positive" : "aaf-negative")."\">".sprintf("%.2f", $perf)."%</td>";

    $tab .= "</tr>";

    $tab_date[] = $day;
    $tab_valo[] = $valo;
    $tab_invt[] = $invest_sum;
}
$tab .= "</tbody></table>";

$valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
$perf = $invest_sum == 0 ? 0 : round(($valo - $invest_sum)*100/$invest_sum, 2);
$final_info = "<table id=\"sim_final_info\">";
$final_info .= "<tr><td>Valorisation portefeuille</td><td>".sprintf("%.2f", $valo)." &euro;</td></tr>";
$final_info .= "<tr><td>Capital investit</td><td>".sprintf("%.2f", $invest_sum)." &euro;</td></tr>";
$final_info .= "<tr><td>Performance</td><td class=\"aaf-positive\">".sprintf("%.2f", $perf)." %</td></tr>";
$final_info .= "<tr><td>Max DD</td><td class=\"aaf-negative\">".sprintf("%.2f", $maxdd)." %</td></tr>";
$final_info .= "<tr><td>Duree</td><td>".count(tools::getMonth($date_start, $date_end))." mois</td></tr>";
$final_info .= "</table>";

?>
            <div class="eight wide column">
                <?= uimx::genCard('sim_card1', '', implode(', ', $lst), $final_info); ?>
            </div>

        </div>
    </div>
</div>

<div class="ui container inverted segment">
	<h2>Graphe</h2>
    <canvas id="sim_canvas1" height="100"></canvas>
</div>
<script>
// Our labels along the x-axis
var dates = [<?= '"'.implode('","', $tab_date).'"' ?>];
// For drawing the lines
var valos = [<?= implode(',', $tab_valo) ?>];
var invts = [<?= implode(',', $tab_invt) ?>];

var ctx = document.getElementById('sim_canvas1').getContext('2d');
el("sim_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
    labels: dates,
    datasets: [
        { 
            data: invts,
            label: "Investissement",
            borderColor: "rgba(238, 130, 6, 0.75)",
            backgroundColor: "rgba(238, 130, 6, 0.3)",
            fill: true
        },
        { 
            data: valos,
            label: "Valorisation",
            borderColor: "rgba(23, 109, 181, 0.75)",
            backgroundColor: "rgba(23, 109, 181, 0.3)",
            fill: true
        }
    ],
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
  }
});
</script>

<div class="ui container inverted segment">
	<h2>Evolution DM</h2>
    <canvas id="sim_canvas2" height="100"></canvas>
</div>

<script>
// For drawing the lines
var valos = [<?= implode(',', $tab_perf["BRE.PAR"]) ?>];
var invts = [<?= implode(',', $tab_perf["ESE.PAR"]) ?>]; 
var toto = [<?= implode(',', $tab_perf["PUST.PAR"]) ?>];
var toto2 = [<?= implode(',', $tab_perf["OBLI.PAR"]) ?>];

var ctx = document.getElementById('sim_canvas2').getContext('2d');
el("sim_canvas2").height = document.body.offsetWidth > 700 ? 100 : 300;

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
    labels: dates,
    datasets: [
        { 
            data: invts,
            label: "BRE.PAR",
            borderColor: "rgba(238, 130, 6, 0.75)",
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: toto,
            label: "PUST.PAR",
            borderColor: "rgba(97, 194, 97, 0.75)",
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: toto2,
            label: "OBLI.PAR",
            borderColor: "rgba(252, 237, 34, 0.75)",
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: valos,
            label: "ESE.PAR",
            borderColor: "rgba(23, 109, 181, 0.75)",
            borderWidth: 0.5,
            fill: false
        }
    ],
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
  }
});
</script>

<div class="ui container inverted segment">
	<h2>Detail</h2>
    <?= $tab ?>
</div>

<script>
	Dom.addListener(Dom.id('sim_go_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?capital_init='+valof('capital_init')+'&invest='+valof('invest')+'&date_start='+valof('date_start')+'&date_end='+valof('date_end'), loading_area: 'sim_go_bt' }); });
</script>