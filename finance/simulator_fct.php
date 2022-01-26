<?

function strategieSimulator($params) {

    $use_ampplt   = false;
    $use_new_perf = true;

    // Initialisation du tableau de valeurs a retourner
    $ret = array();

    // paramsètres
    $strategie_data    = isset($params['strategie_data'])    ? $params['strategie_data']    : "";
    $strategie_methode = isset($params['strategie_methode']) ? $params['strategie_methode'] : 1;
    $montant_retrait   = isset($params['montant_retrait'])   ? $params['montant_retrait']   : 500;
    $delai_retrait     = isset($params['delai_retrait'])     ? $params['delai_retrait']     : 1;
    $invest       = isset($params['invest'])       ? $params['invest']  : ($strategie_methode == 1 ? 1000 : 6000);
    $cycle_invest = isset($params['cycle_invest']) ? $params['cycle_invest'] : ($strategie_methode == 1 ? 1 : 6);
    $compare_to   = isset($params['compare_to'])   ? $params['compare_to']   : "SPY";
    $capital_init = isset($params['capital_init']) ? $params['capital_init'] : 0;
    $date_start   = isset($params['date_start'])   ? $params['date_start']   : "0000-00-00";
    $date_end     = isset($params['date_end'])     ? $params['date_end']     : date("Y-m-d");
    $retrait      = isset($params['retrait'])      ? $params['retrait']      : 0;

    // Recuperation des actifs de la strategie sous forme de tableau
    $lst_symbols = array();
    $lst_decode_symbols = json_decode($strategie_data, true);

    // Recherche de la plage de donnees communes a tous les actifs
    foreach($lst_decode_symbols['quotes'] as $key => $val) {
        $lst_symbols[] = $key;
        $d = calc::getMaxDailyHistoryQuoteDate($key);
        if ($d > $date_start) $date_start = $d;
    }

    // Initialisation suivi cash disponible
    $cash    = $capital_init;
    $cash_RC = $capital_init;

    // Initialisation suivi somme investie
    $sum_invest = $capital_init;

    // Initialisations diverses
    $nb_mois     = 0;
    $valo_pf     = 0;
    $perf_pf     = 0;
    $maxdd_min   = 999999999999;
    $maxdd_max   = 0;
    $maxdd       = 0;
    $retrait_sum = 0;
    $ampplt      = 0; // Apports moyen ponderes par le temps
    $ampplt_RC   = 0; // Apports moyen ponderes par le temps
    $valo_prev   = 0; // Valorisation précédente pour calcul perf entre 2 valo successives
    $valo_RC_prev = 0;


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

    $tab_date = array();
    $tab_valo = array();
    $tab_invt = array();
    $tab_perf = array();
    $tab_detail = array();

    // Pour le calcul du rendement comparatif
    $sym_RC = $compare_to;
    $nb_actions_RC = 0;
    $valo_pf_RC = 0;
    $perf_pf_RC = 0;
    $tab_valo_RC = array();
    $maxdd_RC_min = 99999999999999;
    $maxdd_RC_max = 0;
    $maxdd_RC = 0;

    $dt_start = new DateTime($date_start);
    $dt_end   = new DateTime($date_end);
    $interval_ref = $dt_end->diff($dt_start)->format("%a");

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

        // Interval entre la date en cours de trt et la date de fin de la simulation
        $dt_ref   = new DateTime($day);
        $interval = $dt_end->diff($dt_ref)->format("%a");

        // Recuperation du premier jour du mois 
        // $day = substr($i, 0, 4)."-".substr($i, 4, 2)."-01";

        // Retrait programmé ?
        $retrait_programme = false;
        if ($retrait == 1) {
            if ($i >=  date("Ym", strtotime((intval(substr($date_start, 0, 4)) + $delai_retrait)."-".substr($date_start, 5, 2)."-01"))) {
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
            if ($strategie_methode == 1) {

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
                        $retrait_sum += intval($montant_retrait);

                        // On ampule le cash du retrait
                        if ($cash <= intval($montant_retrait)) {

                            if ($auMoinsUnActif && ($actifs_achetes_nb * $pu) > intval($montant_retrait)) {

                                // Calcul nb actifs a vendre
                                $nb_actifs_a_vendre = floor(intval($montant_retrait) / $pu);

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

                    $valo_pf = round($cash + ($actifs_achetes_nb * $actifs_achetes_pu), 2);

                    // Apports moyen ponderes par le temps
                    $ampplt += $interval_ref == 0 ? 0 : ($actifs_achetes_nb * $actifs_achetes_pu) * ($interval / $interval_ref);
                    if ($use_ampplt) echo $ampplt."-";

                    $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf + $retrait_sum - $sum_invest) * 100 / $sum_invest, 2);
                    if ($use_ampplt) $perf_pf = $ampplt == 0 ? 0 : round((($valo_pf + $retrait_sum - $sum_invest) / $ampplt) * 100, 2);
                    if ($use_new_perf) {
                        $valo_prev += $invest;
                        $valo_new  = $valo_pf + $cash + $retrait_sum;
                        $perf_pf   = $valo_prev == 0 ? 0 : round((($valo_new - $valo_prev) * 100 ) / $valo_prev, 2);
                        $valo_prev = $valo_new;
                    }
    
                    $detail["td_cash"]          = sprintf("%.2f", round($cash, 2)).$curr;
                    $detail["td_valo_pf"]       = sprintf("%.2f", round($valo_pf)).$curr;
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
            if ($strategie_methode == 2) {

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
                    $retrait_sum += intval($montant_retrait);

                    // Il faut determiner combien de chaque action il faut vendre et les retirer du portfolio pour un montant de montant_retrait
                    $panier = calc::getAchatActifsDCAInvest($day, $lst_decode_symbols['quotes'], $lst_actifs_achetes_pu, $montant_retrait);

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

                    $nbbuy = [];
                    // Intégration des achats au portefeuille
                    foreach($panier["buy"] as $key => $val) {
                        $symbol = $val['sym'];

                        // Memorisation ordres
                        $ordres[$day.":".$symbol] = '{ "date": "'.$day.'", "action": "'.($val['nb'] >= 0 ? "Achat" : "Vente").'", "symbol": "'.$symbol.'", "quantity": "'.abs($val['nb']).'", "price": "'.$val['pu'].'", "currency": "'.$curr.'" }';

                        // Cumul des actions acquises + achetees
                        $lst_actifs_achetes_nb[$symbol] += $val['nb'];
                        $nbbuy[$symbol] = $val['nb'];

                        // Calcul cash restant
                        $cash -= $val['nb'] * $val['pu'];
                    }
                }

                $valo_pf = 0;
                foreach($lst_decode_symbols['quotes'] as $key => $val) { 

                    // Valorisation de l'actif
                    $valo_actif = $lst_actifs_achetes_nb[$key] * $lst_actifs_achetes_pu[$key];

                    // Valorisation du portefeuille
                    $valo_pf += $valo_actif;

                    // Recap actifs dans portefeuille
                    // if ($nb_actions2buy > 0)
                    $recap_actifs_portefeuille .= ($recap_actifs_portefeuille == "" ? "" : ", ").$lst_actifs_achetes_nb[$key]." [".$key."] à ".sprintf("%.2f", $lst_actifs_achetes_pu[$key]).$curr;

                    // Apports moyen ponderes par le temps
                    $ampplt += $interval_ref == 0 ? 0 : ($nbbuy[$key] * $lst_actifs_achetes_pu[$key]) * ($interval / $interval_ref);
                    if ($use_ampplt) echo $ampplt."-";

                }

                // Performance (on ajoute les sommes retirées pour garder en perf les gains/pertes qui auraient été retirées)
                $perf_pf = $sum_invest == 0 ? 0 : round(($valo_pf + $cash + $retrait_sum - $sum_invest) * 100 / $sum_invest, 2);
                if ($use_ampplt) $perf_pf = $ampplt == 0 ? 0 : round((($valo_pf + $cash + $retrait_sum - $sum_invest) / $ampplt) * 100, 2);
                if ($use_new_perf) {
                    $valo_prev += $invest;
                    $valo_new  = $valo_pf + $cash + $retrait_sum;
                    $perf_pf   = $valo_prev == 0 ? 0 : round((($valo_new - $valo_prev) * 100 ) / $valo_prev, 2);
                    $valo_prev = $valo_new;
                }
                
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
                $tab_valo[] = round($valo_pf, 2);
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
                $nb_actions2sell = floor($montant_retrait / $pu_action_RC);
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
            $tab_valo_RC[] = round($valo_pf_RC, 2);

            // Apports moyen ponderes par le temps
            $ampplt_RC += $interval_ref == 0 ? 0 : ($nb_actions_RC * $pu_action_RC) * ($interval / $interval_ref);

            // Performance 
            $perf_pf_RC = $sum_invest == 0 ? 0 : round(($valo_pf_RC + $cash_RC + $retrait_sum - $sum_invest) * 100 / $sum_invest, 2);
            if ($use_ampplt) $perf_pf_RC = $ampplt_RC == 0 ? 0 : round((($valo_pf_RC + $retrait_sum - $sum_invest) / $ampplt_RC) * 100, 2);
            if ($use_new_perf) {
                $valo_RC_prev += $invest;
                $valo_RC_new  = $valo_pf_RC + $cash_RC + $retrait_sum;
                $perf_pf_RC   = $valo_RC_prev == 0 ? 0 : round((($valo_RC_new - $valo_RC_prev) * 100 ) / $valo_RC_prev, 2);
                $valo_RC_prev = $valo_RC_new;
            }

            // MaxDrawdown
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

    // DM
    if ($strategie_methode == 1) {
        $valo_pf = round($cash + ($actifs_achetes_nb * $actifs_achetes_pu), 2);
    }

    // Synthese Performance
    $perf_pf    = $sum_invest == 0 ? 0 : round(($valo_pf + $cash + $retrait_sum - $sum_invest) * 100 / $sum_invest, 2);
    $perf_pf_RC = $sum_invest == 0 ? 0 : round(($valo_pf_RC + $cash_RC + $retrait_sum - $sum_invest) * 100 / $sum_invest, 2);

    if ($use_ampplt) {
        echo ($valo_pf + $cash + $retrait_sum - $sum_invest);
        echo "-";
        echo ($ampplt);
        echo "-";
        echo $perf_pf;
    }

    $ret['valo_pf']      = $valo_pf;
    $ret['sum_invest']   = $sum_invest;
    $ret['perf_pf']      = $perf_pf;
    $ret['maxdd']        = $maxdd;
    $ret['retrait_sum']  = $retrait_sum;
    $ret['valo_pf_RC']   = $valo_pf_RC;
    $ret['perf_pf_RC']   = $perf_pf_RC;
    $ret['maxdd_RC']     = $maxdd_RC;


    $ret['tab_date']    = $tab_date;
    $ret['tab_valo']    = $tab_valo;
    $ret['tab_valo_RC'] = $tab_valo_RC;
    $ret['tab_invt']    = $tab_invt;
    $ret['tab_perf']    = $tab_perf;
    $ret['tab_detail']  = $tab_detail;
    $ret['ordres']      = $ordres;
    $ret['sym_RC']      = $sym_RC;

    return $ret;
}

?>