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

$stocks = $data2['stocks'];
$perfs = $data2['perfs'];

$positions       = [];
$trend_following = [];
$user_email = 'vmlf71@gmail.com';

// Tableau des notifs
$notifs = [];

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='".$user_email."'";
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


// Parcours de tous les actifs
// On regarde si actif est sous surveillance de l'utilisateur (alarme activé dans home_content => champ seuils dans trend_following)
// On regarde si actif dans le portefeuille utilisateur (tous les champs de trend_following si positionnes )

// Nouveau version tableau valeurs trend_following
$trend_following = [];

// Recuperation trend_following user
$req = "SELECT * FROM trend_following WHERE user_id='".$user_id."'";
$res = dbc::execSql($req);
while($row = mysqli_fetch_assoc($res)) $trend_following[$row['symbol']] = $row;

// Parcours de tous les actifs
foreach($stocks as $key => $val) {

	// var_dump($val);

	if (!isset($trend_following[$key]) && !isset($positions[$key])) continue;

	$symbol   = $val['symbol'];
	$price    = $val['price'];
	$previous = $val['previous'];
	$pru      = isset($positions[$key]) ? $positions[$key]['pru'] : 0;
	$objectif = isset($trend_following[$key]['objectif']) && $trend_following[$key]['objectif'] != '' ? $trend_following[$key]['objectif'] : 0;
	$seuils   = explode(';', isset($trend_following[$key]['seuils']) ? $trend_following[$key]['seuils'] : '');

	$colr_up   = $symbol == "VIX" ? "red" : "green";
	$icon_up   = $symbol == "VIX" ? "arrow down" : "arrow up";
	$sens_up   = $symbol == "VIX" ? -1 : 1;
	$colr_down = $symbol == "VIX" ? "green" : "red";
	$icon_down = $symbol == "VIX" ? "arrow up" : "arrow down";
	$sens_down = $symbol == "VIX" ? 1 : -1;

	// Parcours du cours de chaque seuil ?
	foreach($seuils as $i => $v) {
		if (depassementALaHausse($previous, $price, $v))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'seuil', 'sens' => $sens_up, 'seuil' => $v, 'colr' => $colr_up, 'icon' => $icon_up ];
		if (depassementALaBaisse($previous, $price, $v))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'seuil', 'sens' => $sens_down, 'seuil' => $v, 'colr' => $colr_down, 'icon' => $icon_down ];
	}

	// Depassement MM200
	if (depassementALaHausse($previous, $price, $val['MM200']))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'mm200', 'sens' => $sens_up, 'seuil' => $val['MM200'], 'colr' => 'green', 'icon' => 'arrow up' ];	
	if (depassementALaBaisse($previous, $price, $val['MM200']))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'mm200', 'sens' => $sens_down, 'seuil' => $val['MM200'], 'colr' => 'red', 'icon' => 'arrow down' ];

	// Depassement MM20
	if (depassementALaHausse($previous, $price, $val['MM20']))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'mm20', 'sens' => $sens_up, 'seuil' => $val['MM20'], 'colr' => 'green', 'icon' => 'arrow up' ];	
	if (depassementALaBaisse($previous, $price, $val['MM20']))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'mm20', 'sens' => $sens_down, 'seuil' => $val['MM20'], 'colr' => 'red', 'icon' => 'arrow down' ];

	// Hausse journaliere +10% par rapport a la veille
	if (pourcentagevariation($previous, $price) >= 10)
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => '10%', 'sens' => $sens_up, 'seuil' => $previous, 'colr' => 'green', 'icon' => 'arrow up' ];

	// Depassement +20% PRU
	if ($pru > 0 && depassementALaHausse($previous, $price, $pru * 1.2))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'pru20%', 'sens' => $sens_up, 'seuil' => $pru, 'colr' => 'green', 'icon' => 'arrow up' ];

	// Depassement a la baisse PRU
	if ($pru > 0 && depassementALaBaisse($previous, $price, $pru))
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'pru', 'sens' => $sens_down, 'seuil' => $pru, 'colr' => 'green', 'icon' => 'arrow up' ];
}

var_dump($notifs);

exit(0);


// Alerte sur actifs en portefeuille
foreach($positions as $key => $val) {

	if (isset($data2["stocks"][$key])) {

		$d = $data2["stocks"][$key];

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