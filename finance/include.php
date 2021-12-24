<?php

date_default_timezone_set("Europe/Paris");

// ini_set('display_errors', false);
ini_set('error_log', './finance.log');

//header( 'content-type: text/html; charset=iso-8859-1' );
header( 'content-type: text/html; charset=iso-8859-1' );

$dbg = false;
$dbg_data = false;

// On place la timezone à UTC pour pouvoir gerer les fuseaux horaires des places boursieres
date_default_timezone_set("UTC");

//
// Boite a outils
//
class tools {

    public static function pretty($data) {

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );
    
        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;
    
                    case '{': case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;
    
                    case ':':
                        $post = " ";
                        break;
    
                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
        }
    
        echo "<br /><pre>".$result."</pre>";
    }

    public static function useGoogleFinanceService() {
        return true;
    }

    public static function isLocalHost() {
        return (strtolower(getenv('SERVER_NAME')) == "localhost" || strtolower(getenv('REMOTE_ADDR')) == "127.0.0.1" || strtolower(getenv('REMOTE_ADDR')) == "localhost") ? true : false;
    }

    public static function do_redirect($url) {
?>
        <script type="text/javascript">
        top.location.href="<?= $url ?>";
        </script>
        <noscript>
        <meta http-equiv="refresh" content="0; url=<?= $url ?>" />
        </noscript>
<?    
        exit(0);
    }

    public static function getMonth($strDate1, $strDate2) {

        $date1 = new DateTime(date('Y-m-01', strtotime($strDate1)));
        $date2 = new DateTime(date('Y-m-01', strtotime($strDate2)));
        
        if($date1 <= $date2) {
            $arr_mois = array();
            $arr_mois[] =  $date1->format('m');
            while($date1 < $date2){
                $date1->add(new DateInterval("P1M"));
                $arr_mois[] = $date1->format('m');
            }
        } else {
            echo "Erreur : Date1 est plus grand que Date2 !";
            $arr_mois = NULL;
        }
    
        return $arr_mois;
    }

    public static function getLibelleBtAction($action) {
        return $action == "new" ? "Ajouter" : ($action == "upt" ? "Modifier" : ($action == "copy" ? "Copier" : "Supprimer"));
    }
}

//
// Connection DB
//
class dbc {

    public static $link;

    public static function connect()
    {
        if (tools::isLocalHost())
            self::$link = mysqli_connect("localhost", "root", "root", "finance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());
        else
            self::$link = mysqli_connect("jorkersfinance.mysql.db", "jorkersfinance", "Rnvubwi2021", "jorkersfinance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());

        self::$link->set_charset("utf8");

        return self::$link;
    }

    public static function execSQL($requete)
    {
        $res = mysqli_query(self::$link, $requete) or die("Error on request : " . $requete);
        return $res;
    }
}

//
// Compute Data
//
class calc {

    public static function aggregatePortfolio($id, $quotes = array()) {

        global $sess_context;

        $portfolio      = array();
        $positions      = array();
        $transfert_in   = 0;
        $transfert_out  = 0;
        $sum_depot      = 0;
        $sum_retrait    = 0;
        $sum_dividende  = 0;
        $sum_commission = 0;
        $valo_ptf       = 0;
        $cash           = 0;
        $ampplt         = 0; // Apports moyen ponderes par le temps

        $portfolio['orders']    = array();
        $portfolio['positions'] = array();

        $req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId()." AND id=".$id;
        $res = dbc::execSql($req);
        if (!$row = mysqli_fetch_array($res)) {
            uimx::staticInfoMsg("Bad data !", "alarm", "red");
            exit(0);
        }
        $portfolio['infos'] = $row;

        // Si portefeuille synthese on fusionne les eventuelles saisies de cotation
        if ($portfolio['infos']['synthese'] == 1) {
            $local_quotes = '';
            $req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId()." AND id IN (".$portfolio['infos']['all_ids'].")";
            $res = dbc::execSql($req);
            while($row = mysqli_fetch_array($res)) $local_quotes .= ($local_quotes ==  '' ? '' : ',').$row['quotes'];
            $portfolio['infos']['quotes'] = $local_quotes;
        }

        // On recupere les eventuelles saisies de cotation manuelles
        if (!isset($quotes['stocks'])) $quotes['stocks'] = array();
        $t = explode(',', $portfolio['infos']['quotes']);
        if ($t[0] != '') {
            foreach($t as $key2 => $val2) {
                $x = explode('|', $val2);
                if (!isset($quotes['stocks'][$x[0]])) $quotes['stocks'][$x[0]] = array();
                $quotes['stocks'][$x[0]]['price'] = $x[1];
            }
        }

        $i = 0;
        $interval_ref = 0;
        $interval_year = 0;
        $interval_month = 0;
        $today = new DateTime(date("Y-m-d"));

        $req = "SELECT * FROM orders WHERE portfolio_id IN (".($portfolio['infos']['synthese'] == 1 ? $portfolio['infos']['all_ids'] : $id).") ORDER BY date, datetime ASC";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            $date_ref = new DateTime($row['date']);

            // Recuperation de la date de debut du portefeuille
            if ($i == 0) {
                $date_ref = new DateTime($row['date']);
                $interval_ref = $today->diff($date_ref)->format("%a");
                $interval_year = $today->diff($date_ref)->format("%y");
                $interval_month = $today->diff($date_ref)->format("%m");
                $i++;
            }

            $interval = $today->diff($date_ref)->format("%a");

            // Traitement des ordres de type "Autre"
            $row['other_name'] = substr($row['product_name'], 0, 5) == "AUTRE" ? true : false;
            $pname = $row['other_name'] ? substr($row['product_name'], 6) : $row['product_name'];
            $row['product_name'] = $pname;

            // Tableau des ordres
            $portfolio['orders'][] = $row;
            
            // Achat/Vente
            if ($row['action'] == 1 || $row['action'] == -1) {

                $nb = 0;
                $pru = 0;
                $achat = $row['action'] >= 0 ? true : false;
                
                if (isset($positions[$pname]['nb'])) {

                    $nb = $positions[$pname]['nb'] + ($row['quantity'] * ($achat ? 1 : -1));
                    
                    // Si achat on recalcule PRU mais pas si vente
                    $pru = $achat ? ($positions[$pname]['pru'] * $positions[$pname]['nb'] + $row['quantity'] * $row['price']) / $nb : $positions[$pname]['pru'];

                } else {
                    $nb  = $row['quantity'];
                    $pru = $row['price'];
                }

                $cash += ($row['quantity'] * $row['price'] * ($achat ? -1 : 1)); // ajout si vente, retrait achat

                $positions[$pname]['nb']  = $nb;
                $positions[$pname]['pru'] = $pru;
                $sum_commission += $row['commission'];
            }

            // Transfert IN
            if ($row['action'] == 5) {
                $transfert_in += $row['quantity'] * $row['price'];
                $cash         += $row['quantity'] * $row['price'];
                $ampplt       += $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }

            // Transfert OUT
            if ($row['action'] == -5) {
                $transfert_out += $row['quantity'] * $row['price'];
                $cash          -= $row['quantity'] * $row['price'];
                $ampplt        -= $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }

            // Depot
            if ($row['action'] == 2) {
                $sum_depot += $row['quantity'] * $row['price'];
                $cash      += $row['quantity'] * $row['price'];
                $ampplt    += $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }

            // Retrait
            if ($row['action'] == -2) {
                $sum_retrait += $row['quantity'] * $row['price'];
                $cash        -= $row['quantity'] * $row['price'];
                $ampplt      -= $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }

            // Divende
            if ($row['action'] == 4) {
                $sum_dividende += $row['quantity'] * $row['price'];
                $cash          += $row['quantity'] * $row['price'];
                $ampplt        += $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }
   
        }

        // On retire des positions les actifs dont le nb = 0 (plus dans le portefeuille)
        foreach($positions as $key => $val) {
            if ($val['nb'] == 0)
                unset($positions[$key]);
            else
                $valo_ptf += $val['nb'] * (isset($quotes['stocks'][$key]) ? $quotes['stocks'][$key]['price'] : $val['pru']);
        }

        $portfolio['valo_ptf']   = $valo_ptf + $cash;
        $portfolio['cash']       = $cash;
        $portfolio['gain_perte'] = $portfolio['valo_ptf'] + $transfert_out - $sum_depot - $transfert_in;
        $portfolio['ampplt']     = $ampplt;
        $portfolio['perf_ptf']   = $ampplt == 0 ? 0 : ($portfolio['gain_perte'] / $ampplt) * 100;
        $portfolio['transfert_in']  = $transfert_in;
        $portfolio['transfert_out'] = $transfert_out;
        $portfolio['depot']      = $sum_depot;
        $portfolio['retrait']    = $sum_retrait;
        $portfolio['dividende']  = $sum_dividende;
        $portfolio['commission'] = $sum_commission;
        $portfolio['positions']  = $positions;
        $portfolio['interval_year']  = $interval_year;
        $portfolio['interval_month'] = $interval_month;

        return $portfolio;
    } 

    public static function getAchatActifsDCAInvest($day, $lst_decode_symbols, $lst_actifs_achetes_pu, $invest_montant) {

        $ret = array();

        $ret['invest'] = $invest_montant;
        $ret['buy'] = array();
        $ret['valo_achats'] = 0;

        foreach($lst_decode_symbols as $key => $val) {

            // Si on n'a pas d'histo pour cet actif a cette date on passe ...
            if ($lst_actifs_achetes_pu[$key] == 0) continue;

            // Montant par actif à posséder
            $montant2get = floor(intval($invest_montant) * $lst_decode_symbols[$key] / 100);

            // Nombre d'actions à acheter
            $nb_actions2buy = 0;
            // if ($montant2get >= 0)
            $nb_actions2buy = floor($montant2get / $lst_actifs_achetes_pu[$key]);

            $ret['buy'][] = array("day" => $day, "sym" => $key, "nb" => $nb_actions2buy, "pu" => $lst_actifs_achetes_pu[$key]);

            // Calcul de la valorisation des achats
            $ret['valo_achats'] += $nb_actions2buy * $lst_actifs_achetes_pu[$key];
        }

        return $ret;

    }

    public static function getMaxDailyHistoryQuoteDate($symbol) {

        $ret = date("Y-m-d");

        $req = "SELECT min(day) day FROM daily_time_series_adjusted WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);
        if ($row = mysqli_fetch_array($res))
            $ret = $row['day'];

        return $ret;
    }

    public static function getDailyHistoryQuote($symbol, $day) {

        $ret = "0";

        $req = "SELECT adjusted_close FROM daily_time_series_adjusted WHERE symbol='".$symbol."' AND day='".$day."'";
        $res = dbc::execSql($req);
        if ($row = mysqli_fetch_array($res))
            $ret = $row['adjusted_close'];
        else {
            // On essaie de trouver la derniere quotation
            $req2 = "SELECT adjusted_close FROM daily_time_series_adjusted WHERE symbol='".$symbol."' AND day < '".$day."' ORDER BY day DESC LIMIT 1";
            $res2 = dbc::execSql($req2);
            if ($row2 = mysqli_fetch_array($res2))
                $ret = $row2['adjusted_close'];
        }

        return floatval($ret);
    }

    // Fait la meme chose mais le nom de la fonction est plus explicite
    public static function getLastMonthDailyHistoryQuote($symbol, $day) {
        return calc::getDailyHistoryQuote($symbol, $day);
    }

    public static function getAllMaxHistoryDate() {

        $ret = array();

        $file_cache = 'cache/TMP_MAX_HISTORYDATE.json';

        if (cacheData::refreshCache($file_cache, 600)) {

            $req = "SELECT symbol, max(day) day FROM daily_time_series_adjusted GROUP by symbol" ;
            $res = dbc::execSql($req);
            while ($row = mysqli_fetch_assoc($res)) $ret[$row['symbol']] = $row['day'];

            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getMaxHistoryDate($symbol) {

        $ret = "0000-00-00";

        $req = "SELECT day FROM daily_time_series_adjusted WHERE symbol='".$symbol."' ORDER BY day ASC LIMIT 1" ;
        $res = dbc::execSql($req);
        if ($row = mysqli_fetch_array($res)) $ret = $row['day'];

        return $ret;
    }

    // ////////////////////////////////////////////////////////////
    // Calcul du DM d'un actif d'une journee
    // ////////////////////////////////////////////////////////////
    public static function processDataDM($day, $data) {

        global $dbg, $dbg_data;

        $ret = array();

        $i = 0;
        $ref_DAY = "";
        $ref_PCT = "";
        $ref_MJ0 = "";
        $ref_YJ0 = "";
        $ref_TJ0 = 0;
        $ref_T1M = 0;
        $ref_T3M = 0;
        $ref_T6M = 0;
        $ref2_T1M = 0;
        $ref2_T3M = 0;
        $ref2_T6M = 0;
        $ref_D1M = "0000-00-00";
        $ref_D3M = "0000-00-00";
        $ref_D6M = "0000-00-00";

        $quote = $data['quote'];

        $tab_close = array();

        // On parcours les cotations en commencant la plus rescente et on remonte le temps
        foreach($data['data'] as $key => $row) {

            // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
            $close_value = is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];

            $tab_close[] = $close_value;

            // Valeurs de reference J0
            if ($i == 0) {
                // Si day is today && Si on a recupere une quotation en temps réel du jour > à la première cotation historique alors on la prend comme référence
                // Comme la cotation est au minimum à la date de la dernière cotation historique on peut la prendre en ref par defaut
                if ($day == date("Y-m-d") && isset($quote['day'])) {
                    $ref_TJ0 = floatval($quote['price']);
                    $ref_DAY = $quote['day'];
                    $ref_PCT = $quote['percent'];
                }
                else {
                    $ref_TJ0 = floatval($close_value);
                    $ref_DAY = $row['day'];
                    $ref_PCT = $row['open'] == 0 ? 0 : ($row['close'] - $row['open']) * 100 / $row['open'];
                }
                $ref_MJ0 = intval(explode("-", $ref_DAY)[1]);
                $ref_YJ0 = intval(explode("-", $ref_DAY)[0]);

                // Recuperation dernier jour ouvre J0-1M
                $ref_D1M = date('Y-m-d', strtotime($ref_YJ0.'-'.$ref_MJ0.'-01'.' -1 day'));
                
                // Recuperation dernier jour ouvre J0-3M
                $m = $ref_MJ0 - 2;
                $y = $ref_YJ0;
                if ($m <= 0) { $m += 12; $y -= 1; }
                $ref_D3M = date('Y-m-d', strtotime($y.'-'.$m.'-01'.' -1 day'));

                // Recuperation dernier jour ouvre J0-6M
                $m = $ref_MJ0 - 5;
                $y = $ref_YJ0;
                if ($m <= 0) { $m += 12; $y -= 1; }
                $ref_D6M = date('Y-m-t', strtotime($y.'-'.$m.'-01'.' -1 day'));
            }

            // $ref_  pour le calcul DM mois flottant MMF
            // $ref2_ pour le calcul DM mois fixe MMZ (DM TKL)
            // MM = momemtum

            // Récupration cotation en mois flottant
            if ($i == 22)  $ref_T1M = floatval($close_value); // 22j ouvrés par mois en moy
            if ($i == 66)  $ref_T3M = floatval($close_value);
            if ($i == 132) $ref_T6M = floatval($close_value);

            // Recuperation cotation en fin de mois fixe (le mois en cours pouvant etre non terminé)
            if ($ref2_T1M == 0 && substr($row['day'], 0, 7) == substr($ref_D1M, 0, 7)) {
                $ref2_T1M = $close_value;
                $ret['MMZ1MPrice'] = $close_value;
                $ret['MMZ1MDate'] = $row['day'];
            }

            // Recuperation cotation en fin de 3 mois
            if ($ref2_T3M == 0 && substr($row['day'], 0, 7) == substr($ref_D3M, 0, 7)) {
                $ref2_T3M = $close_value;
                $ret['MMZ3MPrice'] = $close_value;
                $ret['MMZ3MDate'] = $row['day'];
            }

            // Recuperation cotation en fin de 6 mois
            if ($ref2_T6M == 0 && substr($row['day'], 0, 7) == substr($ref_D6M, 0, 7)) {
                $ref2_T6M = $close_value;
                $ret['MMZ6MPrice'] = $close_value;
                $ret['MMZ6MDate'] = $row['day'];
            }

            $i++;
        }

        $ret['ref_day'] = $ref_DAY;

        // Vraiment utile ? On ne peut pas le recuperer de la DB ?
        $ret['ref_close'] = $ref_TJ0;
        $ret['ref_pct'] = $ref_PCT;


        // A QUOI CA SERT DE CALCULER MMX ??? On le fait dans Indicators !!!
/*
        $ret['MMF1M'] = $ref_T1M == 0 ? -9999 : round(($ref_TJ0 - $ref_T1M)*100/$ref_T1M, 2);
        $ret['MMF3M'] = $ref_T3M == 0 ? -9999 : round(($ref_TJ0 - $ref_T3M)*100/$ref_T3M, 2);
        $ret['MMF6M'] = $ref_T6M == 0 ? -9999 : round(($ref_TJ0 - $ref_T6M)*100/$ref_T6M, 2);
        $ret['MMFDM'] = $ref_T6M > 0 ? round(($ret['MMF1M']+$ret['MMF3M']+$ret['MMF6M'])/3, 2) : ($ref_T3M > 0 ? round(($ret['MMF1M']+$ret['MMF3M'])/2, 2) : ($ref_T1M > 0 ? $ret['MMF1M'] : -9999));
*/
        $ret['MMZ1M'] = $ref2_T1M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T1M)*100/$ref2_T1M, 2);
        $ret['MMZ3M'] = $ref2_T3M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T3M)*100/$ref2_T3M, 2);
        $ret['MMZ6M'] = $ref2_T6M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T6M)*100/$ref2_T6M, 2);
        $ret['MMZDM'] = $ref2_T6M > 0 ? round(($ret['MMZ1M']+$ret['MMZ3M']+$ret['MMZ6M'])/3, 2) : ($ref2_T3M > 0 ? round(($ret['MMZ1M']+$ret['MMZ3M'])/2, 2) : ($ref2_T1M > 0 ? $ret['MMZ1M'] : -9999));
 
        return $ret;
    }

    public static function getDirectDM($data) {

        $ret = array();

        $price = $data['price'];
        $perf  = array();
        $close = array();

        foreach(['DMD1', 'DMD2', 'DMD3'] as $key) {
            $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$data['symbol']."' AND day='".$data[$key]."'";
            $res = dbc::execSql($req);
            if ($row = mysqli_fetch_assoc($res)) {
                $close[$key] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
                $perf[$key]  = round($close[$key] != 0 ? (($price - $close[$key]) * 100) / $close[$key] : 0, 2); 
            } else {
                $close[$key] = $price;
                $perf[$key] = 0;
            }
        }

        $ret['price'] = $price;
        $ret['close'] = $close;
        $ret['perf']  = $perf;
        $ret['dm']    = round(($perf['DMD1'] + $perf['DMD2'] + $perf['DMD3']) / 3, 2);

        return $ret;
    }

    // //////////////////////////////////////////////////////////////////
    // Reccuperation des data pour le calcul du DM d'un actif d'un jour
    // //////////////////////////////////////////////////////////////////
    public static function getDualMomentumData($symbol, $day) {

        $quote = array();
        $data = array();
    
        // On regarde s'il y a une cotation du jour
        $req = "SELECT * FROM quotes WHERE symbol='".$symbol."' AND day='".$day."'";
        $res = dbc::execSql($req);
        if ($row = mysqli_fetch_assoc($res))
            $quote = $row;

        // On parcours les cotations en commencant la plus rescente et on remonte le temps
        $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$symbol."' AND day <= '".$day."' ORDER BY day DESC LIMIT 200";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }

        return (array("quote" => $quote, "data" => $data));
    }

    // ////////////////////////////////////////////////////
    // DM de tous les actifs d'un jour
    // ////////////////////////////////////////////////////
    public static function getDualMomentum($day) {

        $file_cache = 'cache/TMP_DUAL_MOMENTUM_'.$day.'.json';

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => $day, 'compute_time' => date("Y-d-m H:i:s") );

        if (cacheData::refreshCache($file_cache, 600)) { // Cache de 10 min

            $req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol ORDER BY s.symbol";
            $res = dbc::execSql($req);
            while($row = mysqli_fetch_assoc($res)) {
                $symbol = $row['symbol'];
                $data   = calc::getDualMomentumData($symbol, $day);
                $ret["stocks"][$symbol] = array_merge($row, calc::processDataDM($day, $data));
                // On isole les perfs pour pouvoir trier par performance decroissante
                $ret["perfs"][$symbol] = $ret["stocks"][$symbol]['MMZDM'];
            }

            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getSymbolIndicators($symbol, $day) {

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => $day, 'compute_time' => date("Y-d-m H:i:s") );

        $req = "SELECT * FROM indicators i, stocks s, daily_time_series_adjusted d WHERE i.symbol = '".$symbol."' AND i.period='DAILY' AND i.day='".$day."' AND s.symbol=i.symbol AND d.symbol=i.symbol AND d.day='".$day."'";
        $res = dbc::execSql($req);

        if ($row = mysqli_fetch_assoc($res)) {

            // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
            $row['price'] = is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
            $row['ref_close'] = $row['price'];
            $row['ref_day'] = $day;

        } else {

            // Qd day == today alors cad row=null on regarde dans quote et plus dans daily....
            $req = "SELECT * FROM stocks s, quotes q LEFT JOIN indicators i1 ON q.symbol=i1.symbol WHERE s.symbol = q.symbol AND i1.symbol='".$symbol."' and i1.day='".$day."' AND i1.period='DAILY'";
            $res = dbc::execSql($req);

            if ($row = mysqli_fetch_assoc($res)) {
                $row['ref_close'] = $row['price'];
                $row['ref_day'] = $day;
            }
        }

//        tools::pretty($row);
//        exit(0);

        return $row;
    }

    public static function getIndicatorsLastQuote() {

        $file_cache = 'cache/TMP_CURRENT_DUAL_MOMENTUM_.json';

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => date("Y-m-d"), 'compute_time' => date("Y-d-m H:i:s") );

        if (cacheData::refreshCache($file_cache, 600)) { // Cache de 10 min

            $req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol=q.symbol LEFT JOIN indicators i1 ON s.symbol=i1.symbol WHERE (i1.symbol, i1.day, i1.period) IN (SELECT i2.symbol, max(i2.day), i2.period FROM indicators i2 WHERE i2.period='DAILY' GROUP BY i2.symbol) GROUP BY s.symbol";
//            $req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol=q.symbol LEFT JOIN last_day_indicators i ON s.symbol=i.i_symbol ORDER BY s.symbol ASC ";
            $res = dbc::execSql($req);
            while($row = mysqli_fetch_assoc($res)) {
                $symbol = $row['symbol'];
                $ret["stocks"][$symbol] = $row;
                // On isole les perfs pour pouvoir trier par performance decroissante
                $ret["perfs"][$symbol] = $row['DM'];
            }

            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getSymbolIndicatorsLastQuote($symbol) {

        $data = calc::getIndicatorsLastQuote();

        return isset($data['stocks'][$symbol]) ? $data['stocks'][$symbol] : false;
    }

    public static function getFilteredSymbolIndicators($lst_symbols, $day) {

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => $day, 'compute_time' => date("Y-d-m H:i:s") );

        foreach($lst_symbols as $key => $symbol) {

            $data = calc::getSymbolIndicators($symbol, $day);

            // Encore utiliser dans simulator => peut etre utiliser day dans simulator
            $data['ref_day']   = $day;
            $data['ref_close'] = $data['price'];

            //tools::pretty($data);
            //exit(0);

            $ret['stocks'][$symbol] = $data;
            $ret['perfs'][$symbol]  = $data["DM"];

        }

        return $ret;
    }

    public static function getLastDayMonthQuoteIndicators($lst_symbols, $day) {

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => $day, 'compute_time' => date("Y-d-m H:i:s") );

        $req = "SELECT symbol, max(day) max_day FROM indicators WHERE period='DAILY' AND day LIKE '".substr($day, 0, 8)."%' AND symbol IN ("."'".implode("','", $lst_symbols)."'".") GROUP BY symbol";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            $data = calc::getSymbolIndicators($row['symbol'], $row['max_day']);

            $ret['stocks'][$row['symbol']] = $data;
            $ret['perfs'][$row['symbol']]  = $data["DM"];

        }

        return $ret;
    }

}

//
// API Alphavantage
//
class aafinance {

    public static $apikey = "ZFO6Y0QL00YIG7RH";
    public static $apikey_local = "X6K6Z794TD321PTH";
    public static $premium = false;

    public static function getData($function, $symbol, $options) {

        global $dbg, $dbg_data;

        $url  = 'https://www.alphavantage.co/query?function='.$function.'&'.$options.'&apikey='.(tools::isLocalHost() ? self::$apikey_local : self::$apikey);
        $json = file_get_contents($url);
        $data = json_decode($json,true);

        $data['status'] = 0;
        if (isset($data['Error Message'])) {
            $data['status'] = 2;
            logger::error("ALPHAV", $symbol, "[".$function."] [".$options."] [".$data['Error Message']."]");
        } elseif (isset($data['Note'])) {
            $data['status'] = 1;
            logger::warning("ALPHAV", $symbol, "[".$function."] [".$options."] [".$data['Note']."]");
        } elseif (isset($data['Information'])) {
            $data['status'] = 3;
            logger::warning("ALPHAV", $symbol, "[".$function."] [".$options."] [".$data['Information']."]");
        } else {
            logger::info("ALPHAV", $symbol, "[".$function."] [".$options."] [OK]");
        }

        if ($dbg_data) tools::pretty($data);

        return $data;
    }

    public static function getOverview($symbol, $options = "") {    
        return self::getData("OVERVIEW", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getIntraday($symbol, $options = "") {    
        return self::getData("TIME_SERIES_INTRADAY", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getDailyTimeSeries($symbol, $options = "") {
        return self::getData("TIME_SERIES_DAILY", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getDailyTimeSeriesAdjusted($symbol, $options = "") {
        return self::getData("TIME_SERIES_DAILY_ADJUSTED", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getWeeklyTimeSeries($symbol, $options = "") {
        return self::getData("TIME_SERIES_WEEKLY", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getWeeklyTimeSeriesAdjusted($symbol, $options = "") {
        return self::getData("TIME_SERIES_WEEKLY_ADJUSTED", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getMonthlyTimeSeries($symbol, $options = "") {
        return self::getData("TIME_SERIES_MONTHLY", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getMonthlyTimeSeriesAdjusted($symbol, $options = "") {
        return self::getData("TIME_SERIES_MONTHLY_ADJUSTED", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function getQuote($symbol, $options = "") {    
        return self::getData("GLOBAL_QUOTE", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
    }

    public static function searchSymbol($str, $options = "") {    
        return self::getData("SYMBOL_SEARCH", $str, "keywords=".$str.($options == "" ? "" : "&").$options);
    }

}

//
// Cache des donnees
//
class cacheData {

    public static $lst_cache = ["OVERVIEW", "QUOTE", "DAILY_TIME_SERIES_ADJUSTED_FULL", "DAILY_TIME_SERIES_ADJUSTED_COMPACT", "WEEKLY_TIME_SERIES_ADJUSTED_FULL", "WEEKLY_TIME_SERIES_ADJUSTED_COMPACT", "MONTHLY_TIME_SERIES_ADJUSTED_FULL", "MONTHLY_TIME_SERIES_ADJUSTED_COMPACT", "INTRADAY"];

    public static function isMarketOpen($timezone, $market_open, $market_close) {

        $ret = false;

        if (tools::isLocalHost()) return true;

        // Si on n'est pas en semaine
        if (date("N") >= 6) return false;

        // Ajustement heure par rapport UTC (On ajoute 15 min pour etre sur d'avoir la premiere cotation)
        $my_date_time=time();
        $my_new_date_time=$my_date_time+(3600*(intval(substr($timezone, 3))));
        $my_new_date=date("Y-m-d H:i:s", $my_new_date_time);

        $dateTimestamp0 = strtotime(date($my_new_date));
        $dateTimestamp1 = strtotime(date("Y-m-d ".$market_open)) + (15*60);  // On attend 15min pour etre sur d'avoir le cours d'ouverture
        $dateTimestamp2 = strtotime(date("Y-m-d ".$market_close)) + (30*60); // On prolonge de 30min pour etre sur d'avoir le cours de cloture

        if ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 < $dateTimestamp2) $ret = true;

        return $ret;
    }

    public static function findPatternInLog($pattern) {

        // $searchthis = date("d-M-Y").".*".$symbol.".*".$period."=";
        $matches = array();
    
        $handle = @fopen("./finance.log", "r");
        fseek($handle, -81920, SEEK_END); // +/- 900 lignes 
        if ($handle)
        {
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                if (preg_match("/".$pattern."/i", $buffer))
                    $matches[] = $buffer;
            }
            fclose($handle);
        }

        return count($matches) > 0 ? true : false;
    }
    
    public static function readCacheData($file) {
        return json_decode(file_get_contents($file), true);
    }

    public static function writeCacheData($file, $data) {
        file_put_contents($file, json_encode($data));
    }

    public static function writeData($file, $data) {
        $fp = fopen($file, 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);
    }

    public static function refreshCache($filename, $timeout) {

        $update_cache = false;

        if (file_exists($filename)) {

            $fp = fopen($filename, "r");
            $fstat = fstat($fp);
            fclose($fp);
            if ($timeout != -1 && (date("U")-$fstat['mtime']) > $timeout) $update_cache = true;
        } else 
            $update_cache = true;

        return $update_cache;
    }

    public static function refreshOnceADayCache($filename) {

        $update_cache = false;

        if (file_exists($filename)) {
            // Est-ce que le cache est du jour ou plus vieux ?
            if (date("d", filemtime($filename)) != date("d")) 
                $update_cache = true;
        } else 
            $update_cache = true;

        return $update_cache;
    }

    public static function buildCacheOverview($symbol) {

        $ret = false;

        $file_cache = 'cache/OVERVIEW_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {
            $data = aafinance::getOverview($symbol);
            if ($data['status'] == 0) {
                cacheData::writeData($file_cache, $data);
                $ret = true;
            } else {
                logger::error("ALPHAV", $symbol, "[OVERVIEW] [NOK]".print_r($data, true));
            }
        }
        else
            logger::info("CACHE", $symbol, "[OVERVIEW] [No update]");

        return $ret;
    }

    public static function buildCacheIntraday($symbol) {

        $file_cache = 'cache/INTRADAY_'.$symbol.'.json';
        if (self::refreshCache($file_cache, 600)) {
            try {
                $data = aafinance::getIntraday($symbol, "interval=60min&outputsize=compact");

                // Delete old entries for symbol before insert new ones ?
        
                if (isset($data["Time Series (60min)"])) {
                    foreach($data["Time Series (60min)"] as $key => $val) {
                        $update = "INSERT INTO intraday (symbol, day, open, high, low, close, volume) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. volume']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', volume='".$val['5. volume']."'";
                        $res2 = dbc::execSql($update);
                    }
    
                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
                } else {
                    logger::error("ALPHAV", $symbol, "[INTRADAY] [NOK]".print_r($data, true));
                }
    
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[INTRADAY]".$e->getMessage()); }
        }
        else
            logger::info("CACHE", $symbol, "[INTRADAY] [No update]");    
    }

    public static function buildCacheDailyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/DAILY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {
            try {

                if (aafinance::$premium)
                    $data = aafinance::getDailyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
                else
                    $data = aafinance::getDailyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = aafinance::$premium ? "Time Series (Daily)" : "Time Series (Daily)";

                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {

                        if (!aafinance::$premium) {
                            $val['5. adjusted close']    = $val['4. close'];
                            $val['6. volume']            = $val['5. volume'];
                            $val['7. dividend amount']   = 0;
                            $val['8. split coefficient'] = 0;
                        }

                        $update = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."', '".$val['8. split coefficient']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."', split_coef='".$val['8. split coefficient']."'";
                        $res2 = dbc::execSql($update);
                    }
    
                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
    
                    $ret = true;
                } else {
                    logger::error("ALPHAV", $symbol, "[DAILY_TIME_SERIES_ADJUSTED] [NOK]".print_r($data, true));
                }
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[DAILY_TIME_SERIES_ADJUSTED]".$e->getMessage()); }
        }
        else
            logger::info("CACHE", $symbol, "[DAILY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [No update]");

        return $ret;
    }

    public static function buildCacheWeeklyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/WEEKLY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {
            try {

                if (aafinance::$premium)
                    $data = aafinance::getWeeklyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
                else
                    $data = aafinance::getWeeklyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = aafinance::$premium ? "Weekly Adjusted Time Series" : "Weekly Time Series";

                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {

                        if (!aafinance::$premium) {
                            $val['5. adjusted close']    = $val['4. close'];
                            $val['6. volume']            = $val['5. volume'];
                            $val['7. dividend amount']   = 0;
                            $val['8. split coefficient'] = 0;
                        }

                        $update = "INSERT INTO weekly_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."'";
                        $res2 = dbc::execSql($update);
                    }

                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);

                    $ret = true;

                } else {
                    logger::error("ALPHAV", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED] [NOK]".print_r($data, true));
                }

            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED]".$e->getMessage()); }
        }
        else
            logger::info("CACHE", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [No update]");

        return $ret;
    }

    public static function buildCacheMonthlyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/MONTHLY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {
            try {

                if (aafinance::$premium)
                    $data = aafinance::getMonthlyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
                else
                    $data = aafinance::getMonthlyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = aafinance::$premium ? "Monthly Adjusted Time Series" : "Monthly Time Series";
                
                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {

                        if (!aafinance::$premium) {
                            $val['5. adjusted close']    = $val['4. close'];
                            $val['6. volume']            = $val['5. volume'];
                            $val['7. dividend amount']   = 0;
                            $val['8. split coefficient'] = 0;
                        }

                        $update = "INSERT INTO monthly_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."'";
                        $res2 = dbc::execSql($update);
                    }

                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
    
                    $ret = true;
                } else {
                    logger::error("ALPHAV", $symbol, "[MONTHY_TIME_SERIES_ADJUSTED] [NOK]".print_r($data, true));
                }
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[MONTHY_TIME_SERIES_ADJUSTED]".$e->getMessage()); }
        }
        else
            logger::info("CACHE", $symbol, "[MONTHY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [No update]");

        return $ret;
    }

    public static function buildCacheQuote($symbol) {

        $ret = false;

        $file_cache = 'cache/QUOTE_'.$symbol.'.json';
        if (self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getQuote($symbol);
        
                if (isset($data["Global Quote"])) {
                    $val = $data["Global Quote"];
                    $update = "INSERT INTO quotes (symbol, open, high, low, price, volume, day, previous, day_change, percent) VALUES ('".$symbol."', '".$val['02. open']."', '".$val['03. high']."', '".$val['04. low']."', '".$val['05. price']."', '".$val['06. volume']."', '".$val['07. latest trading day']."', '".$val['08. previous close']."', '".$val['09. change']."', '".$val['10. change percent']."') ON DUPLICATE KEY UPDATE open='".$val['02. open']."', high='".$val['03. high']."', low='".$val['04. low']."', price='".$val['05. price']."', volume='".$val['06. volume']."', day='".$val['07. latest trading day']."', previous='".$val['08. previous close']."', day_change='".$val['09. change']."', percent='".$val['10. change percent']."'";
                    $res2 = dbc::execSql($update);
    
                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
    
                    $ret = true;
                } else {
                    logger::error("ALPHAV", $symbol, "[GLOBAL_QUOTE] [NOK]".print_r($data, true));
                }
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[GLOBAL_QUOTE]".$e->getMessage()); }
        }
        else
            logger::info("CACHE", $symbol, "[GLOBAL_QUOTE] [No update]");

        return $ret;
    }

    public static function buildCachesSymbol($symbol, $full = false, $options) {

        $ret = array();

        foreach($options as $key => $val) $ret[$val] = false;

        if (isset($options['daily']))    $ret["daily"]    = self::buildCacheDailyTimeSeriesAdjusted($symbol, $full);
        if (isset($options['weekly']))   $ret["weekly"]   = self::buildCacheWeeklyTimeSeriesAdjusted($symbol, $full);
        if (isset($options['monthly']))  $ret["monthly"]  = self::buildCacheMonthlyTimeSeriesAdjusted($symbol, $full);
        if (isset($options['quote']))    $ret["quote"]    = self::buildCacheQuote($symbol);
        if (isset($options['overview'])) $ret["overview"] = self::buildCacheOverview($symbol);

        return $ret;
    }

    public static function buildAllCachesSymbol($symbol, $full = false) {
        return cacheData::buildCachesSymbol($symbol, $full, array("overview" => 1, "quote" => 1, "daily" => 1, "weekly" => 1, "monthly" => 1));
    }

    public static function buildDailyCachesSymbol($symbol, $full = false) {
        return cacheData::buildCachesSymbol($symbol, $full, array("overview" => 1, "quote" => 1, "daily" => 1));
    }

    public static function buildWeekendCachesSymbol($symbol, $full = false) {

        $options = array("weekly" => 0, "monthly" => 0);

        // Le samedi on fait les weekly
        if (date("N") == 6) $options['weekly'] = 1;

        // Le dimanche on fait les monthly
        if (date("N") == 7) $options['monthly'] = 1;

        return cacheData::buildCachesSymbol($symbol, $full, array("weekly" => 1, "monthly" => 1));
    }

    public static function deleteTMPFiles() {
        foreach (glob("cache/TMP_*.json") as $filename) {
            unlink($filename);
        }
    }

    public static function deleteCacheSymbol($symbol) {
        foreach(self::$lst_cache as $key)
            if (file_exists("cache/".$key."_".$symbol.".json")) unlink("cache/".$key."_".$symbol.".json");

        cacheData::deleteTMPFiles();
    }

}

//
// Log
//
class logger {
    
    public static function log($level, $function, $symbol, $msg) {
        global $dbg;

        $str = sprintf("%-5s %-6s %-10s %s", $level, $function, $symbol, $msg);

        if ($dbg) echo $str."<br />";
    
        error_log($str);
    }
    
    public static function info($function, $symbol, $msg) {
        self::log("INFO", $function, $symbol, $msg);
    }
    public static function error($function, $symbol, $msg) {
        self::log("ERROR", $function, $symbol, $msg);
    }
    public static function debug($function, $symbol, $msg) {
        self::log("DEBUG", $function, $symbol, $msg);
    }
    public static function warning($function, $symbol, $msg) {
        self::log("WARN", $function, $symbol, $msg);
    }

    public static function purgeLogFile($filename, $size_purge) {
        $offset = filesize($filename)-$size_purge;        
        if($offset > 0) {
            $logsToKeep = file_get_contents($filename, false, NULL, $offset, $size_purge);
            file_put_contents($filename, $logsToKeep);
        }
    }

}


//
// Log
//
class uimx {

    public static $invest_cycle        = [ 1 => [ "tip" => "Mensuel", "colr" => "orange" ], 3 => [ "tip" => "Trimestriel", "colr" => "green" ], 6 => [ "tip" => "Semestriel", "colr" => "yellow" ], 12 => [ "tip" => "Annuel", "colr" => "purple" ] ];
    public static $invest_methode      = [ 1 => 'Dual Momemtum', 2 => 'DCA' ];
    public static $invest_methode_icon = [ 1 => 'diamond', 2 => 'cubes' ];
    public static $invest_distribution = [ 0 => "Capitalisation", 1 => "Distribution" ];
    public static $invest_categories   = [
        0  => "Autre",
        1  => "Biens conso & Services",
        2  => "Communication",
        3  => "Eau",
        4  => "Ecologie",
        5  => "Energie",
        6  => "Finances",
        7  => "Indice",
        8  => "Infrastuctures",
        9  => "Matériaux & Industrie",
        10 => "Métaux Précieux",
        11 => "Mixte",
        12 => "Santé",
        13 => "Services Publics",
        14 => "Technologie",
        15 => "Obligations",
        16 => "Immobilier"
    ];
    public static $order_actions   = [
         2 => "Dépot",
         1 => "Achat",
        -1 => "Vente",
        -2 => "Retrait",
         4 => "Dividende",
         5 => "Transfert IN",
        -5 => "Transfert OUT"
    ];

    public static function staticInfoMsg($msg, $icon, $color) {
?>
        <div class="ui icon <?= $color ?> message">
        <i class="<?= $icon ?> <?= $color ?> inverted icon"></i>
        <div class="content">
            <div class="header">
            <?= $msg ?>
            </div>
        </div>
    </div>
<?
    }

    public static function genCard($id, $header, $meta, $desc, $cn = "") {
    ?>
        <div class="ui inverted card <?= $cn ?>" id="<?= $id ?>" style="height: 100%; background: rgba(255, 255, 255, 0.05);">
            <div class="content">
                <div class="header"><?= $header ?></div>
                <div class="meta"><?= $meta ?></div>
                <div class="description"><?= $desc ?></div>
            </div>
        </div>
    <?
}

    public static function perfCard($user_id, $strategie, $day, $perfs) {

        global $sess_context;

        $t = json_decode($strategie['data'], true);

        $desc = '<table class="ui inverted single line very compact unstackable table"><tbody>';
        
        $x = 0;
        foreach($perfs as $key => $val) {
            if (isset($t["quotes"][$key])) {
                $desc .= '<tr '.($x == 0 ? 'style="background: green"' : '').'><td>'.$key.'</td><td>'.sprintf("%.2f", $val).'%</td></tr>';
                $x++;
            }
        }

        $desc .= '</tbody></table>';

        $desc .= '<div style="bottom: 5px; position: absolute; width: 90%;">
            <button class="ui small '.self::$invest_cycle[$strategie['cycle']]['colr'].' button badge tooltip2" data-tooltip2="'.self::$invest_cycle[$strategie['cycle']]['tip'].'">'.substr(self::$invest_cycle[$strategie['cycle']]['tip'], 0, 1).'</button>
            <button class="ui small brown button badge tooltip2" data-tooltip2="'.self::$invest_methode[$strategie['methode']].'"><i class="inverted '.self::$invest_methode_icon[$strategie['methode']].' icon"></i></button>
            <button id="home_sim_bt_'.$strategie['id'].'" class="ui right floated small grey icon button">Backtesting</button>
        </div>';

        $title = utf8_decode($strategie['title']).($sess_context->isUserConnected() ? "<i id=\"home_strategie_".$strategie['id']."_bt\" class=\"ui inverted right floated black small ".($user_id == $strategie['user_id'] ? "settings" : "copy")." icon\"></i>" : "");
        uimx::genCard("home_card_".$strategie['id'], $title, $day, $desc, "perf_card");
    }

    public static function portfolioCard($portfolio, $portfolio_data) {

        global $sess_context;

        $desc  = '
        <div id="portfolio_orders_'.$portfolio['id'].'_bt" class="ui labeled button" tabindex="0">
            <div class="ui '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).'  button">
                <i class="chart pie inverted icon"></i>'.sprintf("%.2f &euro;", $portfolio_data['valo_ptf']).'
            </div>
            <a class="ui basic '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).' left pointing label">'.sprintf("%.2f ", $portfolio_data['perf_ptf']).' %</a>
        </div>';

        $title = utf8_decode($portfolio['name']).($sess_context->isUserConnected() ? "<i id=\"portfolio_edit_".$portfolio['id']."_bt\" class=\"ui inverted right floated black small settings icon\"></i>" : "");
        uimx::genCard("portfolio_card_".$portfolio['id'], $title, date('Y-m-d'), $desc);
    }


}

?>