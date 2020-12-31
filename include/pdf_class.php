<?

class pdf_class
{

var $pdf;

var $doc_height   = 842;
var $doc_width    = 595;
var $doc_marginx  = 50;
var $doc_marginy  = 50;
var $current_posy = 750;
var $current_posx = 50;
var $cellborder_color = 0.5;
var $cellbg_color     = 0;
var $cols_size;
var $row_size;
var $cols_alignement;
var $cell_marginx = 2;
var $cell_marginy = 3;
var $font_normal;
var $font_bold;
var $fontsize = 12;
var $fontsize_title = 16;

// //////////////////////////////////////////////////////////////////////////////////
// CONSTRUCTEURS
// //////////////////////////////////////////////////////////////////////////////////
//
function pdf_class()
{
	// Création du pdf
	$this->pdf = PDF_new();
	PDF_open_file($this->pdf, "");

	// Paramètres généraux
	PDF_set_parameter($this->pdf, "warning", "true");
	PDF_set_info($this->pdf, "Creator", "pdf_class.php");
	PDF_set_info($this->pdf, "Author",  "VF");
	PDF_set_info($this->pdf, "Title",   "VF PDF GENERATION");

	// Création des fontes par défauts
	$this->font_normal = PDF_findfont($this->pdf, "Helvetica", "host", 0);
	$this->font_bold   = PDF_findfont($this->pdf, "Helvetica-Bold", "host", 0);

	// Divers
	$this->row_size = $this->doc_width - ($this->doc_marginx * 2);
}

function newPage()
{
	PDF_begin_page($this->pdf, $this->doc_width, $this->doc_height);
	PDF_setfont($this->pdf, $this->font_normal, $this->fontsize);

	// Positionnement relatif en haut à gauche de la feuille
	$this->setBeginPosDocument($this->doc_marginx, $this->doc_height - $this->doc_marginy);	
}

function endPage()		{ PDF_end_page($this->pdf); }
function closePDF() 	{ PDF_close($this->pdf); }
function getBufferPDF()	{ return PDF_get_buffer($this->pdf); }
function deletePDF()	{ PDF_delete($this->pdf); }

function setFontSize($fontsize)	{ $this->fontsize = $fontsize; }

function setNormalFont($size)
{
	PDF_setfont($this->pdf, $this->font_normal, $size);
	$this->setFontSize($size);
}

function setDefaultNormalFont()
{
	$this->setNormalFont($this->fontsize);
}

function setBoldFont($size)
{
	PDF_setfont($this->pdf, $this->font_bold, $size);
	$this->setFontSize($size);
}

function setDefaultBoldFont()
{
	$this->setBoldFont($this->fontsize);
}

function setDocumentSize($height, $width)
{
	$this->doc_height = $height;
	$this->doc_width  = $width;

	$this->row_size = $this->doc_width - ($this->doc_marginx * 2);
}

function setDocumentMargin($marginx, $marginy)
{
	$this->doc_marginy = $marginy;
	$this->doc_marginx = $marginx;
}

function setBeginPosDocument($posx, $posy)
{
	$this->current_posx = $posx;
	$this->current_posy = $posy;
}

function setColsSize($cols_size)
{
	$this->cols_size = $cols_size;

	$this->row_size = 0;
	foreach($cols_size as $value) $this->row_size += $value;

}

function setColsAlig($cols_alignement)
{
	$this->cols_alignement = $cols_alignement;
}

function setCellBgColor($color)
{
	$this->cellbg_color = $color;
}

function setCellBorderColor($color)
{
	$this->cellborder_color = $color;
}

function resize_word(&$str, $width, $begin = 0)
{
	$strtmp  = "";
	$lentmp  = $begin;

	for($i = 0; $i < strlen($str); $i++)
	{
		$car = substr($str, $i, 1);
		$len = PDF_stringwidth($this->pdf, $car);
		
		if (($lentmp + $len) > ($width - (2 * $this->marginx) - 3))
		{
			$strtmp .= "\n";
			$lentmp = 0;
		}
		
		$strtmp .= $car;
		$lentmp += $len;
	}
	$str = $strtmp;

	return $lentmp;
}

function getNumberLines(&$str, $width)
{
	$str_size = $this->cell_marginx;
	$nb_lines = 1;
	$blank_size = PDF_stringwidth($this->pdf, " ");
	
	$tot = 0;
	$tabstr = explode(" ", $str);
	while(list($cle, $valeur) = each($tabstr))
	{
		$cur_size = PDF_stringwidth($this->pdf, $valeur);
		if ($cur_size > ($width - (2 * $this->cell_marginx)))
		{
			$str_size = $this->resize_word($tabstr[$cle], $width, $str_size);
			$nb_lines += substr_count($tabstr[$cle], "\n") + 1;
		}
		else
		{
			$tot += $cur_size+$blank_size;

			if (($cur_size + $str_size) > ($width - $this->cell_marginx))
			{
				$str_size = $this->cell_marginx + $cur_size;
				$nb_lines++;
			}
			else
				$str_size += $cur_size;

		}
		$str_size += $blank_size;
	}
	$str = implode(" ", $tabstr);
	
	return $nb_lines;
}

function getCellHeight(&$str, $width)
{
	$nb_lines =0;
	
	$sub_str = explode("\n", $str);
	while(list($cle, $valeur) = each($sub_str))
	{
		$nb_lines += $this->getNumberLines($sub_str[$cle], $width);
	}
	$str = implode("\n", $sub_str);
	
	return (($nb_lines * $this->fontsize) + (2 * $this->cell_marginy));
}

function getRowHeight(&$str_array)
{
	$height = 0;
	$width = reset($this->cols_size);
	foreach ($str_array as $str)
	{
		$extensions = explode(".", $str);
		end($extensions);
		$ext = current($extensions);

		if ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "tif")
		{
			$size   = @GetImageSize($str);
			$height = max($height, $size[1] + (2 * $this->cell_marginy));
		}
		else
			$height = max($height, $this->getCellHeight($str, $width));
		
		$width = next($this->cols_size);
	}
	
	return $height;
}

function drawCellBorder($str, $x, $y, $width, $height, $align = "left", $border = true)
{
	$h = max($height, $this->getCellHeight($str, $width));

	if ($border == 1)
	{
		PDF_setgray_fill($this->pdf, $this->cellbg_color);
		PDF_setgray_stroke($this->pdf, $this->cellborder_color);
		PDF_setlinewidth($this->pdf, 0.5);
		PDF_rect($this->pdf, $x, $y - $h, $width, $h);
		PDF_fill_stroke($this->pdf);
		PDF_setgray_fill($this->pdf, 0);
	}
	
	$xx = (strtolower($align) == "left") ? $x + $this->cell_marginx : ((strtolower($align) == "right") ? $x - $this->cell_marginx : $x);
	PDF_show_boxed($this->pdf, $str, $xx, $y - $height, $width, $h - $this->cell_marginy, strtolower($align));

	return $h;
}

function drawCell($str, $x, $y, $width, $height, $align = "left")
{
	return $this->drawCellBorder($str, $x, $y, $width, $height, $align, false);
}

function drawCellImageBorder($image, $ext, $x, $y, $width, $height, $align = "left", $border = true)
{
	$ext = strtolower($ext);
	if ($ext == "jpg") $ext = "jpeg";

	if ($border) $height = $this->drawCellBorder("", $x, $y, $width, $height);
	
	$pdf_img = @PDF_open_image_file($this->pdf, $ext, $image);
	
	if ($pdf_img != "")
	{
		$img_width  = PDF_get_image_width($this->pdf,  $pdf_img);
		$img_height = PDF_get_image_height($this->pdf, $pdf_img);

		PDF_place_image($this->pdf, $pdf_img, $x + $this->cell_marginx, $y - $img_height - $this->cell_marginy, 1);
		PDF_close_image($this->pdf, $pdf_img);
	}
	else
	{
		if ($ext == "jpeg")
			$img = @ImageCreateFromJPEG($image);
		else if ($ext == "gif")
			$img = @ImageCreateFromGif($image);
		else if ($ext == "png")
			$img = @ImageCreateFromPng($image);
		
		if ($pdf_img != "")
		{
			$pdf_img = PDF_open_memory_image($this->pdf, $img);
			PDF_place_image($this->pdf, $pdf_img, 100, 100, 1);
			PDF_close_image($this->pdf, $pdf_img);
			ImageDestroy($img);
		}
	}
}

function drawCellImage($image, $ext, $x, $y, $width, $height, $align = "left")
{
	return $this->drawCellImageBorder($image, $ext, $x, $y, $width, $height, $align, false);
}

function drawRow($str_array)
{
	// Recherche de la hauteur de la cellule la + haute
	$max_height = $this->getRowHeight($str_array);
	
	// On regarde si on peut afficher cette ligne sur cette page
	if (($this->current_posy - $max_height) < $this->doc_marginy)
		return false;

	// Positionnement
	$x = floor(($this->doc_width - $this->row_size) / 2);

	// Affichage des cellules
	$width = reset($this->cols_size);
	$align = reset($this->cols_alignement);
	foreach ($str_array as $str)
	{
		if (!isset($align) || $align == "") $align = "left";
		
		$extensions = explode(".", $str);
		end($extensions);
		$ext = current($extensions);

		if ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "tif")
			$height = $this->drawCellImageBorder($str, $ext, $x, $this->current_posy, $width, $max_height, $align);
		else
			$height = $this->drawCellBorder(" ".$str, $x, $this->current_posy, $width, $max_height, $align);

		$x += $width;
		$width = next($this->cols_size);
		$align = next($this->cols_alignement);
	}
	$this->current_posy -= $max_height;
	
	return true;
}

function drawTitle($title)
{
	$height = $this->drawCell($title, 0, $this->current_posy, $this->doc_width, 30, "center");
	$this->current_posy -= $height;
}

function drawFooter($footer)
{
	if ($this->current_posy > $this->doc_marginy) $this->current_posy = $this->doc_marginy;
	
	$height = $this->drawCell($footer, 0, $this->current_posy, $this->doc_width, 15, "center");
	$this->current_posy -= $height;

}

function alert($message, $x = 10, $y = 800)
{
	PDF_save($this->pdf);
	PDF_show_xy($this->pdf, $message, $x, $y);
	PDF_restore($this->pdf);
}

}

?>
