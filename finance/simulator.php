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
$row = mysqli_fetch_assoc($res);

if ($row['total'] != 1) {
    echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>"';
    exit(0);
}

$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_assoc($res);

$invest = $row['methode'] == 1 ? 1000 : 6000;
$cycle_invest = $row['methode'] == 1 ? 1 : 6;
$capital_init = 0;
$date_start = "0000-00-00";
$date_end = date("Y-m-d");
$f_retrait = 0;
$f_montant_retrait = 500;
$f_delai_retrait = 1;

foreach(['f_retrait', 'f_montant_retrait', 'f_delai_retrait', 'strategie_id', 'invest', 'cycle_invest', 'date_start', 'date_end', 'capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$lst_symbols = array();
$lst_decode_symbols = json_decode($row['data'], true);
foreach($lst_decode_symbols['quotes'] as $key => $val) {
    $lst_symbols[] = $key;
    $d = calc::getMaxDailyHistoryQuoteDate($key);
    if ($d > $date_start) $date_start = $d;
}

// Cash disponible
$cash    = $capital_init;
$cash_RC = $capital_init;
// Somme investie
$sum_invest = $capital_init;

$nb_mois = 0;
$valo_pf = 0;
$perf_pf = 0;
$maxdd_min = 999999999999;
$maxdd_max = 0;
$maxdd = 0;
$retrait_cumule = 0;

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

$infos1 = '
<table id="sim_imput_card">
    <tr>
        <td>
            <div class="ui inverted fluid right labeled input">
                <div class="ui label">Capital</div>
                <input type="text" id="capital_init" value="'.$capital_init.'" size="8" placeholder="0">
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
                <input type="text" id="invest" value="'.$invest.'" placeholder="0" size="10">
                <div id="sim_par" class="ui floated right label" style="margin-left: 5px;">par</div>
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
                <input type="text" size="10" id="date_start" value="'.$date_start.'" placeholder="0000-00-00">
                <input type="text" size="10" id="date_end" value="'.$date_end.'" placeholder="0000-00-00" style="margin-left: 10px">
                <i class="inverted black calendar alternate outline icon"></i>

            </div>
        </td>
        <td class="rowspanned"></td>
    </tr>
</table>
';

$infos2 = '
<table id="sim_imput_card">
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div class="ui label">Benchmark</div>

                <div class="ui inverted labeled input">
                    <select id="f_compare_to" class="ui selection">
                        <option value="SPY"  '.($f_compare_to == "SPY"  ? "selected=\"selected\"" : "").'>SPY</option>
                        <option value="TLT"  '.($f_compare_to == "TLT"  ? "selected=\"selected\"" : "").'>TLT</option>
                        <option value="SCZ"  '.($f_compare_to == "SCZ"  ? "selected=\"selected\"" : "").'>SCZ</option>
                    </select>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div class="ui label">Retrait progressif</div>
                <div class="ui fitted toggle checkbox" style="padding: 8px 0px;">
                    <input id="f_retrait" type="checkbox" '.($f_retrait == 1 ? 'checked="checked"' : '').' />
                    <label></label>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div id="retrait_option1" style="width: 100%">
                    <div class="ui label">Montant</div>
                    <div class="ui inverted labeled input">
                        <input type="text" id="f_montant_retrait" value="'.$f_montant_retrait.'" placeholder="0" size="6">
                    </div>
                    <div class="ui label">Délai</div>
                    <div class="ui inverted labeled input">
                        <input type="text" id="f_delai_retrait" value="'.$f_delai_retrait.'" placeholder="0" size="3">
                    </div>
                    <div class="ui basic label">An(s)</div>
                </div>
            </div>
        </td>
    </tr>
</table>
';

?>

<input type="hidden" id="strategie_id" value="<?= $strategie_id ?>" />

<div class="ui container inverted segment">
    <h2>Informations</h2>
    <div class="ui stackable grid container">
          <div class="row">
          <div class="eight wide column">
                <?= uimx::genCard('sim_card2', '<i style="margin-right: 10px;" class="inverted '.($row['methode'] == 2 ? 'cubes' : 'diamond').' blurely line icon"></i>'.$row['title'], '', $infos1); ?>
            </div>
            <div class="eight wide column">
                <?= uimx::genCard('sim_card2', implode(', ', $lst_symbols), '', $infos2); ?>
            </div>

            <div class="center aligned sixteen wide column" id="sim_card_bt">
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
$nb_actions_RC = 0;
$valo_pf_RC = 0;
$perf_pf_RC = 0;
$tab_valo_RC = array();
$maxdd_RC_min = 99999999999999;
$maxdd_RC_max = 0;
$maxdd_RC = 0;

// /////////////////////////////////////////////////////////////
// On boucle sur les mois depuis date_start jusqu'a date_end
// /////////////////////////////////////////////////////////////
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

    // Retrait programmé ?
    $retrait_programme = false;
    if ($f_retrait == 1) {
        if ($i >=  date("Ym", strtotime((intval(substr($date_start, 0, 4)) + $f_delai_retrait)."-".substr($date_start, 5, 2)."-01"))) {
            $retrait_programme = true;
        }
    }

    // /////////////////////////////////////////////
    // Cycle investissement ?
    // /////////////////////////////////////////////
    if (fmod($month, $cycle_invest) == 0) {

        // On investit !!!
        $cash += $invest;
        $cash_RC += $invest;

        // On investit !!!
        $sum_invest += $invest;

        // //////////////////////////////////////////////////////////////
        // BEST DM
        // //////////////////////////////////////////////////////////////
        if ($row['methode'] == 1) {

            // Calcul du DM sur les valeurs selectionnees
            $data = calc::getLastDayMonthQuoteIndicators($lst_symbols, $day);

            // Tri par performance decroissante en gardant l'index qui contient le symbol
            arsort($data["perfs"]);

            // Recuperation de l'actif le plus performant
            if (count(array_keys($data["perfs"])) != 0) {
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
                
                $auMoinsUnActif = count($data["perfs"]) > 0 ? true : false;

                $detail["tr_onclick"] = "Swal.fire({ title: '".$info_title."', icon: 'info', html: '".$info_content."' });";
                $detail["td_day"]     = $auMoinsUnActif ? $data["stocks"][$best_quote]["ref_day"] : $day;

                $pu = $actifs_achetes_symbol == "" ? 0 : calc::getDailyHistoryQuote($actifs_achetes_symbol, $data["stocks"][$best_quote]["ref_day"]);

                // Vente anciens actifs si different du nouveau plus performant
                if ($auMoinsUnActif && $actifs_achetes_nb > 0 && $actifs_achetes_symbol != $best_quote) {

                    $cash += $actifs_achetes_nb * $pu;

                    $perf_pf = $actifs_achetes_pu == 0 ? 0 : round(($pu - $actifs_achetes_pu)*100/$actifs_achetes_pu, 2);

                    // Calcul max drawdown
                    $maxdd_min = min($maxdd_min, $valo_pf);
                    $maxdd_max = max($maxdd_max, $valo_pf);

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

                // Retrait programmé
                if ($retrait_programme) {
                
                    // Retrait de l'invest precedent ajouté
                    $cash       -= $invest;
                    $sum_invest -= $invest;

                    // Ajustement retrait cumulé
                    $retrait_cumule += intval($f_montant_retrait);

                    // On ampule le cash du retrait
                    if ($cash <= intval($f_montant_retrait)) {

                        if ($auMoinsUnActif && ($actifs_achetes_nb * $pu) > intval($f_montant_retrait)) {

                            // Calcul nb actifs a vendre
                            $nb_actifs_a_vendre = floor(intval($f_montant_retrait) / $pu);

                            // Ajustement du nb d'actifs detenu
                            $actifs_achetes_nb -= $nb_actifs_a_vendre;

                        }

                    }
                    
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
                $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf + $retrait_cumule - $sum_invest)*100/$sum_invest, 2);

                $detail["td_cash"]          = sprintf("%.2f", round($cash, 2)).$curr;
                $detail["td_valo_pf"]       = sprintf("%.2f", $valo_pf).$curr;
                $detail["td_perf_glob"]     = sprintf("%.2f", $perf_pf)."%";
                $detail["td_perf_glob_val"] = $perf_pf;

                $tab_detail[] = $detail;
                $tab_date[] = $day;
                $tab_valo[] = $valo_pf;
                $tab_invt[] = $sum_invest;
            }
        }
        // END BEST DM

        // //////////////////////////////////////////////////////////////
        // DCA
        // //////////////////////////////////////////////////////////////
        if ($row['methode'] == 2) {

            $curr = "&euro;";
            $recap_actifs_portefeuille = "";

            // Recupereration de la dernière cotation du mois de chaque valeur
            foreach($lst_actifs_achetes_nb as $key => $val)
                $lst_actifs_achetes_pu[$key] = calc::getLastMonthDailyHistoryQuote($key, $day);

            // Retrait programmé ?
            if ($retrait_programme) {

                // Retrait de l'invest precedent ajouté
                $sum_invest -= $invest;

                // Ajustement retrait cumulé
                $retrait_cumule += intval($f_montant_retrait);

                // Il faut determiner combien de chaque action il faut vendre et les retirer du portfolio pour un montant de f_montant_retrait
                $panier = calc::getAchatActifsDCAInvest($day, $lst_decode_symbols['quotes'], $lst_actifs_achetes_pu, $f_montant_retrait);

                // Intégration des ventes au portefeuille
                foreach($panier["buy"] as $key => $val) {
                    $symbol = $val['sym'];

                    // Memorisation ordres
                    $ordres[$day.":".$symbol] = '{ "date": "'.$day.'", "action": "Vente", "symbol": "'.$symbol.'", "quantity": "'.abs($val['nb']).'", "price": "'.$val['pu'].'", "currency": "'.$curr.'" }';

                    // Ajustement du nb d'actif detenu
                    $lst_actifs_achetes_nb[$symbol] -= $val['nb'] > $lst_actifs_achetes_nb[$symbol] ? $lst_actifs_achetes_nb[$symbol] : $val['nb'];
                }

            } else {

                // Combien on achete de chaque actif en DCA
                $panier = calc::getAchatActifsDCAInvest($day, $lst_decode_symbols['quotes'], $lst_actifs_achetes_pu, $cash);

                // Intégration des achats au portefeuille
                foreach($panier["buy"] as $key => $val) {
                    $symbol = $val['sym'];

                    // Memorisation ordres
                    $ordres[$day.":".$symbol] = '{ "date": "'.$day.'", "action": "'.($val['nb'] >= 0 ? "Achat" : "Vente").'", "symbol": "'.$symbol.'", "quantity": "'.abs($val['nb']).'", "price": "'.$val['pu'].'", "currency": "'.$curr.'" }';

                    // Cumul des actions acquises + achetees
                    $lst_actifs_achetes_nb[$symbol] += $val['nb'];

                    // Calcul cash restant
                    $cash -= $val['nb'] * $val['pu'];
                }
            }

            $valo_pf = 0;
            foreach($lst_decode_symbols['quotes'] as $key => $val) { 
                // Calcul de la valorisation du portefeuille
                $valo_pf += $lst_actifs_achetes_nb[$key] * $lst_actifs_achetes_pu[$key];

                // Recap actifs dans portefeuille
                // if ($nb_actions2buy > 0)
                $recap_actifs_portefeuille .= ($recap_actifs_portefeuille == "" ? "" : ", ").$lst_actifs_achetes_nb[$key]." [".$key."] à ".$lst_actifs_achetes_pu[$key].$curr;
            }

            // Performance 
            $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf + $retrait_cumule - $sum_invest)*100/$sum_invest, 2);
            
            $detail['tr_onclick']   = "";
            $detail['td_day']       = $day;
            $detail['td_cash']      = sprintf("%.2f", round($cash, 2)).$curr;
            $detail['td_ordres']    = $recap_actifs_portefeuille;
            $detail['td_valo_pf']   = sprintf("%.2f", round($valo_pf, 2)).$curr;
            $detail['td_perf_glob'] = sprintf("%.2f", $perf_pf)."%";
            $detail["td_perf_glob_val"] = $perf_pf;

            // Calcul max drawdown
            $maxdd_min = min($maxdd_min, $valo_pf);
            $maxdd_max = max($maxdd_max, $valo_pf);

            $tab_detail[] = $detail;
            $tab_date[] = $day;
            $tab_valo[] = $valo_pf;
            $tab_invt[] = $sum_invest;

        }
        // END DCA

        // Calcul Max Drawdown
        // pas vraiment maxDD mais en attendant de mettre les DM en bases pour pouvoir calculer la valo du portefeuille sur toutes les journées
        // $maxdd = max($maxdd, $maxdd_max == 0 ? 0 : ($maxdd_max - $maxdd_min)/$maxdd_max);
        $maxdd = min($maxdd, $perf_pf);


        // //////////////////////////////////////////////////////////////////
        // Calcul pour le rendement comparatif

        // Recupereration de la dernière cotation du mois de chaque valeur
        $pu_action_RC = calc::getLastMonthDailyHistoryQuote($sym_RC, $day);

        // Achat actif
        if ($retrait_programme) {
            // Retrait de l'invest precedent ajouté
            $cash_RC -= $invest;
            // Vente actifs pour retrait
            $nb_actions2sell = floor($f_montant_retrait / $pu_action_RC);
            // Ajustement du nb d'actifs en possession
            $nb_actions_RC -= $nb_actions2sell > $nb_actions_RC ? $nb_actions_RC : $nb_actions2sell;
        } else {
            // Achat nouveaux actifs
            $nb_actions2buy = floor($cash_RC / $pu_action_RC);
            // Ajustement du cash dispo
            $cash_RC -= $nb_actions2buy*$pu_action_RC;
            // Ajustement du nb d'actifs en possession
            $nb_actions_RC += $nb_actions2buy;
        }

        // Valorisation portefeuille RC
        $valo_pf_RC = ($nb_actions_RC * $pu_action_RC) + $cash_RC;
        $tab_valo_RC[] = $valo_pf_RC;

        // Performance 
        $perf_pf_RC = $sum_invest == 0 ? 0 : round(($valo_pf_RC + $retrait_cumule - $sum_invest)*100/$sum_invest, 2);

        $maxdd_RC = min($maxdd_RC, $perf_pf_RC);

        // End Calcul pour le rendement comparatif
        // ////////////////////////////////////////////////////////////////////
    }
    // END Cycle Investissement

    $nb_mois++;

    // Compteur de mois
    if(substr($i, 4, 2) == "12")
        $i = (date("Y", strtotime($i."01")) + 1)."01";
    else
        $i++;

    // if ($nb_mois == 4) exit(0);

}

if ($row['methode'] == 1) {
    $valo_pf = round($cash+($actifs_achetes_nb * $actifs_achetes_pu), 2);
}

$perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf + $retrait_cumule - $sum_invest)*100/$sum_invest, 2);

$final_info = '
    <table id="sim_final_info">
    <tr>
        <th>Portfolio</th>
        <th>Valorisation</th>
        <th>Capital investit</th>
        <th>Performance</th>
        <th>Max DD</th>
        <th>Retrait</th>
        <th>Duree</th>
    </tr>
    <tr>
        <td>'.$row['title'].'</td>
        <td>'.sprintf("%.2f", $valo_pf).' &euro;</td>
        <td>'.sprintf("%.2f", $sum_invest).' &euro;</td>
        <td class="'.($perf_pf >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $perf_pf).' %</td>
        <td class="'.($maxdd >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $maxdd).' %</td>
        <td>'.sprintf("%.2f", $retrait_cumule).' &euro;</td>
        <td>'.count(tools::getMonth($date_start, $date_end)).' mois</td>
    </tr>
    <tr>
        <td>Benchmark</td>
        <td>'.sprintf("%.2f", $valo_pf_RC).' &euro;</td>
        <td>'.sprintf("%.2f", $sum_invest).' &euro;</td>
        <td class="'.($perf_pf_RC >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $perf_pf_RC).' %</td>
        <td class="'.($maxdd_RC >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $maxdd_RC).' %</td>
        <td>'.sprintf("%.2f", $retrait_cumule).' &euro;</td>
        <td>'.count(tools::getMonth($date_start, $date_end)).' mois</td>
    </tr>
    </table>
';

?>
            <div class="sixteen wide column" style="margin-top: 15px;">
                <?= uimx::genCard('sim_card1', 'Synthèse', '', $final_info); ?>
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
            label: "<?= $row['title'] ?>",
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


<!-- GRAPHE 2 : EVOLUTION DM -->

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

var ctx2 = document.getElementById('sim_canvas2').getContext('2d');
el("sim_canvas2").height = document.body.offsetWidth > 700 ? 100 : 300;

const horizontalLines = {
    id: 'horizontalLines',
    beforeDraw(chart, args, options) {
        const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
        ctx.save();
        ctx.strokeStyle = 'rgba(255, 215, 0, 0.5)';
        // Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
        ctx.beginPath();
        ctx.setLineDash([3, 3]);
        h = (height/2) + top;
        ctx.moveTo(left, h);
        ctx.lineTo(right, h);
        ctx.stroke();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.restore();
    }
};

<?
/*     $toto = array();
    $req4 = "SELECT * FROM daily_time_series_adjusted dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='DAILY' AND dtsa.symbol='ESE.PAR' ORDER BY dtsa.day ASC";
    $res4 = dbc::execSql($req4);    
    while ($row4 = mysqli_fetch_assoc($res4)) {
        $mmday = date("Ym", strtotime($row4['day']));
        $toto[$mmday] = $row4;
    }
    $tutu = array();
    $req4 = "SELECT * FROM daily_time_series_adjusted dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='DAILY' AND dtsa.symbol='OBLI.PAR' ORDER BY dtsa.day ASC";
    $res4 = dbc::execSql($req4);    
    while ($row4 = mysqli_fetch_assoc($res4)) {
        $mmday = date("Ym", strtotime($row4['day']));
        $tutu[$mmday] = $row4;
    }
 */
?>

// var dataset_x = [ \<\?= // implode(',', array_slice(array_column($toto, "DM"), count($toto) - count($tab_date), count($tab_date) )) ?> ];
// var dataset_z = [ \<\?= // implode(',', array_slice(array_column($tutu, "DM"), count($tutu) - count($tab_date), count($tab_date) )) ?> ];

var data2 = {
    labels: dates,
    datasets: [
//        {
//            data: dataset_x, label: "test", borderColor: "pink", cubicInterpolationMode: 'monotone', tension: 0.4, borderWidth: 0.5, fill: false
//        },
//        {
//            data: dataset_z, label: "test2", borderColor: "pink", cubicInterpolationMode: 'monotone', tension: 0.4, borderWidth: 0.5, fill: false
//        },
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "{ data: dataset_".$x.", label: \"".$val."\", borderColor: \"".$sess_context->getSpectreColor($x)."\", cubicInterpolationMode: 'monotone', pointRadius: 1, tension: 0.4, borderWidth: 0.5, fill: false },";
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

var myChart2 = new Chart(ctx2, { type: 'line', data: data2, options: options2, plugins: [horizontalLines] } );

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
    echo '<th>Actifs en portefeuille</th><th></th><th></th><th></th><th></th><th></th><th></th>';
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
		params = attrs(['f_delai_retrait', 'f_montant_retrait', 'strategie_id', 'capital_init', 'invest', 'cycle_invest', 'date_start', 'date_end', 'f_compare_to' ]);
        go({ action: 'sim', id: 'main', url: 'simulator.php?'+params+'&f_retrait='+(valof('f_retrait') == 0 ? 0 : 1), loading_area: 'sim_go_bt' });
    }
    
    Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher(); });
    Dom.addListener(Dom.id('sim_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher(); });
    Dom.addListener(Dom.id('f_retrait'),  Dom.Event.ON_CHANGE, function(event) { toogle('retrait_option1'); });

    hide('retrait_option1');
    <? if ($f_retrait == 1) { ?>
    toogle('retrait_option1');
    <? } ?>

    const datepicker1 = new TheDatepicker.Datepicker(el('date_start'));
    datepicker1.options.setInputFormat("Y-m-d")
    datepicker1.render();
    const datepicker2 = new TheDatepicker.Datepicker(el('date_end'));
    datepicker2.options.setInputFormat("Y-m-d")
    datepicker2.render();
</script>