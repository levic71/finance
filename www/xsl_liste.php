<?

define("_XML_PARSE_DEBUG_",			1);
define("_XML_PARSE_CHAMPIONNAT_",	2);
define("_XML_PARSE_CLASSEMENT_",	3);

// ///////////////////////////////////////////////////////////////////////////
// TRTS FICHIER CHAMPIONNAT XML
// ///////////////////////////////////////////////////////////////////////////
function parse_domelement_attributes_championnat($attributs)
{
	$tab = array();
	
	// Parcours du tableau des attributs
	while(list($cle, $val) = each($attributs))
		$tab[$val->name] = $val->value;
		
	echo "<DIV STYLE=\"width:300px;\">";
	echo "<DIV>".$tab['NOM']."::".$tab['SAISON_NOM']."</DIV>";
	echo "<DIV STYLE=\"margin-left: 50px;text-align:right;\">Prochaine journée: ".$tab['NEXT_JOURNEE']."</DIV>";
	echo "<DIV STYLE=\"margin-left: 50px;text-align:right;\">Dernière journée: ".$tab['LAST_JOURNEE']."</DIV>";
	echo "<DIV STYLE=\"margin-left: 50px;text-align:right;\"><A HREF=\"".$tab['URL']."\">Accès au classement</A></DIV>";
	echo "</DIV>";
}

function parse_domelement_children_championnat($children)
{
	// Parcours du tableau des enfants
	while(list($cle, $val) = each($children))
		if (is_a($val, "domelement")) parse_domelement_championnat($val);
}

function parse_domelement_championnat($elt)
{
	if (isset($elt->attributes)) parse_domelement_attributes_championnat($elt->attributes);
	if (isset($elt->children))   parse_domelement_children_championnat($elt->children);
}


// ///////////////////////////////////////////////////////////////////////////
// TRTS FICHIER CLASSEMENT XML
// ///////////////////////////////////////////////////////////////////////////
function parse_domelement_attributes_classement($attributs, $tag)
{
	$tab = array();
	
	// Parcours du tableau des attributs
	while(list($cle, $val) = each($attributs))
		$tab[$val->name] = $val->value;
	
	if ($tag == "EQUIPE")
	{
		echo "<TR><TD>".$tab['CLASSEMENT']."</TD>";
		echo "    <TD>".$tab['NOM']."</TD>";
		echo "    <TD>".$tab['POINTS']."</TD>";
		$m = explode('|', $tab['MATCHS']);
		echo "    <TD>".$m[0]."</TD>";
		echo "    <TD>".$m[1]."</TD>";
		echo "    <TD>".$m[2]."</TD>";
		$s = explode('|', $tab['SETS']);
		echo "    <TD>".$s[0]."</TD>";
		echo "    <TD>".$s[1]."</TD>";
		echo "    <TD>".$s[2]."</TD>";
		echo "    <TD>".$s[3]."</TD>";
		$b = explode('|', $tab['BUTS']);
		echo "    <TD>".$b[0]."</TD>";
		echo "    <TD>".$b[1]."</TD>";
		echo "    <TD>".$b[2]."</TD>";
		echo "    <TD>".$tab['MOYENNE']."</TD>";
	}
}

function parse_domelement_children_classement($children)
{
	// Parcours du tableau des enfants
	while(list($cle, $val) = each($children))
		if (is_a($val, "domelement")) parse_domelement_classement($val);
}

function parse_domelement_classement($elt)
{
	if ($elt->tagname == "CLASSEMENT") echo "<TABLE BORDER=0>";
	if (isset($elt->attributes)) parse_domelement_attributes_classement($elt->attributes, $elt->tagname);
	if (isset($elt->children))   parse_domelement_children_classement($elt->children);
	if ($elt->tagname == "CLASSEMENT") echo "</TABLE>";
}


// ///////////////////////////////////////////////////////////////////////////
// TRTS GENERIQUE XML
// ///////////////////////////////////////////////////////////////////////////
function parse_log($str)
{
	echo $str."<BR>";
}

function parse_domelement_attributes($attributs)
{
	parse_log("parse_domelement_attributes: début");
	
	// Parcours du tableau des attributs
	while(list($cle, $val) = each($attributs))
		parse_log($val->name."=".$val->value);
		
	parse_log("parse_domelement_attributes: fin");
}

function parse_domelement_children($children)
{
	parse_log("parse_domelement_children: début");
	
	// Parcours du tableau des enfants
	while(list($cle, $val) = each($children))
		if (is_a($val, "domelement")) parse_domelement($val);

	parse_log("parse_domelement_children: fin");
}

function parse_domelement($elt)
{
	parse_log("parse_domelement: début");
	parse_log($elt->tagname);
	if (isset($elt->attributes)) parse_domelement_attributes($elt->attributes);
	if (isset($elt->children))   parse_domelement_children($elt->children);
	parse_log("parse_domelement: fin");
}

function xmlparse($objet_chaine, $xml_type = _XML_PARSE_DEBUG_)
{
	if ($xml_type == _XML_PARSE_DEBUG_) parse_log("parse: début");
	
	$tab = domxml_xmltree($objet_chaine);

	// On récupère les infos du fichier XML
	while(list($cle, $val) = each($tab))
	{
		// Récupération des balises de premier niveau
		if (is_array($val))
		{
			while(list($cle2, $val2) = each($val))
			{
				if (is_a($val2, "domelement"))
				{
					if ($xml_type == _XML_PARSE_CHAMPIONNAT_)
						parse_domelement_championnat($val2);
					else if ($xml_type == _XML_PARSE_CLASSEMENT_)
						parse_domelement_classement($val2);
					else
						parse_domelement($val2);
				}
			}
		}
		else
			if ($xml_type == _XML_PARSE_DEBUG_) parse_log("parse: ".$cle."=".$val);
	}
	if ($xml_type == _XML_PARSE_DEBUG_) parse_log("parse: fin");
}

function xmlparse_file($file, $xml_type = _XML_PARSE_DEBUG_)
{
	$objet_chaine = implode("", file($file));
	xmlparse($objet_chaine, $xml_type);
}

function xmlparse_url($url, $params = "", $xml_type = _XML_PARSE_DEBUG_)
{
	$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result=curl_exec($ch);
	curl_close($ch);
	
	xmlparse($result, $xml_type);
}

?>
<HTML>
<BODY>
<?

//xmlparse_file("liste.xml");
//xmlparse_url("http://localhost/jorkyball/www/xml_liste.php");
//xmlparse_url("http://localhost/jorkyball/www/xml_championnat.php", "id_championnat=17", _XML_PARSE_CHAMPIONNAT_);
//xmlparse_url("http://localhost/jorkyball/www/xml_championnat.php", "id_championnat=23", _XML_PARSE_CHAMPIONNAT_);
xmlparse_url("http://localhost/jorkyball/www/xml_classement.php", "id_championnat=17", _XML_PARSE_CLASSEMENT_);

?>
</BODY>
</HTML>