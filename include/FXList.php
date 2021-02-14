<?

// ////////////////////////////////////////////////////////////////////////////
// PRE-REQUIS:
// ////////////////////////////////////////////////////////////////////////////
// pré-include HTMLTable.php
// + inc_db.php si utilisation de FXBodySQL + accès base ouvert
// ////////////////////////////////////////////////////////////////////////////

// Pour le bon fonctionnement des cookies aucune instruction envoyant des paquets http (sauf entête) ne doit être exécutée
// Pas de echo, ...

if (isset($FXDeltaChoice) && $FXDeltaChoice != "")
{
	setcookie("FXDelta", $FXDeltaChoice, time()+(3600*24*30*6));
	$FXDelta = $FXDeltaChoice;
}

// ////////////////////////////////////////////////////////////////////////////

define("_FXLIST_FULL_",   -1);
define("_FXLIST_EXPORT_", -2);
define("_FXLINESEPARATOR_",     "FXLINESEP");
define("_FXSEPARATOR_",         "FXSEP");
define("_FXSEPARATORWITHINIT_", "FXSEPWITHINIT");

class FXBody
{
	var $start;
	var $delta;
	var $size;
	var $tab;
	var $nb_cols;
	var $option;
	var $search;

	function __construct()
	{
		global $FXStart, $FXOption, $FXDelta, $FXSearch;

		// On récupére la valeur de début en global si elle existe, sinon 0 ...
		$this->start   = isset($FXStart) && $FXStart != "" ? $FXStart : 0;
		$this->delta   = isset($FXDelta) && $FXDelta != "" ? $FXDelta : 10;
		$this->tab     = array();
		$this->nb_cols = 0;
		$this->option  = isset($FXOption) && $FXOption != "" ? $FXOption : "";
		$this->search  = isset($FXSearch) && $FXSearch != "" ? $FXSearch : "";
	}

	function joinElements($tableau)
	{
		$str = "";

		if (!is_array($tableau) || count($tableau) == 0) return $str;

		reset($tableau);
		foreach($tableau as $elt) $str .= $elt;

		return $str;
	}

	function search($tableau, $search)
	{
		$tab = array();

		if (!is_array($tableau) || count($tableau) == 0) return $tab;

		reset($tableau);
		foreach($tableau as $elt)
		{
			$row = is_object($elt) ? get_object_vars($elt) : $elt;
			if (preg("/".$search."/i", preg_replace("/<.*>/i", "", urldecode($this->joinElements($row))))) $tab[] = $row;
		}

		return $tab;
	}
}

class FXBodyArray extends FXBody
{
	var $tableau;

	function __construct($tableau, $delta = "")
	{
		parent::__construct();

		if (is_array($tableau) && count($tableau) > 0)
		{
			// Astuce pour afficher la liste complète
			if ($delta == _FXLIST_FULL_) $this->option = _FXLIST_FULL_;

			if ($delta != "") $this->delta = $delta;

			$ref_tableau = $this->search == "" ? $tableau : $this->search($tableau, $this->search);

			$tmp = $this->option == _FXLIST_FULL_ || $this->option == _FXLIST_EXPORT_ ? $ref_tableau : array_slice($ref_tableau, $this->start, $this->delta);
			foreach($tmp as $elt)
			{
				$row = is_object($elt) ? get_object_vars($elt) : $elt;
				$this->tab[] = $row;
				$this->nb_cols = count(array($row));
			}

			$this->size = count($ref_tableau);

			if ($this->option == _FXLIST_FULL_ || $this->option == _FXLIST_EXPORT_) $this->delta = $this->size;
		}
	}
}

class FXBodySQL extends FXBody
{
	var $requete;

	function __construct($requete, $delta = "")
	{
		$this->requete = $requete;

		parent::__construct();

		// Astuce pour afficher la liste complète
		if ($delta == _FXLIST_FULL_) $this->option = _FXLIST_FULL_;

		if ($delta != "") $this->delta = $delta;

		$tmp = ($this->option == _FXLIST_FULL_ || $this->option == _FXLIST_EXPORT_ || $this->search != "") ? $this->requete : $this->requete." LIMIT ".$this->start.",".$this->delta;
	   	$res = dbc::execSql($tmp);
	   	while($row = mysqli_fetch_array($res))
		{
			$this->tab[]   = $row;
			$this->nb_cols = count($row);
		}

		if ($this->search == "")
		{
		   	$res = dbc::execSql(preg_replace("/ORDER BY.*$/i", "", preg_replace("/^.*FROM/i", "SELECT count(*) total FROM", $this->requete)));
		   	$row = mysqli_fetch_array($res);
			$this->size = $row['total'];
		}
		else
		{
			$ref_tableau = $this->search($this->tab, $this->search);
			$this->tab = $this->option == _FXLIST_FULL_ || $this->option == _FXLIST_EXPORT_ ? $ref_tableau : array_slice($ref_tableau, $this->start, $this->delta);
			$this->size = count($ref_tableau);
		}

		if ($this->option == _FXLIST_FULL_ || $this->option == _FXLIST_EXPORT_) $this->delta = $this->size;
	}
}

class FXList
{
	var $table_id;
	var $body;
	var $body_id;
	var $img_id;
	var $title;
	var $titleAlign;
	var $columnsDisplayed;
	var $columnsNames;
	var $columnsWidth;
	var $columnsAlign;
	var $columnsColor;
	var $columnsPadBefore;
	var $columnsPadAfter;
	var $pagination;
	var $url;
	var $numerotation;
	var $numeroInverse;
	var $arrayProperties;
	var $footer;
	var $nb_cols;
	var $extraIcons;
	var $mouseOverEffect;
	var $color_numero_title;
	var $color_numero_column;
	var $c1_title;
	var $c1_column;
	var $c2_title;
	var $c2_column;
	var $c3_title;
	var $c3_column;
	var $color_action_column;
	var $color_title_column;
	var $sortable;

	function __construct($body)
	{
		$this->table_id            = "mytable";
		$this->body                = $body;
		$this->body_id             = "FXList_Body_".ToolBox::keyBlock(5, 1);
		$this->img_id              = "FXList_Img_".ToolBox::keyBlock(5, 1);
		$this->title               = "";
		$this->titleAlign          = "center";
		$this->columnsDisplayed    = array();
		$this->columnsNames        = array();
		$this->columnsSort         = array();
		$this->columnsWidth        = array();
		$this->columnsAlign        = array();
		$this->columnsColor        = array();
		$this->columnsPadBefore    = array();
		$this->columnsPadAfter     = array();
		$this->pagination          = false;
		$this->url                 = "";
		$this->numerotation        = true;
		$this->numero_inserve      = false;
		$this->arrayProperties     = "";
		$this->footer			   = "";
		$this->nb_cols			   = 0;
		$this->extraIcons          = "";
		$this->mouseOverEffect     = true;
		$this->sortable            = false;
		/*
		$this->color_numero_title  = "#BCC5EA";
		$this->color_numero_column = "#DEDFF1";
		$this->c1_title            = "#EFAB5B";
		$this->c1_column           = "#FEE5AC";
		$this->c2_title            = "#7CCD7C";
		$this->c2_column           = "#B4EEB4";
		$this->c3_title            = "#EEA2AD";
		$this->c3_column           = "#FFC0CB";
		$this->color_action_column = "#D5D9EA";
		*/
		$this->color_numero_title  = "#BBBBBB";
		$this->color_numero_column = "#DCDCDC";
		$this->c1_title            = "#EFAB5B";
		$this->c1_column           = "#FEE5AC";
		$this->c2_title            = "#7CCD7C";
		$this->c2_column           = "#B4EEB4";
		$this->c3_title            = "#EEA2AD";
		$this->c3_column           = "#FFC0CB";
		$this->color_action_column = "#DCDCDC";
		$this->color_title_column  = "#BBBBBB";
	}

	function FXSetTitle($title, $alg = "center") { $this->title = $title; $this->titleAlign = $alg; }
	function FXSetNbCols($nb)              { $this->nb_cols = $nb; }
	function FXSetColumnsDisplayed($cols)  { $this->columnsDisplayed = $cols; }
	function FXSetColumnsName($cols)       { $this->columnsNames = $cols; }
	function FXSetColumnsSort($sort)       { $this->columnsSort = $sort; }
	function FXSetColumnsWidth($width)     { $this->columnsWidth = $width; }
	function FXSetColumnsAlign($align)     { $this->columnsAlign = $align; }
	function FXSetColumnsColor($color)     { $this->columnsColor = $color; }
	function FXSetColumnsPadBefore($pad)   { $this->columnsPadBefore = $pad; }
	function FXSetColumnsPadAfter($pad)    { $this->columnsPadAfter = $pad; }
	function FXSetArrayProperties($properties) { $this->arrayProperties = $properties; }
	function FXSetFooter($footer)  	       { $this->footer = $footer; }
	function FXSetNumerotation($bool)	   { $this->numerotation  = $bool; }
	function FXSetNumeroInverse($bool)	   { $this->numeroInverse = $bool; }
	function FXSetExtraIcons($icons)	   { $this->extraIcons = $icons; }
	function FXSetMouseOverEffect($bool)   { $this->mouseOverEffect = $bool; }
	function FXSetNumerotationColorTitle($color)	   { $this->color_numero_title  = $color; }
	function FXSetNumerotationColorColumn($color)	   { $this->color_numero_column = $color; }
	function FXSetSortable($bool)	       { $this->sortable = $bool; }
	function FXSetTableId($str)	           { $this->table_id = $str; }

	function FXSetPagination($url)
	{
		$this->pagination = true;
		$this->url = $url;
	}

	function FXDisplay($enveloppe_end = true)
	{
		// Nb cols
		if ($this->nb_cols == 0) $this->nb_cols = count($this->columnsNames) > 0 ? count($this->columnsNames) : $this->body->nb_cols;
		if ($this->numerotation) $this->nb_cols++;

		// Début du tableau
		echo "\n<table border=\"0\" id=\"".$this->table_id."\" class=\"FXList_TABLE\" cellpadding=\"0\" cellspacing=\"0\" ".$this->arrayProperties." summary=\"\">\n";

		// Affichage du titre
		$this->FXDisplayTitle();

		// Affichage des noms de colonnes
		echo "\n<thead class=\"FXList_HEAD\">\n";
		$this->FXDisplayColumnsName();
		echo "</thead>\n";

		// Affichage des lignes
		$this->FXDisplayBody();

		// Affichage de la pagination
		echo "<tfoot class=\"FXList_FOOT\">\n";
		$this->FXDisplayPagination();

		// Affichage du pied de tableau
		$this->FXDisplayFooter();
		echo "</tfoot>\n";

		// Fin du tableau
		if ($enveloppe_end) echo "\n</table>\n";

		// Script pour tri colonne
		if ($this->sortable) { ?>
<script>
sortableManager = new SortableManager();
addLoadEvent(function () {
	sortableManager.initWithTable('<?= $this->table_id ?>');
});
</script>
		<? }
	}

	function FXDisplayTitle()
	{
		if ($this->title != "")
		{
			echo "<caption class=\"FXList_CAPTION\">".$this->title."</caption>\n";
		}
	}

	function FXDisplayColumnsName()
	{
		if (count($this->columnsNames) > 0)
		{
			echo "<tr style=\"height: 25px;\">";
			$i = 0;

			if ($this->numerotation)
				HTMLTable::printCellHR("N°", $this->color_numero_title, "3%", "center", _CELLBORDER_U_, $this->sortable ? "mochi:format=\"number\"" : "");

			reset($this->columnsNames);
			foreach($this->columnsNames as $name)
			{
				$sort = $this->sortable && isset($this->columnsSort[$i]) ? "mochi:format=\"".$this->columnsSort[$i]."\"" : "";
				$border =  ($i++ > 0 || $this->numerotation) ? _CELLBORDER_SE_ : _CELLBORDER_U_;
				HTMLTable::printCellHR($name, $this->color_title_column, "", "center", $border, $sort);
			}
			echo "</tr>";
		}
	}

	function FXDisplayBody()
	{
		if (count($this->body->tab) == 0) return;

		echo "<tbody class=\"FXList_BODY\">\n";
		$k = 1;
		$nb = 1;
		$counter = 0;
		$nb_rows = count($this->body->tab);
		reset($this->body->tab);
		while(list($cle, $val) = each($this->body->tab))
		{
			echo "<tr id=\"tr".$k++."\" ".($this->mouseOverEffect ? "onmouseover=\"this.className='on';\" onmouseout=\"this.className='off';\"" : "").">";

			if ($val == _FXLINESEPARATOR_)
			{
				echo "<tr><td colspan=\"".$this->nb_cols."\" style=\"background-color: navy; height: 1px;\"></td></tr>";
				continue;
			}

			if ($val == _FXSEPARATOR_)
			{
				HTMLTable::printCellWITHCOLSPAN("", $this->color_title_column." height=2", "", "center", _CELLBORDER_U_, $this->nb_cols);
				continue;
			}

			if ($val == _FXSEPARATORWITHINIT_)
			{
				HTMLTable::printCellWITHCOLSPAN("", $this->color_title_column." height=2", "", "center", _CELLBORDER_U_, $this->nb_cols);
				$nb = 1;
				continue;
			}

			// On récupère les colonnes à afficher
			$col2display = count($this->columnsDisplayed) > 0 ? $this->columnsDisplayed : array_keys($val);

			$i = 0;

			if ($this->numerotation)
			{
				$num = $this->numeroInverse ? $this->body->size - $this->body->start - ($nb++) + 1 : $this->body->start+($nb++);
				HTMLTable::printCell($num, $this->color_numero_column, "", "center", $nb_rows == $counter ? _CELLBORDER_T4_ : _CELLBORDER_T1_);
			}

			$nb_cols = count($col2display);
			reset($col2display);
			foreach($col2display as $column)
			{
				$padB   = isset($this->columnsPadBefore[$i]) ? $this->columnsPadBefore[$i] : "";
				$padA   = isset($this->columnsPadAfter[$i])  ? $this->columnsPadAfter[$i]  : "";
				$width  = isset($this->columnsWidth[$i]) ? $this->columnsWidth[$i] : "";
				$align  = isset($this->columnsAlign[$i]) && $this->columnsAlign[$i] != "" ? $this->columnsAlign[$i] : "center";
				$color  = isset($this->columnsColor[$i]) ? $this->columnsColor[$i] : "";
				if ($i++ > 0 || $this->numerotation)
				{
					$border = $i == $nb_cols ? ($nb_rows == $counter ? _CELLBORDER_SE_ : _CELLBORDER_T3_) : ($nb_rows == $counter ? _CELLBORDER_T5_ : _CELLBORDER_T2_);
				}
				else
				{
					$border = $nb_rows == $counter ? _CELLBORDER_T4_ : _CELLBORDER_T1_;
				}

				HTMLTable::printCell($padB.(strlen($val[$column]) == 0 ? "&nbsp;" : $val[$column]).$padA, $color == "" ? "" : "#".ToolBox::hexLighter($color, $counter), $width, $align, $border);
			}
			echo "</tr>";
		}
		echo "</tbody>\n";
	}

	function FXDisplayPagination()
	{
		if ($this->pagination && $this->body->option != _FXLIST_FULL_&& $this->body->option != _FXLIST_EXPORT_)
		{
			$last_start = $this->body->delta == 0 ? 0 : floor($this->body->size / $this->body->delta) * $this->body->delta;
			if ($last_start == $this->body->size) $last_start = $this->body->size - $this->body->delta;
			if ($last_start < 0) $last_start = 0;

			$next_start = $this->body->start + $this->body->delta;
			if ($next_start >= $this->body->size) $next_start = $last_start;

			$prev_start = $this->body->start - $this->body->delta;
			if ($prev_start < 0) $prev_start = 0;

			$cur_page = $this->body->delta == 0 ? 0 : ($this->body->start / $this->body->delta) + 1;
			$max_page = $this->body->delta == 0 ? 0 : ceil($this->body->size / $this->body->delta);
			if ($max_page == 0) $cur_page = 0;

			$new_url = (strstr($this->url, "?") ? $this->url."&amp;" : $this->url."?").($this->body->search == "" ? "" : "FXSearch=".urlencode($this->body->search)."&amp;");

?>
<input type="hidden" name="FXDeltaChoice" value="<?= $this->body->delta ?>"" />
<input type="hidden" name="FXSearch"      value="<?= $this->body->search ?>" />
<?
			echo "<tr>";
			$bottom  = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" summary=\"\">";
			$bottom .= "<tr><td width=\"120\" align=\"center\">".$this->extraIcons."</td>";
			$bottom .= "<td align=\"center\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" summary=\"\"><tr>";
			$bottom .= "<td><a href=\"".$new_url."FXStart=0\"><img src=\"../images/arrow_ll.gif\" alt=\"Première page\" /></a></td>";
			$bottom .= "<td><a href=\"".$new_url."FXStart=".$prev_start."\"><img src=\"../images/arrow_l.gif\"  alt=\"Page précédente\" /></a></td>";
			$bottom .= "<td><a href=\"".$new_url."FXStart=".$next_start."\"><img src=\"../images/arrow_r.gif\"  alt=\"Page suivante\" /></a></td>";
			$bottom .= "<td><a href=\"".$new_url."FXStart=".$last_start."\"><img src=\"../images/arrow_rr.gif\" alt=\"Dernière page\" /></a></td>";
			$bottom .= "</tr></table></td>";
			$display_search = $this->body->search == "" ? "style=\"display:none;\"" : "";
			$bottom .= "<td width=\"25\" align=\"center\"><table border=0><td><a href=\"#\" onclick=\"javascript:controlDisplaySearch();\"><img src=\"../images/search.gif\"   alt=\"Rechercher\" /></a></td><td id=\"search1\" ".$display_search."><input type=text name=FXSearch size=16 value=\"".$this->body->search."\"></input></td><td id=\"search2\" ".$display_search."><input type=submit value=Rechercher></td></table></td>";
			$bottom .= "<td width=\"25\" align=\"center\"><table border=0><td><a href=\"#\" onclick=\"javascript:controlDisplayNbline();\"><img src=\"../images/numligne.gif\" alt=\"Nombre de lignes dans la liste\" /></a></td><td id=nbline1 style=\"display: none;\"><select name=FXDeltaRadio onchange=\"javascript:callFXDeltaChange(this);\"><option value=10 ".($this->body->delta == 10 ? "selected" : "")."> 10 lignes</option><option value=15 ".($this->body->delta == 15 ? "selected" : "")."> 15 lignes</option><option value=20 ".($this->body->delta == 20 ? "selected" : "")."> 20 lignes</option></select></td></table></td>";
			$bottom .= "<td width=\"25\" align=\"center\"><a href=\"#\" onclick=\"javascript:window.open('".$new_url."FXOption="._FXLIST_EXPORT_."', '', 'width=720, height=500, screenX=50, screenY=50, pageXOffset=50, pageYOffset=50, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=yes, resizable=yes, scrollbars=yes');\"><img src=\"../images/extlist.gif\" alt=\"Affichage complet de la liste\" /></a></td>";
			$bottom .= "<td width=\"50\" align=\"center\">".$cur_page."/".$max_page."</td></tr></table>";
			HTMLTable::printCellWithColSPAN($bottom, $this->color_title_column, "", "center", _CELLBORDER_U_, $this->nb_cols);
			echo "</tr>";
?>
<script type="text/javascript">
callFXDeltaChange = function(obj)
{
	nb_sel=obj.length;
	for(i=0; i < nb_sel; i++)
	{
		if (obj.options[i].selected == true)
		{
			document.forms[0].FXDeltaChoice.value=obj.options[i].value;
		}
	}
	document.forms[0].action = '<?= $new_url ?>';
	document.forms[0].submit();
}
controlDisplaySearch = function()
{
	if (document.getElementById('search1').style.display == 'none')
	{
		document.getElementById('search1').style.display='block';
		document.getElementById('search2').style.display='block';
	}
	else
	{
		document.getElementById('search1').style.display='none';
		document.getElementById('search2').style.display='none';
	}
}
controlDisplayNbline = function()
{
	if (document.getElementById('nbline1').style.display == 'none')
	{
		document.getElementById('nbline1').style.display='block';
	}
	else
	{
		document.getElementById('nbline1').style.display='none';
	}
}
</script>
<?
		}
	}

	function FXDisplayFooter()
	{
		if ($this->footer != "")
		{
			echo "<tr>\n";
			echo "<td style=\"".($this->color_numero_column != "" ? "background-color: ".$this->color_numero_column.";" : "")." text-align: center;\" colspan=\"".$this->nb_cols."\"> ".$this->footer." </td>";
			echo "</tr>\n";

//			echo "<TR>";
//			HTMLTable::printCellWithColSPAN($this->footer, $this->color_title_column, "", "center", "", $this->nb_cols);
		}
	}

	function FXHTLMExportBegin()
	{
		TemplateBox::htmlBegin();
		echo "<tr><td align=\"center\">";
	}

	function FXHTLMExportEnd()
	{
		echo "</td></tr>";
		TemplateBox::htmlEnd();
	}
}

?>
