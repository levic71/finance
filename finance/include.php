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


// ///////////////////////////////////////////////////////////////////////////////////////
class tools {

    public static function UTF8_encoding($val) {
        return $val == NULL ? "" : mb_convert_encoding($val, 'ISO-8859-1', 'UTF-8');
    }

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
        return (strtolower(getenv('LOGNAME')) == "ferreira" || strtolower(getenv('SERVER_NAME')) == "localhost" || strtolower(getenv('REMOTE_ADDR')) == "127.0.0.1" || strtolower(getenv('REMOTE_ADDR')) == "localhost") ? true : false;
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


// ///////////////////////////////////////////////////////////////////////////////////////
// Importer un export en local :
// /Applications/MAMP/Library/bin/mysql -u root -p finance2 < /Users/ferreira/Downloads/jorkersfinance.sql
// mpd root/root 
// ///////////////////////////////////////////////////////////////////////////////////////


// ///////////////////////////////////////////////////////////////////////////////////////
class dbc {

    public static $link;

    public static function connect()
    {
        if (tools::isLocalHost())
            self::$link = mysqli_connect("localhost", "root", "root", "finance2") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());
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


// ///////////////////////////////////////////////////////////////////////////////////////
class StockComputing {

    protected $quotes  = [];
    protected $ptf     = [];
    protected $devises = [];
    protected $infos   = [];
    protected $positions = [];
    protected $orders    = [];
    protected $orders_futur    = [];
    protected $trend_following = [];
    protected $save_quotes = [];
    protected $strat_ptf   = 1;

    public function __construct($quotes, $ptf, $devises) {

        $this->quotes  = $quotes;
        $this->ptf     = $ptf;
        $this->devises = $devises;

        // On récupère les infos du portefeuille + les positions et les ordres
        if (isset($this->ptf['infos']))           $this->infos     = $this->ptf['infos'];
        if (isset($this->ptf['positions']))       $this->positions = $this->ptf['positions'];
        if (isset($this->ptf['orders']))          $this->orders    = $this->ptf['orders'];
        if (isset($this->ptf['orders_futur']))    $this->orders_futur    = $this->ptf['orders_futur'];
        if (isset($this->ptf['trend_following'])) $this->trend_following = $this->ptf['trend_following'];

        $this->saveQuotes();

    }

    public function setStratPtf($strat) { $this->strat_ptf = $strat; }
    public function getStratPtf()       { return $this->strat_ptf; }
    public function isPtfSynthese()     { return $this->ptf['infos']['synthese'] == 1 ? true : false; }
    public function getTrendFollowing() { return $this->trend_following; }
    public function getPositions()      { return $this->positions; }
    public function getOrders()         { return $this->orders; }
    public function getOrdersFutur()    { return $this->orders_futur; }
    public function getInfos()          { return $this->infos; }

    public function saveQuotes() {
        if (isset($this->ptf['infos']['quotes'])) {
            $t = explode(',', $this->ptf['infos']['quotes']);
            if ($t[0] != '') {
                foreach($t as $key => $val) {
                    $x = explode('|', $val);
                    $this->save_quotes[$x[0]] = $x[1];
                }
            }
        }
    }

    public function getPositionAttr($symbol, $attr, $def = null) {
        return isset($this->positions[$symbol][$attr]) ? $this->positions[$symbol][$attr] : $def;
    }

    public function getTrendFollowingAttr($symbol, $attr, $def = null) {
        return isset($this->trend_following[$symbol][$attr]) ? $this->trend_following[$symbol][$attr] : $def; 
    }

    public function getSavedQuote($symbol)    { return $this->save_quotes[$symbol]; }
    public function getQuote($symbol)         { return isset($this->quotes['stocks'][$symbol]) ? $this->quotes['stocks'][$symbol] : [ 'DM' => 0, 'MM7' => 0, "MM20" => 0, "MM50" => 0, "MM100" => 0, "MM200" => 0]; }
    public function getDeviseTaux($currency)  { return $currency == "EUR" ? 1 : calc::getCurrencyRate($currency."EUR", $this->devises); }
    public function getPtfName()              { return $this->infos['name']; }
    public function getCountPositionsInPtf()  { return count($this->positions); }

    public function isSavedQuoteExits($symbol) { return !isset($this->save_quotes[$symbol]) ? false : true; }
    public function isInPtf($symbol)           { return isset($this->positions[$symbol]) ? true : false; }
    public function isInQuotes($symbol)        { return isset($this->quotes['stocks'][$symbol]); }
}


// ///////////////////////////////////////////////////////////////////////////////////////
class QuoteComputing {

    protected $symbol;
    protected $sc;

    protected $quote; // Contient toutes les data de l'actif
    protected $is_price_from_pru = false;
    protected $price;
    protected $currency;

    // Type strategie ptf   => 1: Protectrice,  2: Passive, 3: Offensive, 4: Aggressive
    // Type strategie actif => 1: Speculatif, 2: Dividende, 3: Croissance, 4: D&C
    protected $limits_objectif = [
        1 => [ 1 => 0,  2 => 0,  3 => 0,  4 => 0  ],
        2 => [ 1 => 0,  2 => 30, 3 => 20, 4 => 25 ],
        3 => [ 1 => 20, 2 => 60, 3 => 40, 4 => 50 ], 
        4 => [ 1 => 60, 2 => 90, 3 => 60, 4 => 75 ]
    ];
    protected $limits_stopprofit = [
        1 => [ 1 => 0,  2 => 0,  3 => 0,  4 => 0 ],
        2 => [ 1 => 0,  2 => 0,  3 => 0,  4 => 0 ],
        3 => [ 1 => 5,  2 => 10, 3 => 10, 4 => 10 ], 
        4 => [ 1 => 10, 2 => 20, 3 => 20, 4 => 20 ]
    ];
    protected $limits_pru = [
        1 => [ 1 => 5,  2 => 5,  3 => 5,  4 => 5 ], 
        2 => [ 1 => 10, 2 => 20, 3 => 20, 4 => 20 ], 
        3 => [ 1 => 20, 2 => 40, 3 => 60, 4 => 50 ], 
        4 => [ 1 => 40, 2 => 60, 3 => 90, 4 => 70 ]
    ];

    // $val -> $sc->getPositionAttr($symbol,
    // $qs -> this->quote

    public function __construct($sc, $symbol) {
        
        $this->symbol = $symbol;
        $this->sc = $sc;

        // Infos sur actif courant
        $this->quote = $sc->getQuote($symbol);

        // Si aucun pricing enregistré, on prend le pru
        $this->is_price_from_pru = isset($this->quote['price']) && !$sc->isSavedQuoteExits($symbol) ? false : true;

        // Si aucun pricing enregistré, on prend le pru
        $this->price = $this->is_price_from_pru ? ($sc->isSavedQuoteExits($symbol) ? $sc->getSavedQuote($symbol) : $sc->getPositionAttr($symbol, 'pru')) : $this->quote['price'];
        $this->quote['price'] = $this->price; // On force au cas ou ce serait vide

        $this->currency = $this->getOtherName() ? $sc->getPositionAttr($symbol, 'devise') : $this->quote['currency'];
    }

    public static function getQuoteNameWithoutExtension($name) {
        $s_search = array('.PAR', 'EPA.', '.DEX', 'SWX.', '.LON', '.AMX', '.LIS', '.AMS', 'INDEXCBOE.', 'INDEXDJX..', 'INDEXEURO.', 'INDEXNASDAQ..', 'INDEXRUSSELL.', 'INDEXSP..', 'NASDAQ:');
        $s_replace = array('');
        return str_replace($s_search, $s_replace, $name);
    }

    public function getQuote()    { return $this->quote; }
    public function getCurrency() { return $this->currency; }
    public function getPrice()    { return $this->price; }

    public function refreshQuote($row) {
        foreach($row as $key => $val) $this->quote[$key] = $val;
    }

    public function getPositionAttr($attr, $def = null) {
        return $this->sc->getPositionAttr($this->symbol, $attr, $def);
    }

    public function getQuoteAttr($attr, $def = "") {
        return isset($this->quote[$attr]) ? $this->quote[$attr] : $def;
    }

    public function getSumValoInEuro()    { return $this->sc->getPositionAttr($this->symbol, 'sum_valo_in_euro'); }
    public function getOtherName()        { return $this->sc->getPositionAttr($this->symbol, 'other_name'); }
    public function getPru()              { return $this->sc->getPositionAttr($this->symbol, 'pru', 0); }
    public function getNbPositions()      { return $this->sc->getPositionAttr($this->symbol, 'nb', 0); }
    public function getStopLoss()         { return $this->sc->getTrendFollowingAttr($this->symbol, 'stop_loss')   ? $this->sc->getTrendFollowingAttr($this->symbol, 'stop_loss')   : 0; }
    public function getStopProfit()       { return $this->sc->getTrendFollowingAttr($this->symbol, 'stop_profit') ? $this->sc->getTrendFollowingAttr($this->symbol, 'stop_profit') : 0; }
    public function getObjectif()         { return $this->sc->getTrendFollowingAttr($this->symbol, 'objectif')    ? $this->sc->getTrendFollowingAttr($this->symbol, 'objectif')    : 0; }
    public function getSeuils()           { return $this->sc->getTrendFollowingAttr($this->symbol, 'seuils')      ? $this->sc->getTrendFollowingAttr($this->symbol, 'seuils')      : ""; }
    public function getOptions()          { return $this->sc->getTrendFollowingAttr($this->symbol, 'options')     ? $this->sc->getTrendFollowingAttr($this->symbol, 'options')     : 0; }
    public function getStrategieType()    { return $this->sc->getTrendFollowingAttr($this->symbol, 'strategie_type', 1);    }
    public function getRegressionType()   { return $this->sc->getTrendFollowingAttr($this->symbol, 'regression_type', 1);   }
    public function getRegressionPeriod() { return $this->sc->getTrendFollowingAttr($this->symbol, 'regression_period', 0); }
    public function getTaux()             { return $this->sc->getDeviseTaux($this->currency); }
    public function getTauxChangeMoyen()  { return $this->sc->getPositionAttr($this->symbol, 'taux_change_moyen', 1); }

    public function getRegion()           { return $this->getQuoteAttr('region'); }
    public function getOpenCloseMarket()  { return $this->getQuoteAttr('marketopen')."-".$this->getQuoteAttr('marketclose'); }
    public function getTimeZone()         { return $this->getQuoteAttr('timezone'); }
    public function getDateCotation()     { return $this->getQuoteAttr('day'); }
    public function getMM7()              { return $this->getQuoteAttr('MM7'); }
    public function getMM200()            { return $this->getQuoteAttr('MM200'); }
    public function getDM()               { return $this->getQuoteAttr('DM'); }
    public function getType()             { return $this->getQuoteAttr('type'); }
    public function getTags()             { return $this->getQuoteAttr('tags'); }
    public function getOtherName2()       { return $this->getQuoteAttr('other_name', null); }
    public function getPct()              { return $this->getQuoteAttr('percent', 0); }
    public function getDividendeAnnuel()  { return $this->getQuoteAttr('dividende_annualise', 0); }

    public function getName()          { return $this->getQuoteAttr(('name')); /* Concatenation de 2 strings */ }
    public function getPName()         { return $this->symbol.($this->getOtherName() ? '(*)' : ''); /* Concatenation de 2 strings */ }
    public function getValo()          { return sprintf("%.2f", $this->sc->getPositionAttr($this->symbol, 'nb') * $this->price); }
    public function getPctMM200()      { return $this->getQuoteAttr('MM200') ? (($this->getQuoteAttr('MM200') - $this->price) * 100) / $this->price : 0; }
    public function getPerf()          { return round($this->getPru() ? ($this->getPru() * 100) / $this->getPru() : 0, 2); }
    public function getPerfIndicator() { return calc::getPerfIndicator($this->quote); }
    public function getEstimationDividende() { return $this->getDividendeAnnuel() * $this->sc->getPositionAttr($this->symbol, 'nb') * $this->getTaux(); }

    public function isPriceFromPru()   { return $this->is_price_from_pru; }
    public function isAlerteActive()   { return $this->sc->getTrendFollowingAttr($this->symbol, 'active') && $this->sc->getTrendFollowingAttr($this->symbol, 'active') == 1 ? true : false; }
    public function isWatchlist()      { return $this->sc->getTrendFollowingAttr($this->symbol, 'watchlist') && $this->sc->getTrendFollowingAttr($this->symbol, 'watchlist') == 1 ? true : false; }
    public function isTypeIndice()     { return $this->getType() == "INDICE"; }
    public function isInPtf()          { return $this->sc->isInPtf($this->symbol); }

    public function getAvis() {

        $ret = [];

        $price      = $this->getPrice();
        $previous   = $this->getQuoteAttr('previous');
        $pru        = $this->getPru();
        $stoploss   = $this->getStoploss();
        $objectif   = $this->getObjectif();
        $stopprofit = $this->getStopProfit();
        $options    = $this->getOptions();
        $seuils     = explode(';', $this->getSeuils());
        $strat_type = $this->getStrategieType();
        $strat_ptf  = $this->sc->getStratPtf();
        $DM    = $this->getDM();
        $PI    = $this->getPerfIndicator();
        $MM200 = $this->getMM200();

        //
        // Limites atteintes
        // 
        $ret['limit_objectif']   = $objectif > 0   && $this->pourcentagevariation($objectif,   $price) >= $this->limits_objectif[$strat_ptf][$strat_type]   ? 1 : 0;
        $ret['limit_stopprofit'] = $stopprofit > 0 && $this->pourcentagevariation($stopprofit, $price) >= $this->limits_stopprofit[$strat_ptf][$strat_type] ? 1 : 0;
        $ret['limit_pru']        = $pru > 0        && $this->pourcentagevariation($pru, $price)        >= $this->limits_pru[$strat_ptf][$strat_type]        ? 1 : 0;

        // Price < PRU
        if ($pru > 0 && $this->pourcentagevariation($pru, $price) < 0) $ret['limit_pru'] = -1;

        $ret['limit_seuil'] = 0;
        if (count($seuils) > 0 && $seuils[0] != "") {

            // Parcours du cours de chaque seuil
            $x = 1;
            foreach($seuils as $i => $v) {
                $tmp = explode('|', $v);
                $inverse = isset($tmp[1]) ? $tmp[1] : 1;
                $ret['limit_seuil'] = $price < $tmp[0] ? -1 * $x++ : 0;
            }
        }

        $ret['limit_tendance'] = 0;
        $ret['limit_mm200']    = 0;

        // Si aucun seuil fixé
        if (count($seuils) == 0 || $seuils[0] == "") {
            // Tendance de fond
            $ret['limit_tendance'] = $DM >= 0 && ($PI >= 2 && $PI <= 8) ? 1 : -1;    // Renforcer position, price < pru

            // Price par rapport MM200
            $ret['limit_mm200'] = $MM200 >= $price ? 1 : -1;    // Renforcer position, price < pru
        }

        // Position par rapport 1 ou 2 EC


        if (false) { 
            echo $this->symbol.":".$price.":".$pru.":".$objectif.":".$stopprofit.":".$this->getSeuils().":".$DM.":".$PI.":".$MM200."<br/>";
            echo $this->symbol.":obj  :".$this->pourcentagevariation($objectif,   $price).":".$this->limits_objectif[$strat_ptf][$strat_type]."<br/>";
            echo $this->symbol.":stopp:".$this->pourcentagevariation($stopprofit, $price).":".$this->limits_stopprofit[$strat_ptf][$strat_type]."<br/>";
            echo $this->symbol.":pru  :".$this->pourcentagevariation($pru,   $price).":".$this->limits_pru[$strat_ptf][$strat_type]."<br/>";
            var_dump($ret);
        }

        return $ret;
    }

    public function getScoreAvis($avis) {
        $ret = 3;

        if ($this->isInPtf()) {

            if ($avis['limit_pru']        >= 1) $ret = 4;   // Alléger
            if ($avis['limit_objectif']   >= 1) $ret = 4;   // Alléger
            if ($avis['limit_stopprofit'] >= 1) $ret = 5;   // Vendre
            if ($avis['limit_tendance']   >= 1 && $avis['limit_pru'] < 0) $ret = 2;   // Renforcer position, price < pru

        } else {

            if ($avis['limit_seuil'] < 0) $ret = 1;                                    // Initier position
            if ($avis['limit_mm200'] < 0 && $avis['limit_tendance'] >= 1) $ret = 1;    // Initier position
            if ($avis['limit_tendance'] < 0 && $ret == 2) $ret = 1;                    // Attendre plutot qu'observer

        }

        return $ret;
    }

    public function getBGColorAvis($avis) {
        $tab_colr = [
            1 => [ 1 => "green", 2 => "blue", 3 => "lightgrey", 4 => "yellow", 5 => "red" ],    // In Ptf
            2 => [ 1 => "green", 2 => "lightgrey", 3 => "black" ]   // Out
        ];

        return $tab_colr[$this->sc->isInPtf($this->symbol) ? 1 : 2][$avis];
    }

    public function getLabelAvis($avis) {

        $tab_libelle = [
            1 => [ 1 => "Acheter", 2 => "Renforcer",  3 => "Observer", 4 => "Alléger",  5 => "Vendre"],   // In Ptf
            2 => [ 1 => "Initier", 2 => "Observer",   3 => "Attendre" ]  // Out
        ];

        return $tab_libelle[$this->sc->isInPtf($this->symbol) ? 1 : 2][$avis];
    }

    public function getColorAlert($sens) {
        $colr = [ 1 => "green", -1 => "red", 0 => "orange", 2 => "green"];
        return $colr[$sens];
    }
    
    public function getIconAlert($sens) {
        $colr = [ 1 => "arrow up", -1 => "red", 0 => "bell", 2 => "trophy"];
        return $colr[$sens];
    }
    
    public function pourcentagevariation($vi, $vf) {
        return $vi == 0 ? 0 : (($vf / $vi) - 1) * 100;
    }
    
    public function depassementJournalierALaHausse($vi, $vf, $s) {
        $ret = false;
    
        if ($vi < $s && $vf > $s) $ret = true;
    
        return $ret;
    }
    
    public function depassementJournalierALaBaisse($vi, $vf, $s) {
        $ret = false;
    
        if ($vi > $s && $vf < $s) $ret = true;
    
        return $ret;
    }
    
    public function hausseJournaliere($seuil) {
        
        $ret = [];

        $price    = $this->getPrice();
        $previous = $this->getQuoteAttr('previous');

        if ($this->pourcentagevariation($previous, $price) >= $seuil)
            $ret[] = [ 1, $seuil.'%',  $previous ];

        return $ret;
    }

    public function performancePRU($seuil) {
        
        $ret = [];

        $price    = $this->getPrice();
        $pru      = $this->getPru();
        $stoploss = $this->getStoploss();

        if ($pru > 0 && $this->pourcentagevariation($pru, $price) >= $seuil) {
            $ret[] = [ 1, 'PRU+'.$seuil.'%',  $price ];
            if ($stoploss == 0)
                $ret[] = [ 0, 'NO_STOPLOSS',  $pru ];
            }
    
        return $ret;
    }

    public function depassementALaBaissePRU() {
        
        $ret = [];

        $price    = $this->getPrice();
        $previous = $this->getQuoteAttr('previous');
        $pru      = $this->getPru();

        if ($pru > 0 && $this->depassementJournalierALaBaisse($previous, $price, $pru)) {
            $ret[] = [ -1, 'PRU',  $price ];
        }
    
        return $ret;
    }

    public function depassementJournalier($type) {

        $ret = [];

        $price    = $this->getPrice();
        $previous = $this->getQuoteAttr('previous');
        $stoploss = $this->getStopLoss();
        $seuil    = 0;

        if ($type == "objectif")   $seuil = $this->getObjectif();
        if ($type == "stoploss")   $seuil = $this->getStopLoss();
        if ($type == "stopprofit") $seuil = $this->getStopProfit();

        if ($seuil > 0) {

            if ($this->depassementJournalierALaHausse($previous, $price, $seuil)) {
                $ret[] = [ 1, $type,  $seuil ];
                if ($stoploss == 0)
                    $ret[] = [ 0, 'NO_STOPLOSS',  $seuil ];
            }
            if ($this->depassementJournalierALaBaisse($previous, $price, $seuil))
                $ret[] = [ -1, $type,  $seuil ];
        }

        return $ret;
    }

    public function depassementJournalierSeuils() {
        
        $ret = [];

        $price    = $this->getPrice();
        $previous = $this->getQuoteAttr('previous');
        $seuils   = explode(';', $this->getSeuils());
        
        // Parcours du cours de chaque seuil
        foreach($seuils as $i => $v) {
            $tmp = explode('|', $v);
            $inverse = isset($tmp[1]) ? $tmp[1] : 1;
            if ($this->depassementJournalierALaHausse($previous, $price, $tmp[0])) $ret[] = [ $inverse == 1 ? 1 : -1, 'seuil', $tmp[0] ];
            if ($this->depassementJournalierALaBaisse($previous, $price, $tmp[0])) $ret[] = [ $inverse == 1 ? -1 : 1, 'seuil', $tmp[0] ];
        }

        return $ret;
    }

    public function depassementJournalierMM() {
        
        $ret = [];

        $price    = $this->getPrice();
        $previous = $this->getQuoteAttr('previous');
        $options  = $this->getOptions();
        
        $tab_mm = [];
        if (($options & 16) == 16) $tab_mm[] = 200;
        if (($options & 8)  == 8)  $tab_mm[] = 100;
        if (($options & 4)  == 4)  $tab_mm[] = 50;
        if (($options & 2)  == 2)  $tab_mm[] = 20;
        if (($options & 1)  == 1)  $tab_mm[] = 7;

        foreach ($tab_mm as $i => $mm) {
            $val_mm = $this->getQuoteAttr('MM'.$mm);
            if ($this->depassementJournalierALaHausse($previous, $price, $val_mm)) $ret[] = [ 1, 'MM'.$mm,  $val_mm ];
            if ($this->depassementJournalierALaBaisse($previous, $price, $val_mm)) $ret[] = [ 2, 'MM'.$mm,  $val_mm ];
        }
    
        return $ret;
    }

    public static function getHtmlTableHeader() {

        $ret = '';

        $ret .= '
            <tr>
                <th class="center aligned"></th>
                <th class="center aligned">Actif</th>
                <th class="center aligned">PRU<br />Qté</th>
                <th class="center aligned">Cotation<br />%</th>
                <th class="center aligned">MM200<br />%</th>
                <th class="center aligned" data-sortable="false">Alertes</th>
                <th class="center aligned">DM</th>
                <th class="center aligned">Tendance</th>
                <th class="center aligned">Valorisation<br />Poids</th>
                <th class="center aligned">Perf</th>
                <th class="center aligned">Rendement<br /><small>PRU/Cours</small></th>
                <th class="center aligned">Avis</th>
            </tr>
        ';

        return $ret;
    }

    public function getHtmlTableLine($i) { 
    
        global $sess_context;

        $ret = "";

        $currency  = $this->getCurrency();                   // Choix de la devise
        $taux      = $this->getTaux();                       // Taux conversion devise
        $taux_change_moyen = $this->getTauxChangeMoyen();    // Taux change moyen
        $dividende = $this->getDividendeAnnuel();            // Dividende annualise s'il existe
        $price     = $this->getPrice();                      // Prix de l'actif
        $pct       = $this->getPct();
        $pname     = '<button class="tiny ui primary button">'.$this->getPName().'</button>';
        $isAlerteActive = $this->isAlerteActive();
        $isWatchlist = $this->isWatchlist();
        $stop_loss   = $this->getStopLoss();
        $stop_profit = $this->getStopProfit();
        $objectif    = $this->getObjectif();
        $seuils      = $this->getSeuils();
        $strat_type  = $this->getStrategieType();
        $reg_type    = $this->getRegressionType();
        $reg_period  = $this->getRegressionPeriod();
        $options     = $this->getOptions();
        $perf_indicator = $this->getPerfIndicator();
        $perf_bullet    = "<span data-tootik-conf=\"left multiline\" data-tootik=\"".uimx::$perf_indicator_libs[$perf_indicator]."\"><a class=\"ui empty ".uimx::$perf_indicator_colrs[$perf_indicator]." circular label\"></a></span>";
        $mm200        = $this->getMM200();
        $dm           = $this->getDM();
        $pct_mm200    = $this->getPctMM200();
        $tags         = $this->getTags();
        $tags_infos   = uimx::getIconTooltipTag($tags);
        $other_name   = $this->getOtherName();
        $position_nb  = $this->getNbPositions();
        $position_pru = $this->getPru();
        $type         = $this->getType();
        $isInPtf      = $this->sc->isInPtf($this->symbol);
        $sum_valo_in_euro = $this->getSumValoInEuro();
        $avis         = $this->getScoreAvis($this->getAvis());
        $avis_lib     = $this->getLabelAvis($avis);
        $avis_bg_colr = $this->getBGColorAvis($avis);

//        echo $this->symbol; var_dump($avis);

        $ret .= '<tr id="tr_item_'.$i.'" data-tags="'.tools::UTF8_encoding($tags).'" data-in-ptf="'.($isInPtf ? 1 : 0).'" data-pname="'.$this->symbol.'" data-other="'.($other_name ? 1 : 0).'" data-taux-moyen="'.$taux_change_moyen.'" data-taux="'.$taux.'" data-sum-valo-in-euro="'.$sum_valo_in_euro.'" data-iuc="'.($sess_context->isUserConnected() ? 1 : 0).'" class="'.strtolower($type).'">
            <td data-geo="'.$tags_infos['geo'].'" data-value="'.$tags_infos['icon_tag'].'" data-tootik-conf="right" data-tootik="'.$tags_infos['tooltip'].'" class="center align collapsing">
                <i data-secteur="'.$tags_infos['icon_tag'].'" class="inverted grey '.$tags_infos['icon'].' icon"></i>
            </td>

            <td class="center aligned" id="f_actif_'.$i.'" data-tootik-conf="right" data-tootik="'.tools::UTF8_encoding($this->getName()).'" data-pname="'.$this->symbol.'">'.QuoteComputing::getQuoteNameWithoutExtension($pname).'</td>

            <td class="center aligned" id="f_pru_'.$i.'" data-nb="'.$position_nb.'" data-pru="'.sprintf("%.2f", $position_pru).'" data-value="'.sprintf("%.2f", $position_pru * $position_nb).'"><div>
                <button class="tiny ui button">'.sprintf("%.2f%s", $position_pru, uimx::getCurrencySign($currency)).'</button>
                <label>'.$position_nb.'</label>
            </div></td>

            <td class="center aligned" data-value="'.$pct.'"><div>
                <button id="f_price_'.$i.'" data-value="'.sprintf("%.2f", $price).'" data-name="'.$this->symbol.'" data-pru="'.($this->isPriceFromPru() ? 1 : 0).'" class="tiny ui button">'.sprintf("%.2f%s", $price, $type == "INDICE" ? "" : uimx::getCurrencySign($currency)).'</button>
                <label id="f_pct_jour_'.$i.'" class="'.($pct >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $pct).'%</label>
            </div></td>
        
            <td class="center aligned" data-value="'.$pct_mm200.'"><div>
                <button class="tiny ui button" style="background: '.uimx::getRedGreenColr($mm200, $price).'">'.sprintf("%.2f%s", $mm200, $type == "INDICE" ? "" : uimx::getCurrencySign($currency)).'</button>
                <label style="color: '.uimx::getRedGreenColr($mm200, $price).'">'.sprintf("%s%.2f", ($pct_mm200 >= 0 ? '+' : ''), $pct_mm200).'%</label>
            </div></td>

            <td class="center aligned" data-watchlist="'.($isWatchlist ? 1 : 0).'" data-active="'.($isAlerteActive ? 1 : 0).'" data-value="'.$price.'" data-seuils="'.sprintf("%s", $seuils).'" data-options="'.$options.'" data-strat-type="'.$strat_type.'" data-reg-type="'.$reg_type.'" data-reg-period="'.$reg_period.'"><div class="small ui right group input" data-pname="'.$this->symbol.'">
                <div class="'.(!$isAlerteActive || intval($stop_loss)   == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_loss).'</div>
                <div class="'.(!$isAlerteActive || intval($objectif)    == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $objectif).'</div>
                <div class="'.(!$isAlerteActive || intval($stop_profit) == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_profit).'</div>
            </div></td>

            <td id="f_dm_'.$i.'"       class="center aligned '.($dm >= 0 ? "aaf-positive" : "aaf-negative").'" data-value="'.$dm.'">'.$dm.'%</td>
            <td id="f_tendance_'.$i.'" class="center aligned" data-value="'.$perf_indicator.'">'.$perf_bullet.'</td>

            <td id="f_valo2_'.$i.'" class="center aligned" data-value="0">
                <button id="f_valo_'.$i.'" class="tiny ui button"></button>
                <label id="f_poids_'.$i.'"></label>
            </td>

            <td id="f_perf_pru_'.$i.'" class="center aligned"></td>
            <td id="f_rand_'.$i.'"     class="center aligned">
                <div>
                    <label>'.($dividende == 0 || !$isInPtf ? "-" : sprintf("%.2f%%", ($dividende * 100) / $position_pru)).'</label>
                    <label>'.($dividende == 0 ? "-" : sprintf("%.2f%%", ($dividende * 100) / $price)).'</label>
                </div>
            </td>
            <td class="center aligned"><div class="ui '.$avis_bg_colr.' horizontal label">'.$avis_lib.'</div></td>
        </tr>';

        return $ret;

    }
}


// ///////////////////////////////////////////////////////////////////////////////////////
class calc {

    public static function prediction_update($user_id) {

        $where_statement = $user_id == "" ? "" : "AND user_id=".$user_id;

        $req9 = "SELECT * FROM prediction WHERE status=0 ".$where_statement;
        $res9 = dbc::execSql($req9);
        
        while($row = mysqli_fetch_array($res9)) {
        
            $date_objectif = date('Y-m-d');
            $date_stoploss = date('Y-m-d');
            $date_gain_max = date('Y-m-d');
            $objectif = 0;
            $stoploss = 0;
            $gain_max = 0;
            $stop_gain_max = 1;
            $previous_price = 0;
        
            $req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$row['symbol']."' AND day >= '".$row['date_avis']."'";
            $res2 = dbc::execSql($req2);
            while($row2 = mysqli_fetch_array($res2)) {

                if (!is_numeric($row2['adjusted_close'])) break;

                if (!is_numeric($row2['open'])) $row2['open'] = $row2['adjusted_close'];
                if (!is_numeric($row2['low']))  $row2['low']  = $row2['adjusted_close'];
                if (!is_numeric($row2['high'])) $row2['high'] = $row2['adjusted_close'];
        
                if ($previous_price == 0) $previous_price = $row2['open'];
        
                // Stoploss atteint
                if ($objectif == 0 && $row['stoploss'] >= $row2['low']) {
                    $stoploss = 1;
                    $date_stoploss = $row2['day'];
                }
        
                // Objectif atteint
                if ($stoploss == 0 && $row2['high'] >= $row['objectif']) {
                    $objectif = 1;
                    $date_objectif = $row2['day'];
                    $stop_gain_max = 0;
                }
        
                // Recherche gain max en cas d'objectif atteint avec stoploss à max(objectif, -5% cloture veille)
                if ($previous_price > 0 && $objectif == 1 && $stop_gain_max == 0) {
        
                    $stoploss_ref = $row['objectif'] * 0.97;
                    if ($row2['adjusted_close'] < $stoploss_ref) {
                        $stop_gain_max = 1;
                        $gain_max = $row['objectif'];
                        $date_gain_max = $row2['day'];
                    } else {
                        if ($row2['adjusted_close'] > max($stoploss_ref, $gain_max)) {
                            $date_gain_max = $row2['day'];
                            $gain_max = $row2['adjusted_close'];
                        }
                    }
        
                }
        
                $previous_price = $row2['adjusted_close'];
        
            }
        
            // Enregistrement stoploss atteint
            if ($stoploss == 1) {
                $update = "UPDATE prediction SET status=-1, date_status='".$date_stoploss."' WHERE id=".$row['id'];
                $res3 = dbc::execSql($update);

                $req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$user_id.", '".$date_stoploss."', '".$row['symbol']."', 0, 0, 'PREDICT_NOK', -1, 'red', 'watch', '".$date_stoploss."') ON DUPLICATE KEY UPDATE sens=-1, couleur='red', icone='watch', seuil='".$row['stoploss']."'";
                $res = dbc::execSql($req);
        }
        
            // Enregistrement objectif atteint
            if ($objectif == 1) {
                $update = "UPDATE prediction SET status=1, date_status='".$date_objectif."' WHERE id=".$row['id'];
                $res3 = dbc::execSql($update);

                $req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$user_id.", '".$date_objectif."', '".$row['symbol']."', 0, 0, 'PREDICT_OK', 1, 'green', 'watch', '".$date_objectif."') ON DUPLICATE KEY UPDATE sens=1, couleur='green', icone='watch', seuil='".$row['objectif']."'";
                $res = dbc::execSql($req);
            }
        
            // Enregistrement max gain (on enregistre meme si gaim_max=0)
            $update = "UPDATE prediction SET gain_max='".$gain_max."', gain_max_date='".$date_gain_max."' WHERE id=".$row['id'];
            $res3 = dbc::execSql($update);
        
            // Prediction cloturée après 6 mois
            $datetime1 = new DateTime($row['date_avis']);
            $datetime2 = new DateTime(date("Y-m-d"));
            $difference = $datetime1->diff($datetime2);
            if ($row['status'] == 0 && $difference->m >= 6) {
                $update = "UPDATE prediction SET status=-2, date_status='".date("Y-m-d")."' WHERE id=".$row['id'];
                $res3 = dbc::execSql($update);

                $req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$user_id.", '".date("Y-m-d")."', '".$row['symbol']."', 0, 0, 'PREDICT_NOK', -1, 'red', 'watch', '".date("Y-m-d")."') ON DUPLICATE KEY UPDATE sens=-1, couleur='red', icone='watch', seuil=''";
                $res = dbc::execSql($req);
            }
        
        }
    }

    public static function getPName($name) {
        // Prise en compte des actifs suivis manuellement
        return substr($name, 0, 5) == "AUTRE" ? substr($name, 6) : $name;

    }
    
    public static function formatDataOrder($val) {

        $val['valo'] = $val['quantity'] * $val['price'] * $val['taux_change'];
        $val['icon'] = $val['action'] >= 0 ? "right green" : "left orange";
        $val['action_lib']   = uimx::$order_actions[$val['action']];
        $val['devise_sign']  = uimx::getCurrencySign($val['devise']);
        $val['action_colr']  = $val['action'] >= 0 ? "aaf-positive" : "aaf-negative";
        $val['price_signed'] = sprintf("%.2f%s", $val['price'], $val['devise_sign']);
        $val['valo_signed']  = sprintf("%s%.2f%s", $val['action'] >= 0 ? '+' : '-', $val['valo'], '&euro;');

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
        $sum_mv         = 0; // Somme des positions en MV

        $portfolio['orders']        = array();
        $portfolio['orders_futur']  = array();
        $portfolio['positions']     = array();

        $portfolio['infos'] = $infos;
        $user_id = $infos['user_id'];

        // Recuperation de tous les actifs
        $quotes = calc::getIndicatorsLastQuote();

        // Reccuperation du cours des devises
        $devises = calc::getGSDevisesWithNoUpdate();

        // Récupération des données de trend_following de l'utilisateur
        $portfolio['trend_following'] = array();
        $req = "SELECT * FROM trend_following t, stocks s WHERE t.user_id=".$user_id." AND t.symbol = s.symbol";
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
        $req = "SELECT  o.*, p.shortname FROM orders o, portfolios p WHERE date <= '".date('Y-m-d')."' AND portfolio_id IN (".($portfolio['infos']['synthese'] == 1 ? $portfolio['infos']['all_ids'] : $infos['id']).") AND o.portfolio_id = p.id ORDER BY date, datetime ASC";
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

            // Ajustement nom produit
            $pname = calc::getPName($row['product_name']);
            $row['product_name'] = $pname;

            // Init compteur ttf
            $row['ttf'] = 0;
            
            // Si ordre non confirme
            if ($row['confirme'] == 0) { $portfolio['orders'][] = $row; continue; }
            
            // Achat/Vente/Dividende en action
            if ($row['action'] == 1 || $row['action'] == -1 || $row['action'] == 6) {

                $achat = $row['action'] >= 0 ? true : false;
                
                if (isset($positions[$pname]['nb'])) {

                    $nb = $positions[$pname]['nb'] + ($row['quantity'] * ($achat ? 1 : -1));
                    
                    // Recalcul si achat mais pas si vente
                    $pru = $achat ? ($positions[$pname]['pru'] * $positions[$pname]['nb'] + $row['quantity'] * $row['price']) / $nb : $positions[$pname]['pru'];
                    $taux_change_moyen = $achat ? ($positions[$pname]['taux_change_moyen'] * $positions[$pname]['nb'] + $row['taux_change'] * $row['quantity']) / $nb : $positions[$pname]['taux_change_moyen'];

                } else {
                    $nb  = $row['quantity'];
                    $pru = $row['price'];
                    $taux_change_moyen = $row['taux_change'];
                }

                // Valorisation operation avec le taux de change le jour de la transaction
                $valo_ope = $row['quantity'] * $row['price'] * $row['taux_change'];

                // Maj cash
                $cash += $valo_ope * ($achat ? -1 : 1); // ajout si vente, retrait achat

                // TTF si actif FR
                if ($row['action'] == 1) {
                    if (isset($quotes['stocks'][$pname]['type']) && $quotes['stocks'][$pname]['type'] == 'Equity' && strstr($pname, ".PAR"))
                        $row['ttf'] = $valo_ope * 0.003;
                    $sum_ttf += $row['ttf'];
                }

                $positions[$pname]['sum_valo_in_euro'] = (isset($positions[$pname]['sum_valo_in_euro']) ? $positions[$pname]['sum_valo_in_euro'] : 0) + ($valo_ope * ($achat ? 1 : -1));
                $positions[$pname]['nb']  = $nb;
                $positions[$pname]['pru'] = $pru;
                $positions[$pname]['taux_change_moyen'] = $taux_change_moyen;
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
   
            // Divende Action
            if ($row['action'] == 6) {
                $sum_dividende += $row['quantity'] * $row['price'];
                $ampplt        += $interval_ref == 0 ? 0 : ($row['quantity'] * $row['price']) * ($interval / $interval_ref);
            }
   
            // Tableau des ordres
            $portfolio['orders'][] = $row;

        }

        // Récupération et TRT des ordres futur
        $req = "SELECT  o.*, p.shortname, 0 AS ttf FROM orders o, portfolios p WHERE date > '".date('Y-m-d')."' AND portfolio_id IN (".($portfolio['infos']['synthese'] == 1 ? $portfolio['infos']['all_ids'] : $infos['id']).") AND o.portfolio_id = p.id ORDER BY date, datetime ASC";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res))
            $portfolio['orders_futur'][] = $row;
        
        // On retire des positions les actifs dont le nb = 0 (plus dans le portefeuille)
        foreach($positions as $key => $val) {
            if ($val['nb'] == 0)
                unset($positions[$key]);
            else {
                $taux_du_jour = calc::getCurrencyRate($val['devise']."EUR", $devises);
                $last_price = isset($quotes['stocks'][$key]) ? $quotes['stocks'][$key]['price'] : $val['pru'];
                // On applique le dernier taux connu
                $current_valo = $val['nb'] * $last_price * $taux_du_jour;
                // Cumul des positions totales
                $valo_ptf += $current_valo;
                // Cumul des positions en MV de plus de 20%
                $sum_mv += $last_price < ($val['pru'] * 0.8) ? $current_valo : 0;
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
        $portfolio['mvvr']       = ($sum_mv / $portfolio['valo_ptf']) * 100; // Risque exposition (poids des lignes en MV vs valo ptf - <10 expo faible, entre 10 et 30 modéré, >30 fort)
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
            $ref_PCT = intval($c['open']) == 0 ? 0 : (intval($c['close']) - intval($c['open'])) * 100 / intval($c['open']);
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

            if (!isset($data[$ref_DD1M]['day']) || !isset($data[$ref_DD3M]['day']) || !isset($data[$ref_DD6M]['day'])) { $k--; continue; }; 

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

    public static function resetCacheUserPortfolio($user_id) {

        $file_cache = 'cache/TMP_AGGREGATE_USER_PTF_'.$user_id.'_.json';

        if (file_exists($file_cache)) unlink($file_cache);

    }

    public static function getAggregatePortfoliosByUser($user_id) {

        $file_cache = 'cache/TMP_AGGREGATE_USER_PTF_'.$user_id.'_.json';

        $ret = array();

        if (tools::isLocalHost() || cacheData::refreshCache($file_cache, 600)) {

            // Recuperation de tous les actifs
            $quotes = calc::getIndicatorsLastQuote();

            // Aggregation de tous les portefeuilles de l'utilisateur connecte
            $ret = calc::aggregatePortfolioByUser($user_id, $quotes);

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

        if (cacheData::refreshCache($file_cache, 300)) { // Cache de 5 min

            $req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol=q.symbol LEFT JOIN indicators i1 ON s.symbol=i1.symbol WHERE (i1.symbol, i1.day, i1.period) IN (SELECT i2.symbol, max(i2.day), i2.period FROM indicators i2 WHERE i2.period='DAILY' GROUP BY i2.symbol) GROUP BY s.name ORDER BY s.name ASC";
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

    public static function removeDataSymbol($symbol, $tables) {

        foreach($tables as $key) {
            $req = "DELETE FROM ".$key." WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req); 
        }

        cacheData::deleteCacheSymbol($symbol);

    }

    public static function removeTimeSeriesAndIndicatorsSymbol($symbol) {
        calc::removeDataSymbol($symbol, ['daily_time_series_adjusted', 'weekly_time_series_adjusted', 'monthly_time_series_adjusted', 'indicators']);
    }

    public static function removeSymbol($symbol) {

        calc::removeDataSymbol($symbol, ['stocks', 'quotes']);
        calc::removeTimeSeriesAndIndicatorsSymbol($symbol);

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

    public static function aggregateDailyInWeeklyAndMonthly($data) {

        $ret = array();
    
        foreach($data as $key => $val) {
    
            $day = $val['Date'];
    
            $week  = date("w", strtotime($day));
            $week_start = date('Y-m-d', strtotime('-'.($week-1).' days', strtotime($day)));
            $week_end = date('Y-m-d', strtotime('+'.(5-$week).' days', strtotime($day)));	
            $month = date("Y-m-t", strtotime($day));
    
            // Les cotations sont dans l'ordre chronologique
            
            if (!isset($ret['weekly'][$week_end])) {
                $ret['weekly'][$week_end]['Date']   = $week_end;
                $ret['weekly'][$week_end]['Open']   = $val['Open'];
                $ret['weekly'][$week_end]['Close']  = $val['Close'];
                $ret['weekly'][$week_end]['High']   = $val['High'];
                $ret['weekly'][$week_end]['Low']    = $val['Low'];
                $ret['weekly'][$week_end]['Volume'] = $val['Volume'];
                $ret['weekly'][$week_end]['day']    = $week_end;     // Pour le calcul des indicateurs plus tard
                $ret['weekly'][$week_end]['open']   = $val['Open']; // Pour le calcul des indicateurs plus tard
                $ret['weekly'][$week_end]['adjusted_close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
                $ret['weekly'][$week_end]['close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
            } else {
                $ret['weekly'][$week_end]['Close']  = $val['Close'];
                $ret['weekly'][$week_end]['High']   = max($ret['weekly'][$week_end]['High'], $val['High']);
                $ret['weekly'][$week_end]['Low']    = min($ret['weekly'][$week_end]['Low'], $val['Low']);
                $ret['weekly'][$week_end]['Volume'] += $val['Volume'];
                $ret['weekly'][$week_end]['open']  = $val['Open']; // Pour le calcul des indicateurs plus tard
                $ret['weekly'][$week_end]['adjusted_close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
                $ret['weekly'][$week_end]['close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
            }
    
            if (!isset($ret['monthly'][$month])) {
                $ret['monthly'][$month]['Date']   = $month;
                $ret['monthly'][$month]['Open']   = $val['Open'];
                $ret['monthly'][$month]['Close']  = $val['Close'];
                $ret['monthly'][$month]['High']   = $val['High'];
                $ret['monthly'][$month]['Low']    = $val['Low'];
                $ret['monthly'][$month]['Volume'] = $val['Volume'];
                $ret['monthly'][$month]['day']    = $month;        // Pour le calcul des indicateurs plus tard
                $ret['monthly'][$month]['open']  = $val['Open']; // Pour le calcul des indicateurs plus tard
                $ret['monthly'][$month]['adjusted_close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
                $ret['monthly'][$month]['close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
            } else {
                $ret['monthly'][$month]['Close']  = $val['Close'];
                $ret['monthly'][$month]['High']   = max($ret['monthly'][$month]['High'], $val['High']);
                $ret['monthly'][$month]['Low']    = min($ret['monthly'][$month]['Low'], $val['Low']);
                $ret['monthly'][$month]['Volume'] += $val['Volume'];
                $ret['monthly'][$month]['close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
                $ret['monthly'][$month]['adjusted_close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
                $ret['monthly'][$month]['close']  = $val['Close']; // Pour le calcul des indicateurs plus tard
            }
    
        }
    
        return array($ret['weekly'], $ret['monthly']);
    
    }

    public static function getAllDataStockFromGS($symbol, $gf_symbol, $type) {

        // Init de l'object Stock recherché
        $stock = [];
        $stock['gf_symbol']   = $gf_symbol;
        $stock['symbol']      = $symbol;
        $stock['type']        = $type;
        $stock['region']      = "Europe";
        $stock['engine']      = "google";
        $stock['marketopen']  = "09:00";
        $stock['marketclose'] = "17:30";
        $stock['timezone']    = "UTC+01";
        $stock['daily']       = [];
        $stock['status']      = false;

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
            if ($ret[1][0] == "#N/A") return $stock;

            // Creation de l'objet stock avec les valeurs recuperees
            foreach(range(0, 20) as $i) $stock[$ret[0][$i]] = $ret[0][$i] != "name" ? str_replace(',', '.', $ret[1][$i]) : $ret[1][$i];
            
            // Ajustement de certaines data par rapport à la devise
            if ($stock['currency'] == "USD") {
                $stock['region']      = "US";
                $stock['marketopen']  = "9:30";
                $stock['marketclose'] = "16:00";
                $stock['timezone']    = "UTC-04";
            }
    
            // Recuperation historique cotation actif en daily
            $ret = getGoogleSheetStockData("C3:H".($nb+2), "daily");

            $col_names = [];
            foreach($ret as $key => $val) {

                if ($key == 0) {

                    // Recuperation des noms de colonnes
                    foreach(range(0, 5) as $i) $col_names[$i] = $ret[$key][$i];

                } else {

                    $daily_data = [];

                    // Récupération des données journalières + transformation des ',' en '.' pour les chiffres
                    foreach(range(0, 5) as $i) $daily_data[$col_names[$i]] = str_replace(',', '.', $ret[$key][$i]);

                    // Reformatage de la date
                    $daily_data['Date']  = substr($daily_data['Date'], 6, 4)."-".substr($daily_data['Date'], 3, 2)."-".substr($daily_data['Date'], 0, 2);
                    $daily_data['day']   = $daily_data['Date'];  // Pour le calcul des indicateurs plus tard
                    $daily_data['open'] = $daily_data['Open']; // Pour le calcul des indicateurs plus tard
                    $daily_data['adjusted_close'] = $daily_data['Close']; // Pour le calcul des indicateurs plus tard
                    $daily_data['close'] = $daily_data['Close']; // Pour le calcul des indicateurs plus tard
                    
                    // Mise en tableau des données journalières
                    $stock['daily'][] = $daily_data;
                        
                }

            }

            $stock['status'] = true;
        }

        return $stock;

    }
    
    public static function insertOrUpdateDataQuoteFromGS($data) {

        // RAS data daily/weekly/monthly/indicators
        calc::removeTimeSeriesAndIndicatorsSymbol($data['symbol']);

        // Maj data stock
        $req = "INSERT INTO stocks (symbol, gf_symbol, name, type, region, marketopen, marketclose, timezone, currency, engine, pe, eps, beta, shares, marketcap) VALUES ('".$data['symbol']."', '".$data['gf_symbol']."', '".addslashes($data['name'])."', '".$data['type']."', '".$data['region']."', '".$data['marketopen']."', '".$data['marketclose']."', '".$data['timezone']."', '".$data['currency']."', '".$data['engine']."', ".$data['pe'].", ".$data['eps'].", ".$data['beta'].", ".$data['shares'].", ".$data['marketcap'].") AS NEW ON DUPLICATE KEY UPDATE gf_symbol=new.gf_symbol, name=new.name, type=new.type, region=new.region, marketopen=new.marketopen, marketclose=new.marketclose, timezone=new.timezone, currency=new.currency, engine=new.engine, pe=new.pe, eps=new.eps, beta=new.beta, shares=new.shares, marketcap=new.marketcap";
        $res = dbc::execSql($req);

        // Maj quote quotes
        $req = "INSERT INTO quotes (symbol, open, high, low, price, volume, day, previous, day_change, percent) VALUES ('".$data['symbol']."','".$data['priceopen']."', '".$data['high']."', '".$data['low']."', '".$data['price']."', '".$data['volume']."', '".substr($data['tradetime'], 6, 4)."-".substr($data['tradetime'], 3, 2)."-".substr($data['tradetime'], 0, 2)."', '".$data['closeyest']."', '".$data['change']."', '".$data['changepct']."') AS NEW ON DUPLICATE KEY UPDATE open=new.open, high=new.high, low=new.low, price=new.price, volume=new.volume, day=new.day, previous=new.previous, day_change=new.day_change, percent=new.percent";
        $res = dbc::execSql($req);

        // Insert today quotation in daily
        $req = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$data['symbol']."','".substr($data['tradetime'], 6, 4)."-".substr($data['tradetime'], 3, 2)."-".substr($data['tradetime'], 0, 2)."', '".$data['priceopen']."', '".$data['high']."', '".$data['low']."', '".$data['price']."', '".$data['price']."', '".$data['volume']."', '0', '0') AS NEW ON DUPLICATE KEY UPDATE open=new.open, high=new.high, low=new.low, close=new.close, adjusted_close=new.adjusted_close, volume=new.volume, dividend=new.dividend, split_coef=new.split_coef";
        $res = dbc::execSql($req);

        // Insert quotations and build indicators
        $time_series_tables = [ "daily" => "daily_time_series_adjusted", "weekly" => "weekly_time_series_adjusted", "monthly" => "monthly_time_series_adjusted" ];
        foreach($time_series_tables as $serie => $table) {
            foreach($data[$serie] as $key => $val) {
                $req = "INSERT INTO ".$table." (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$data['symbol']."','".$val['Date']."', '".$val['Open']."', '".$val['High']."', '".$val['Low']."', '".$val['Close']."', '".$val['Close']."', '".$val['Volume']."', '0', '0') AS NEW ON DUPLICATE KEY UPDATE open=new.open, high=new.high, low=new.low, close=new.close, adjusted_close=new.adjusted_close, volume=new.volume, dividend=new.dividend, split_coef=new.split_coef";
                $res = dbc::execSql($req);
            }
            computeIndicatorsAndInsertIntoBD($data['symbol'], $data[$serie], $serie, 0);
        }

    }

    public static function getAndInsertAllDataQuoteFromGS($symbol, $type) {

        $ret = false;

        // Fichier cache
        $filename = 'cache/GS_QUOTE_'.$symbol.'.json';

        // Récupération des data (information + daily) depuis GS
        $data = cacheData::getAllDataStockFromGS($symbol, $symbol, $type);

        if (count($data['daily'])) {

            // Calcul des data weekly/Monthly avec les daily
            list($data['weekly'], $data['monthly']) = cacheData::aggregateDailyInWeeklyAndMonthly($data['daily']);

            // Ecriture en cache des data
            cacheData::writeCacheData($filename, $data);

            // Ecriture en BD des data
            cacheData::insertOrUpdateDataQuoteFromGS($data);

            // Reset des caches TMP pour recalcul
            cacheData::deleteTMPFiles();

            $ret = true;
        }

        return $ret;

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
// Ce n'est plus premium 
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

    public static function buildCachesSymbol($symbol, $full, $options) {
        
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
        1  => [ "tag" => "Energie",             "icon" => "fire", "desc" => "Constitué d'entreprises exerçant des activités d'exploration, de production, de commercialisation, de raffinage et de transport des produits du pétrole et du gaz. Ce secteur comprend aussi des sociétés qui prennent part au domaine des services liés à l'énergie." ],
        2  => [ "tag" => "Matériaux",           "icon" => "wrench", "desc" => "Se compose d'entreprises appartenant à une vaste gamme d'industries manufacturières et minières axées sur les produits de base, notamment les métaux, les minéraux, les produits chimiques, les matériaux de construction, le verre, le papier et les produits forestiers." ],
        3  => [ "tag" => "Industrie",           "icon" => "industry", "desc" => "Comprend des entreprises dont le domaine d'activité principal est l'aérospatiale et la défense, la construction, l'outillage de précision et les produits de bâtiment ou encore les services de transport, y compris les lignes aériennes, les chemins de fer et les infrastructures de transport." ],
        4  => [ "tag" => "Consommation discrétionnaire", "icon" => "shipping fast", "desc" => "Comprend des entreprises dans les domaines suivants : transport routier, objets ménagers et biens durables, textiles, habillement et équipements de loisirs. Les restaurants, les hôtels, les établissements de loisirs, les services médiatiques et la vente au détail relèvent du segment des services." ],
        5  => [ "tag" => "Telecommunication",   "icon" => "tty", "desc" => "Se compose d'entreprises qui offrent des services de communication principalement au moyen de lignes téléphoniques fixes ou de réseaux cellulaires, sans fil, à large bande passante et de câble à fibres optiques." ],
        6  => [ "tag" => "Technologies",        "icon" => "microchip", "desc" => "Constitué d'entreprises appartenant aux trois domaines généraux suivants : technologie, logiciels et services." ],
        7  => [ "tag" => "Biens de consommation de base", "icon" => "shopping cart", "desc" => "Se compose de fabricants et de distributeurs de denrées alimentaires, de boissons et de tabac, de même que de producteurs d'objets ménagers non durables et de produits personnels. Il comprend également les détaillants d'aliments et de médicaments ainsi que les centres commerciaux." ],
        8  => [ "tag" => "Services publics",    "icon" => "universal access", "desc" => "Se compose de sociétés gazières, d'électricité et de services d'eau, ainsi que d'entreprises qui agissent à titre de producteurs ou de distributeurs d'énergie." ],
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
        12 => [ "tag" => "Asie",             "desc" => "" ]
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
        3  => [ "tag" => "Matière première",  "desc" => "" ],
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
         6 => "Dividende Action",
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
    public static $conseillers = [
        0 => 'MOI', 1 => 'BourseDirect', 2 => 'CHERON', 3 => 'GAVE', 4 => 'ILT', 5 => 'KPI', 6 => 'KOUBAR', 7 => 'PAVEL', 8 => 'TKL', 9 => 'ZoneBourse', 10 => 'Hiboo'
    ];

    // Conseillers to tags
    public static $tags_conseillers = [];

    public static function getCurrencySign($cur) {
        $ret = "&euro;";
        if ($cur == "USD") $ret = "$";

        return $ret;
    }

    public static function getGraphCurrencySign($cur) {
        $ret = "\u20ac";
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

        $ret = array("icon" => "", "icon_tag" => "N/A", "tooltip" => "", "geo" => "");

        if (!$tags) return $ret;

        $tab_tags = array_flip(explode("|", tools::UTF8_encoding($tags)));

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
                    $desc .= '<tr '.($x == 0 ? 'style="background: green"' : '').'><td>'.QuoteComputing::getQuoteNameWithoutExtension($key).'</td><td>'.sprintf("%.2f", $val).'%</td></tr>';
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

        $title = tools::UTF8_encoding($strategie['title']).($sess_context->isUserConnected() ? "<i id=\"home_strategie_".$strategie['id']."_bt\" class=\"ui inverted right floated black small ".($user_id == $strategie['user_id'] ? "settings" : "copy")." icon\"></i>" : "");
        uimx::genCard("home_card_".$strategie['id'], $title, $day, $desc, "perf_card");
    }

    public static function portfolioCard($portfolio, $portfolio_data, $daily_perf) {

        global $sess_context;

        $desc  = '
        <div id="portfolio_dashboard_'.$portfolio['id'].'_bt" class="ui labeled button" tabindex="0">
            <div class="ui '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).'  button">
                <i class="chart pie inverted icon"></i>'.sprintf("%.2f &euro;", $portfolio_data['valo_ptf']).'
            </div>
            <a class="ui basic '.($portfolio_data['perf_ptf'] >= 0 ? 'green' : 'red' ).' left pointing label">'.sprintf("%.2f ", $portfolio_data['perf_ptf']).'%</a>
        </div>
        <div class="zone_bts ui buttons">
            <button id="portfolio_graph_'.$portfolio['id'].'_bt" class="ui icon very small right floated grey button"><i class="inverted white chart bar outline icon"></i></button>
            <button id="ptf_balance_'.$portfolio['id'].'_bt" class="ui icon very small right floated lightgrey button"><i class="inverted black balance icon"></i></button>
        </div>
        ';

        $title = tools::UTF8_encoding($portfolio['name']).($sess_context->isUserConnected() ? "<i id=\"portfolio_edit_".$portfolio['id']."_bt\" class=\"ui inverted right floated black small settings icon\"></i>" : "");
        uimx::genCard("portfolio_card_".$portfolio['id'], "<button class=\"right floating tiny ui ".($daily_perf >=0 ? "green" : "red")." label\">".(($daily_perf >= 0 ? "+" : "").sprintf("%.2f", $daily_perf))."%</button>".$title, date('Y-m-d'), $desc);
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

    public static function redirectLoginPage($item) { ?>
        <script>go({ action: 'login', id: 'main', url: 'login.php?redirect=1&goto=<?= $item ?>', menu: 'm1_<?= $item ?>_bt' });</script><?
	    exit(0);
    }  

}

// Permet de rajouter des items n'importe ou dans la liste
asort(uimx::$invest_secteur);
asort(uimx::$invest_zone_geo);
asort(uimx::$invest_classe);
asort(uimx::$invest_factorielle);
asort(uimx::$conseillers);
foreach(uimx::$conseillers as $key => $val) uimx::$tags_conseillers[] = [ "tag" => $val, "desc" => "" ]; 

?>