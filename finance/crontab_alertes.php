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

// Si on n'est pas en semaine
if (date("N") >= 6) exit(0);

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

$positions       = [];
$trend_following = [];
$email = 'vmlf71@gmail.com';

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='".$email."'";
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
          'i'147' => $ret[2][0],
          'nam'BNP Paribas SA' (length=14)
          'symbo'BNP.PAR' => $ret[2][0],
          'currenc'EUR' => $ret[2][0],
          'typ'Equity' => $ret[2][0],
          'regio'Paris' => $ret[2][0],
          'marketope'09:00' => $ret[2][0],
          'marketclos'17:30' => $ret[2][0],
          'timezon'UTC+01' => $ret[2][0],
          'pe'0' => $ret[2][0],
          'gf_symbo'' => $ret[2][0],
          'ISI'' => $ret[2][0],
          'provide'' => $ret[2][0],
          'categori'' => $ret[2][0],
          'frai'0' => $ret[2][0],
          'distributio'0' => $ret[2][0],
          'actif'0' => $ret[2][0],
          'link'{"link1":"","link2":""}' (length=23)
          'tag'Actions|Industrie|Services financiers|MarchÃ© dÃ©veloppÃ©|Europe|Value|Large Cap|' (length=81)
          'ratin'6' => $ret[2][0],
          'dividende_annualis'3.67' => $ret[2][0],
          'date_dividend'2022-06-02' (length=10)
          'ope'50.4200' => $ret[2][0],
          'hig'51.2600' => $ret[2][0],
          'lo'49.8500' => $ret[2][0],
          'pric'49.8500' => $ret[2][0],
          'volum'2879032' => $ret[2][0],
          'da'2022-05-09' (length=10)
          'previou'50.5900' => $ret[2][0],
          'day_chang'-0.7400' => $ret[2][0],
          'percen'-1.4627%' => $ret[2][0],
          'perio'DAILY' => $ret[2][0],
          'D'-4.76' => $ret[2][0],
          'DMD'2022-04-29' (length=10)
          'DMD'2022-02-28' (length=10)
          'DMD'2021-11-30' (length=10)
          'MM'50.282857142857' (length=15)
          'MM2'50.0655' => $ret[2][0],
          'MM5'50.5656' => $ret[2][0],
          'MM10'56.5983' => $ret[2][0],
          'MM20'56.22625' => $ret[2][0],
          'RSI1'46.497137366824' (length=15)
          'Bollinge'' => $ret[2][0], */

/* 'positions' => 
array (size=9)
  'GLE.PAR' => 
	array (size=4)
	  'nb' => int 1000
	  'pru' => float 13.6625
	  'other_name' => boolean false
	  'devis'EUR' => $ret[2][0],
  'BRE.PAR' => 
  
'trend_following' => 
    array (size=4)
      'BRE.PAR' => 
        array (size=6)
          'user_i'4' => $ret[2][0],
          'symbo'BRE.PAR' => $ret[2][0],
          'stop_los'0.000000' => $ret[2][0],
          'stop_profi'200.000000' (length=10)
          'objecti'100.000000' (length=10)
          'manual_pric'0' => $ret[2][0],
      'ESE.PAR' => 
 */

$gsa = calc::getGSAlertes();

/* echo "Liste des actifs en surveillance : ";
foreach($gsa as $key => $val) echo $val[0].", ";

echo "<br />Liste des actifs en portefeuille : ";
foreach($positions as $key => $val) echo $key.", ";
 */

function pourcentagevariation($vi, $vf) {
	return $vi == 0 ? 0 : (($vf / $vi) - 1) * 100;
}

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
				$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'cours', 'sens' => 1, 'seuil' => $v, 'colr' => 'green', 'icon' => 'arrow up' ];
			if (depassementALaBaisse($val[10], $val[4], $v))
				$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'cours', 'sens' => -1, 'seuil' => $v, 'colr' => 'red', 'icon' => 'arrow down' ];

		}

		// Depassement MM200
		// if (depassementALaHausse($val[10], $val[4], $val[22]))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'mm200', 'sens' => 1, 'seuil' => 'MM200', 'colr' => 'green', 'icon' => 'arrow up' ];	
		// if (depassementALaBaisse($val[10], $val[4], $val[22]))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'mm200', 'sens' => -1, 'seuil' => 'MM200', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM20
		// if (depassementALaHausse($val[10], $val[4], $val[19]))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'mm20', 'sens' => 1, 'seuil' => 'MM20', 'colr' => 'green', 'icon' => 'arrow up' ];	
		// if (depassementALaBaisse($val[10], $val[4], $val[19]))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $val[0], 'type' => 'mm20', 'sens' => -1, 'seuil' => 'MM20', 'colr' => 'red', 'icon' => 'arrow down' ];
	}
}

// Alerte sur actifs en portefeuille
foreach($positions as $key => $val) {

	if (isset($data2["stocks"][$key])) {

		$d = $data2["stocks"][$key];

		// Hausse journaliere +10% par rapport a la veille
		if (pourcentagevariation($d['previous'], $d['price']) >= 10)
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'var10', 'sens' => 1, 'seuil' => 'QUOTE', 'colr' => 'green', 'icon' => 'arrow up' ];

		// Depassement +20% PRU
		if (depassementALaHausse($d['previous'], $d['price'], $val['pru'] * 1.2))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'pru20', 'sens' => 1, 'seuil' => 'PRU', 'colr' => 'green', 'icon' => 'arrow up' ];
		
		// Depassement a la baisse PRU
		if (depassementALaBaisse($d[('previous')], $d['price'], $val['pru']))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'pru', 'sens' => -1, 'seuil' => 'PRU', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM200
		// if (depassementALaHausse($d['previous'], $d['price'], $d['MM200']))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm200', 'sens' => 1, 'seuil' => 'MM200', 'colr' => 'green', 'icon' => 'arrow up' ];
		// if (depassementALaBaisse($d[('previous')], $d['price'], $d['MM200']))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm200', 'sens' => -1, 'seuil' => 'MM200', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement MM20
		// if (depassementALaHausse($d['previous'], $d['price'], $d['MM20']))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm20', 'sens' => 1, 'seuil' => 'MM20', 'colr' => 'green', 'icon' => 'arrow up' ];
		// if (depassementALaBaisse($d[('previous')], $d['price'], $d['MM20']))
		//	$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'mm20', 'sens' => -1, 'seuil' => 'MM20', 'colr' => 'red', 'icon' => 'arrow down' ];

		// Depassement objectif/stoploss/stopprofit
		if (isset($trend_following[$key]['objectif'])) {

			if ($trend_following[$key]['objectif'] > 0) {
				if (depassementALaHausse($d['previous'], $d['price'], $trend_following[$key]['objectif'])) {
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'objectif', 'sens' => 1, 'seuil' => 'Objectif', 'colr' => 'green', 'icon' => 'trophy' ];
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
					$notifs[] =  [ 'user_id' => $user_id, 'actif' => $key, 'type' => 'stop_profit', 'sens' => 1, 'seuil' => 'Stop Profit', 'colr' => 'green', 'icon' => 'trophy' ];
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

	$req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$val['user_id'].", '".date('Y-m-d')."', '".$val['actif']."', 0, 0, '".$val['type']."', ".$val['sens'].", '".$val['colr']."', '".$val['icon']."', '".$val['seuil']."') ON DUPLICATE KEY UPDATE sens='".$val['sens']."', couleur='".$val['colr']."', icone='".$val['icon']."', seuil='".$val['seuil']."'";
	$res = dbc::execSql($req);

}

echo count($notifs)." item(s)<br />";
echo "Done";

?>