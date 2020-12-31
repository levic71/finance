<html>
<head>
<title>Delete champ</title>
</head>
<body>
<?php

$local = false;
$debug = false;
$jks   = $local ? "http://localhost:8088/jorkyball/soap" : "http://www.jorkers.com/soap5";

require_once('lib/nusoap.php');

$options = array(
	"entity"  => "FOOTSPOTS",
	"login"   => "login",
	"pwd"     => "xxxxx",
	"id"      => "254"
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

//$result = $client->call('DisableChampionship', array('options' => json_encode($options)), '', '', false, true);
//$result = $client->call('EnableChampionship', array('options' => json_encode($options)), '', '', false, true);
$result = $client->call('DeleteChampionship', array('options' => json_encode($options)), '', '', false, true);

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