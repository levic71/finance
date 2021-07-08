<?php

// ini_set('display_errors', false);
ini_set('error_log', './finance.log');

header( 'content-type: text/html; charset=utf-8' );

$dbg = true;
$dbg_data = false;

//
// Connection DB
//
class dbc
{
    public static $link;

    public static function connect()
    {
        if (strtolower(getenv('SERVER_NAME') == "localhost" || strtolower(getenv('REMOTE_ADDR')) == "127.0.0.1" || strtolower(getenv('REMOTE_ADDR')) == "localhost"))
            self::$link = mysqli_connect("localhost", "root", "root", "finance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());
        else
            self::$link = mysqli_connect("jorkersfinance.mysql.db", "jorkersfinance", "Rnvubwi2021", "finance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());

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
class calc
{
    public static function processData($symbol) {

        $ret = array();

        $i = 0;
        $sum_MM7   = 0;
        $sum_MM20  = 0;
        $sum_MM200 = 0;
        $ref_DAY = "";
        $ref_DJ0 = "";
        $ref_MJ0 = "";
        $ref_YJ0 = "";
        $ref_TJ0 = 0;
        $ref_T1M = 0;
        $ref_T3M = 0;
        $ref_T6M = 0;
        $ref2_T1M = 0;
        $ref2_T3M = 0;
        $ref2_T6M = 0;

        $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$symbol."' ORDER BY day DESC LIMIT 200" ;
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_array($res)) {

            // Valeurs de reference
            if ($i == 0) {
                $ref_TJ0 = floatval($row['close']);
                $ref_DAY = $row['day'];
                $ref_DJ0 = intval(explode("-", $ref_DAY)[2]);
                $ref_MJ0 = intval(explode("-", $ref_DAY)[1]);
                $ref_YJ0 = intval(explode("-", $ref_DAY)[0]);

                // Recuperation dernier jour ouvre J0-1M
                $ref_D1M = date('Y-m-d', strtotime($ref_YJ0.'-'.$ref_MJ0.'-01'.' -1 day'));
                
                // Recuperation dernier jour ouvre J0-3M
                $m = $ref_MJ0 - 2;
                $y = $ref_YJ0;
                if ($m <= 0) {
                    $m += 12 + 1;
                    $y -= 1;
                }
                $ref_D3M = date('Y-m-d', strtotime($y.'-'.$m.'-01'.' -1 day'));

                // Recuperation dernier jour ouvre J0-6M
                $m = $ref_MJ0 - 5;
                $y = $ref_YJ0;
                if ($m <= 0) {
                    $m += 12 + 1;
                    $y -= 1;
                }
                $ref_D6M = date('Y-m-d', strtotime($y.'-'.$m.'-01'.' -1 day'));
            }

            // R�cupration cotation en mois flottant
            if ($i == 22)  $ref_T1M = floatval($row['close']); // 22j ouvr�s par mois en moy
            if ($i == 66)  $ref_T3M = floatval($row['close']);
            if ($i == 132) $ref_T6M = floatval($row['close']);

            // MM200, MM20, MM7
            if ($i < 7)   $sum_MM7   += floatval($row['close']);
            if ($i < 20)  $sum_MM20  += floatval($row['close']);
            if ($i < 200) $sum_MM200 += floatval($row['close']);

            // Recuperation cotation en fin de mois fixe (le mois en cours pouvant etre non termin�)
            if ($ref2_T1M == 0 && substr($row['day'], 0, 7) == substr($ref_D1M, 0, 7)) {
                $ref2_T1M = $row['close'];
                $ret['MMZ1MDate'] = $row['day'];
            }
            if ($ref2_T3M == 0 && substr($row['day'], 0, 7) == substr($ref_D3M, 0, 7)) {
                $ref2_T3M = $row['close'];
                $ret['MMZ3MDate'] = $row['day'];
            }
            if ($ref2_T6M == 0 && substr($row['day'], 0, 7) == substr($ref_D6M, 0, 7)) {
                $ref2_T6M = $row['close'];
                $ret['MMZ6MDate'] = $row['day'];
            }

            $i++;
        }

        $ret['MM7']   = round(($sum_MM7   / 7),   2);
        $ret['MM20']  = round(($sum_MM20  / 20),  2);
        $ret['MM200'] = round(($sum_MM200 / 200), 2);

        $ret['MMF1M'] = round(($ref_TJ0 - $ref_T1M)*100/$ref_T1M, 2);
        $ret['MMF3M'] = round(($ref_TJ0 - $ref_T3M)*100/$ref_T3M, 2);
        $ret['MMF6M'] = round(($ref_TJ0 - $ref_T6M)*100/$ref_T6M, 2);
        $ret['MMFDM'] = round(($ret['MMF1M']+$ret['MMF3M']+$ret['MMF6M'])/3, 2);

        $ret['MMZ1M'] = round(($ref_TJ0 - $ref2_T1M)*100/$ref2_T1M, 2);
        $ret['MMZ3M'] = round(($ref_TJ0 - $ref2_T3M)*100/$ref2_T3M, 2);
        $ret['MMZ6M'] = round(($ref_TJ0 - $ref2_T6M)*100/$ref2_T6M, 2);
        $ret['MMZDM'] = round(($ret['MMZ1M']+$ret['MMZ3M']+$ret['MMZ6M'])/3, 2);

        return $ret;
    }
}

//
// API Alphavantage
//
class aafinance {

    public static $apikey = "ZFO6Y0QL00YIG7RH";

    public static function getData($function, $options) {

        global $dbg, $dbg_data;

        $url  = 'https://www.alphavantage.co/query?function='.$function.'&'.$options.'&apikey='.self::$apikey;
        $json = file_get_contents($url);
        $data = json_decode($json,true);

        if ($dbg_data) {
            print("<pre>".$json."</pre>");
        }

        if (isset($data['Error Message'])) {
            logger::error("ERROR", "getData", $url);
            throw new RuntimeException($data['Error Message'], 1);
        } elseif (isset($data['Note'])) {
            throw new RuntimeException($data['Note'], 2);
        } else {
            return $data;
        }
    }

    public static function getOverview($symbol, $options = "") {    
        return self::getData("OVERVIEW", "symbol=".$symbol."&".$options);
    }

    public static function getIntraday($symbol, $options = "") {    
        return self::getData("TIME_SERIES_INTRADAY", "symbol=".$symbol."&".$options);
    }

    public static function getDailyTimeSeriesAdjusted($symbol, $options = "") {    
        return self::getData("TIME_SERIES_DAILY_ADJUSTED", "symbol=".$symbol."&".$options);
    }

    public static function getQuote($symbol, $options = "") {    
        return self::getData("GLOBAL_QUOTE", "symbol=".$symbol."&".$options);
    }

    public static function searchSymbol($str, $options = "") {    
        return self::getData("SYMBOL_SEARCH", "keywords=".$str."&".$options);
    }

}

//
// Cache des donnees
//
class cacheData {

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

        // On ne fait rien le weekend
        // if (date("N") >= 6) return false;

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

        $msg = "";
        $file_cache = 'cache/OVERVIEW_'.$symbol.'.json';
        if (self::refreshCache($file_cache, -1)) {
            try {
                $data = aafinance::getOverview($symbol);
        
                $fp = fopen($file_cache, 'w');
                fwrite($fp, json_encode($data));
                fclose($fp);
    
                $msg .= ", CACHE refresh Ok";
    
            } catch(RuntimeException $e) {
                $msg = "Runtime Exception ".($e->getCode() == 1 ? "ERROR" : "NOTE");
                $f = "logger::".($e->getCode() == 1 ? "error" : "info");
                $f("CRON", $symbol, $e->getMessage());
            }
        }
        else {
            $msg = "[Overview] => No update";
        }
    
        logger::info("CRON", $symbol, $msg);
    }

    public static function buildCacheIntraday($symbol) {

        $msg = "";
        $file_cache = 'cache/INTRADAY_'.$symbol.'.json';
        if (self::refreshCache($file_cache, 600)) {
            try {
                $data = aafinance::getIntraday($symbol, "interval=60min&outputsize=compact");

                // Delete old entries for symbol before insert new ones ?
        
                $msg = "[Intraday] => Update DB NOk";
                if (isset($data["Time Series (60min)"])) {
                    foreach($data["Time Series (60min)"] as $key => $val) {
                        $update = "INSERT INTO intraday (symbol, day, open, high, low, close, volume) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. volume']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', volume='".$val['5. volume']."'";
                        $res2 = dbc::execSql($update);
                    }
                    $msg = "[Intraday] => Update DB Ok";
                }
    
                $fp = fopen($file_cache, 'w');
                fwrite($fp, json_encode($data));
                fclose($fp);
    
                $msg .= ", CACHE refresh Ok";
    
            } catch(RuntimeException $e) {
                $msg = "Runtime Exception ".($e->getCode() == 1 ? "ERROR" : "NOTE");
                $f = "logger::".($e->getCode() == 1 ? "error" : "info");
                $f("CRON", $symbol, $e->getMessage());
            }
        }
        else {
            $msg = "[Intraday] => No update";
        }
    
        logger::info("CRON", $symbol, $msg);
    }

    public static function buildCacheDailyTimeSeriesAdjusted($symbol, $full = true) {

        $msg = "";
        $extension = $full ? 'FULL' : "COMPACT";
        $file_cache = 'cache/DAILY_TIME_SERIES_ADJUSTED_'.$extension.'_'.$symbol.'.json';

        if ($full ? self::refreshCache($file_cache, -1) : self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getDailyTimeSeriesAdjusted($symbol, $full ? "outputsize=full" : "outputsize=compact");
    
                if (count($data) == 0) logger::warning("CRON", $symbol, "Array empty, manual db update needed !!!");
    
                $msg = "[Daily Time Series Adjusted ".($full ? "FULL" : "COMPACT")."] => Update DB NOk";
                if (isset($data["Time Series (Daily)"])) {
                    foreach($data["Time Series (Daily)"] as $key => $val) {
                        $update = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, ajusted_close, volume, dividend, split_coef) VALUES ('".$symbol."', '".$key."', '".$val['1. open']."', '".$val['2. high']."', '".$val['3. low']."', '".$val['4. close']."', '".$val['5. adjusted close']."', '".$val['6. volume']."', '".$val['7. dividend amount']."', '".$val['8. split coefficient']."') ON DUPLICATE KEY UPDATE open='".$val['1. open']."', high='".$val['2. high']."', low='".$val['3. low']."', close='".$val['4. close']."', ajusted_close='".$val['5. adjusted close']."', volume='".$val['6. volume']."', dividend='".$val['7. dividend amount']."', split_coef='".$val['8. split coefficient']."'";
                        $res2 = dbc::execSql($update);
                    }
                    $msg = "[Daily Time Series Adjusted ".($full ? "FULL" : "COMPACT")."] => Update DB Ok";
                }
    
                $fp = fopen($file_cache, 'w');
                fwrite($fp, json_encode($data));
                fclose($fp);
    
                $msg .= ", CACHE refresh Ok";
    
            } catch(RuntimeException $e) {
                $msg = "Runtime Exception ".($e->getCode() == 1 ? "ERROR" : "NOTE");
                $f = "logger::".($e->getCode() == 1 ? "error" : "info");
                $f("CRON", $symbol, $e->getMessage());
            }
        }
        else {
            $msg = "[Daily Time Series Adjusted ".($full ? "FULL" : "COMPACT")."] => No update";
        }
    
        logger::info("CRON", $symbol, $msg);

    }

    public static function buildCacheQuote($symbol) {

        $msg = "";
        $file_cache = 'cache/QUOTE_'.$symbol.'.json';
        if (self::refreshOnceADayCache($file_cache)) {
            try {
                $data = aafinance::getQuote($symbol);
        
                $msg = "[Quote] => Update DB NOk";
                if (isset($data["Global Quote"])) {
                    $val = $data["Global Quote"];
                    $update = "INSERT INTO quote (symbol, open, high, low, price, volume, day, previous, day_change, percent) VALUES ('".$symbol."', '".$val['02. open']."', '".$val['03. high']."', '".$val['04. low']."', '".$val['05. price']."', '".$val['06. volume']."', '".$val['07. latest trading day']."', '".$val['08. previous close']."', '".$val['09. change']."', '".$val['10. change percent']."') ON DUPLICATE KEY UPDATE open='".$val['02. open']."', high='".$val['03. high']."', low='".$val['04. low']."', price='".$val['05. price']."', volume='".$val['06. volume']."', day='".$val['07. latest trading day']."', previous='".$val['08. previous close']."', day_change='".$val['09. change']."', percent='".$val['10. change percent']."'";
                    $res2 = dbc::execSql($update);
                    $msg = "[Quote] => Update DB Ok";
                }
    
                $fp = fopen($file_cache, 'w');
                fwrite($fp, json_encode($data));
                fclose($fp);
    
                $msg .= ", CACHE refresh Ok";
    
            } catch(RuntimeException $e) {
                $msg = "Runtime Exception ".($e->getCode() == 1 ? "ERROR" : "NOTE");
                $f = "logger::".($e->getCode() == 1 ? "error" : "info");
                $f("CRON", $symbol, $e->getMessage());
            }
        }
        else {
            $msg = "[Quote] => No update";
        }
    
        logger::info("CRON", $symbol, $msg);
    }

    public static function buildCacheSymbol($symbol) {
        self::buildCacheOverview($symbol);
        self::buildCacheDailyTimeSeriesAdjusted($symbol, true);
        self::buildCacheDailyTimeSeriesAdjusted($symbol, false);
        self::buildCacheQuote($symbol);
        // self::buildCacheIntraday($symbol);
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
}

?>