<html>
<head>
<title>Create champ</title>
</head>
<body>
<?php

$local = false;
$debug = false;
$jks   = $local ? "http://localhost:8088/jorkyball/soap" : "http://www.jorkers.com/soap";

require_once('lib/nusoap.php');
require_once("json.php");

$json = new Services_JSON();

$players = array(); // Not implemented

$teams = array();
$teams[0] = array("id" => "12", "name" => "Marseille", "photo" => "", "comment" => "");
$teams[1] = array("id" => "15", "name" => "Porto", "photo" => "", "comment" => "");

$options = array(
	"entity"   => "FOOTSPOTS",
	"name"     => "Foot Nancy",
	"desc"     => "Description championnat ....",
	"place"    => "Nancy",
	"manager"  => "Victor FERREIRA",
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
	"saison_name"  => "Saison 2011-2012",
	"players" => $players,
	"teams"   => $teams
);

// Create the client instance
if ($local)
	$client = new soapclient($jks.'/server.php');
else
{
	$client = new soapclient($jks.'/server.php');
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