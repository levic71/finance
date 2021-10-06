<?php

// ini_set('display_errors', false);
ini_set('error_log', './finance.log');

header( 'content-type: text/html; charset=iso-8859-1' );

$dbg = false;
$dbg_data = false;

// On place la timezone � UTC pour pouvoir gerer les fuseaux horaires des places boursieres
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
        return $action == "new" ? "Ajouter" : ($action == "upt" ? "Modifier" : "Supprimer");
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


    public static function getAchatActifsDCAInvest($day, $lst_decode_symbols, $lst_actifs_achetes_pu, $invest_montant) {

        $ret = array();

        $ret['invest'] = $invest_montant;
        $ret['buy'] = array();
        $ret['valo_achats'] = 0;

        foreach($lst_decode_symbols as $key => $val) {

            // Si on n'a pas d'histo pour cet actif a cette date on passe ...
            if ($lst_actifs_achetes_pu[$key] == 0) continue;

            // Montant par actif � poss�der
            $montant2get = floor(intval($invest_montant) * $lst_decode_symbols[$key] / 100);

            // Nombre d'actions � acheter
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

    public static function processDataDM($symbol, $day) {

        global $dbg, $dbg_data;

        if ($dbg_data) echo $symbol.", ".$day."<br />";

        $ret = array();

        $i = 0;
        $sum_MM7   = 0;
        $sum_MM20  = 0;
        $sum_MM50  = 0;
        $sum_MM200 = 0;
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

        $req = "SELECT * FROM quotes WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);
        if ($row = mysqli_fetch_assoc($res)) {
            $quote_day   = $row['day'];
            $quote_price = $row['price'];
            $quote_pct   = $row['percent'];            
        }

        $tab_close = array();

        // On parcours les cotations en commencant la plus rescente et on remonte le temps
        $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$symbol."' AND day <= '".$day."' ORDER BY day DESC LIMIT 200";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            // On prend la valeur de cloture ajust�e pour avoir les courbes coh�rentes
            $close_value = is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];

            $tab_close[] = $close_value;

            // Valeurs de reference J0
            if ($i == 0) {
                // Si day is today && Si on a recupere une quotation en temps r�el du jour > � la premi�re cotation historique alors on la prend comme r�f�rence
                // Comme la cotation est au minimum � la date de la derni�re cotation historique on peut la prendre en ref par defaut
                if ($day == date("Y-m-d") && isset($quote_day)) {
                    $ref_TJ0 = floatval($quote_price);
                    $ref_DAY = $quote_day;
                    $ref_PCT = $quote_pct;
                }
                else {
                    $ref_TJ0 = floatval($close_value);
                    $ref_DAY = $row['day'];
                    $ref_PCT = ($row['close'] - $row['close']) / $row['open'];
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

            // R�cupration cotation en mois flottant
            if ($i == 22)  $ref_T1M = floatval($close_value); // 22j ouvr�s par mois en moy
            if ($i == 66)  $ref_T3M = floatval($close_value);
            if ($i == 132) $ref_T6M = floatval($close_value);

            // MM200, MM50, MM20, MM7
            if ($i < 7)   $sum_MM7   += floatval($close_value);
            if ($i < 20)  $sum_MM20  += floatval($close_value);
            if ($i < 50)  $sum_MM50  += floatval($close_value);
            if ($i < 200) $sum_MM200 += floatval($close_value);

            // Recuperation cotation en fin de mois fixe (le mois en cours pouvant etre non termin�)
            if ($ref2_T1M == 0 && substr($row['day'], 0, 7) == substr($ref_D1M, 0, 7)) {
                $ref2_T1M = $close_value;
                $ret['MMZ1MDate'] = $row['day'];
            }

            // Recuperation cotation en fin de 3 mois
            if ($ref2_T3M == 0 && substr($row['day'], 0, 7) == substr($ref_D3M, 0, 7)) {
                $ref2_T3M = $close_value;
                $ret['MMZ3MDate'] = $row['day'];
            }

            // Recuperation cotation en fin de 6 mois
            if ($ref2_T6M == 0 && substr($row['day'], 0, 7) == substr($ref_D6M, 0, 7)) {
                $ref2_T6M = $close_value;
                $ret['MMZ6MDate'] = $row['day'];
            }

            if ($dbg_data) echo $row['day']." ".$row['symbol']." ".$close_value."<br />";

            $i++;
        }

        if ($dbg_data) echo $symbol." => ref_D6M = ".$ref_D6M.", ref2_T6M = ".$ref2_T6M."<br />";

        $ret['ref_close'] = $ref_TJ0;
        $ret['ref_day'] = $ref_DAY;
        $ret['ref_pct'] = $ref_PCT;

        $ret['MM7']   = round(($sum_MM7   / 7),   2);
        $ret['MM20']  = round(($sum_MM20  / 20),  2);
        $ret['MM50']  = round(($sum_MM50  / 50),  2);
        $ret['MM200'] = round(($sum_MM200 / 200), 2);

        $ret['MMF1M'] = $ref_T1M == 0 ? -9999 : round(($ref_TJ0 - $ref_T1M)*100/$ref_T1M, 2);
        $ret['MMF3M'] = $ref_T3M == 0 ? -9999 : round(($ref_TJ0 - $ref_T3M)*100/$ref_T3M, 2);
        $ret['MMF6M'] = $ref_T6M == 0 ? -9999 : round(($ref_TJ0 - $ref_T6M)*100/$ref_T6M, 2);
        $ret['MMFDM'] = $ref_T6M > 0 ? round(($ret['MMF1M']+$ret['MMF3M']+$ret['MMF6M'])/3, 2) : ($ref_T3M > 0 ? round(($ret['MMF1M']+$ret['MMF3M'])/2, 2) : ($ref_T1M > 0 ? $ret['MMF1M'] : -9999));

        $ret['MMZ1M'] = $ref2_T1M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T1M)*100/$ref2_T1M, 2);
        $ret['MMZ3M'] = $ref2_T3M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T3M)*100/$ref2_T3M, 2);
        $ret['MMZ6M'] = $ref2_T6M == 0 ? -9999 : round(($ref_TJ0 - $ref2_T6M)*100/$ref2_T6M, 2);
        $ret['MMZDM'] = $ref2_T6M > 0 ? round(($ret['MMZ1M']+$ret['MMZ3M']+$ret['MMZ6M'])/3, 2) : ($ref2_T3M > 0 ? round(($ret['MMZ1M']+$ret['MMZ3M'])/2, 2) : ($ref2_T1M > 0 ? $ret['MMZ1M'] : -9999));

//        $rsi14_tab = computeRSIX($tab_close, 14);
//        $ret["RSI14"] = $rsi14_tab[length($rsi14_tab)];
        $ret["RSI14"] = 50;

        return $ret;
    }

    public static function getDualMomentum($day) {

        $file_cache = 'cache/TMP_DUAL_MOMENTUM_'.$day.'.json';

        $ret = array( 'stocks' => array(), 'perfs' => array(), 'day' => $day, 'compute_time' => date("Y-d-m H:i:s") );

        if (cacheData::refreshCache($file_cache, 600)) { // Cache de 10 min

            $req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol ORDER BY s.symbol";
            $res = dbc::execSql($req);
            while($row = mysqli_fetch_assoc($res)) {
                $symbol = $row['symbol'];
                $ret["stocks"][$symbol] = array_merge($row, calc::processDataDM($symbol, $day));
                // On isole les perfs pour pouvoir trier par performance decroissante
                $ret["perfs"][$symbol] = $ret["stocks"][$symbol]['MMZDM'];
            }

            cacheData::writeCacheData($file_cache, $ret);

        } else {
            $ret = cacheData::readCacheData($file_cache);
        }

        return $ret;
    }

    public static function getFilteredDualMomentum($lst_symbols, $day) {

        $stocks = array();
        $perfs  = array();

        $data = self::getDualMomentum($day);

        foreach($data["stocks"] as $key => $val) {
            if (!in_array($key, $lst_symbols)) {
                unset($data["stocks"][$key]);
                unset($data["perfs"][$key]);
            }
        }

        return $data;
    }

}

//
// API Alphavantage
//
class aafinance {

    public static $apikey = "ZFO6Y0QL00YIG7RH";
    public static $apikey_local = "X6K6Z794TD321PTH";

    public static function getData($function, $symbol, $options) {

        global $dbg, $dbg_data;

        $url  = 'https://www.alphavantage.co/query?function='.$function.'&'.$options.'&apikey='.(tools::isLocalHost() ? self::$apikey_local : self::$apikey);
        $json = file_get_contents($url);
        $data = json_decode($json,true);

        if ($dbg_data) {
            print("<pre>".$json."</pre>");
        }

        $data['status'] = 0;
        if (isset($data['Error Message'])) {
            $data['status'] = 2;
            logger::error("ALPHAV", $symbol, "[".$function."] [".$options."] [".$data['Error Message']."]");
        } elseif (isset($data['Note'])) {
            $data['status'] = 1;
            logger::warning("ALPHAV", $symbol, "[".$function."] [".$options."] [".$data['Note']."]");
        } else {
            logger::info("ALPHAV", $symbol, "[".$function."] [".$options."] [OK]");
        }

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

    public static function getWeeklyTimeSeriesAdjusted($symbol, $options = "") {
        return self::getData("TIME_SERIES_WEEKLY_ADJUSTED", $symbol, "symbol=".$symbol.($options == "" ? "" : "&").$options);
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

    public static $lst_cache = ["OVERVIEW", "QUOTE", "DAILY_TIME_SERIES_ADJUSTED_FULL", "DAILY_TIME_SERIES_ADJUSTED_COMPACT", "INTRADAY"];

    public static function isComputeIndicatorsDoneToday($symbol) {

        $searchthis = date("d-M-Y").".*".$symbol.".*daily=";
        $matches = array();
    
        $handle = @fopen("./finance.log", "r");
        fseek($handle, -81920, SEEK_END); // +/- 900 lignes 
        if ($handle)
        {
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                if (preg_match("/".$searchthis."/i", $buffer))
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
                }    
            } catch(RuntimeException $e) { }
        }
        else
            logger::info("CACHE", $symbol, "[INTRADAY] [No update]");    
    }

    public static function buildCacheDailyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/DAILY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        if (self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getDailyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = "Time Series (Daily)";
                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {
                        $update = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."', '".$val['8. split coefficient']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."', split_coef='".$val['8. split coefficient']."'";
                        $res2 = dbc::execSql($update);
                    }
    
                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
    
                    $ret = true;
                }        
            } catch(RuntimeException $e) { }
        }
        else
            logger::info("CACHE", $symbol, "[DAILY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [No update]");

        return $ret;
    }

    public static function buildCacheWeeklyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/WEEKLY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        // Si on n'est pas samedi
        if (date("N") != 6 && !$full) return false;

        if (self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getWeeklyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = "Weekly Adjusted Time Series";
                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {
                        $update = "INSERT INTO weekly_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."'";
                        $res2 = dbc::execSql($update);
                    }

                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);

                    $ret = true;
                }
            } catch(RuntimeException $e) { }
        }
        else
            logger::info("CACHE", $symbol, "[WEEKLY_TIME_SERIES_ADJUSTED] [".($full ? "FULL" : "COMPACT")."] [No update]");

        return $ret;
    }

    public static function buildCacheMonthlyTimeSeriesAdjusted($symbol, $full = true) {

        $ret = false;

        $file_cache = 'cache/MONTHLY_TIME_SERIES_ADJUSTED_'.($full ? 'FULL' : "COMPACT").'_'.$symbol.'.json';

        // Si on n'est pas dimanche
        if (date("N") != 7 && !$full) return false;

        if (self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getMonthlyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (is_array($data) && count($data) == 0) logger::warning("CACHE", $symbol, "Array empty, manual db update needed !!!");
    
                $key = "Monthly Adjusted Time Series";
                if (isset($data[$key])) {
                    foreach($data[$key] as $key => $val) {
                        $update = "INSERT INTO monthly_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', adjusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."'";
                        $res2 = dbc::execSql($update);
                    }

                    $fp = fopen($file_cache, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);
    
                    $ret = true;
                }        
            } catch(RuntimeException $e) { }
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
                }        
            } catch(RuntimeException $e) { }
        }
        else
            logger::info("CACHE", $symbol, "[GLOBAL_QUOTE] [No update]");

        return $ret;
    }

    public static function buildAllsCachesSymbol($symbol, $full = false) {

        $ret = array("overview" => false, "daily" => false, "weekly" => false, "monthly" => false, "quote" => false);

        // ASSET OVERVIEW
        $ret["overview"] = self::buildCacheOverview($symbol);

        // DAILY HISTORIQUE
        $ret["daily"] = self::buildCacheDailyTimeSeriesAdjusted($symbol, $full);

        // WEEKLY HISTORIQUE
        $ret["weekly"] = self::buildCacheWeeklyTimeSeriesAdjusted($symbol, $full);

        // MONTHLY HISTORIQUE
        $ret["monthly"] = self::buildCacheMonthlyTimeSeriesAdjusted($symbol, $full);

        // ASSET COTATION
        $ret["quote"] = self::buildCacheQuote($symbol);

        return $ret;
    }

    public static function deleteCacheSymbol($symbol) {
        foreach(self::$lst_cache as $key)
            if (file_exists("cache/".$key."_".$symbol.".json")) unlink("cache/".$key."_".$symbol.".json");
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
    
    public static function genCard($id, $header, $meta, $desc) {
    ?>
        <div class="ui inverted card" id="<?= $id ?>">
            <div class="content">
                <div class="header"><?= $header ?></div>
                <div class="meta"><?= $meta ?></div>
                <div class="description"><?= $desc ?></div>
            </div>
        </div>
    <?
}

    public static function perfCard($id, $strategie_id, $title, $day, $perfs, $strategie, $methode) {

        global $sess_context;

        $t = json_decode($strategie, true);

        $desc = '<table class="ui inverted single line very compact unstackable table"><tbody>';
        
        $x = 0;
        foreach($perfs as $key => $val) {
            if (isset($t["quotes"][$key])) {
                $desc .= '<tr '.($x == 0 ? 'style="background: green"' : '').'><td>'.$key.'</td><td>'.sprintf("%.2f", $val).'%</td></tr>';
                $x++;
            }
        }

        $desc .= '</tbody>';
        $desc .= '<tfoot class="full-width"><tr>
            <th colspan="2">
                <button id="home_sim_bt_'.$strategie_id.'" class="ui right floated small grey labeled icon button"><i class="inverted '.($methode == 2 ? 'cubes' : 'diamond').' icon"></i> Backtesting</button>
            </th>
        </tr></tfoot>';
        $desc .= '</table>';

        $title = $title.($sess_context->isUserConnected() ? "<i id=\"home_strategie_".$strategie_id."_bt\" class=\"ui inverted right floated black small settings icon\"></i>" : "");
        uimx::genCard($id, $title, $day, $desc);
    }
}

?>