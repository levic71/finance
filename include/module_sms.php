<?

// GENERER LES SMS toutes les 5 minutes en injectant un affichage aléatoire

$subsite = isset($subsite) ? true : false;
$standalone = isset($standalone) ? true : false;
$id_zone = isset($id_zone) ? $id_zone : 0;


if ($standalone)
{
	include "../include/sess_context.php";
	session_start();
	include "../www/common.php";
	include "../include/inc_db.php";
	$db = dbc::connect();
}

if ($subsite)
{
	include "../subsites/sess_site.php";
	session_start();
	include "../subsites/common.php";
}

// Code repli de sms.php
$row = array();
$row['sms_pseudo'] = isset($sms_pseudo) ? $sms_pseudo : "";
$row['sms_msg']    = isset($sms_msg)    ? $sms_msg    : "";
$row['sms_jour']   = isset($sms_jour)   ? $sms_jour   : date("d/m/Y");
$row['sms_heure']  = isset($sms_heure)  ? $sms_heure  : date("G");
$row['sms_minute'] = isset($sms_minute) ? $sms_minute : date("i");

if ($standalone)
{
	echo $row['sms_heure'].":".$row['sms_minute']."<br />";
}

if ($row['sms_minute'] < 15) $row['sms_minute'] = 0;
else if ($row['sms_minute'] >= 15 && $row['sms_minute'] < 30) $row['sms_minute'] = 15;
else if ($row['sms_minute'] >= 30 && $row['sms_minute'] < 45) $row['sms_minute'] = 30;
else if ($row['sms_minute'] >= 45) $row['sms_minute'] = "45";

?>

<applet code="ticker.class" align="absMiddle" width="468" height="52">
      <param name="borderwidth" value="0">
      <param name="unlitcolor" value="102,4,4">

      <param name="anchor" value="http://www.jorkers.com/www/sms.php">
      <param name="font" value="dialog,bold,12">
      <param name="borderwidth" value="0">
      <param name="speed" value="30">
      <param name="LEDSize" value="2">

<?

$my_sms_heure  = ($row['sms_heure']  < 9 ? "0" : "").$row['sms_heure'];
$my_sms_minute = ($row['sms_minute'] < 9 ? "0" : "").$row['sms_minute'];
$my_sms_horaire = $my_sms_heure.":".$my_sms_minute;

$item = explode('/', $row['sms_jour']);
$my_sms_jour = $item[2]."-".$item[1]."-".$item[0];

$request = "SELECT count(*) total FROM jb_sms WHERE id_zone = ".$id_zone." AND date = '".$my_sms_jour."' AND heure = '".$my_sms_horaire."';";
$res = dbc::execSQL($request);
$total = ($row = mysqli_fetch_array($res)) ? $row['total'] : 0;

$mycolor = array();
$mycolor[] = "0,212,255";
$mycolor[] = "255,0,0";
$mycolor[] = "252,254,4";

if ($total == -1)
{ ?>
	<param name="Text1" value="COLOR:255,0,0;STRING:***  LE JORKERS ACCESSIBLE SUR VOTRE MOBILE  ***">
	<param name="Text2" value="IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;PAUSE:3000;COLOR:0,212,255;STRING:%A  %d %b  %X">
<?
}
else if ($total == 0)
{ ?>
	<param name="Text1" value="IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;COLOR:255,0,0;STRING:***  DEPOSEZ VOS MSG  ***">
	<param name="Text2" value="IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;COLOR:252,254,4;STRING:CLIQUEZ SUR CE PANNEAU">
	<param name="Text3" value="IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;PAUSE:3000;COLOR:0,212,255;STRING:%A  %d %b  %X">
<?
}
else
{
	$request = "SELECT * FROM jb_sms WHERE id_zone = ".$id_zone." AND date = '".$my_sms_jour."' AND heure = '".$my_sms_horaire."';";
	$res = dbc::execSQL($request);

	// CONCATENER LES MESSAGES MAIS POUVOIR METTRE DES COULEURS DIFFERENTES POUR CHAQUE MESSAGE !!!!!!!!
	$tab = array();
	while($row = mysqli_fetch_array($res))
	{
		$tab[] = $row['pseudo'].": ".$row['message'];
	}

	$i = 1;
	srand((float)microtime()*1000000);
	shuffle($tab);
	foreach($tab as $item)
		echo "<param name=\"Text".$i."\" value=\"COLOR:".$mycolor[$i++ %3].";STRING:".$item."\">";

	echo "<param name=\"Text".$i."\" value=\"IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;COLOR:".$mycolor[$i++ %3].";STRING:***  DEPOSEZ VOS SMS  ***\">";
	echo "<param name=\"Text".$i."\" value=\"IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;COLOR:".$mycolor[$i++ %3].";STRING:CLIQUEZ SUR CE PANNEAU\">";
	echo "<param name=\"Text".$i."\" value=\"IN:YRINWARD;OUT:YRINWARD;PAUSE:1500;FLASH:500,10,500;COLOR:".$mycolor[$i++ %3].";STRING:%A  %d %b  %X\">";
}

?>

      <param name="code" value="ticker.class">
      <param name="align" value="absMiddle">
      <param name="width" value="468">
      <param name="height" value="52">
      <param name="codeBase" value="http://www.jorkers.com/">
</applet>

<?

if ($standalone)
{
	$request = "SELECT * FROM jb_sms WHERE id_zone = ".$id_zone." AND date = '".$my_sms_jour."' AND heure like '".$my_sms_heure."%';";
	$res = dbc::execSQL($request);
	echo "<br />".$request."<br />";

	// CONCATENER LES MESSAGES MAIS POUVOIR METTRE DES COULEURS DIFFERENTES POUR CHAQUE MESSAGE !!!!!!!!
	$tab = array();
	while($row = mysqli_fetch_array($res))
	{
		echo "[".$row['heure']."] [".$row['cookie']."] [".$row['ip']."] ".$row['pseudo']."::".$row['message']."<br />";
	}
}

?>