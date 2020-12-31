<?

include "../include/pdf_class.php";

// ///////////////////////////////////////////////////////////////////////////////////////////////////
// GenTab																					29/09/2001
// ///////////////////////////////////////////////////////////////////////////////////////////////////
//
// Classe permettant la gestion de l affichage d un tableau avec pagination virtuelle
//
// Nécessite xlist_style.css
//
// ///////////////////////////////////////////////////////////////////////////////////////////////////
//
class XList
{

var $tableProp;					// Propriétés de la balise TABLE
var $title;						// Titre du tableau GenTab
var $columnsNames;				// Tableau qui contient les noms des colonnes
var $columnsNamesTRProp;		// Propriétés de la balise TR pour les colonnes
var $columnsAlign;				// Alignement des colonnes
var $nbColumns;					// Nb de colonnes
var $pairRowTRProp;				// Propriétés de la balise TR pour les lignes paires
var $unpairRowTRProp;			// Propriétés de la balise TR pour les lignes impaires
var $columnsTDProps;			// Tableau qui contient les propriétés des TD des cellules
var $nbRowsAdded = 0;			// Nb d enregistements ajoutés à l objet GenTab
var $nbDisplayRows = 10;		// Nb d enregistrements maximum à afficher
var $nbRows = 0;				// Nb d enregistrements total (potentiellement affichable avec la pagination virtuelle)
var $firstNumRowDisplay = 0;	// Position relative dans la pagination de la première ligne affichée
var $rowsTab;					// Tableau qui contient les enregistrement à afficher
var $numerotation = 0;			// Flag pour savoir si on affiche les N° de ligne
var $colGroup;					// Tableau qui contient la description des groupes de colonnes
var $footer = 1;				// Flag pour savoir si on affiche le footer
var $target;					// Lien pour la navigation virtuelle
var $search = "";				// Variable de recherche
var $requete;					// Requête SQL
var $mappingColumns;			// Mapping des colonnes à afficher
var $nbPagePdf   = 0;			// Nombre de pages PDF
var $margexPdf   = 30;
var $margeyPdf   = 60;
var $tabwidthPdf = 0;
var $font_normal;
var $font_bold;
var $tabfontsize = 8.0;
var $displayColAction = false;	// Gestion affichage colonne ACTION
var $displayAll = false;        // Permet de savoir si on affiche tout la liste
var $statusHtmlCode;            // Code HTML personnalisé inclut dans la status barre

function XList()
{
    if ($this->isPdf()) $this->setNbDisplayRows(-1);
    $display_all = ToolBox::get_global("display_all");
    if ($display_all == "1") $this->displayAll = true;
}

function setMappingColumns($mappingColumns)
{
	$i = 0;
	while(list($cle, $valeur) = each($mappingColumns))
	{
		$this->mappingColumns[$i] = $valeur;
		$i++;
	}
	$this->nbColumns = $i;
}

function setStatusHtmlCode($code)       { $this->statusHtmlCode   = $code; }
function setDisplayColAction($action)	{ $this->displayColAction = $action; }
function setRequete($requete)			{ $this->requete = $requete; }
function setTargetStatement($target)	{ $this->target = $target; }
function setSearchStatement($search)	{ $this->search = $search; }
function setTitle($title)				{ $this->title = $title; }
function setNumerotationOn()			{ $this->numerotation = 1; }
function setNumerotationOff()			{ $this->numerotation = 0; }
function setFooterOn()					{ $this->footer = 1; }
function setFooterOff()					{ $this->footer = 0; }
function setTableProp($propriete)		{ $this->tableProp = $propriete; }
function setTRPropPairRow($propriete)	{ $this->pairRowTRProp = $propriete; }
function setTRPropUnpairRow($propriete)	{ $this->unpairRowTRProp = $propriete; }
function setTRPropColumnsNames($propriete)	{ $this->columnsNamesTRProp = $propriete; }
function setNbRows($nbRows)					{ $this->nbRows = $nbRows; }
function setFirstNumRowDisplay($firstNumRowDisplay)		{ $this->firstNumRowDisplay = $firstNumRowDisplay; }

function isPdf()
{
	$pdf = ToolBox::get_global("xlist_pdf");

	if ($pdf == 1)	return true;
	else			return false;
}

function setNbDisplayRows($nbDisplayRows)
{
    if (!$this->displayAll)  $this->nbDisplayRows = $nbDisplayRows;
}

function setColumnsNames($columnsNames)
{
	$i = 0;
	while(list($cle, $valeur) = each($columnsNames))
	{
		$this->columnsNames[$i] = $valeur;
		$i++;
	}
}

function setColumnsProps($columnsTDProps)
{
	$i = 0;
	while(list($cle, $valeur) = each($columnsTDProps))
	{
		$this->columnsTDProps[$i] = $valeur;
		$i++;
	}
}

function setColumnsAlign($columnsAlign)
{
	$i = 0;
	while(list($cle, $valeur) = each($columnsAlign))
	{
		$this->columnsAlign[$i] = $valeur;
		$i++;
	}
}

function setColGroup($colGroup)
{
	$i = 0;
	while(list($cle, $valeur) = each($colGroup))
	{
		$this->colGroup[$i] = $valeur;
		$i++;
	}
}

function addRow($row)
{
	$this->rowsTab[] = $row;
	$this->nbRowsAdded++;
}

function executeSQL()
{
	$res = mysqli_query($this->requete) or die ("Couln't execute query : ".$this->requete);

	if ($res)
	{
		$i = 0;
		$this->setNbRows(mysqli_num_rows($res));

		while($row = mysqli_fetch_array($res))
		{
			// Si <> -1, on affiche que le nombre de lignes souhaitées
            // Si = -1, on affiche toutes les lignes
			if (!$this->displayAll)
			{
				if ($i < $this->firstNumRowDisplay) { $i++; continue; }
				if ($i >= ($this->firstNumRowDisplay + $this->nbDisplayRows)) break;
			}

			$this->addRow($row); $i++;
		}
	}
}

function getFirstRow()		{ return (($this->nbRowsAdded == 0) ? false : reset($this->rowsTab)); }
function getNextRow()		{ return (($this->nbRowsAdded == 0) ? false : next($this->rowsTab)); }

function changeCurrentRow($row)
{
	$num = key($this->rowsTab);
	while(list($cle, $valeur) = each($row))
	{
		$this->rowsTab[$num][$cle] = $row[$cle];
	}
}

function display()
{
    // Si on doit tout afficher, il faut mettre le nb de ligne total dans nbDisplayRows
    if ($this->displayAll) $this->nbDisplayRows = count($this->rowsTab);
?>
	<LINK REL="stylesheet" HREF="../css/submenu.css" TYPE="text/css">
    <SCRIPT SRC="../js/submenu.js"></SCRIPT>
    <DIV id="popmenu" class="menuskin" onMouseover="clearhidemenu();highlightmenu(event,'on')" onMouseout="highlightmenu(event,'off');dynamichide(event)"></DIV>
	<LINK REL="stylesheet" HREF="../css/XList.css" TYPE="text/css">
	<TABLE CLASS=xlist_table1 CELLPADDING=0 CELLSPACING=0 BORDER=0>
	<TR VALIGN=CENTER><TD ALIGN=CENTER>
	<TABLE CLASS=xlist_table2 <?= $this->tableProp ?> FRAME="void" RULES="groups" CELLPADDING=2 CELLSPACING=0>
<?
	// Affichage du Titre du tableau
	echo "<CAPTION>";
    if ($this->displayAll)
    {
        $img_updown = "../images/list_up.gif";
        $alt_updown = "Affichage partiel de la liste";
        $url_updown = $this->target.(strstr("?", $this->target) ? "&": "?")."first_row=0&select_search=".urlencode($this->search);
    }
    else
    {
        $img_updown = "../images/list_down.gif";
        $alt_updown = "Affichage de toutes les lignes de la liste";
        $url_updown = $this->target.(strstr("?", $this->target) ? "&": "?")."display_all=1&first_row=0&select_search=".urlencode($this->search);
    }
   	echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 HEIGHT=30 ALIGN=CENTER WIDTH=100%><TR><TD CLASS=xlist_title>".$this->title."</TD></TABLE>";

	// Affichage des infos COLGROUP du tableau
	if (count($this->colGroup) > 0)
	{
		if ($this->numerotation == 1)
			echo "<COLGROUP WIDTH=25>\n";

		while(list($cle, $valeur) = each($this->colGroup))
		{
			echo "<COLGROUP WIDTH=".$valeur.">\n";
		}
	}

	// Affichage de la ligne titre des colonnes du tableau
	if (count($this->columnsNames) > 0)
	{
		echo "<THEAD>";
		$i = 0;
		echo "<TR CLASS=xlist_trCols ".$this->columnsNamesTRProp.">";

		if ($this->numerotation == 1)
			echo "<TD ALIGN=CENTER><FONT CLASS=xlist_tdcol> N° </FONT></TD>";

        reset($this->columnsNames);
		while(list($cle, $valeur) = each($this->columnsNames))
		{
			echo "<TD ALIGN=CENTER ".$this->columnsTDProps[$i]."><FONT CLASS=xlist_tdcol>".$valeur."</FONT></TD>";
			$i++;
		}
		echo "\n";
	}

	// Affichage des lignes du tableau
	echo "<TBODY CLASS=xlist toto>";
	$j = 0;
	if (count($this->rowsTab) > 0)
	{
		$this->getFirstRow();
		while(list($cle, $valeur) = each($this->rowsTab))
		{
			$i = 0;
//			echo "<TR ".((($j % 2) == 0) ? "CLASS=xlist_trUnpairCols ".$this->unpairRowTRProp : "CLASS=xlist_trPairCols ".$this->pairRowTRProp).">";
			echo "<TR onMouseOver=\"this.bgColor='#D5D9EA';-moz-opacity:0.5; filter:Alpha(Opacity=50);
\" onMouseOut =\"this.bgColor=''\">";

			if ($this->numerotation == 1)
				echo "<TD ALIGN=CENTER><FONT CLASS=".((($j % 2) == 0) ? "xlist_tdunpairrow" :  "xlist_tdpairrow").">".($this->firstNumRowDisplay+1+$j)."</FONT></TD>";

			for($k = 0; $k < $this->nbColumns; $k++)
			{
				echo "<TD ALIGN=".$this->columnsAlign[$i]." ".$this->columnsTDProps[$i]."><FONT CLASS=".((($j % 2) == 0) ? "xlist_tdunpairrow" :  "xlist_tdpairrow").">".$valeur[$this->mappingColumns[$k]]."</FONT></TD>";
				$i++;
			}

			echo "\n";
			$j++;
		}
	}

	// Affichage des lignes vides du tableau (si besoin)
	if ($j < $this->nbDisplayRows)
	{
		for($i = $j; $i < $this->nbDisplayRows; $i++)
		{
//			echo "<TR ".((($i % 2) == 0) ? "CLASS=xlist_trUnpairCols ".$this->unpairRowTRProp : "CLASS=xlist_trPairCols ".$this->pairRowTRProp).">";
			echo "<TR onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";

			if ($this->numerotation == 1)
				echo "<TD ALIGN=CENTER><SMALL> - </SMALL></TD>";

			for($k = 0; $k < $this->nbColumns; $k++)
				echo "<TD ALIGN=".$this->columnsAlign[$k]." ".$this->columnsTDProps[$k]."><SMALL> - </SMALL></TD>";

			echo "\n";
		}
	}

	// Calcul des valeurs pour l'accéder à la pagination virtuelle
	$debut       = $this->firstNumRowDisplay;
	$lines       = $this->nbRows;
	$max_display = $this->nbDisplayRows;
	$max_page = $loc_page = $value_beg = $value_pre = $value_nxt = $value_end = 0;

	if ($lines > 0 && $max_display > 0)
	{
		$fin       = $debut + $max_display;
		$max_page  = ceil(($lines) / $max_display);
		$loc_page  = ceil(($fin  ) / $max_display);
		$value_beg = 0;
		$value_pre = $debut - $max_display;
		$value_nxt = $debut + $max_display;
		$value_end = $lines - $max_display;

		if ($value_pre < 0)			$value_pre = 0;
		if ($value_nxt >= $lines)	$value_nxt = $lines - $max_display;
		if ($value_nxt < 0)			$value_nxt = 0;
		if ($max_page == $loc_page)	$value_nxt = $lines - $max_display;
		if ($max_page == $loc_page)	$value_end = $lines - $max_display;
		if ($value_nxt < 0)			$value_nxt = 0;
		if ($value_end < 0)			$value_end = 0;
	}

	// Affichage de la barre FOOTER du tableau
	$nbcol = $this->nbColumns + (($this->numerotation == 1) ? 1 : 0);
	$urls = explode("?", $this->target);
	if (!isset($urls[0])) $urls[0] = $this->target;
	if (!isset($urls[1])) $urls[1] = "";
	$local_url = $urls[0]."?".$urls[1]."&xlist_pdf=1&first_row=".$value_end."&select_search=".urlencode($this->search);
?>
<SCRIPT>
linkset2='<div  class="menuitems"><A HREF="<?= $url_updown ?>"><?= $alt_updown ?></a></div>';
linkset2+='<HR>';
linkset2+='<div class="menuitems"><a href="#" onClick="javascript:window.open(\'<?= $local_url ?>\', \'pdf_html\', \'\');">Impression HTML</a></div>';
linkset2+='<div class="menuitems"><a href="#" onClick="javascript:window.open(\'<?= $local_url ?>\', \'pdf_imp\', \'\');">Impression PDF</a></div>';
</SCRIPT>
		<TFOOT>
		<TR CLASS=xlist_trCols2 <?= $this->columnsNamesTRProp ?>><TD COLSPAN=<?= $nbcol ?>>
            <TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100%>
            	<TR VALIGN=CENTER>
					<TD WIDTH=50> &nbsp; </TD>
            	    <TD ALIGN=CENTER WIDTH=50> <?= $this->statusHtmlCode ?> </TD>
<?
	if ($this->footer == 1 && $max_page > 0)
	{
?>
            		<TD ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
            			<TR><TD><A HREF="<?= $urls[0]."?".$urls[1] ?>&first_row=<?= $value_beg ?>&select_search=<?= urlencode($this->search) ?>"><IMG SRC=../images/arrow_ll.gif BORDER=0 ALT="Première Page"></IMG></A></TD>
            				<TD><A HREF="<?= $urls[0]."?".$urls[1] ?>&first_row=<?= $value_pre ?>&select_search=<?= urlencode($this->search) ?>"><IMG SRC=../images/arrow_l.gif  BORDER=0 ALT="Page Précédente"></IMG></A></TD>
            				<TD><A HREF="<?= $urls[0]."?".$urls[1] ?>&first_row=<?= $value_nxt ?>&select_search=<?= urlencode($this->search) ?>"><IMG SRC=../images/arrow_r.gif  BORDER=0 ALT="Page Suivante"></IMG></A></TD>
            				<TD><A HREF="<?= $urls[0]."?".$urls[1] ?>&first_row=<?= $value_end ?>&select_search=<?= urlencode($this->search) ?>"><IMG SRC=../images/arrow_rr.gif BORDER=0 ALT="Dernière Page"></IMG></A></TD>
            		</TABLE></TD>
            		<TD WIDTH=50 ALIGN=CENTER><A HREF="#" onMouseover="showmenu2(event,linkset2, 250);" onMouseout="delayhidemenu();"><IMG SRC=../images/xl_submenu.gif BORDER=0></IMG></A></TD>
            		<TD WIDTH=50 ALIGN=RIGHT><SMALL><?= $loc_page."/".$max_page ?></SMALL></TD>
<?
	}
	else
	{
?>
					<TD WIDTH=200> </TD>
            		<TD WIDTH=50 ALIGN=CENTER><A HREF="#" onMouseover="showmenu2(event,linkset2, 250);" onMouseout="delayhidemenu();"><IMG SRC=../images/xl_submenu.gif BORDER=0></IMG></A></TD>
            		<TD WIDTH=50 ALIGN=RIGHT><SMALL> </SMALL></TD>
<?
	}
?>
				</TABLE></TD>
			</TABLE></TD>
		</TABLE>
<?
}

function displayHtmlPrint()
{
    // On affiche toutes les lignes => il faut mettre le nb de ligne total dans nbDisplayRows
    $this->nbDisplayRows = count($this->rowsTab);

?>
	<LINK REL="stylesheet" HREF="../css/XList.css" TYPE="text/css">
    <TR><TD><TABLE BORDER=0 WIDTH=100% CELLPADDING=0 CELLSPACING=0>
<?
    $nb_cols = count($this->columnsNames) + (($this->numerotation == 1) ? 1 : 0);

	// Affichage du Titre du tableau
	echo "<TR>";
	ToolBox::htmlPrintCelluleWithColSpan("<FONT CLASS=big>".$this->title."</FONT>", "cell_center_navy", "100%", _CELL_BORDER_ALL_, $nb_cols);

	// Affichage de la ligne titre des colonnes du tableau
	if (count($this->columnsNames) > 0)
	{
    	echo "<TR>";
		if ($this->numerotation == 1)
        	ToolBox::htmlPrintCellule("N°", "cell_center_gray", "", _CELL_BORDER_U_);

        reset($this->columnsNames);
		while(list($cle, $valeur) = each($this->columnsNames))
        	ToolBox::htmlPrintCellule($valeur, "cell_center_gray", "", _CELL_BORDER_SE_);
	}

	// Affichage des lignes du tableau
	$j = 1;
	if (count($this->rowsTab) > 0)
	{
		reset($this->rowsTab);
		while(list($cle, $valeur) = each($this->rowsTab))
		{
			echo "<TR>";

			if ($this->numerotation == 1)
              	ToolBox::htmlPrintCellule($j, "cell_center_white", "", _CELL_BORDER_U_);

			for($k = 0; $k < $this->nbColumns; $k++)
              	ToolBox::htmlPrintCellule($valeur[$this->mappingColumns[$k]], "cell_center_white", "", _CELL_BORDER_SE_);
//				echo "<TD ALIGN=".$this->columnsAlign[$i]." ".$this->columnsTDProps[$i]."><FONT CLASS=".((($j % 2) == 0) ? "xlist_tdunpairrow" :  "xlist_tdpairrow").">".$valeur[$this->mappingColumns[$k]]."</FONT></TD>";
            $j++;
		}
	}

	echo "</TABLE>";
}

function displayPDFheader(&$pdfgen)
{
	$pdfgen->newPage();
	$pdfgen->setNormalFont(8.0);
	$pdfgen->setCellBorderColor(0.75);

	// Affichage du titre
	$pdfgen->setCellBgColor(1.0);
	$pdfgen->setBoldFont(16.0);
	$pdfgen->drawTitle($this->title);

	// Affichage de la ligne titre des colonnes du tableau
	if (count($this->columnsNames) > 0)
	{
		$pdfgen->setBoldFont(8.0);
		$pdfgen->setCellBgColor(0.8);
		$pdfgen->drawRow(($this->numerotation == 1) ? array_merge(array("N°"), $this->columnsNames) : $this->columnsNames);
	}

	$pdfgen->setNormalFont(8.0);
	$pdfgen->setCellBgColor(1.0);
}

function displayPDFfooter(&$pdfgen)
{
	// Affichage de la barre FOOTER du tableau
	if ($this->footer == 1)
	{
		$pdfgen->setCellBgColor(1.0);
		$pdfgen->setNormalFont(8.0);
		$pdfgen->drawFooter("Page ".(++$this->nbPagePdf));
	}

	$pdfgen->endPage();
}

function displayPDF($doc_width = 595, $doc_height = 842)
{
		// Calcul de la largeurs des colonnes. On les divise de 1.5 pour que le ratio pdf/html reste correct
		if ($this->numerotation == 1) $tmp_cp[] = 30;
		foreach($this->colGroup as $cl)
		{
			$tmp_cp[] = ($cl / 1.5);
			$this->tabwidthPdf += ($cl / 1.5);
		}

		// Création d'un objet pdfgen
		$pdfgen = new pdf_class();
		$pdfgen->setFontSize($this->tabfontsize);
		$pdfgen->setDocumentSize($doc_height, $doc_width);
		$pdfgen->setColsSize($tmp_cp);
		$pdfgen->setColsAlig(($this->numerotation == 1) ? array_merge(array("RIGHT"), $this->columnsAlign) : $this->columnsAlign);

		$this->displayPDFheader($pdfgen);

		// Affichage des lignes du tableau
		if (count($this->rowsTab) > 0)
		{
			$pdfgen->setDefaultNormalFont();
			$this->getFirstRow();
			$j = 0;
			while(list($cle, $valeur) = each($this->rowsTab))
			{
				$pdfgen->setCellBgColor((($j % 2) == 0) ? 1.0 : 0.95);
				unset($tmp_ligne);

				if ($this->numerotation == 1) $tmp_ligne[] = $j + 1;

				if ($this->displayColAction)
				{
					for($k = 0; $k < $this->nbColumns; $k++)
						$tmp_ligne[] = ($this->mappingColumns[$k] == "action") ? "-" : $valeur[$this->mappingColumns[$k]];
				}

				$ret = $pdfgen->drawRow($tmp_ligne);

				// Si la page n'a pas pu être affichée, alors on passe à la page suivante
				if ($ret == false)
				{
					$this->displayPDFfooter($pdfgen);
					$this->displayPDFheader($pdfgen);

					$pdfgen->setCellBgColor((($j % 2) == 0) ? 1.0 : 0.95);
					$ret = $pdfgen->drawRow($tmp_ligne);
				}

				$j++;
			}
		}

		// Affichage des lignes vides du tableau (si besoin)
		if ($j < $this->nbDisplayRows)
		{
			for($i = $j; $i < $this->nbDisplayRows; $i++)
			{
				$pdfgen->setCellBgColor((($i % 2) == 0) ? 1.0 : 0.95);
				unset($tmp_ligne);

				if ($this->numerotation == 1) $tmp_ligne[] = "-";

				for($k = 0; $k < $this->nbColumns; $k++) $tmp_ligne[] = "-";

				$pdfgen->drawRow($tmp_ligne);
			}
		}

		$this->displayPDFfooter($pdfgen);
		$pdfgen->closePDF();

		$buf = $pdfgen->getBufferPDF();
		$len = strlen($buf);

		header("Content-type: application/pdf");
		header("Content-Length: $len");
		header("Content-Disposition: inline; filename=xlist_class.pdf");

		print $buf;

		$pdfgen->deletePDF();
}


function exemple()
{
	echo "<TABLE BGCOLOR=#AABEBD CELLPADDING=0 CELLSPACING=0 BORDER=0>";
	echo "<TR><TD HEIGHT=1 COLSPAN=5 BGCOLOR=black></TD>";
	echo "<TR HEIGHT=20><TD WIDTH=1 BGCOLOR=black></TD><TD WIDTH=30></TD><TD></TD><TD WIDTH=30></TD><TD WIDTH=1 BGCOLOR=black></TD>";
	echo "<TR><TD WIDTH=1 BGCOLOR=black></TD><TD WIDTH=20></TD><TD ALIGN=CENTER>";

	$this->setTitle("C'est mon titre");
	$this->setTableProp("BORDER=1");
	$this->setTargetStatement("TestTab.php");
	$this->setNumerotationOn();
	$this->setTRPropColumnsNames("BGCOLOR=#CCCCCC");
	$this->setTRPropPairRow("BGCOLOR=#FFFFEE");
	$this->setTRPropUnpairRow("BGCOLOR=#EEEEEE");
	$this->setColumnsNames(array("toto||ALIGN=RIGHT", "titi||ALIGN=CENTER", "ahaha||ALIGN=CENTER"));
	$this->setColGroup(array(200, 100, 100));
	$this->setNbRows(6);
	$this->addRow(array("1", "2", "3"));
	$this->addRow(array("1", "2", "3"));
	$this->addRow(array("1", "2", "3"));
	$this->addRow(array("1", "2", "3"));
	$this->addRow(array("1", "2", "3"));
	$this->addRow(array("1", "2", "3"));
	$this->setSearchStatement("'TestTab.php");

	$this->display();

	echo "</TD><TD WIDTH=20></TD><TD WIDTH=1 BGCOLOR=black></TD>";
	echo "<TR HEIGHT=30><TD WIDTH=1 BGCOLOR=black></TD><TD WIDTH=30></TD><TD></TD><TD WIDTH=30></TD><TD WIDTH=1 BGCOLOR=black></TD>";
	echo "<TR><TD HEIGHT=1 COLSPAN=5 BGCOLOR=black></TD>";
	echo "</TABLE>";
}

}

?>
