<?

include "../include/sess_context.php";

session_start();

include "../include/inc_db.php";
include "../include/constantes.php";

$db = dbc::connect();

$lst_date = array();
$lst_val  = array();
$lst_msg  = array();
$visteurs_uniques = array();

$today = getdate();
$tstamp = mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);

$wstamp = $tstamp-(24*3600*31);
$w["day"]   = date("d",$wstamp);
$w["month"] = date("m",$wstamp);
$w["year"]  = date("Y",$wstamp);

$wstamp = $tstamp+(24*3600);
$z["day"]   = date("d",$wstamp);
$z["month"] = date("m",$wstamp);
$z["year"]  = date("Y",$wstamp);

// Initialisation des dates
for($i=0; $i < 31; $i++)
{
	$istamp = $tstamp-(24*3600*$i);
	$x["day"]   = date("d",$istamp);
	$x["month"] = date("m",$istamp);
	$x["year"]  = date("Y",$istamp);
	$lst_date[$x["year"].$x["month"].$x["day"]] = $x["day"]."-".$x["month"]."-".$x["year"];
}

$filtre = "";
if (isset($ref_champ) && $ref_champ != 0 && $ref_champ != 99999)
{
	$filtre = "id_champ=".$ref_champ." AND ";

	$select = "SELECT * FROM jb_championnat WHERE id=".$ref_champ;
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);
	$libelle_chart = "STATISTIQUE DE FREQUENTATION ".$row['nom'];
}
else
	$libelle_chart = "STATISTIQUE DE FREQUENTATION GLOBAL";



$req = "SELECT * FROM jb_stats WHERE ".$filtre." date BETWEEN '".$w["year"].$w["month"].$w["day"]."' AND '".$z["year"].$z["month"].$z["day"]."'";
$res = dbc::execSql($req);
while($row = mysql_fetch_array($res))
{
	$d = substr($row['date'], 0, 8);
	$lst_date[$d] = substr($row['date'], 6, 2)."-".substr($row['date'], 4, 2)."-".substr($row['date'], 0, 4);
	if ($row['admin'] == _TRACK_ACCES_HOME_)
		$lst_val[_TRACK_ACCES_HOME_][$d] = !isset($lst_val[_TRACK_ACCES_HOME_][$d]) ? 1 : $lst_val[_TRACK_ACCES_HOME_][$d]+1;
	if ($row['admin'] == _TRACK_ADMIN_)
		$lst_val[_TRACK_ADMIN_][$d] = !isset($lst_val[_TRACK_ADMIN_][$d]) ? 1 : $lst_val[_TRACK_ADMIN_][$d]+1;
	if ($row['admin'] == _TRACK_PARTENAIRE_)
		$lst_val[_TRACK_PARTENAIRE_][$d] = !isset($lst_val[_TRACK_PARTENAIRE_][$d]) ? 1 : $lst_val[_TRACK_PARTENAIRE_][$d]+1;
	if ($row['admin'] == _TRACK_EXPORT_)
		$lst_val[_TRACK_EXPORT_][$d] = !isset($lst_val[_TRACK_EXPORT_][$d]) ? 1 : $lst_val[_TRACK_EXPORT_][$d]+1;
	if ($row['admin'] == _TRACK_SONDAGE_)
		$lst_val[_TRACK_SONDAGE_][$d] = !isset($lst_val[_TRACK_SONDAGE_][$d]) ? 1 : $lst_val[_TRACK_SONDAGE_][$d]+1;
	if ($row['admin'] == _TRACK_ACTUALITE_)
		$lst_val[_TRACK_ACTUALITE_][$d] = !isset($lst_val[_TRACK_ACTUALITE_][$d]) ? 1 : $lst_val[_TRACK_ACTUALITE_][$d]+1;
	if ($row['admin'] == _TRACK_AFFICHE_)
		$lst_val[_TRACK_AFFICHE_][$d] = !isset($lst_val[_TRACK_AFFICHE_][$d]) ? 1 : $lst_val[_TRACK_AFFICHE_][$d]+1;
	if ($row['admin'] == _TRACK_PDF_)
		$lst_val[_TRACK_PDF_][$d] = !isset($lst_val[_TRACK_PDF_][$d]) ? 1 : $lst_val[_TRACK_PDF_][$d]+1;
	$visteurs_uniques[$row['ip']] = $row['ip'];
}

$req = "SELECT count(*) total, DATE_FORMAT(date, '%Y%m%d') date FROM jb_forum WHERE ".$filtre." date BETWEEN '".$w["year"].$w["month"].$w["day"]."' AND '".$z["year"].$z["month"].$z["day"]."' GROUP BY DATE_FORMAT(date, '%Y%m%d')";
$res = dbc::execSql($req);
while($row = mysql_fetch_array($res))
{
	$lst_msg[$row['date']] = $row['total'];
}


$cumul_visites = 0;
$cumul_admin = 0;
$cumul_msg = 0;
$cumul_part = 0;
$cumul_exp = 0;
$cumul_asks = 0;
$cumul_actu = 0;
$cumul_pdf = 0;

?>

<chart>
	<chart_data>
		<row>
			<null/>
<?
	$i = 0;
	reset($lst_date);
	ksort($lst_date);
	foreach($lst_date as $d)
	{
		echo "<string>".($i % 2 == 0 ? $d : "")."</string>\n";
		$i++;
	}
?>
		</row>

		<row>
		<string>Visites</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_ACCES_HOME_][$cle]) ? $lst_val[_TRACK_ACCES_HOME_][$cle] : 0;
		if ($ref_champ == 99999) $val = 0;
		echo "<number>".$val."</number>\n";
		$cumul_visites += $val;
	}
?>
		</row>

		<row>
			<string>Admin</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_ADMIN_][$cle]) ? $lst_val[_TRACK_ADMIN_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_admin += $val;
	}
?>
		</row>

		<row>
			<string>Messages</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_msg[$cle]) ? $lst_msg[$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_msg += $val;
	}
?>
		</row>

		<row>
			<string>Partenaires</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_PARTENAIRE_][$cle]) ? $lst_val[_TRACK_PARTENAIRE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_part += $val;
	}
?>
		</row>

		<row>
			<string>Export</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_EXPORT_][$cle]) ? $lst_val[_TRACK_EXPORT_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_exp += $val;
	}
?>
		</row>

		<row>
			<string>Sondage</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_SONDAGE_][$cle]) ? $lst_val[_TRACK_SONDAGE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_asks += $val;
	}
?>
		</row>

		<row>
			<string>Actualites</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_ACTUALITE_][$cle]) ? $lst_val[_TRACK_ACTUALITE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_actu += $val;
	}
?>
		</row>

		<row>
			<string>Affiches</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_AFFICHE_][$cle]) ? $lst_val[_TRACK_AFFICHE_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_actu += $val;
	}
?>
		</row>

		<row>
			<string>PDF</string>
<?
	reset($lst_date);
	ksort($lst_date);
	while(list($cle, $val) = each($lst_date))
	{
		$val = isset($lst_val[_TRACK_PDF_][$cle]) ? $lst_val[_TRACK_PDF_][$cle] : 0;
		echo "<number>".$val."</number>\n";
		$cumul_pdf += $val;
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
			><?= $libelle_chart ?></text>
		<text  transition='slide_right'
			delay='0'
			duration='1'
			x='<?= $x_label ?>'
			y='<?= $y_label ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_label ?>'
			alpha='90'
			>Total visites : </text>
		<text  transition='drop'
			delay='0'
			duration='1'
			x='<?= $x_value ?>'
			y='<?= $y_label ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_value ?>'
			alpha='90'
			><?= ($cumul_visites + $cumul_admin)." [".$cumul_visites."+".$cumul_admin."]" ?></text>
		<text  transition='dissolve'
			delay='0'
			duration='1'
			x='<?= $x_label ?>'
			y='<?= $y_label+$font_size+10 ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_label ?>'
			alpha='90'
			>Moy par jour : </text>
		<text  transition='dissolve'
			delay='0'
			duration='1'
			x='<?= $x_value ?>'
			y='<?= $y_label+$font_size+10 ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_value ?>'
			alpha='90'
			><?= round(($cumul_visites + $cumul_admin)/31)." [".round($cumul_visites/31)."+".round($cumul_admin/31)."]" ?></text>
		<text  transition='dissolve'
			delay='0'
			duration='1'
			x='<?= $x_label ?>'
			y='<?= $y_label+($font_size+10)*2 ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_label ?>'
			alpha='90'
			>Visiteurs uniques : </text>
		<text  transition='dissolve'
			delay='0'
			duration='1'
			x='<?= $x_value ?>'
			y='<?= $y_label+($font_size+10)*2 ?>'
			h_align='left'
			v_align='top'
			rotation='0'
			font='arial'
			bold='true'
			size='<?= $font_size ?>'
			color='<?= $color_value ?>'
			alpha='90'
			><?= count($visteurs_uniques) ?></text>
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
