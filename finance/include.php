<?php

date_default_timezone_set("Europe/Paris");

// ini_set('display_errors', false);
ini_set('error_log', './finance.log');

//header( 'content-type: text/html; charset=iso-8859-1' );
header( 'content-type: text/html; charset=iso-8859-1' );
header('Access-Control-Allow-Origin: *');

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

    public static function isColTable($table, $column)
    {
        $req = "SELECT count(*) total FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."' AND column_name LIKE '".$column."'";
        $res = dbc::execSql($req);
        $row = mysqli_fetch_assoc($res);

        return $row['total'];
    }

    public static function addColTable($table, $column, $requete)
    {
        $ret = 0;

        if (!dbc::isColTable($table, $column)) {
            $res = dbc::execSql($requete);
            echo $requete." OK";
            $ret = 1;
        }

        return $ret;
    }

    public static function delColTable($table, $column, $requete)
    {
        $ret = 0;

        if (dbc::isColTable($table, $column)) {
            $res = dbc::execSql($requete);
            echo $requete." OK";
            $ret = 1;
        }

        return $ret;
    }
}

//
// Compute Data
//
class calc {

    public static function formatDataOrder($val) {

        $val['valo'] = $val['quantity'] * $val['price'] * $val['taux_change'];
        $val['icon'] = $val['action'] >= 0 ? "right green" : "left orange";
        $val['action_lib']   = uimx::$order_actions[$val['action']];
        $val['devise_sign']  = uimx::getCurrencySign($val['devise']);
        $val['action_colr']  = $val['action'] >= 0 ? "aaf-positive" : "aaf-negative";
        $val['price_signed'] = sprintf("%.2f %s", $val['price'], $val['devise_sign']);
        $val['valo_signed']  = sprintf("%s%.2f %s", $val['action'] >= 0 ? '+' : '-', $val['valo'], '&euro;');

        return $val;
    }

    public static function getCurrencyRate($currency, $liste) {

        $taux = 1;

        if (isset($liste[$currency][1])) $taux = $liste[$currency][1];

        return floatval($taux);

    }

    public static function currencyConverter($price, $currency, $liste) {

        return (floatval($price) * self::getCurrencyRate($currency, $liste));

    }

    public static function aggregatePortfolio($infos) {

        // Penser à mettre en cache 10' le calcul ?

        global $sess_context;

        $portfolio      = array();
        $positions      = array();
        $sum_depot      = 0;
        $sum_retrait    = 0;
        $sum_dividende  = 0;
        $sum_commission = 0;
        $sum_ttf        = 0; // Taxe Transaction financiere sur achat actifs FR de 0,3%
        $valo_ptf       = 0;
        $cash           = 0;
        $ampplt         = 0; // Apports moyen ponderes par le temps

        $portfolio['orders']    = array();
        $portfolio['positions'] = array();

        $portfolio['infos'] = $infos;
        $user_id = $infos['user_id'];

        // Recuperation de tous les actifs
        $quotes = calc::getIndicatorsLastQuote();

        // Reccuperation du cours des devises
        $devises = calc::getGSDevisesWithNoUpdate();

        // Récupération des données de trend_following de l'utilisateur
        $portfolio['trend_following'] = array();
        $req = "SELECT * FROM trend_following WHERE user_id=".$user_id;
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) $portfolio['trend_following'][$row['symbol']] = $row;

        // Ajout des cotations potentiellement saisies manuellement dans Trend Following
        $local_quotes = '';
        $req = "SELECT * FROM trend_following WHERE user_id=".$user_id;
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_array($res)) if ($row['manual_price']) $local_quotes .= ($local_quotes ==  '' ? '' : ',').$row['symbol'].'|'.$row['manual_price'];
        $portfolio['infos']['quotes'] = $local_quotes;

        // On recupere les eventuelles saisies de cotation manuelles
        if (!isset($quotes['stocks'])) $quotes['stocks'] = array();
        $t = explode(',', $portfolio['infos']['quotes']);
        if ($t[0] != '') {
            foreach($t as $key2 => $val2) {
                $x = explode('|', $val2);
                if (!isset($quotes['stocks'][$x[0]])) $quotes['stocks'][$x[0]] = array();
                if (isset($x[1])) $quotes['stocks'][$x[0]]['price'] = $x[1];
            }
        }

        $i = 0;
        $interval_ref = 0;
        $interval_year = 0;
        $interval_month = 0;
        $today = new DateTime(date("Y-m-d"));

        // Récupération et TRT des ordres passes
        $req = "SELECT * FROM orders WHERE portfolio_id IN (".($portfolio['infos']['synthese'] == 1 ? $portfolio['infos']['all_ids'] : $infos['id']).") ORDER BY date, product_name, datetime ASC";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            $date_ref = new DateTime($row['date']);

            // Recuperation de la date de debut du portefeuille
            if ($i == 0) {
                $date_ref = new DateTime($row['date']);
                $interval_ref   = $today->diff($date_ref)->format("%a");
                $interval_year  = $today->diff($date_ref)->format("%y");
                $interval_month = $today->diff($date_ref)->format("%m");
                $i++;
            }

            $interval = $today->diff($date_ref)->format("%a");

            // Traitement des ordres de type "Autre"
            $row['other_name'] = substr($row['product_name'], 0, 5) == "AUTRE" ? true : false;
            $pname = $row['other_name'] ? substr($row['product_name'], 6) : $row['product_name'];
            $row['product_name'] = $pname;
            $row['ttf'] = 0;

            // Si ordre non confirme
            if ($row['confirme'] == 0) { $portfolio['orders'][] = $row; continue; }
            
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

                // Valorisation operation avec le taux de change le jour de la transaction
                $valo_ope = $row['quantity'] * $row['price'] * $row['taux_change'];

                // Maj cash
                $cash += $valo_ope * ($achat ? -1 : 1); // ajout si vente, retrait achat

                // TTF si actif FR 
                if (isset($quotes['stocks'][$pname]['type']) && $quotes['stocks'][$pname]['type'] == 'Equity' && strstr($pname, ".PAR")) $row['ttf'] = $valo_ope * 0.003;
                $sum_ttf += $row['ttf'];

                $positions[$pname]['nb']  = $nb;
                $positions[$pname]['pru'] = $pru;
                $positions[$pname]['other_name'] = $row['other_name'];
                $positions[$pname]['devise'] = $row['devise'];
                $sum_commission += $row['commission'];
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
   
            // Tableau des ordres
            $portfolio['orders'][] = $row;

        }

        // On retire des positions les actifs dont le nb = 0 (plus dans le portefeuille)
        foreach($positions as $key => $val) {
            if ($val['nb'] == 0)
                unset($positions[$key]);
            else {
                $taux_du_jour = calc::getCurrencyRate($val['devise']."EUR", $devises);
                $last_price = isset($quotes['stocks'][$key]) ? $quotes['stocks'][$key]['price'] : $val['pru'];
                // On applique le dernier taux connu
                $valo_ptf += $val['nb'] * $last_price * $taux_du_jour;
            }
        }

        $portfolio['cash']       = $cash - $sum_commission - $sum_ttf;
        $portfolio['valo_ptf']   = $valo_ptf + $cash;
        $portfolio['depot']      = $sum_depot;
        $portfolio['gain_perte'] = $portfolio['valo_ptf'] - $sum_depot - $sum_retrait; // Est-ce qu'on enlève les retraits ?
        $portfolio['ampplt']     = $ampplt;
//        $portfolio['perf_ptf']   = $ampplt == 0 ? 0 : ($portfolio['gain_perte'] / $ampplt) * 100;
        $portfolio['perf_ptf']   = $portfolio['depot'] == 0 ? 0 : (($portfolio['valo_ptf'] - $portfolio['depot']) * 100) / $portfolio['depot'];
        $portfolio['retrait']    = $sum_retrait;
        $portfolio['dividende']  = $sum_dividende;
        $portfolio['commission'] = $sum_commission;
        $portfolio['ttf']        = $sum_ttf;
        $portfolio['positions']  = $positions;
        $portfolio['interval_year']  = $interval_year;
        $portfolio['interval_month'] = $interval_month;

        return $portfolio;
    } 

    public static function aggregatePortfolioById($id) {

        // Récupération des infos du portefeuille
        $req = "SELECT * FROM portfolios WHERE id=".$id;
        $res = dbc::execSql($req);

        if (!$row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            uimx::staticInfoMsg("Bad data !", "alarm", "red");
            exit(0);
        }

        return calc::aggregatePortfolio($row);
    }

    public static function aggregatePortfolioByUser($user_id) {

        $ret = array();

        // Récupération des infos du portefeuille
        $req = "SELECT * FROM portfolios WHERE user_id=".$user_id;
        $res = dbc::execSql($req);

        $infos = array();
        $infos['all_ids'] = "";

        while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
            $infos['all_ids'] .= ($infos['all_ids'] == "" ? "" : ",").$row['id'];

        $infos['id']           = "";
        $infos['user_id']      = $user_id;
        $infos['name']         = "Synthese User";
        $infos['strategie_id'] = "";
        $infos['synthese']     = 1;
        $infos['creation']     = "";
        $infos['quotes']       = "";
        $infos['ordre']        = "";

        if ($infos['all_ids'] != "") $ret = calc::aggregatePortfolio($infos);

        return $ret;
    }

    public static function getAchatActifsDCAInvest($day, $data_decode_symbols, $lst_actifs_achetes_pu, $invest_montant) {

        $ret = array();

        $ret['invest'] = $invest_montant;
        $ret['buy'] = array();
        $ret['valo_achats'] = 0;

        foreach($data_decode_symbols as $key => $val) {

            // Si on n'a pas d'histo pour cet actif a cette date on passe ...
            if ($lst_actifs_achetes_pu[$key] == 0) continue;

            // Montant par actif à posséder
            $montant2get = floor(intval($invest_montant) * $data_decode_symbols[$key] / 100);

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

    public static function getLastExistingDateInMonth($d, $data) {

        $ret = $d;

        $deb = substr($d, 0, 7);

        $i = 31;
        while ($i > 0) {
            if (isset($data[$deb.sprintf("-%'02s", $i)])) {
                $ret = $deb.sprintf("-%'02s", $i);
                break;
            }
            $i--;
        }

        return $ret;

    }

    public static function getFirstExistingDateInMonth($d, $data) {

        $ret = $d;

        $deb = substr($d, 0, 7);

        $i = 1;
        while ($i < 31) {
            if (isset($data[$deb.sprintf("-%'02s", $i)])) {
                $ret = $deb.sprintf("-%'02s", $i);
                break;
            }
            $i++;
        }

        return $ret;

    }

    public static function getClosestExistingDateInMonth($d, $data) {

        $ret = $d;


        $i = 1;
        while ($i < 31) {
            $deb = date('Y-m-d', strtotime($d.' -'.$i.' day'));
            if (isset($data[$deb])) {
                $ret = $deb;
                break;
            }
            $i++;
        }

        return $ret;

    }

    public static function getClosedValue($d, $data) {

        $ret = 0;

        if (isset($data[$d])) {

            $item = $data[$d];
            $ret = is_numeric($item['adjusted_close']) ? $item['adjusted_close'] : $item['close'];

        }

        return floatval($ret);
    }

    // ////////////////////////////////////////////////////////////
    // Calcul du DM d'un actif d'une journee
    // ////////////////////////////////////////////////////////////
    public static function processDataDM($data) {
 
        global $dbg, $dbg_data;

        $ret  = array();
        $item = array();

        $ref_DAY  = "";
        $ref_PCT  = "";
        $ref_MJ0  = "";
        $ref_YJ0  = "";
        $ref_TJ0  = 0;
        $ref_T1M  = 0; // Pour calcul variation 1M
        $ref_TYTD = 0; // Pour calcul variation YTD
        $ref_T1W  = 0; // Pour calcul variation 1W
        $ref_T1Y  = 0; // Pour calcul variation 1Y
        $ref_T3Y  = 0; // Pour calcul variation 3Y
        $ref_D1M  = "0000-00-00";
        $ref_D3M  = "0000-00-00";
        $ref_D6M  = "0000-00-00";

        $k = count($data);
        $keys = array_keys($data);

        while($k > 132) {
            
            // On récupère l'item courant
            $c = current($data);

            // Valeurs de reference J0
            $ref_TJ0 = calc::getClosedValue($c['day'], $data);
            $ref_DAY = $c['day'];
            $ref_PCT = $c['open'] == 0 ? 0 : ($c['close'] - $c['open']) * 100 / $c['open'];
            $ref_MJ0 = intval(explode("-", $ref_DAY)[1]);
            $ref_YJ0 = intval(explode("-", $ref_DAY)[0]);

            // Recuperation dernier jour ouvre J0-1M
            $ref_DD1M = calc::getLastExistingDateInMonth(date('Y-m-d', strtotime($ref_YJ0.'-'.$ref_MJ0.'-01'.' -1 day')), $data);

            // Recuperation dernier jour ouvre J0-3M
            $m = $ref_MJ0 - 2;
            $y = $ref_YJ0;
            if ($m <= 0) { $m += 12; $y -= 1; }
            $ref_DD3M = calc::getLastExistingDateInMonth(date('Y-m-d', strtotime($y.'-'.$m.'-01'.' -1 day')), $data);

            // Recuperation dernier jour ouvre J0-6M
            $m = $ref_MJ0 - 5;
            $y = $ref_YJ0;
            if ($m <= 0) { $m += 12; $y -= 1; }
            $ref_DD6M = calc::getLastExistingDateInMonth(date('Y-m-t', strtotime($y.'-'.$m.'-01'.' -1 day')), $data);

            // Recuperation jour YTD
            $ref_DYTD = calc::getFirstExistingDateInMonth(date("Y-01-31"), $data);
            $ref_TYTD = calc::getClosedValue($ref_DYTD, $data);

            // Recuperation jour J - 1W
            $ref_D1W = $keys[5];
            $ref_T1W = calc::getClosedValue($ref_D1W, $data);

            // Recuperation jour J - 1M
            $ref_D1M = $keys[21];
            $ref_T1M = calc::getClosedValue($ref_D1M, $data);

            // Recuperation jour J - 1Y
            // $ref_D1Y = isset($keys[252]) ? $keys[252] : 0 ;
            $ref_D1Y = self::getClosestExistingDateInMonth((intval(substr($c['day'], 0, 4)) - 1).substr($c['day'], 4), $data);
            $ref_T1Y = calc::getClosedValue($ref_D1Y, $data);

            // Recuperation jour J - 3Y
            // $ref_D3Y = isset($keys[252*3]) ? $keys[252*3] : 0;
            $ref_D3Y = self::getClosestExistingDateInMonth((intval(substr($c['day'], 0, 4)) - 3).substr($c['day'], 4), $data);
            $ref_T3Y = calc::getClosedValue($ref_D3Y, $data);

            $item['MMJ0MPrice'] = $ref_TJ0;
            $item['MMJ0MDate']  = $ref_DAY;
            $item['MMZ1MPrice'] = calc::getClosedValue($ref_DD1M, $data);
            $item['MMZ1MDate']  = $data[$ref_DD1M]['day'];
            $item['MMZ3MPrice'] = calc::getClosedValue($ref_DD3M, $data);
            $item['MMZ3MDate']  = $data[$ref_DD3M]['day'];
            $item['MMZ6MPrice'] = calc::getClosedValue($ref_DD6M, $data);
            $item['MMZ6MDate']  = $data[$ref_DD6M]['day'];

            $item['MMZ1M'] = $item['MMZ1MPrice'] == 0 ? -9999 : round(($ref_TJ0 - $item['MMZ1MPrice'])*100/$item['MMZ1MPrice'], 2);
            $item['MMZ3M'] = $item['MMZ3MPrice'] == 0 ? -9999 : round(($ref_TJ0 - $item['MMZ3MPrice'])*100/$item['MMZ3MPrice'], 2);
            $item['MMZ6M'] = $item['MMZ6MPrice'] == 0 ? -9999 : round(($ref_TJ0 - $item['MMZ6MPrice'])*100/$item['MMZ6MPrice'], 2);
            $item['MMZDM'] = $item['MMZ6MPrice'] > 0 ? round(($item['MMZ1M']+$item['MMZ3M']+$item['MMZ6M'])/3, 2) : ($item['MMZ3MPrice'] > 0 ? round(($item['MMZ1M']+$item['MMZ3M'])/2, 2) : ($item['MMZ1MPrice'] > 0 ? $item['MMZ1M'] : -9999));
    
            // Calcul variation YTD/1W/1M/1Y/3Y
            $item['date_YTD'] = $ref_DYTD;
            $item['var_YTD']  = $ref_TYTD == 0 || $ref_TJ0 == 0 ? 0 : ($ref_TJ0 / $ref_TYTD) - 1;
            $item['date_1W'] = $ref_D1W;
            $item['var_1W']  = $ref_T1W  == 0 || $ref_TJ0 == 0 ? 0 : ($ref_TJ0 / $ref_T1W) - 1;
            $item['date_1M'] = $ref_D1M;
            $item['var_1M']  = $ref_T1M  == 0 || $ref_TJ0 == 0 ? 0 : ($ref_TJ0 / $ref_T1M) - 1;
            $item['date_1Y'] = $ref_D1Y;
            $item['var_1Y']  = $ref_T1Y  == 0 || $ref_TJ0 == 0 ? 0 : ($ref_TJ0 / $ref_T1Y) - 1;
            $item['date_3Y'] = $ref_D3Y;
            $item['var_3Y']  = $ref_T3Y  == 0 || $ref_TJ0 == 0 ? 0 : ($ref_TJ0 / $ref_T3Y) - 1;
    
            // Encore utilie ?    
            $item['ref_close'] = $ref_TJ0;
            $item['ref_pct']   = $ref_PCT;

            $ret[] = $item;

            // On deplace d'un le curseur du tableau
            array_shift($data);
            array_shift($keys);

            $k--;

        }

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

    public static function getGSDevisesWithNoUpdate() {
        return cacheData::readCacheData('cache/CACHE_GS_DEVISES.json');
    }

    public static function getGSDevises() {

        $file_cache = 'cache/CACHE_GS_DEVISES.json';

        $ret = array();

        if (cacheData::refreshCache($file_cache, 600)) {

            $ret = updateGoogleSheetDevises();
            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getGSAlertes($force = false) {

        $file_cache = 'cache/CACHE_GS_ALERTES.json';

        $ret = array();

        if ($force || cacheData::refreshCache($file_cache, 600)) {

            $ret = updateGoogleSheetAlertes();

            if (count($ret) > 0)
                cacheData::writeCacheData($file_cache, $ret);
            else
                $ret = cacheData::readCacheData($file_cache);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getMinMaxQuotations() {

        $file_cache = 'cache/TMP_MIN_MAX_QUOTATIONS.json';

        $ret = array();

        if (tools::isLocalHost() || cacheData::refreshOnceADayCache($file_cache)) {

            foreach([ "all" => "", "3Y" => "WHERE day >= DATE_SUB(NOW(), INTERVAL 3 YEAR)", "1Y" => "WHERE day >= DATE_SUB(NOW(), INTERVAL 1 YEAR)" ] as $key => $where) {

                $req = "SELECT symbol, max(cast(adjusted_close as DECIMAL(20, 5))) max, min(cast(adjusted_close AS DECIMAL(20, 5))) min FROM `daily_time_series_adjusted` ".$where." group by symbol";
                $res = dbc::execSql($req);

                while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {

                    // On recupere les infos du min
                    $req2 = "SELECT symbol, day, adjusted_close FROM daily_time_series_adjusted WHERE symbol='".$row['symbol']."' AND CAST(adjusted_close AS DECIMAL(20, 5))=".$row['min']." ".str_replace('WHERE', "AND", $where);
                    $res2 = dbc::execSql($req2);
                    $row2 = mysqli_fetch_array($res2, MYSQLI_ASSOC);

                    $ret[$row2['symbol']][$key.'_min_price'] = $row2['adjusted_close'];
                    $ret[$row2['symbol']][$key.'_min_day']   = $row2['day'];

                    // On recupere les infos du max
                    $req4 = "SELECT symbol, day, adjusted_close FROM daily_time_series_adjusted WHERE symbol='".$row['symbol']."' AND CAST(adjusted_close AS DECIMAL(20, 5))=".$row['max']." ".str_replace("WHERE", "AND", $where);
                    $res4 = dbc::execSql($req4);
                    $row4 = mysqli_fetch_array($res4, MYSQLI_ASSOC);

                    $ret[$row4['symbol']][$key.'_max_price'] = $row4['adjusted_close'];
                    $ret[$row4['symbol']][$key.'_max_day']   = $row4['day'];

                    // Prise en compte de la dernière cotation
                    $req3 = "SELECT * FROM quotes WHERE symbol='".$row['symbol']."'";
                    $res3 = dbc::execSql($req3);
                    if ($row3 = mysqli_fetch_array($res3, MYSQLI_ASSOC)) {
                        if ($row3['price'] > $ret[$row2['symbol']][$key.'_max_price']) $ret[$row2['symbol']][$key.'_max_price'] = $row3['price'];
                        if ($row3['price'] < $ret[$row2['symbol']][$key.'_min_price']) $ret[$row2['symbol']][$key.'_min_price'] = $row3['price'];
                    }
               }
            }

            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getAggregatePortfoliosByUser($user_id) {

        $file_cache = 'cache/TMP_AGGREGATE_USER_PTF_'.$user_id.'_.json';

        $ret = array();

        if (tools::isLocalHost() || cacheData::refreshOnceADayCache($file_cache)) {

            // Recuperation de tous les actifs
            $quotes = calc::getIndicatorsLastQuote();

            // Aggregation de tous les portefeuilles de l'utilisateur connecte
            $ret = calc::aggregatePortfolioByUser($user_id, $quotes);
            unset($ret['orders']);

            // On met en cache uniquement s'il y a des data
            if (count($ret) > 0) cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getIndicatorsLastQuote() {

        $file_cache = 'cache/TMP_CURRENT_DUAL_MOMENTUM_.json';

        $ret = array('stocks' => array(), 'perfs' => array(), 'day' => date("Y-m-d"), 'compute_time' => date("Y-d-m H:i:s"));

        if (cacheData::refreshCache($file_cache, 600)) { // Cache de 10 min

            $req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol=q.symbol LEFT JOIN indicators i1 ON s.symbol=i1.symbol WHERE (i1.symbol, i1.day, i1.period) IN (SELECT i2.symbol, max(i2.day), i2.period FROM indicators i2 WHERE i2.period='DAILY' GROUP BY i2.symbol) GROUP BY s.symbol";
// $req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol=q.symbol LEFT JOIN last_day_indicators i ON s.symbol=i.i_symbol ORDER BY s.symbol ASC ";
// A CREUSER CAR PB sur valeur des dates de DM qui ne sont pas bonnes
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

    public static function getPerfIndicator($data) {

        $ret = 0;

        //Indicateur tendance
        $MM7   = $data['MM7'];
        $MM20  = $data['MM20'];
        $MM50  = $data['MM50'];
        $MM100 = $data['MM100'] != "" ? $data['MM100'] : ($data['MM200'] + $data['MM50']) / 2;
        $MM200 = $data['MM200'];
        $close = $data['price'];

        // Si les MM sont a zero, bye bye
        if ($MM7 == 0 && $MM200 == 0) return $ret;

/*
//rouge (baissier prix sous moyenne mobile 200)
c1= (close<MM200)
//orange( rebond technique)
c2= c1 and(MM20<close)
//jaune (phase1 nouveau cycle)
c3= c1 and(MM50<close)and(mm50<mm200)and(close<mm200)
//vert fluo ( phase 2 nouveau cycle)
c4= (MM200<close)and(MM50<close)and(MM50<MM200)
//vert foncé ( au dessus de tte moyenne mobile, cycle mur)
C5= (MM200<MM50)and(MM50<close)and(MM20<close)
//bleu (retournement de tendance)
c6= (MM100<MM200) and(MM50<MM200)and (close<MM200)and(MM100<close)and(MM50<MM100)
//bleu (retournement de tendance 2 )
c6bis= (MM100<MM200) and(MM50<MM200)and (close<MM200)and(MM50<close)and(MM100<MM50)
//gris (phase 5 affaiblissement ou retournement à la baisse, neutre)
c7= (MM200<close) and(close<MM100)and (MM200<MM100)and(MM200<MM50)
//bleu bouteille (consolidation)
c8=(MM200<MM50)and(MM200<close)and(MM100<close)and((close<MM50)or(close<MM20))
*/
        if ($MM200 < $MM50 && $MM200 < $close && $MM100 < $close && ($close < $MM50 || $close < $MM200))
            $ret = 1; // bleu bouteille (consolidation)
        else if ($MM200 < $close && $close < $MM100 && $MM200 < $MM100 && $MM200 < $MM50)
            $ret = 2; // gris (phase 5 affaiblissement ou retournement à la baisse, neutre)
        else if ($MM100 < $MM200 && $MM50 < $MM200 && $close < $MM200 && $MM50 < $close && $MM100 < $MM50)
            $ret = 3; // bleu (retournement de tendance 2)
        else if ($MM100 < $MM200 && $MM50 < $MM200 && $close < $MM200 && $MM100 < $close && $MM50 < $MM100)
            $ret = 4; // bleu (retournement de tendance)
        else if ($MM200 < $MM50 && $MM50 < $close && $MM20 < $close)
            $ret = 5; // vert foncé (au dessus de tte moyenne mobile, cycle mur)
        else if ($MM200 < $close && $MM50 < $close && $MM50 < $MM200)
            $ret = 6; // vert fluo (phase 2 nouveau cycle)
        else if ($MM50 < $close && $MM50 < $MM200 && $close < $MM200)
            $ret = 7; // jaune (phase1 nouveau cycle)
        else if ($MM20 < $close)
            $ret = 8; // orange (rebond technique)
        else if ($close < $MM200)
            $ret = 9; // rouge (baissier prix sous moyenne mobile 200)

        return $ret;
    }

    public static function removeSymbol($symbol) {

        foreach(['daily_time_series_adjusted', 'weekly_time_series_adjusted', 'monthly_time_series_adjusted', 'stocks', 'quotes', 'indicators'] as $key) {
            $req = "DELETE FROM ".$key." WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req); 
        }

        cacheData::deleteCacheSymbol($symbol);

    }

}

//
// API Alphavantage
//
class aafinance {

    public static $apikey = "ZFO6Y0QL00YIG7RH";
    public static $apikey_local = "X6K6Z794TD321PTH";
    public static $premium = false;
    public static $cache_load = false; // Mettre à true et republier le code + lancer cron via le site

    public static function getData($function, $symbol, $options) {

        global $dbg, $dbg_data;

        if (tools::isLocalHost()) {
            ini_set('default_socket_timeout', 300); // 5 Minutes
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        }

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

    public static function isMarketOpen($market_status)  { return ($market_status > 0); }
    public static function isMarketClose($market_status) { return ($market_status < 1); }
    public static function isMarketAfterClosing($market_status) { return ($market_status == -1); }
    public static function isMarketOnWeekend($market_status) { return ($market_status == -2); }

    public static function getMarketStatus($timezone, $market_open, $market_close) {

        $ret = 0;

        // if (tools::isLocalHost()) return true;

        // Si on n'est pas en semaine
        if (date("N") >= 6) return -2;

        // Ajustement heure par rapport UTC (On ajoute 15 min pour etre sur d'avoir la premiere cotation)
        $my_date_time=time();
        $my_new_date_time=$my_date_time+(3600*(intval(substr($timezone, 3))));
        $my_new_date=date("Y-m-d H:i:s", $my_new_date_time);

        $dateTimestamp0 = strtotime(date($my_new_date));
        $dateTimestamp1 = strtotime(date("Y-m-d ".$market_open)) + (15*60);  // On attend 15min pour etre sur d'avoir le cours d'ouverture
        $dateTimestamp2 = strtotime(date("Y-m-d ".$market_close)) + (30*60); // On prolonge de 30min pour etre sur d'avoir le cours de cloture

        // Market Open
        if ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 < $dateTimestamp2) $ret = 1;

        // Market Close (after closing)
        if ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 > $dateTimestamp2) $ret = -1;

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
    
    public static function readCacheData($filename) {

        $ret = "{}";
    
        if (file_exists($filename)) {
            logger::info("CACHE", "", "[READ]{".$filename."}");
            $ret = file_get_contents($filename);
        }

        return json_decode($ret, true);
    }

    public static function writeCacheData($file, $data) {
        $json = json_encode($data);
        if ($json)
            file_put_contents($file, json_encode($data));
        else
            echo "Pb encodage json";
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

        // Si on force a reloader le cache local
        if (aafinance::$cache_load) $update_cache = true;

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

        // Si on force a reloader le cache local
        if (aafinance::$cache_load) $update_cache = true;

        return $update_cache;
    }

    public static function insertAllDataQuoteFromGS($symbol, $gf_symbol) {

        $retour = false;

        // Init de l'object Stock recherché
        $stock = [];
        $stock['gf_symbol']   = $gf_symbol;
        $stock['symbol']      = $symbol;
        $stock['type']        = "INDICE";
        $stock['region']      = "Europe";
        $stock['engine']      = "google";
        $stock['marketopen']  = "09:00";
        $stock['marketclose'] = "17:30";
        $stock['timezone']    = "UTC+01";
        $stock_histo = [];

        // Affectation du symbol recherche dans la feuille de calcul
        setGoogleSheetStockSymbol($stock['gf_symbol'], "daily");

        // Pause pour laisser le temps a GS de bosser
        sleep(5);

        // Recuperation du nombre de ligne de cotation daily
        $ret = getGoogleSheetStockData("A3", "daily");
        $nb = $ret[0][0];

        if ($nb > 1) {

            // Recuperation infos actif 
            $ret = getGoogleSheetStockData("C1:W2", "daily");

            // Si tradetime non existant bye bye
            if ($ret[1][0] == "#N/A") return $retour;

            // RAZ data
            calc::removeSymbol($stock['symbol']);

            // Creation de l'objet stock avec les valeurs recuperees
            foreach(range(0, 20) as $i) $stock[$ret[0][$i]] = $ret[1][$i];
            
            $req = "INSERT INTO stocks (symbol, gf_symbol, name, type, region, marketopen, marketclose, timezone, currency, engine) VALUES ('".$stock['symbol']."', '".$stock['gf_symbol']."', '".addslashes($stock['name'])."', '".$stock['type']."', '".$stock['region']."', '".$stock['marketopen']."', '".$stock['marketclose']."', '".$stock['timezone']."', '".$stock['currency']."', '".$stock['engine']."')";
            $res = dbc::execSql($req);

            $req = "INSERT INTO quotes (symbol, open, high, low, price, volume, day, previous, day_change, percent) VALUES ('".$stock['symbol']."','".str_replace(',', '.', $stock['priceopen'])."', '".str_replace(',', '.', $stock['high'])."', '".str_replace(',', '.', $stock['low'])."', '".str_replace(',', '.', $stock['price'])."', '".str_replace(',', '.', $stock['volume'])."', '".substr($stock['tradetime'], 6, 4)."-".substr($stock['tradetime'], 3, 2)."-".substr($stock['tradetime'], 0, 2)."', '".str_replace(',', '.', $stock['closeyest'])."', '".str_replace(',', '.', $stock['change'])."', '".str_replace(',', '.', $stock['changepct'])."')";
            $res = dbc::execSql($req);

            $req = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$stock['symbol']."','".substr($stock['tradetime'], 6, 4)."-".substr($stock['tradetime'], 3, 2)."-".substr($stock['tradetime'], 0, 2)."', '".str_replace(',', '.', $stock['priceopen'])."', '".str_replace(',', '.', $stock['high'])."', '".str_replace(',', '.', $stock['low'])."', '".str_replace(',', '.', $stock['price'])."', '".str_replace(',', '.', $stock['price'])."', '".str_replace(',', '.', $stock['volume'])."', '0', '0') ON DUPLICATE KEY UPDATE open='".str_replace(',', '.', $stock['priceopen'])."', high='".str_replace(',', '.', $stock['high'])."', low='".str_replace(',', '.', $stock['low'])."', close='".str_replace(',', '.', $stock['price'])."', adjusted_close='".str_replace(',', '.', $stock['price'])."', volume='".str_replace(',', '.', $stock['volume'])."', dividend='0', split_coef='0'";
            $res = dbc::execSql($req);

            // Recuperation historique cotation actif en daily
            $ret = getGoogleSheetStockData("C3:H".($nb+2), "daily");

            $col_names = [];
            foreach($ret as $key => $val) {

                if ($key == 0) {

                    // Recuperation des noms de colonnes
                    foreach(range(0, 5) as $i) $col_names[$i] = $ret[$key][$i];

                } else {

                    $stock_histo['symbol'] = $symbol;

                    foreach(range(0, 5) as $i) $stock_histo[$col_names[$i]] = $ret[$key][$i];

                    $date  = substr($stock_histo['Date'], 6, 4)."-".substr($stock_histo['Date'], 3, 2)."-".substr($stock_histo['Date'], 0, 2);
                    $close = str_replace(',', '.', $stock_histo['Close']);
                    $open  = str_replace(',', '.', $stock_histo['Open']);
                    $high  = str_replace(',', '.', $stock_histo['High']);
                    $low   = str_replace(',', '.', $stock_histo['Low']);
                    $vol   = str_replace(',', '.', $stock_histo['Volume']);
                    
                    $req = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$stock_histo['symbol']."','".$date."', '".$open."', '".$high."', '".$low."', '".$close."', '".$close."', '".$vol."', '0', '0') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', adjusted_close='".$close."', volume='".$vol."', dividend='0', split_coef='0'";
                    $res = dbc::execSql($req);
                        
                }

            }

            $retour = true;
        }

        return $retour;

    }

    public static function buildCacheOverview($symbol) {

        $ret = false;

        $file_cache = 'cache/OVERVIEW_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {

            if (aafinance::$cache_load && file_exists($file_cache)) {
                $data = cacheData::readCacheData($file_cache);
            } else {
                $data = aafinance::getOverview($symbol);
            }

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

                if (aafinance::$cache_load && file_exists($file_cache)) {
                    $data = cacheData::readCacheData($file_cache);
                } else {
                    $data = aafinance::getIntraday($symbol, "interval=60min&outputsize=compact");
                }

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

                if (aafinance::$cache_load && file_exists($file_cache)) {
                    $data = cacheData::readCacheData($file_cache);
                } else {
//                    if (aafinance::$premium)
//                        $data = aafinance::getDailyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
//                    else
                        $data = aafinance::getDailyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
                }
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = aafinance::$premium ? "Time Series (Daily)" : "Time Series (Daily)";

                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {

//                        if (!aafinance::$premium) {
                        if (!isset($val['5. adjusted close'])) {
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
                    logger::error("ALPHAV", $symbol, "[DAILY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [NOK]".print_r($data, true));
                }
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[DAILY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."]".$e->getMessage()); }
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

                if (aafinance::$cache_load && file_exists($file_cache)) {
                    $data = cacheData::readCacheData($file_cache);
                } else {
//                    if (aafinance::$premium)
                        $data = aafinance::getWeeklyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
//                    else
//                        $data = aafinance::getWeeklyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
                }
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");

//                Pour mieux ancien format vs nouveau format notament qd reload from local cache
//                $key = aafinance::$premium ? "Weekly Adjusted Time Series" : "Weekly Time Series";
//                if (isset($data[$key])) {
                if (isset($data["Weekly Adjusted Time Series"]) || isset($data["Weekly Time Series"])) {
                    foreach($data[isset($data["Weekly Adjusted Time Series"]) ? "Weekly Adjusted Time Series" : "Weekly Time Series"] as $key => $val) {

                        if (isset($data["Weekly Time Series"])) {
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
                    logger::error("ALPHAV", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [NOK]".print_r($data, true));
                }

            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."]".$e->getMessage()); }
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

                if (aafinance::$cache_load && file_exists($file_cache)) {
                    $data = cacheData::readCacheData($file_cache);
                } else {
//                    if (aafinance::$premium)
                        $data = aafinance::getMonthlyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
//                    else
//                        $data = aafinance::getMonthlyTimeSeries($symbol, $full ? "outputsize=full" : "outputsize=compact");
                }

                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
//                Pour mieux ancien format vs nouveau format notament qd reload from local cache
//                $key = aafinance::$premium ? "Monthly Adjusted Time Series" : "Monthly Time Series";
//                if (isset($data[$key])) {
                if (isset($data["Monthly Adjusted Time Series"]) || isset($data["Monthly Time Series"])) {
                    foreach($data[isset($data["Monthly Adjusted Time Series"]) ? "Monthly Adjusted Time Series" : "Monthly Time Series"] as $key => $val) {

//                        if (!aafinance::$premium) {
                        if (isset($data["Monthly Time Series"])) {
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
                    logger::error("ALPHAV", $symbol, "[MONTHY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [NOK]".print_r($data, true));
                }
            } catch(RuntimeException $e) { logger::error("ALPHAV", $symbol, "[MONTHY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."]".$e->getMessage()); }
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

                if (aafinance::$cache_load && file_exists($file_cache)) {
                    $data = cacheData::readCacheData($file_cache);
                } else {
                    $data = aafinance::getQuote($symbol);
                }
        
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
// UIMX
//
class uimx {

    public static $redgreen_colrs = [
        1 => "#62BD18",
        2 => "#8DDD00",
        3 => "#A3EE3F",
        4 => "#FFCE00",
        5 => "#FF7200",
        6 => "#FC3D31"
    ];
    public static $invest_cycle        = [ 1 => [ "tip" => "Mensuel", "colr" => "orange" ], 3 => [ "tip" => "Trimestriel", "colr" => "green" ], 6 => [ "tip" => "Semestriel", "colr" => "yellow" ], 12 => [ "tip" => "Annuel", "colr" => "purple" ] ];
    public static $invest_methode      = [ 1 => 'Dual Momemtum', 2 => 'DCA', 3 => 'Super Dual Momemtum' ];
    public static $invest_methode_icon = [ 1 => 'diamond', 2 => 'cubes', 3 => 'paper plane' ];
    public static $invest_distribution = [ 0 => "Capitalisation", 1 => "Distribution" ];
    public static $type_actif          = [ 0 => "ETF", 1 => "Equity", 2 => "INDICE" ];
    public static $invest_market = [
        0 => [ "tag" => "Marché développé", "desc" => "" ],
        1 => [ "tag" => "Marché émergent",  "desc" => "" ]
    ];
    public static $invest_theme  = [
        0  => [ "tag" => "ISR",      "icon" => "recycle", "desc" => "Investissement socialement responsable" ],
        1  => [ "tag" => "ESG",      "icon" => "recycle", "desc" => "Critères environnementaux, sociaux et de gouvernance" ],
        2  => [ "tag" => "Eau",      "icon" => "recycle", "desc" => "" ],
        3  => [ "tag" => "Eolien",   "icon" => "recycle", "desc" => "" ],
        4  => [ "tag" => "Solaire",  "icon" => "sun",     "desc" => "" ]
    ];
    public static $invest_secteur = [
        0  => [ "tag" => "Services financiers", "icon" => "university", "desc" => "Se compose essentiellement de banques, de caisses d'épargne et de crédit, de compagnies d'assurance ainsi que de sociétés de fonds communs de placement." ],
        1  => [ "tag" => "Énergie",             "icon" => "fire", "desc" => "Constitué d'entreprises exerçant des activités d'exploration, de production, de commercialisation, de raffinage et de transport des produits du pétrole et du gaz. Ce secteur comprend aussi des sociétés qui prennent part au domaine des services liés à l'énergie." ],
        2  => [ "tag" => "Matériaux",           "icon" => "wrench", "desc" => "Se compose d'entreprises appartenant à une vaste gamme d'industries manufacturières et minières axées sur les produits de base, notamment les métaux, les minéraux, les produits chimiques, les matériaux de construction, le verre, le papier et les produits forestiers." ],
        3  => [ "tag" => "Industrie",           "icon" => "industry", "desc" => "Comprend des entreprises dont le domaine d'activité principal est l'aérospatiale et la défense, la construction, l'outillage de précision et les produits de bâtiment ou encore les services de transport, y compris les lignes aériennes, les chemins de fer et les infrastructures de transport." ],
        4  => [ "tag" => "Consommation discrétionnaire", "icon" => "shipping fast", "desc" => "Comprend des entreprises dans les domaines suivants : transport routier, objets ménagers et biens durables, textiles, habillement et équipements de loisirs. Les restaurants, les hôtels, les établissements de loisirs, les services médiatiques et la vente au détail relèvent du segment des services." ],
        5  => [ "tag" => "Telecommunication",   "icon" => "tty", "desc" => "Se compose d'entreprises qui offrent des services de communication principalement au moyen de lignes téléphoniques fixes ou de réseaux cellulaires, sans fil, à large bande passante et de câble à fibres optiques." ],
        6  => [ "tag" => "Technologies",        "icon" => "microchip", "desc" => "Constitué d'entreprises appartenant aux trois domaines généraux suivants : technologie, logiciels et services." ],
        7  => [ "tag" => "Biens de consommation de base", "icon" => "shopping cart", "desc" => "Se compose de fabricants et de distributeurs de denrées alimentaires, de boissons et de tabac, de même que de producteurs d'objets ménagers non durables et de produits personnels. Il comprend également les détaillants d'aliments et de médicaments ainsi que les centres commerciaux." ],
        8  => [ "tag" => "Services publics",    "icon" => "people", "desc" => "Se compose de sociétés gazières, d'électricité et de services d'eau, ainsi que d'entreprises qui agissent à titre de producteurs ou de distributeurs d'énergie." ],
        9  => [ "tag" => "Santé",               "icon" => "first aid", "desc" => "Comprend des entreprises qui fabriquent du matériel et des fournitures de soins de santé ou offrent des services de soins de santé. Il inclut aussi des sociétés qui se consacrent principalement à la recherche, au développement, à la production ainsi qu'à la commercialisation de produits pharmaceutiques et biotechnologiques." ],
        10 => [ "tag" => "Immobilier",          "icon" => "building outline", "desc" => "" ],
        11 => [ "tag" => "Global",              "icon" => "world", "desc" => "" ]
    ];
    public static $invest_zone_geo = [
        0  => [ "tag" => "Monde",            "desc" => "" ],
        1  => [ "tag" => "Amérique du Nord", "desc" => "" ],
        2  => [ "tag" => "Europe",           "desc" => "" ],
        3  => [ "tag" => "Union Européenne", "desc" => "" ],
        4  => [ "tag" => "Europe du Nord",   "desc" => "" ],
        5  => [ "tag" => "Europe de l'Est",  "desc" => "" ],
        6  => [ "tag" => "UK",               "desc" => "" ],
        7  => [ "tag" => "Brics",            "desc" => "" ],
        8  => [ "tag" => "Afrique",          "desc" => "" ],
        9  => [ "tag" => "MENA",             "desc" => "Afrique du nord + Moyen Orient" ],
        10 => [ "tag" => "EMEA",             "desc" => "Europe Middle East & Africa" ],
        11 => [ "tag" => "Moyen Orient",     "desc" => "" ],
        12 => [ "tag" => "Asie",             "desc" => "" ],
        13 => [ "tag" => "Océanie",          "desc" => "" ]
    ];
    public static $invest_taille = [
        0  => [ "tag" => "Large Cap",  "desc" => "" ],
        1  => [ "tag" => "Middle Cap", "desc" => "" ],
        2  => [ "tag" => "Small Cap",  "desc" => "" ]
    ];
    public static $invest_classe = [
        0  => [ "tag" => "Action",            "desc" => "" ],
        1  => [ "tag" => "Obligation",        "desc" => "" ],
        2  => [ "tag" => "Monétaire",         "desc" => "" ],
        3  => [ "tag" => "Matière première", "desc" => "" ],
        4  => [ "tag" => "Immobilier",        "desc" => "" ],
        5  => [ "tag" => "Factorielle",       "desc" => "" ],
        6  => [ "tag" => "Indice",            "desc" => "" ]
    ];
    public static $invest_factorielle = [
        0  => [ "tag" => "Value",               "desc" => "Critère de sélection sur la valorisation de l'asset (recherche des actifs sous coté par rapport à la valeur intraséque)" ],
        1  => [ "tag" => "Growth",              "desc" => "Critère de sélection lié à la croissance du chiffre d'affaires et des résultats (recherche des actifs dont les bénéfices augmentent plus que le marché)" ],
        2  => [ "tag" => "Momentum",            "desc" => "Critère de sélection qui prend en compte la tendance positive sur les jours/semaines/mois précédents plus forte que le marché" ],
        3  => [ "tag" => "Quality",             "desc" => "Faible endettement et forte profitabilité" ],
        4  => [ "tag" => "Low Volatility",      "desc" => "Faible volatilité" ],
        5  => [ "tag" => "High Dividend Yield", "desc" => "Rendement élevé et constant des dividendes" ],
        6  => [ "tag" => "Equal Weight",        "desc" => "Capitalisation plus petite" ]
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
    public static $perf_indicator_colrs = [
        0 => "black",
        1 => "purple",
        2 => "gray",
        3 => "teal",
        4 => "blue",
        5 => "olive",
        6 => "green",
        7 => "yellow",
        8 => "orange",
        9 => "red"
    ];
    public static $perf_indicator_libs = [
        0 => "N/A",
        1 => "Consolidation",
        2 => "Phase 5 affaiblissement ou retournement à la baisse, neutre",
        3 => "Retournement de tendance 2",
        4 => "Retournement de tendance",
        5 => "Au dessus de toute moyenne mobile, cycle mur",
        6 => "Phase 2 nouveau cycle",
        7 => "Phase1 nouveau cycle",
        8 => "Rebond technique",
        9 => "Baissier prix sous moyenne mobile 200"
    ];

    public static function getCurrencySign($cur) {

        $ret = "&euro;";

        if ($cur == "USD") $ret = "$";

        return $ret;

    }

    public static function getRedGreenColr($x, $y) {

        if ($x == 0) return 0;

        $colr = 6;
        $ratio = ((($y - $x) * 100) / $x);
    
        if ($ratio >= 20)
            $colr = 1;
        else if ($ratio >= 10)
            $colr = 2;
        else if ($ratio >= 0)
            $colr = 3;
        else if ($ratio >= -10)
            $colr = 4;
        else if ($ratio >= -20)
            $colr = 5;
        else if ($ratio >= 20)
            $colr = 6;
        
        return uimx::$redgreen_colrs[$colr];
            
    }

    public static function getIconTooltipTag(&$tags) {

        $ret = array();

        $tab_tags = array_flip(explode("|", utf8_decode($tags)));

        // default values
        $tooltip  = "Entreprise";
        $icon     = "copyright outline";
        $icon_tag = "bt_filter_SEC_99999";
        $geo      = "Monde";

        foreach(uimx::$invest_secteur as $key => $val) {
            if (isset($tab_tags[$val['tag']])) {
                $icon     = $val['icon'];
                $tooltip  = $val['tag'];
                $icon_tag = "bt_filter_SEC_".$key;
            }
        }

        foreach(uimx::$invest_zone_geo as $key => $val) {
            if (isset($tab_tags[$val['tag']])) {
                $geo = $val['tag'];
            }
        }

        if ($tooltip == "Entreprise") $tags .= "|Entreprise";

        $ret['icon']     = $icon;
        $ret['icon_tag'] = $icon_tag;
        $ret['tooltip']  = $tooltip;
        $ret['geo']      = $geo;

        return $ret;
    }

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
        <div class="ui inverted card <?= $cn ?>" id="<?= $id ?>">
            <div class="content">
                <div class="header"><?= $header ?></div>
                <div class="meta"><?= $meta ?></div>
                <div class="description"><?= $desc ?></div>
            </div>
        </div>
    <?
}

    public static function perfCard($user_id, $strategie, $data) {

        global $sess_context;

        $day    = $data['day'];
        $perfs  = $data['perfs'];
        $stocks = $data['stocks'];

        $t = json_decode($strategie['data'], true);
        
        // Perfs contient toutes les perfs DM
        // var_dump($perfs);

        $desc = '<table class="ui inverted single line very compact unstackable table"><tbody>';
        
        $x = 0;
        if ($strategie['methode'] != 3) {
            foreach($perfs as $key => $val) {
                if (isset($t["quotes"][$key])) {
                    $desc .= '<tr '.($x == 0 ? 'style="background: green"' : '').'><td>'.$key.'</td><td>'.sprintf("%.2f", $val).'%</td></tr>';
                    $x++;
                }
            }
        } else {
            foreach($perfs as $key => $val) {
                if ($stocks[$key]['pea'] == 1 && $stocks[$key]['type'] == 'ETF' && intval($stocks[$key]['actifs']) >= 150) {
                    $desc .= '<tr '.($x == 0 ? 'style="background: green"' : '').'><td>'.$key.'</td><td>'.sprintf("%.2f", $val).'%</td></tr>';
                    if ($x++ == 5) break;
                }
            }

        }

        $desc .= '</tbody></table>';

        $desc .= '<div style="bottom: 5px; position: absolute; width: 90%;">
            <button class="ui small '.self::$invest_cycle[$strategie['cycle']]['colr'].' button badge" data-tootik="'.self::$invest_cycle[$strategie['cycle']]['tip'].'">'.substr(self::$invest_cycle[$strategie['cycle']]['tip'], 0, 1).'</button>
            <button class="ui small brown button badge" data-tootik="'.self::$invest_methode[$strategie['methode']].'"><i class="inverted '.self::$invest_methode_icon[$strategie['methode']].' icon"></i></button>
            <button id="home_sim_bt_'.$strategie['id'].'" class="ui right floated small grey icon button">Backtesting</button>
        </div>';

        $title = utf8_decode($strategie['title']).($sess_context->isUserConnected() ? "<i id=\"home_strategie_".$strategie['id']."_bt\" class=\"ui inverted right floated black small ".($user_id == $strategie['user_id'] ? "settings" : "copy")." icon\"></i>" : "");
        uimx::genCard("home_card_".$strategie['id'], $title, $day, $desc, "perf_card");
    }

    public static function portfolioCard($portfolio, $portfolio_data) {

        global $sess_context;

        $desc  = '
        <div id="portfolio_dashboard_'.$portfolio['id'].'_bt" class="ui labeled button" tabindex="0">
            <div class="ui '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).'  button">
                <i class="chart pie inverted icon"></i>'.sprintf("%.2f &euro;", $portfolio_data['valo_ptf']).'
            </div>
            <a class="ui basic '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).' left pointing label">'.sprintf("%.2f ", $portfolio_data['perf_ptf']).' %</a>
        </div>';

        $title = utf8_decode($portfolio['name']).($sess_context->isUserConnected() ? "<i id=\"portfolio_edit_".$portfolio['id']."_bt\" class=\"ui inverted right floated black small settings icon\"></i>" : "");
        uimx::genCard("portfolio_card_".$portfolio['id'], $title, date('Y-m-d'), $desc);
    }

    public static function displayHeadTable($head) {
        echo "<thead><tr>";
        foreach($head as $key => $val) {
            echo "<th ".(isset($val['c']) && $val['c'] != "" ? "class=\"".$val['c']."\"" : "")." ".(isset($val['o']) ? $val['o'] : "").">";
            echo $val['l'];
            echo "</th>";
        }
        echo "</tr></thead>";
    }

}

?>