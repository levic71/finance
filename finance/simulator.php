<?

require_once "sess_context.php";

session_start();

include "common.php";

$strategie_id = -1;
$f_compare_to = "SPY";

foreach(['strategie_id', 'f_compare_to'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

if ($row['total'] != 1) {
    echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>"';
    exit(0);
}

$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$invest = $row['methode'] == 1 ? 1000 : 6000;
$cycle_invest = $row['methode'] == 1 ? 1 : 6;
$capital_init = 0;
$date_start = "0000-00-00";
$date_end = date("Y-m-d");

foreach(['strategie_id', 'invest', 'cycle_invest', 'date_start', 'date_end', 'capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$lst_symbols = array();
$lst_decode_symbols = json_decode($row['data'], true);
foreach($lst_decode_symbols['quotes'] as $key => $val) {
    $lst_symbols[] = $key;
    $d = calc::getMaxDailyHistoryQuoteDate($key);
    if ($d > $date_start) $date_start = $d;
}

// Cash disponible
$cash = $capital_init;
// Somme investie
$sum_invest = $capital_init;

$nb_mois = 0;
$valo_pf = 0;
$perf_pf = 0;
$maxdd = 0;

// Tableau pour mémoriser les ordres achats/ventes
// $ordres["2021-08-01"] = '{ "date": "2021-08-01", "symbol": "PUST.PAR", "quantity": "20", "price": "80" }';
// $o = json_decode($ordres[0]);
// echo $o->{"date"};
$ordres = array();

// Pour la gestion Best DM
$actifs_achetes_nb = 0;
$actifs_achetes_pu = 0;
$actifs_achetes_symbol = "";

// Pour la gestion Par Répartition
$lst_actifs_achetes_pu = array();
$lst_actifs_achetes_nb = array();
foreach($lst_decode_symbols['quotes'] as $key => $val) {
    $lst_actifs_achetes_pu[$key] = 0;
    $lst_actifs_achetes_nb[$key] = 0;
}

$infos = '
    <input type="hidden" id="strategie_id" value="'.$strategie_id.'" />
    <table id="sim_imput_card">
        <tr>
            <td>
                <div class="ui inverted fluid right labeled input">
                    <div class="ui label">Capital</div>
                    <input type="text" id="capital_init" value="'.$capital_init.'" placeholder="0">
                    <div class="ui basic label">&euro;</div>
                </div>
            </td>
            <td rowspan="5" style="vertical-align: bottom; text-align: right">
                <button id="sim_go_bt1" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
            </td>
        </tr>
        <tr>
            <td>
                <div class="ui inverted fluid right labeled input">
                    <div class="ui label">Invest. en &euro;</div>
                    <input type="text" id="invest" value="'.$invest.'" placeholder="0">
                    <div id="sim_par" class="ui floated right label">par</div>
                    <div class="ui inverted labeled input">
                        <select id="cycle_invest" class="ui selection">
                            <option value="1"  '.($cycle_invest == 1  ? "selected=\"selected\"" : "").'>mois</option>
                            <option value="3"  '.($cycle_invest == 3  ? "selected=\"selected\"" : "").'>trimestre</option>
                            <option value="6"  '.($cycle_invest == 6  ? "selected=\"selected\"" : "").'>semestre</option>
                            <option value="12" '.($cycle_invest == 12 ? "selected=\"selected\"" : "").'>an</option>
                        </select>
                    </div>
                </div>
            </td>
            <td class="rowspanned"></td>
        </tr>
        <tr>
            <td>
                <div class="ui right icon inverted left labeled fluid input">
                    <div class="ui label">Période</div>
                    <input type="text" size="12" id="date_start" value="'.$date_start.'" placeholder="0000-00-00">
                    <input type="text" size="12" id="date_end" value="'.$date_end.'" placeholder="0000-00-00">
                    <i class="inverted black calendar alternate outline icon"></i>

                </div>
            </td>
            <td class="rowspanned"></td>
        </tr>
        <tr>
            <td>
                <div class="ui inverted left labeled fluid input">
                    <div class="ui label">Comparer à</div>

                    <div class="ui inverted labeled input">
                        <select id="f_compare_to" class="ui selection">
                            <option value="SPY"  '.($f_compare_to == "SPY"  ? "selected=\"selected\"" : "").'>SPY</option>
                            <option value="TLT"  '.($f_compare_to == "TLT"  ? "selected=\"selected\"" : "").'>TLT</option>
                            <option value="SCZ"  '.($f_compare_to == "SCZ"  ? "selected=\"selected\"" : "").'>SCZ</option>
                        </select>
                    </div>
                </div>
            </td>
            <td class="rowspanned"></td>
        </tr>
    </table>
';

?>

<div class="ui container inverted segment">
    <h2>Informations</h2>
    <div class="ui stackable grid container">
          <div class="row">
            <div class="eight wide column">
                <?= uimx::genCard('sim_card2', $row['title'], '&nbsp;', $infos); ?>
            </div>

            <div class="center aligned eight wide column" id="sim_card_bt">
                <button id="sim_go_bt2" class="ui pink float right button">Go</button>
            </div>

<?

$tab_date = array();
$tab_valo = array();
$tab_invt = array();
$tab_perf = array();
$tab_detail = array();

// Pour le calcul du rendement comparatif
$sym_RC = $f_compare_to;
$cash_RC = 0;
$nb_actions_RC = 0;
$valo_pf_RC = 0;
$perf_pf_RC = 0;
$tab_valo_RC = array();

$i = date("Ym", strtotime($date_start));
while($i <= date("Ym", strtotime($date_end))) {

    // Item pour stocker dans le detail du tableau detail
    $detail = array();

    // Recuperation du dernier jour du mois 
    $day = date("Y-m-t", strtotime(substr($i, 0, 4)."-".substr($i, 4, 2)."-01"));

    // Recuperation du numero du mois
    $month = date("n", strtotime(substr($i, 0, 4)."-".substr($i, 4, 2)."-01"));

    // Recuperation du premier jour du mois 
    // $day = substr($i, 0, 4)."-".substr($i, 4, 2)."-01";

    // Cycle investissement ?
    if (fmod($month, $cycle_invest) == 0) {

        // On investit !!!
        $cash += $invest;
        $cash_RC += $invest;

        // On investit !!!
        $sum_invest += $invest;

        // BEST DM
        if ($row['methode'] == 1) {

            // Calcul du DM sur les valeurs selectionnees
            $data = calc::getDualMomentum("'".implode("', '", $lst_symbols)."'", $day);

            // Tri par performance decroissante en gardant l'index dui contient le symbol
            arsort($data["perfs"]);

            // Recuperation de l'actif le plus performant
            $best_quote = array_keys($data["perfs"])[0];

            $curr = $data["stocks"][$best_quote]['currency'] == "EUR" ? "&euro;" : "$";

            $info_title =  "[".$data["stocks"][$best_quote]["ref_day"]."]";

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

            $detail["tr_onclick"] = "Swal.fire({ title: '".$info_title."', icon: 'info', html: '".$info_content."' });";
            $detail["td_day"]     = $auMoinsUnActif ? $data["stocks"][$best_quote]["ref_day"] : $day;

            // Vente anciens actifs si different du nouveau plus performant
            if ($auMoinsUnActif && $actifs_achetes_nb > 0 && $actifs_achetes_symbol != $best_quote) {

                $pu = calc::getDailyHistoryQuote($actifs_achetes_symbol, $data["stocks"][$best_quote]["ref_day"]);
                $cash += $actifs_achetes_nb * $pu;

                $perf_pf = round(($pu - $actifs_achetes_pu)*100/$actifs_achetes_pu, 2);

                // Calcul max drawdown
                $maxdd = min($maxdd, $perf_pf);

                $detail["td_symbol_vendu"] = $actifs_achetes_symbol;
                $detail["td_nb_vendu"]     = $actifs_achetes_nb;
                $detail["td_pu_vendu"]     = sprintf("%.2f", round($pu, 2)).$curr;
                $detail["td_perf_vendu"]   = sprintf("%.2f", $perf_pf)."%";
                $detail["td_perf_vendu_val"] = $perf_pf;

                // Memorisation ordres
                $ordres[$detail["td_day"].":".$detail["td_symbol_vendu"]] = '{ "date": "'.$detail["td_day"].'", "action": "Vente", "symbol": "'.$detail["td_symbol_vendu"].'", "quantity": "'.abs($detail["td_nb_vendu"]).'", "price": "'.$detail["td_pu_vendu"].'", "currency": "'.$curr.'" }';

                $actifs_achetes_nb = 0;
            }
            else {
                $detail["td_symbol_vendu"] = "-";
                $detail["td_nb_vendu"]     = "-";
                $detail["td_pu_vendu"]     = "-";
                $detail["td_perf_vendu"]   = "-";
                $detail["td_perf_vendu_val"] = "0";
            }

            // Achat nouveaux actifs
            if ($auMoinsUnActif && $cash > 0) {

                $actifs_achetes_pu = $data["stocks"][$best_quote]["ref_close"];

                // achat nouveaux actifs
                $x = floor($cash / $actifs_achetes_pu);
                $actifs_achetes_nb = ($actifs_achetes_symbol == $best_quote) ? $actifs_achetes_nb + $x : $x;
                $cash -= $x * $actifs_achetes_pu;
                $actifs_achetes_symbol = $best_quote;

                $detail["td_symbol_achat"] = $actifs_achetes_symbol;
                $detail["td_nb_achat"]     = $x;
                $detail["td_pu_achat"]     = sprintf("%.2f", round($actifs_achetes_pu, 2)).$curr;

                // Memorisation ordres
                $ordres[$detail["td_day"].":".$detail["td_symbol_achat"]] = '{ "date": "'.$detail["td_day"].'", "action": "Achat", "symbol": "'.$detail["td_symbol_achat"].'", "quantity": "'.abs($detail["td_nb_achat"]).'", "price": "'.$detail["td_pu_achat"].'", "currency": "'.$curr.'" }';
            }
            else {
                $detail["td_symbol_achat"] = "-";
                $detail["td_nb_achat"]     = "-";
                $detail["td_pu_achat"]     = "-";
            }

            $valo_pf = round($cash+($actifs_achetes_nb * $actifs_achetes_pu), 2);
            $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf - $sum_invest)*100/$sum_invest, 2);

            $detail["td_cash"]          = sprintf("%.2f", round($cash, 2)).$curr;
            $detail["td_valo_pf"]       = sprintf("%.2f", $valo_pf).$curr;
            $detail["td_perf_glob"]     = sprintf("%.2f", $perf_pf)."%";
            $detail["td_perf_glob_val"] = $perf_pf;

            $tab_detail[] = $detail;
            $tab_date[] = $day;
            $tab_valo[] = $valo_pf;
            $tab_invt[] = $sum_invest;
        }
        // END BEST DM

        // CUMUL BY REPARTITION
        if ($row['methode'] == 2) {

            $curr = "&euro;";
            $valo_pf = 0;

            // Valeur de chaque actif au jour J & calcul valorisation portefeuille
            foreach($lst_actifs_achetes_nb as $key => $val) {
                // Recupereration de la dernière cotation du mois de chaque valeur
                $lst_actifs_achetes_pu[$key] = calc::getLastMonthDailyHistoryQuote($key, $day);
                $valo_pf += $lst_actifs_achetes_nb[$key] * $lst_actifs_achetes_pu[$key];
            }
            
            $valo_pf_avant_invest = $valo_pf + $cash;
            $valo_pf = 0;

            $lib_ordres_achats = "";
            $cash_ref = $cash;
            // Combien on achete de chaque ?
            foreach($lst_actifs_achetes_nb as $key => $val) {

                // Si on n'a pas d'histo pour cet actif a cette date on passe ...
                if ($lst_actifs_achetes_pu[$key] == 0) continue;

                // Montant par actif à posséder
                $montant = floor($valo_pf_avant_invest * $lst_decode_symbols['quotes'][$key] / 100);

                // Montant à acheter
                $montant2get = $montant - ($lst_actifs_achetes_nb[$key] * $lst_actifs_achetes_pu[$key]);

                $nb_actions2buy = 0;
                // Nombre d'actions à acheter
                // if ($montant2get >= 0)
                    $nb_actions2buy = floor($montant2get / $lst_actifs_achetes_pu[$key]);

                // Memorisation ordres
                $ordres[$day.":".$key] = '{ "date": "'.$day.'", "action": "'.($nb_actions2buy >= 0 ? "Achat" : "Vente").'", "symbol": "'.$key.'", "quantity": "'.abs($nb_actions2buy).'", "price": "'.$lst_actifs_achetes_pu[$key].'", "currency": "'.$curr.'" }';

                // if ($nb_actions2buy > 0)
                    $lib_ordres_achats .= ($lib_ordres_achats == "" ? "" : ", ").($lst_actifs_achetes_nb[$key]+$nb_actions2buy)." [".$key."] à ".$lst_actifs_achetes_pu[$key].$curr;

                // Cumul des actions acquises + achetees
                $lst_actifs_achetes_nb[$key] += $nb_actions2buy;

                // Calcul de la valorisation du portefeuille
                $valo_pf += $lst_actifs_achetes_nb[$key] * $lst_actifs_achetes_pu[$key];

                // Calcul cash restant
                $cash -= $nb_actions2buy * $lst_actifs_achetes_pu[$key];
            }

            // Performance 
            $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf - $sum_invest)*100/$sum_invest, 2);
            
            $detail['tr_onclick']   = "";
            $detail['td_day']       = $day;
            $detail['td_cash']      = sprintf("%.2f", round($cash, 2)).$curr;
            $detail['td_ordres']    = $lib_ordres_achats;
            $detail['td_valo_pf']   = sprintf("%.2f", round($valo_pf, 2)).$curr;
            $detail['td_perf_glob'] = sprintf("%.2f", $perf_pf)."%";
            $detail["td_perf_glob_val"] = $perf_pf;

            // Calcul max drawdown
            $maxdd = min($maxdd, $perf_pf);

            $tab_detail[] = $detail;
            $tab_date[] = $day;
            $tab_valo[] = $valo_pf;
            $tab_invt[] = $sum_invest;

        }
        // END CUMUL BY REPARTITION

        // Calcul pour le rendement comparatif
        if (true) {

            // Recupereration de la dernière cotation du mois de chaque valeur
            $pu_action_RC = calc::getLastMonthDailyHistoryQuote($sym_RC, $day);

            // Achat actif
            $nb_actions2buy = floor($cash_RC / $pu_action_RC);
            $cash_RC -= $nb_actions2buy*$pu_action_RC;
            $nb_actions_RC += $nb_actions2buy;

            // Valorisation portefeuille RC
            $tab_valo_RC[] = ($nb_actions_RC * $pu_action_RC) + $cash_RC;            
        }
        // End Calcul pour le rendement comparatif

    }
    // END Cycle Investissement

    $nb_mois++;

    if(substr($i, 4, 2) == "12")
        $i = (date("Y", strtotime($i."01")) + 1)."01";
    else
        $i++;

}

if ($row['methode'] == 1) {
    $valo_pf = round($cash+($actifs_achetes_nb * $actifs_achetes_pu), 2);
}
$perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf - $sum_invest)*100/$sum_invest, 2);

$final_info = '
    <table id="sim_final_info" class="">
        <tr><td>Valorisation</td><td>'.sprintf("%.2f", $valo_pf).' &euro;</td></tr>
        <tr><td>Capital investit</td><td>'.sprintf("%.2f", $sum_invest).' &euro;</td></tr>
        <tr><td>Performance</td><td class="'.($perf_pf >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $perf_pf).' %</td></tr>
        <tr><td>Max DD</td><td class="'.($maxdd >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $maxdd).' %</td></tr>
        <tr><td>Duree</td><td>'.count(tools::getMonth($date_start, $date_end)).' mois</td></tr>
    </table>
';

?>
            <div class="eight wide column">
                <?= uimx::genCard('sim_card1', 'Synthèse', implode(', ', $lst_symbols), $final_info); ?>
            </div>

        </div>
    </div>
</div>


<!-- GRAPHE 1 -->

<div class="ui container inverted segment">
    <h2>Evolution du portefeuille</h2>
    <canvas id="sim_canvas1" height="100"></canvas>
</div>
<script>
// Our labels along the x-axis
var dates = [<?= '"'.implode('","', $tab_date).'"' ?>];
// For drawing the lines
var valos = [<?= implode(',', $tab_valo) ?>];
var valos_RC = [<?= implode(',', $tab_valo_RC) ?>];
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
            borderColor: "rgba(97, 194, 97, 0.75)",
            backgroundColor: "rgba(97, 194, 97, 0.3)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: valos_RC,
            label: "<?= $sym_RC ?>",
            borderColor: "rgba(23, 109, 181, 0.75)",
            backgroundColor: "rgba(23, 109, 181, 0.3)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: valos,
            label: "Valorisation",
            borderColor: "rgba(238, 130, 6, 0.75)",
            backgroundColor: "rgba(238, 130, 6, 0.05)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: true
        }
    ]},
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
});
</script>


<!-- GRAPHE 2 -->

<? if ($row['methode'] == 1) { ?>

<div class="ui container inverted segment">
    <h2>Evolution DM</h2>
    <canvas id="sim_canvas2" height="100"></canvas>
</div>

<script>
// For drawing the lines
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "var dataset_".$x." = [ ".(isset($tab_perf[$val]) ? implode(',', $tab_perf[$val]) : '')." ];";
        $x++;
    }
?>

var ctx = document.getElementById('sim_canvas2').getContext('2d');
el("sim_canvas2").height = document.body.offsetWidth > 700 ? 100 : 300;

var data2 = {
    labels: dates,
    datasets: [
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "{ data: dataset_".$x.", label: \"".$val."\", borderColor: \"".$sess_context->getSpectreColor($x)."\", cubicInterpolationMode: 'monotone', tension: 0.4, borderWidth: 0.5, fill: false },";
        $x++;
    }
?>
    ]
}

var options2 = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
        xAxes: [{
            gridLines: {
                color: "red"
            }
        }],
        yAxes: [{
            gridLines: {
                color: "red"
            }
        }]
    }
};

var myChart = new Chart(ctx, { type: 'line', data: data2, options: options2 } );

</script>

<? } ?>


<!-- TABLEAU DETAIL -->

<div class="ui container inverted segment">
    <h2>Détail</h2>
    <table id="lst_sim" class="ui selectable inverted single line very compact unstackable table lst_sim_<?= $row['methode'] ?>">
        <thead>
            <tr>
                <th>Date</th>
                <th>Cash</th>

<? if ($row['methode'] == 1)
    echo '<th>Vente</th><th>Nb</th><th>PU</th><th>Perf</th><th>Achat</th><th>Nb</th><th>PU</th>';
else
    echo '<th>Ordres d\'achat</th><th></th><th></th><th></th><th></th><th></th><th></th>';
?>
                <th>Valorisation</th>
                <th>Perf</th>
            </tr>
        </thead>
        <tbody>
<?
foreach($tab_detail as $key => $val) {
    echo "<tr class=\"".($val[$row['methode'] == 1 ? "td_perf_vendu_val" : "td_perf_glob_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\" onclick=\"".$val['tr_onclick']."\">";
    if ($row['methode'] == 1) {
        foreach(['td_day', 'td_cash', 'td_symbol_vendu', 'td_nb_vendu', 'td_pu_vendu', 'td_perf_vendu', 'td_symbol_achat', 'td_nb_achat', 'td_pu_achat', 'td_valo_pf', 'td_perf_glob'] as $ind)
           echo "<td ".($ind == 'td_perf_vendu' || $ind == 'td_perf_glob' ? "class=\"".($val[$ind."_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\"" : "" ).">".$val[$ind]."</td>";
    } else {
        foreach(['td_day', 'td_cash', 'td_ordres', 'td_valo_pf', 'td_perf_glob'] as $ind)
            echo "<td ".($ind == 'td_ordres' ? "colspan=\"7\"" : "" )." ".($ind == 'td_perf_glob' ? "class=\"".($val[$ind."_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\"" : "" ).">".$val[$ind]."</td>";
    }
    echo "</tr>";
}
?>
        </tbody>
    </table>
</div>

<div class="ui container inverted segment">
    <h2>Ordres boursiers</h2>
    <table id="lst_ordres" class="ui selectable inverted single line very compact unstackable table">
        <thead><tr><th>Date</th><th><div>Action</div></th><th>Symbole</th><th>Nb</th><th>Prix</th></tr></thead>
        <tbody>
<?
foreach($ordres as $key => $val) {
    $o = json_decode($val);
    echo "<tr class=\"".$o->{"action"}."\"><td>".$o->{"date"}."</td><td><div>".$o->{"action"}."</div></td><td>".$o->{"symbol"}."</td><td>".$o->{"quantity"}."</td><td>".sprintf("%.2f", $o->{"price"}).$o->{"currency"}."</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<script>

    launcher = function() {
		params = attrs(['strategie_id', 'capital_init', 'invest', 'cycle_invest', 'date_start', 'date_end', 'f_compare_to' ]);
        go({ action: 'sim', id: 'main', url: 'simulator.php?'+params, loading_area: 'sim_go_bt' });
    }
    
    Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher(); });
    Dom.addListener(Dom.id('sim_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher(); });

    const datepicker1 = new TheDatepicker.Datepicker(el('date_start'));
    datepicker1.options.setInputFormat("Y-m-d")
    datepicker1.render();
    const datepicker2 = new TheDatepicker.Datepicker(el('date_end'));
    datepicker2.options.setInputFormat("Y-m-d")
    datepicker2.render();
</script>