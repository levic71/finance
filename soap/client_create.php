<html>
<head>
<title>Create champ</title>
</head>
<body>
<?php

$local = true;
$debug = false;
$jks   = $local ? "http://localhost:8088/jorkyball/soap" : "http://www.jorkers.com/soap";

require_once('lib/nusoap.php');
require_once("json.php");

$json = new Services_JSON();

$players = array(); // Not implemented

$teams = array();
$teams[0] = array("external_id" => "12", "name" => utf8_encode(addslashes("Marseille")), "photo" => "", "comment" => utf8_encode(addslashes("")));
$teams[1] = array("external_id" => "15", "name" => utf8_encode(addslashes("Porto")),     "photo" => "", "comment" => utf8_encode(addslashes("")));

$options = array(
	"entity"   => "FOOTSPOTS",
	"name"     => utf8_encode(addslashes("'ooFoot Nancyé&'   !-")),
	"desc"     => utf8_encode(addslashes("Description championnat ....é&'!-")),
	"place"    => utf8_encode(addslashes("Nancy")),
	"manager"  => utf8_encode(addslashes("Victor FERREIRA")),
	"email"    => "victor.ferreira@laposte.net",
	"login"    => "login",
	"pwd"      => "xxxxx",
	"type"     => "1",									// 0: CHAMPIONNAT LIBRE, 1: CHAMPIONNAT CLASSIQUE, 2: TOURNOI
	"sport"    => "2",									// 1: JORKYBALL, 2: FUTSAL, 3: FOOTBALL, 4: BASKET, 5: HANDBALL, 6: VOLLEYBALL, 7: RUGBY, 8: BABYFOOT, 9: TENNIS, 10: PINGPONG, 11: PETANQUE, 12: PES, 0: AUTRE
	"win"      => "3",									// Nb points pour 1 victoire
	"drawn"    => "1",									// Nb points pour 1 nul
	"lost"     => "0",									// Nb points pour 1 défaite
	"drawn_enable" => "1",								// Gestion des matchs nuls, 0: Non, 1: Oui
	"sets_enable"  => "0",								// Gestion des sets dans un match, 0: Non, 1: Oui
	"saison_name"  => utf8_encode(addslashes("Saison 2011-2012")),
	"players" => $players,
	"teams"   => $teams
);

// Create the client instance
if ($local)
	$client = new soapclient($jks.'/server.php');
else
{
	$client = new soapclient($jks.'/server.php');
//	$client->setCredentials("footspots", "fs2011", 'digest');

//	$proxyhost = '';
//	$proxyport = '';
//	$proxyusername = "footspots";
//	$proxypassword = "fs2011";
//	$client = new soapclient($jks.'/server.php', false, $proxyhost, $proxyport, $proxyusername, $proxypassword);
}

// Call the SOAP method
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	exit();
}

$result = $client->call('CreateChampionship', array('options' => $json->encode($options)), '', '', false, true);

if ($client->fault){
	echo '<h2>Fault</h2><pre>'; print_r($result); echo '</pre>';
} else {
	$err = $client->getError();
	if ($err) {
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
	}
}

if ($debug)
{
	echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

	phpinfo();
}

?>
</body>
</html>