<?

class ToolBox
{

public static function automaticImageZoom($img_small, $img_normal, $libelle)
{
	return	"<a class=\"thumbnail\" href=\"#thumb\"><img src=\"".$img_small."\" /><span><img src=\"".$img_normal."\" /><br />".$libelle."</span></a>";
}

public static function getRand($length)
{
	$length = (int)$length;
	$string = '';
	$letters = 'aAbBCDeEFgGhHJKLmMnNpPqQRsStTuVwWXYZz2345679';
	$number = strlen($letters);
	for($i = 0; $i < $length; $i++)
		$string .= $letters{mt_rand(0, $number - 1)};

	return $string;
}

public static function trackUser($championnat, $page)
{
	// Pour les stats de fréquentation
	$insert = "INSERT INTO jb_stats (id_champ, ip, admin, date) VALUES (".$championnat.", '".$_SERVER["REMOTE_ADDR"]."', ".$page.", SYSDATE())";
	$res = dbc::execSQL($insert);
}

public static function sessionId()
{
	srand((double)microtime()*1000000);
	$session = md5 (uniqid (rand()));

	return $session;
}

public static function nls($code, $default)
{
	global $nls;

	return isset($nls[$code]) ? $nls[$code] : $default;
}

public static function appendLog($str)
{
	$fichier = fopen("../cache/log.txt", "a");
	fputs($fichier, date("Y-m-d H:i:s ").$str."\n");
	fclose($fichier);
}

public static function purgeCaracteresWith($pattern, $str)
{
	$caracteres = array(" ", "é", "è", "ç", "à", "ù", "ü", "û", "â", "ä", "ê", "ë");
	return str_replace($caracteres, $pattern, $str);
}

public static function conv_lib_journee($lib)
{
	$items = explode(':', $lib);
	$num = $items[0];
	$lib = isset($items[1]) ? $items[1] : "";
	$ret = ($lib == "") ? $num.($num == 1 ? "ère" : "ème")." journée" : $lib;

	return $ret;
}

public static function randomKey($chars)
{
	$genChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
	$retkey = "";

   for ($i = 1; $i <= $chars; $i++)
   {
	   $rand = rand(1, strlen($genChars));
	   $retkey .= substr($genChars,$rand -1,1);
   }

   return ($retkey);
}

public static function keyBlock($chars, $blocks)
{
   $key = array();
   for($i = 0; $i < $blocks;$i++)
   {
       //Create an array of keys
       $key[] = ToolBox::randomKey($chars);
   }
   $key = implode("-", $key);

	return($key);
}

public static function ombre($image, $height = -1, $width = -1)
{
	if (!is_readable($image)) return "";

	$taille = GetImageSize($image);
	$img_height = $height != -1 ? $height : $taille[1];
	$img_width  = $width != -1  ? $width  : $taille[0];
	$html = "
<p class=\"shadowed\">
	<img src=\"".$image."\" height=\"".$img_height."\" width=\"".$img_width."\" alt=\"\" />
</p>";

	return $html;
}

public static function ombre2($image, $height = -1, $width = -1)
{
	if (!is_readable($image)) return "";

	$taille = GetImageSize($image);
	$img_height = $height != -1 ? $height : $taille[1];
	$img_width  = $width != -1  ? $width  : $taille[0];
	$html = "<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td rowspan='2' colspan='2'><img src='$image' height='$img_height' width='$img_width' BORDER=0></td>
				<td><img src='../images/ombre_1.gif' BORDER=0></td>
			</tr>
			<tr>
				<td><img src='../images/ombre_2.gif' height='".($img_height-8)."' width='6' BORDER=0></td>
			</tr>
			<tr>
				<td><img src='../images/ombre_3.gif' BORDER=0></td>
				<td><img src='../images/ombre_4.gif' height='8' width='".($img_width-7)."' BORDER=0></td>
				<td><img src='../images/ombre_5.gif' BORDER=0></td>
			</tr>
		</table>";

	return $html;
}

public static function ombre3($image, $height = -1, $width = -1)
{
	if (!is_readable($image)) return "";

	$taille = GetImageSize($image);
	$img_height = $height != -1 ? $height : $taille[1];
	$img_width  = $width != -1  ? $width  : $taille[0];
	$html = "<table border=0 cellpadding=0 cellspacing=0 style=\"border:1px solid black;\">
			<tr>
				<td rowspan='2' colspan='2'><img src='$image' height='$img_height' width='$img_width' BORDER=0></td>
			</tr>
		</table>";

	return $html;
}

public static function hexLighter($hex, $factor = 30)
{
	if ($hex == "") return $hex;

	if (strstr($hex, '#'))
	{
		$items = explode('#', $hex);
		$hex = $items[1];
	}
	else
		return $hex;

	$new_hex = '';

	$base['R'] = hexdec($hex{0}.$hex{1});
	$base['G'] = hexdec($hex{2}.$hex{3});
	$base['B'] = hexdec($hex{4}.$hex{5});

	foreach($base as $k => $v)
	{
		$amount = 255 - $v;
		$amount = $amount / 100;
		$amount = round($amount * $factor);
		$new_decimal = $v + $amount;

		$new_hex_component = dechex($new_decimal);
		if(strlen($new_hex_component) < 2)
			$new_hex_component = "0".$new_hex_component;
		$new_hex .= $new_hex_component;
	}

	return $new_hex;
}

public static function getPuissance2($num)
{
	$i = 0;
	while($num > 1)
	{
		$i++;
		$num = $num / 2;
	}

	return ($num == 1 ? $i : -1);
}

public static function findInArray($item, $tab)
{
	reset($tab);
	while(list($cle, $val) = each($tab))
		if ($val == $item)  return true;

	return false;
}

public static function getArrayString($separator, $tab)
{
	$res = "";
	foreach($tab as $item) $res .= ($res == "" ? "" : $separator).$item;

	return $res;
}

public static function mysqltime2time($date)
{
	$item = explode(' ', $date);

	return ToolBox::mysqldate2date($item[0])." ".$item[1];
}

public static function mysqldate2date($date)
{
	// Gestion des dates du type YYYY/mm/dd HH:mm:ss
	$strtime = explode(' ', $date);
	if (count($strtime) == 2) $date = $strtime[0];

	$item = explode('-', $date);

	return $item[2]."/".$item[1]."/".$item[0];
}

public static function mysqldate2datetime($date)
{
	// Gestion des dates du type YYYY/mm/dd HH:mm:ss
	$strtime = explode(' ', $date);
	if (count($strtime) == 2) $date = $strtime[0];

	$item = explode('-', $date);
	$item2 = explode(':', $strtime[1]);

	return $item[0].$item[1].$item[2].$item2[0].$item2[1].$item2[2];
}

public static function mysqldate2smalldate($date)
{
	// Gestion des dates du type YYYY/mm/dd HH:mm:ss
	$strtime = explode(' ', $date);
	if (count($strtime) == 2) $date = $strtime[0];

	$item = explode('-', $date);

	return $item[2]."/".$item[1];
}

public static function mysqldate2smalldatetime($date)
{
	// Gestion des dates du type YYYY/mm/dd HH:mm:ss
	$strtime = explode(' ', $date);
	if (count($strtime) == 2) $date = $strtime[0];

	$item = explode('-', $date);
	$item2 = explode(':', $strtime[1]);

	return $item[2]."/".$item[1]." ".$item2[0].":".$item2[1];
}

public static function date2mysqldate($date)
{
	$item = explode('/', $date);

	return $item[2]."-".$item[1]."-".$item[0];
}

public static function date2age($date)
{
	$item = explode(strstr($date, "-") ? '-' : '/', $date);

	$yeardiff	= date("Y") - $item[0];
	$monthdiff	= date("m") - $item[1];
	$daydiff 	= date("j") - $item[2];

	if ($monthdiff <= 0 && $daydiff < 0)
	   $age = $yeardiff - 1;
	else
	   $age = $yeardiff;

	return $age;
}
public static function isPdf()
{
	$pdf = ToolBox::get_global("xlist_pdf");

	if ($pdf == 1)	return true;
	else			return false;
}

public static function get_global($variable)
{
	$expr = "global $".$variable."; \$valeur = $".$variable.";";
	eval($expr);

	return(($valeur == "none") ? "" : $valeur);
}

public static function alert($libelle)
{
	echo "<script type=\"text/javascript\">";
	echo "alert('".str_replace("'", "\\'", $libelle)."');";
	echo "</script>";
}

public static function getMyDate()
{
	$jour = date("w");
	switch($jour)
	{
		case 0 : $jour_lib = "Dimanche"; break;
		case 1 : $jour_lib = "Lundi";    break;
		case 2 : $jour_lib = "Mardi";    break;
		case 3 : $jour_lib = "Mercredi"; break;
		case 4 : $jour_lib = "Jeudi";    break;
		case 5 : $jour_lib = "Vendredi"; break;
		case 6 : $jour_lib = "Samedi";   break;
	}

	return $jour_lib.", le ".date("d")."/".date("m")."/".date("Y");
}

public static function reformatDate($date)
{
    return substr($date, 8, 2)."/".substr($date, 5, 2)."/".substr($date, 0, 4);
}

public static function do_redirect_location($url)
{
	Header ("Location: $url");
	echo "<html><head><title>Redirect</title></head><body>"."Please go <a href=\"" . $url . "\">here</a>.</body></html>.\n";

	exit(0);
}


public static function do_redirect($url){

    if (false && !headers_sent()) {
        header('Location: '.$url);
    } else {
        echo '<script type="text/javascript">';
        echo 'top.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }

    exit(0);
}


public static function htmlPrintCelluleWithSpan($libelle, $class, $width, $border, $colspan, $rowspan)
{
	$epaisseur = 1;
	$margin_L  = 3;
	$margin_R  = 3;

	$lib_class   = $class   == "" ? "" : "CLASS=".$class;
	$lib_colspan = $colspan == 1  ? "" : "COLSPAN=".$colspan;
	$lib_rowspan = $rowspan == 1  ? "" : "ROWSPAN=".$rowspan;
	$lib_width   = $width   == "" ? "" : "WIDTH=".$width;

	if ($libelle == "") $libelle = "&nbsp;";

	$l1 = (($border & _CELL_BORDER_LEFT_) | ($border & _CELL_BORDER_TOP_)) ? "c1" : "c0";
	$l2 = ($border  & _CELL_BORDER_TOP_) ? "h1" : "h0";
	$l3 = (($border & _CELL_BORDER_RIGHT_) | ($border & _CELL_BORDER_TOP_)) ? "c1" : "c0";
	$l4 = ($border  & _CELL_BORDER_LEFT_)  ? "v1" : "v0";
	$l5 = ($border  & _CELL_BORDER_RIGHT_) ? "v1" : "v0";
	$l6 = (($border & _CELL_BORDER_LEFT_) | ($border & _CELL_BORDER_BOTTOM_)) ? "c1" : "c0";
	$l7 = ($border & _CELL_BORDER_BOTTOM_) ? "h1" : "h0";
	$l8 = (($border & _CELL_BORDER_RIGHT_) | ($border & _CELL_BORDER_BOTTOM_)) ? "c1" : "c0";

	echo "<TD HEIGHT=\"100%\" ".$lib_width." ".$lib_colspan." ".$lib_rowspan."><TABLE CLASS=t1 CELLPADDING=0 CELLSPACING=0>";
	echo "<TR>";
	echo "<TD CLASS=".$l1."></TD>";
	echo "<TD CLASS=".$l2."></TD>";
	echo "<TD CLASS=".$l3."></TD>";
	echo "<TR>";
	echo "<TD CLASS=".$l4."></TD>";
	echo "<TD CLASS=".$class."><DIV CLASS=cell> ".$libelle." </DIV></TD>";
	echo "<TD CLASS=".$l5."></TD>";
	echo "<TR>";
	echo "<TD CLASS=".$l6."></TD>";
	echo "<TD CLASS=".$l7."></TD>";
	echo "<TD CLASS=".$l8."></TD>";
	echo "</TABLE></TD>\n";
}

public static function htmlPrintCelluleWithColSpan($libelle, $class, $width, $border, $colspan) { ToolBox::htmlPrintCelluleWithSpan($libelle, $class, $width, $border, $colspan, 1); }
public static function htmlPrintCelluleWithRowSpan($libelle, $class, $width, $border, $rowspan) { ToolBox::htmlPrintCelluleWithSpan($libelle, $class, $width, $border, 1, $rowspan); }
public static function htmlPrintCellule($libelle, $class, $width, $border) { ToolBox::htmlPrintCelluleWithSpan($libelle, $class, $width, $border, 1, 1); }

}

?>
