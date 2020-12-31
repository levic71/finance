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

$options = array(
	"entity"       => "FOOTSPOTS",
	"login"        => "login",
	"pwd"          => "xxxxx",
	"idc"          => "297",		// id du championnat
	"idm"          => "20227",		// id du match
	"resultat"     => "7/2",		// Résultat du match (si 1 set alors resultat="7/2" - si 3 sets alors resultat="7/1,7/4,7/2", ...), 5 sets maximum. Si equipe1 forfait alors resultat="-1", si equipe2 forfait alors resultat="-2"
	"nbset"        => "1",			// Nb sets, doit etre cohérent avec le resultat du match
	"match_joue"   => "1",			// Mettre 1, cela sert à dire qu'un "0/0" a été joué
	"prolongation" => "0",			// Mettre 1 s'il y a eu prolongation, 0 sinon
	"penaltys"     => "0",			// Mettre nb de tirs au reussit par eq1|nb de tirs au reussit par eq2, vide sinon (ex: "6|4")
	"play_date"    => "",			// Mettre la date a laquelle le match s'est déroule si elle differente de la date de la journee - JJ/MM/AAAA
	"play_time"    => ""			// Mettre l'heure a laquelle le match s'est déroule si elle differente de l'heure de la journee - HH:MM
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

$result = $client->call('UpdateMatch', array('options' => $json->encode($options)), '', '', false, true);

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