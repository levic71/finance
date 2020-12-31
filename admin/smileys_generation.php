<?

include "../include/constantes.php";

$buffer = "";
$handle = fopen ("../js/smileysv10.js", "w");

$buffer .= "linkset_classique='';\n";
foreach($smileys as $s)
	$buffer .= "linkset_classique += '<DIV CLASS=s_item onMouseOver=\"onover(this);\" onMouseOut=\"onout(this);\"><IMG SRC=\"../forum/smileys/".$s."\" BORDER=0 onClick=\"onclic(this, event);\"></DIV>';\n";
$buffer .= "linkset_buddys1='';\n";
foreach($buddys1 as $s)
	$buffer .= "linkset_buddys1 += '<DIV CLASS=s_item onMouseOver=\"onover(this);\" onMouseOut=\"onout(this);\"><IMG SRC=\"../forum/buddys1/".$s."\" BORDER=0 onClick=\"onclic(this, event);\"></DIV>';\n";
$buffer .= "linkset_buddys2 = '';\n";
foreach($buddys2 as $s)
	$buffer .= "linkset_buddys2 += '<DIV CLASS=s_item onMouseOver=\"onover(this);\" onMouseOut=\"onout(this);\"><IMG SRC=\"../forum/buddys2/".$s."\" BORDER=0 onClick=\"onclic(this, event);\"></DIV>';\n";
$buffer .= "linkset_buddys3 = '';\n";
foreach($buddys3 as $s)
	$buffer .= "linkset_buddys3 += '<DIV CLASS=s_item onMouseOver=\"onover(this);\" onMouseOut=\"onout(this);\"><IMG SRC=\"../forum/buddys2/".$s."\" BORDER=0 onClick=\"onclic(this, event);\"></DIV>';\n";

fputs($handle, $buffer);
fclose($handle);
