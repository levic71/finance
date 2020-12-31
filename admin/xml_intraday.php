<?

include "../include/sess_context.php";

session_start();

include "../include/inc_db.php";
include "../www/SQLServices.php";
include "../include/cache_manager.php";
include "../include/constantes.php";

$db = dbc::connect();

if (!isset($ref_champ)) $ref_champ = 0;
if (!isset($option))
	$option = 0;
else
{
	$items = explode("_", $option);
	$option = $items[0];
	$ref_champ = $items[2];
	if (isset($items[1])) $intradate = $items[1];
}

$lst_date = array();
$lst_val  = array();
$lst_msg  = array();
$visteurs_uniques = array();

if (isset($intradate) && $intradate != "")
{
	$z["day"]   = substr($intradate, 0, 2);
	$z["month"] = substr($intradate, 3, 2);
	$z["year"]  = substr($intradate, 6, 4);
}
else
{
	$today = getdate();
	$tstamp = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);
	$z["day"]   = date("d",$tstamp);
	$z["month"] = date("m",$tstamp);
	$z["year"]  = date("Y",$tstamp);
}

$filtre = "";
if ($option == 1)
	$filtre = " WHERE date like '".$z["year"].$z["month"].$z["day"]."%'";
else
	$filtre = " WHERE DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL 30 DAY) <= date ";
//	$filtre = " WHERE date like '".$z["year"].$z["month"]."%'";

$filtre2 = "";
if ($option == 1)
	$filtre2 = " WHERE date like '".$z["year"]."-".$z["month"]."-".$z["day"]."%'";
else
	$filtre2 = " WHERE DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL 30 DAY) <= date ";
//	$filtre2 = " WHERE date like '".$z["year"]."-".$z["month"]."-%'";


$lst_champs = array();
/*$req = "SELECT * FROM jb_championnat WHERE actif=1";
$res = dbc::execSql($req);
while($row = mysql_fetch_array($res))
	$lst_champs[$row['id']] = $row;
*/




/*

$most_active = JKCache::getCache("../cache/most_active_home.txt", 900, "_FLUX_MOST_ACTIVE_");
$x = 0;
foreach($most_active as $c)
{
	$lst_champs[$c['id']] = $c;
	$x++;
	if ($x > 30) break;
}


*/

$select = "SELECT s.id_champ id, count(*) total, c.nom nom FROM jb_stats s, jb_championnat c WHERE c.id = s.id_champ and DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL ".($option == 1 ? 7 : 30)." DAY) <= s.date group by s.id_champ ORDER BY total desc LIMIT 0,30";
$res = dbc::execSql($select);
while($row = mysql_fetch_array($res))
{
	$lst_champs[$row['id']] = $row;
}

$lst_champs[84] = array("id" => "84", "nom" => "Demo libre");
$lst_champs[85] = array("id" => "85", "nom" => "Demo championnat");
$lst_champs[86] = array("id" => "86", "nom" => "Demo tournoi");







$cumul_visites = array();

$req = "SELECT * FROM jb_stats ".$filtre;
$res = dbc::execSql($req);
while($row = mysql_fetch_array($res))
{
	if ($row['admin'] == _TRACK_ACCES_HOME_)
	{
		if (isset($cumul_visites[_TRACK_ACCES_HOME_][$row['id_champ']]))
			$cumul_visites[_TRACK_ACCES_HOME_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_ACCES_HOME_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_ADMIN_)
	{
		if (isset($cumul_visites[_TRACK_ADMIN_][$row['id_champ']]))
			$cumul_visites[_TRACK_ADMIN_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_ADMIN_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_PARTENAIRE_)
	{
		if (isset($cumul_visites[_TRACK_PARTENAIRE_][$row['id_champ']]))
			$cumul_visites[_TRACK_PARTENAIRE_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_PARTENAIRE_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_EXPORT_)
	{
		if (isset($cumul_visites[_TRACK_EXPORT_][$row['id_champ']]))
			$cumul_visites[_TRACK_EXPORT_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_EXPORT_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_SONDAGE_)
	{
		if (isset($cumul_visites[_TRACK_SONDAGE_][$row['id_champ']]))
			$cumul_visites[_TRACK_SONDAGE_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_SONDAGE_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_ACTUALITE_)
	{
		if (isset($cumul_visites[_TRACK_ACTUALITE_][$row['id_champ']]))
			$cumul_visites[_TRACK_ACTUALITE_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_ACTUALITE_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_AFFICHE_)
	{
		if (isset($cumul_visites[_TRACK_AFFICHE_][$row['id_champ']]))
			$cumul_visites[_TRACK_AFFICHE_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_AFFICHE_][$row['id_champ']] = 1;
	}
	if ($row['admin'] == _TRACK_PDF_)
	{
		if (isset($cumul_visites[_TRACK_PDF_][$row['id_champ']]))
			$cumul_visites[_TRACK_PDF_][$row['id_champ']]++;
		else
			$cumul_visites[_TRACK_PDF_][$row['id_champ']] = 1;
	}
}

$nb_msg_total = 0;
$req = "SELECT count(*) total, id_champ FROM jb_forum ".$filtre2." GROUP BY id_champ";
$res = dbc::execSql($req);
while($row = mysql_fetch_array($res))
{
	$lst_msg[$row['id_champ']] = $row['total'];
	$nb_msg_total += $row['total'];
}

?>

<chart>
	<chart_data>
		<row>
			<null/>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	foreach($lst_champs as $c)
	{
		echo "<string>".htmlentities ($c['nom'])."</string>\n";
	}
?>
		</row>
		<row>
		<string>Visites</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_ACCES_HOME_][$cle]) ? $cumul_visites[_TRACK_ACCES_HOME_][$cle] : 0;
		if ($ref_champ == 99999) $val = 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Admin</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_ADMIN_][$cle]) ? $cumul_visites[_TRACK_ADMIN_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Messages</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($lst_msg[$cle]) ? $lst_msg[$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Partenaires</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_PARTENAIRE_][$cle]) ? $cumul_visites[_TRACK_PARTENAIRE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Export</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_EXPORT_][$cle]) ? $cumul_visites[_TRACK_EXPORT_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Sondage</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_SONDAGE_][$cle]) ? $cumul_visites[_TRACK_SONDAGE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Actualites</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_ACTUALITE_][$cle]) ? $cumul_visites[_TRACK_ACTUALITE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Affiches</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_AFFICHE_][$cle]) ? $cumul_visites[_TRACK_AFFICHE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

		<row>
			<string>Pdf</string>
<?
	reset($lst_champs);
	//ksort($lst_champs);
	while(list($cle, $val) = each($lst_champs))
	{
		$val = isset($cumul_visites[_TRACK_PDF_][$cle]) ? $cumul_visites[_TRACK_PDF_][$cle] : 0;
		echo "<number>".$val."</number>\n";
	}
?>
		</row>

	</chart_data>

   <chart_value prefix=''
          suffix=''
          decimals='0'
          decimal_char=''
          separator=''
          position='cursor'
          hide_zero='false'
          as_percentage='false'
          font='arial'
          bold='true'
          size='12'
          color='FFFFFF'
          alpha='100'
          />

<?
	$font_size = 16;
	$color_label = "000000";
	$color_value = "444444";
	$x_label = 150;
	$y_label = 320;
	$x_value = 310;
	$y_value = 320;
?>

	<draw>
		<text  transition='none'
			delay='0'
			duration='1'
			x='0'
			y=''
			width='600'
			height='30'
			h_align='center'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='20'
			color='FFFFFF'
			alpha='100'
			><?= $option == 1 ? "INTRADAY ".$z["day"]."/".$z["month"]."/".$z["year"] : "HISTORIQUE 30 JOURS" ?></text>
	</draw>
	<series_color>
		<color>0000FF</color>
		<color>FF0000</color>
		<color>00FF00</color>
		<color>FDFF00</color>
		<color>FF00FA</color>
		<color>00FFFA</color>
		<color>FF8A00</color>
		<color>CCCCCC</color>
		<color>AA7765</color>
	</series_color>

	<chart_rect	x='35' y='65' width='530' height='180' />

	<axis_value font='arial' bold='true' size='12' />
	<axis_category orientation='diagonal_up' font='arial' bold='true' size='12' />

	<legend_label font='arial' bold='true' size='12' />
	<legend_rect x='35' y='35' width='530' height='10' margin='5' />

	<chart_type>stacked column</chart_type>

</chart>
