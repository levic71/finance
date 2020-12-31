<?

// Constantes
define ("_CELLBORDER_NONE_",	   "cb0");
define ("_CELLBORDER_LEFT_",	   "cb1");
define ("_CELLBORDER_TOP_",		   "cb2");
define ("_CELLBORDER_RIGHT_",	   "cb3");
define ("_CELLBORDER_BOTTOM_",	   "cb4");
define ("_CELLBORDER_ALL_",		   "cb5");
define ("_CELLBORDER_U_",		   "cb6");
define ("_CELLBORDER_U90_",		   "cb7");
define ("_CELLBORDER_U180_",	   "cb8");
define ("_CELLBORDER_U270_",	   "cb9");
define ("_CELLBORDER_SE_",		   "cb10");
define ("_CELLBORDER_SW_",		   "cb11");
define ("_CELLBORDER_NE_",		   "cb12");
define ("_CELLBORDER_NW_",		   "cb13");
define ("_CELLBORDER_HORIZONTAL_", "cb14");
define ("_CELLBORDER_VERTICAL_",   "cb15");
define ("_CELLBORDER_T1_",         "cb16");
define ("_CELLBORDER_T2_",         "cb17");
define ("_CELLBORDER_T3_",         "cb18");
define ("_CELLBORDER_T4_",         "cb19");
define ("_CELLBORDER_T5_",         "cb20");

class HTMLTable
{

function HTMLTable() {
}

function printFillSeparator($color = "black", $colspan = 1)
{
	$lib_color = $color   == "" ? "" : "bgcolor=\"".$color."\"";
	$lib_cspan = $colspan == 1  ? "" : "colspan=\"".$colspan."\"";

	echo "<tr><td height=\"1\" ".$lib_color." ".$lib_cspan."> </td></tr>";
}

function printSeparator($colspan = 1)
{
	HTMLTable::printFillSeparator("", $colspan);
}

function newLine() {
	echo "<tr>";
}

function printCellWithSpan($libelle, $color, $width, $alg, $border, $colspan, $rowspan, $tag = "td", $extra = "")
{
	$lib_color = $color == "" ? "" : "background-color:".$color.";";
	$lib_width = $width == "" ? "" : "width:".$width.";";
	$lib_align = $alg == "" ? ""   : "text-align:".$alg.";";
	$style = "style=\"".$lib_color.$lib_width.$lib_align."\"";
	$lib_cspan = $colspan == 1 ? "" : "colspan=\"".$colspan."\"";
	$lib_rspan = $rowspan == 1 ? "" : "rowspan=\"".$rowspan."\"";
?>
	<<?= $tag ?> <?= $style." ".$lib_cspan." ".$lib_rspan." ".$extra ?> class="<?= $border ?>"> <?= $libelle ?> </<?= $tag ?>>
<?
}

function printCellWithColSpan($libelle, $color, $width, $alg, $border, $colspan) { HTMLTable::printCellWithSpan($libelle, $color, $width, $alg, $border, $colspan, 1); }
function printCellWithRowSpan($libelle, $color, $width, $alg, $border, $rowspan) { HTMLTable::printCellWithSpan($libelle, $color, $width, $alg, $border, 1, $rowspan); }
function printCell($libelle, $color, $width, $alg, $border) { HTMLTable::printCellWithSpan($libelle, $color, $width, $alg, $border, 1, 1); }
function printCellHR($libelle, $color, $width, $alg, $border, $extra = "") { HTMLTable::printCellWithSpan($libelle, $color, $width, $alg, $border, 1, 1, "th", $extra); }

}

?>
