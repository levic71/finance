<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

// Alerte (id, date YYY-MM-JJ, user_id, actif, mail, lue, type, sens, couleur, icone, libelle, seuil)

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

$positions       = [];
$trend_following = [];

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='vmlf71@gmail.com'";
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$user_id = $row['id'];

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$aggregate_ptf   = calc::getAggregatePortfoliosByUser($user_id);
$positions       = $aggregate_ptf['positions'];
$trend_following = $aggregate_ptf['trend_following'];

// Rajouter à 1 à l'indice !!!
// -1 => Shortname, 0 => Symbol, 1 => Name, 2 => Alertes, 3 => Price, 4 => Priceopen, 5 => High, 6 => Low, 7 => Volume, 8 => Datadelay, 9 => Closeyest, 10 => Change, 11 => Changepct, 12 => ytd, 13 => 1 week, 14 => 1 month, 15 => 1 year, 16 => 3 years, 17 => mm7, 18 => mm20, 19 => mm50, 20 => mm100, 21 => mm200, 22 => j-1
// $gsah = updateGoogleSheetAlertesHeader();
// var_dump($gsah);

// 0 - Liste des actifs en portefeuille ou en surveillance
// 1 - Liste des alertes de franchissemement des alertes et mm200 à hausse et à la baisse
// 2 - Liste des actifs cours proches de la MM200 (2%)
// 3 - Baisse ou hausse >= 5% en intraday/weekly/monthly
// 4 - Si cours dépasse objectif et pas de stoploss ou stoploss trop bas (5% ?)
// 4 - Si cours dépasse stop profit et pas de stoploss ou stoploss trop bas (5% ?)
// 5 - Si croissement H/B cours/MM200


/* $data2

array (size=4)
  'stocks' => 
    array (size=16)
      'BNP.PAR' => 
        array (size=43)
          'id' => string '147' (length=3)
          'name' => string 'BNP Paribas SA' (length=14)
          'symbol' => string 'BNP.PAR' (length=7)
          'currency' => string 'EUR' (length=3)
          'type' => string 'Equity' (length=6)
          'region' => string 'Paris' (length=5)
          'marketopen' => string '09:00' (length=5)
          'marketclose' => string '17:30' (length=5)
          'timezone' => string 'UTC+01' (length=6)
          'pea' => string '0' (length=1)
          'gf_symbol' => string '' (length=0)
          'ISIN' => string '' (length=0)
          'provider' => string '' (length=0)
          'categorie' => string '' (length=0)
          'frais' => string '0' (length=1)
          'distribution' => string '0' (length=1)
          'actifs' => string '0' (length=1)
          'links' => string '{"link1":"","link2":""}' (length=23)
          'tags' => string 'Actions|Industrie|Services financiers|MarchÃ© dÃ©veloppÃ©|Europe|Value|Large Cap|' (length=81)
          'rating' => string '6' (length=1)
          'dividende_annualise' => string '3.67' (length=4)
          'date_dividende' => string '2022-06-02' (length=10)
          'open' => string '50.4200' (length=7)
          'high' => string '51.2600' (length=7)
          'low' => string '49.8500' (length=7)
          'price' => string '49.8500' (length=7)
          'volume' => string '2879032' (length=7)
          'day' => string '2022-05-09' (length=10)
          'previous' => string '50.5900' (length=7)
          'day_change' => string '-0.7400' (length=7)
          'percent' => string '-1.4627%' (length=8)
          'period' => string 'DAILY' (length=5)
          'DM' => string '-4.76' (length=5)
          'DMD1' => string '2022-04-29' (length=10)
          'DMD2' => string '2022-02-28' (length=10)
          'DMD3' => string '2021-11-30' (length=10)
          'MM7' => string '50.282857142857' (length=15)
          'MM20' => string '50.0655' (length=7)
          'MM50' => string '50.5656' (length=7)
          'MM100' => string '56.5983' (length=7)
          'MM200' => string '56.22625' (length=8)
          'RSI14' => string '46.497137366824' (length=15)
          'Bollinger' => string '' (length=0) */

/* 'positions' => 
array (size=9)
  'GLE.PAR' => 
	array (size=4)
	  'nb' => int 1000
	  'pru' => float 13.6625
	  'other_name' => boolean false
	  'devise' => string 'EUR' (length=3)
  'BRE.PAR' => 
  
'trend_following' => 
    array (size=4)
      'BRE.PAR' => 
        array (size=6)
          'user_id' => string '4' (length=1)
          'symbol' => string 'BRE.PAR' (length=7)
          'stop_loss' => string '0.000000' (length=8)
          'stop_profit' => string '200.000000' (length=10)
          'objectif' => string '100.000000' (length=10)
          'manual_price' => string '0' (length=1)
      'ESE.PAR' => 
 */

$gsa = calc::getGSAlertes();

/* echo "Liste des actifs en surveillance : ";
foreach($gsa as $key => $val) echo $val[0].", ";

echo "<br />Liste des actifs en portefeuille : ";
foreach($positions as $key => $val) echo $key.", ";
 */

function depassementALaHausse($vi, $vf, $s) {
	$ret = false;

	if ($vi < $s && $vf > $s) $ret = true;

	return $ret;
}

function depassementALaBaisse($vi, $vf, $s) {
	$ret = false;

	if ($vi > $s && $vf < $s) $ret = true;

	return $ret;
}

$indicateurs_a_suivre = [ 'INDEXEURO:PX1', 'INDEXSP:.INX', 'INDEXDJX:.DJI', 'INDEXNASDAQ:.IXIC', 'INDEXRUSSELL:RUT', 'INDEXCBOE:VIX' ];

// Init à 0 des valeurs des indicateurs dont les donnees pas recupérées
foreach($indicateurs_a_suivre as $key => $val) {
	if (!isset($gsa[$val]))
		$gsa[$val] = array($val,"-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-","-");
}

// Tableau des notifs
$notifs = [];

// Alerte sur les actifs surveilles ?
foreach($gsa as $key => $val) {

	// Depassement des alertes si elles ont ete positionnees
	if (isset($val[3])) {

//				if ($val[0] == "VIX") echo $val[0].":".$val[10].":".$val[4];

		$seuils = explode(';', $val[3]);

		// Parcours du cours de chaque seuil ?
		foreach($seuils as $i => $v) {

			if (depassementALaHausse($val[10], $val[4], $v))
				$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'cours', 'sens' => 1, 'seuil' => $v, 'colr' => 'green', 'icon' => 'arrow up' ];
			if (depassementALaBaisse($val[10], $val[4], $v))
				$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'cours', 'sens' => -1, 'seuil' => $v, 'colr' => 'red', 'icon' => 'arrow down' ];

		}

		// Depassement MM200
		if (depassementALaHausse($val[10], $val[4], $val[22]))
			$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'mm200', 'sens' => 1, 'seuil' => 'MM200', 'colr' => 'green', 'icon' => 'arrow up' ];	
		if (depassementALaBaisse($val[10], $val[4], $val[22]))
			$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'mm200', 'sens' => -1, 'seuil' => 'MM200', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM20
		if (depassementALaHausse($val[10], $val[4], $val[19]))
			$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'mm20', 'sens' => 1, 'seuil' => 'MM20', 'colr' => 'green', 'icon' => 'arrow up' ];	
		if (depassementALaBaisse($val[10], $val[4], $val[19]))
			$notifs[] =  [ 'user_id' => 0, 'actif' => $val[0], 'type' => 'mm20', 'sens' => -1, 'seuil' => 'MM20', 'colr' => 'red', 'icon' => 'arrow down' ];
	}
}

// Alerte sur actifs en portefeuille
foreach($positions as $key => $val) {

	if (isset($data2["stocks"][$key])) {

		$d = $data2["stocks"][$key];

		// Depassement PRU
		if (depassementALaHausse($d['previous'], $d['price'], $val['pru']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'pru', 'sens' => 1, 'seuil' => 'PRU', 'colr' => 'green', 'icon' => 'arrow up' ];
		if (depassementALaBaisse($d[('previous')], $d['price'], $val['pru']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'pru', 'sens' => -1, 'seuil' => 'PRU', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM200
		if (depassementALaHausse($d['previous'], $d['price'], $d['MM200']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm200', 'sens' => 1, 'seuil' => 'MM200', 'colr' => 'green', 'icon' => 'arrow up' ];
		if (depassementALaBaisse($d[('previous')], $d['price'], $d['MM200']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm200', 'sens' => -1, 'seuil' => 'MM200', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM20
		if (depassementALaHausse($d['previous'], $d['price'], $d['MM20']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm20', 'sens' => 1, 'seuil' => 'MM20', 'colr' => 'green', 'icon' => 'arrow up' ];
		if (depassementALaBaisse($d[('previous')], $d['price'], $d['MM20']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm20', 'sens' => -1, 'seuil' => 'MM20', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement objectif/stoploss/stopprofit
		if (isset($trend_following[$key]['objectif'])) {

			if ($trend_following[$key]['objectif'] > 0) {
				if (depassementALaHausse($d['previous'], $d['price'], $trend_following[$key]['objectif'])) {
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'objectif', 'sens' => 1, 'seuil' => 'Objectif', 'colr' => 'green', 'icon' => 'arrow up' ];
					if ($trend_following[$key]['stop_loss'] == 0)
						$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
				}
				if (depassementALaBaisse($d[('previous')], $d['price'], $trend_following[$key]['objectif']))
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'objectif', 'sens' => -1, 'seuil' => 'Objectif', 'colr' => 'red', 'icon' => 'arrow down' ];
			}

			if ($trend_following[$key]['stop_loss'] > 0) {
				if (depassementALaHausse($d['previous'], $d['price'], $trend_following[$key]['stop_loss']))
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'stop_loss', 'sens' => 1, 'seuil' => 'Stop Loss', 'colr' => 'green', 'icon' => 'arrow up' ];
				if (depassementALaBaisse($d[('previous')], $d['price'], $trend_following[$key]['stop_loss']))
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'stop_loss', 'sens' => -1, 'seuil' => 'Stop Loss', 'colr' => 'red', 'icon' => 'arrow down' ];
			}

			if ($trend_following[$key]['stop_profit'] > 0) {
				if (depassementALaHausse($d['previous'], $d['price'], $trend_following[$key]['stop_profit'])) {
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'stop_profit', 'sens' => 1, 'seuil' => 'Stop Profit', 'colr' => 'green', 'icon' => 'arrow up' ];
					if ($trend_following[$key]['stop_loss'] == 0)
						$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
				}
				if (depassementALaBaisse($d[('previous')], $d['price'], $trend_following[$key]['stop_profit']))
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'stop_profit', 'sens' => -1, 'seuil' => 'Stop Profit', 'colr' => 'red', 'icon' => 'arrow down' ];
			}

		}
		
		// croisement PRU/stoploss/stopprofit/objectif/MM200 ?


	}

}

$file_cache = 'cache/TMP_ALERTES.json';
cacheData::writeCacheData($file_cache, $notifs);

foreach($notifs as $key => $val) {

	var_dump($val);

	$req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES(".$val['user_id'].", '".date('Y-m-d')."', '".$val['actif']."', 0, 0, '".$val['type']."', ".$val['sens'].", '".$val['colr']."', '".$val['icon']."', '".$val['seuil']."') ON DUPLICATE KEY UPDATE user_id='".$val['user_id']."', date='".date('Y-m-d')."', actif='".$val['actif']."'";
	$res = dbc::execSql($req);

}

echo count($notifs)." item(s)<br />";
echo "Done";

?>