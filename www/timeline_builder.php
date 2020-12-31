<?

include "../include/inc_db.php";
include "../include/toolbox.php";
include "../include/constantes.php";

$db = dbc::connect();

function onlyChars($string)
{
	$ret = "";
	$strlength = strlen($string);

	for($i = 0; $i < $strlength; $i++)
	{
		if ((ord($string[$i]) >= 48 && ord($string[$i]) <= 57) ||
			(ord($string[$i]) >= 65 && ord($string[$i]) <= 90) ||
			(ord($string[$i]) >= 97 && ord($string[$i]) <= 122))
		{
			$ret .= $string[$i];
		}
		else
			$ret .= "&#".ord($string[$i]).";";
	}
	
	return $ret;   
}

$filename = "timeline_jorkers.xml";
$fichier = fopen($filename, "w");
fputs($fichier, "<data>\n");
$req = "SELECT c.type type, c.lieu lieu, c.gestionnaire pseudo, c.id id_champ, DATE_FORMAT( j.date, '%b %d %Y' ) journee_date, j.nom journee_nom, c.nom championnat_nom from jb_journees j, jb_saisons s, jb_championnat c WHERE j.id_champ=s.id AND s.id_champ = c.id ORDER BY date ASC";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res))
{
	$item = "<event ";
	$item .= "start=\"".$row['journee_date']." 00:00:00 GMT\" ";
	$title = onlyChars($row['championnat_nom']."::".ToolBox::conv_lib_journee($row['journee_nom']));
	$item .= "title=\"".$title."\" ";
	$item .= ">";
	$item .= onlyChars("Géré par : ".$row['pseudo']."<br />Lieu de pratique : ".$row['lieu']."<br />Type : ".$libelle_type[$row['type']]."<br /><a href=\"championnat_acces.php?ref_champ=".$row['id_champ']."\">&#187; accès au championnat</a>");
	$item .= "</event>\n";
	fputs($fichier, $item);
}
fputs($fichier, "</data>\n");
fclose($fichier);

?>

