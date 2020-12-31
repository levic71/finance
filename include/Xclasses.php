<?

class enveloppe
{
	//	 -------------------------------------------------------------
	//	| img_tab11_n |		img_tab12_n		| img_tab13_n |
	//	| ------------+---------------------------------+-------------|
	//	| img_tab21_n |	    	ce qu'on veut  		| img_tab23_n |
	//	| ------------+---------------------------------+-------------|
	//	| img_tab31_n |		img_tab32_n		| img_tab33_n |
	//	 -------------------------------------------------------------

	var $img_tab11_n, $img_tab12_n, $img_tab13_n, $img_tab21_n, $img_tab22_n, $img_tab23_n, $img_tab31_n, $img_tab32_n, $img_tab33_n, $img_tab24_n;
	var $img_tab11_h, $img_tab12_h, $img_tab13_h, $img_tab21_h, $img_tab22_h, $img_tab23_h, $img_tab31_h, $img_tab32_h, $img_tab33_h;
	var $img_tab11_w, $img_tab12_w, $img_tab13_w, $img_tab21_w, $img_tab22_w, $img_tab23_w, $img_tab31_w, $img_tab32_w, $img_tab33_w;
	var $nb_col, $nb_ligne = 0;
	var $style, $icon;

	function enveloppe()
	{
		$this->style = "cbleu";
	}

	function setIcon($icon)
	{
		$this->icon = $icon;
	}
	
	function setStyle($style)
	{
		$this->style = $style;
		
		if ($this->style == "fiche bois")
		{
			$this->img_tab11_n = "../images/tab_11.gif";
			$this->img_tab12_n = "../images/tab_12.gif";
			$this->img_tab13_n = "../images/tab_13.gif";
			$this->img_tab21_n = "../images/tab_21.gif";
			$this->img_tab22_n = "../images/tab_22.gif";
			$this->img_tab23_n = "../images/tab_23.gif";
			$this->img_tab24_n = "../images/tab_24.gif";
			$this->img_tab31_n = "../images/tab_31.gif";
			$this->img_tab32_n = "../images/tab_32.gif";
			$this->img_tab33_n = "../images/tab_33.gif";
			
			$this->img_tab11_h = 27;
			$this->img_tab12_h = 27;
			$this->img_tab13_h = 27;
			$this->img_tab21_h = 22;
			$this->img_tab22_h = 22;
			$this->img_tab23_h = 22;
			$this->img_tab31_h = 12;
			$this->img_tab32_h = 12;
			$this->img_tab33_h = 12;
			
			$this->img_tab11_w = 9;
			$this->img_tab12_w = 27;
			$this->img_tab13_w = 11;
			$this->img_tab21_w = 9;
			$this->img_tab22_w = 27;
			$this->img_tab23_w = 11;
			$this->img_tab31_w = 9;
			$this->img_tab32_w = 27;
			$this->img_tab33_w = 11;
		}
		if ($this->style == "cbois2")
		{
			$this->img_tab11_n = "../images/cbois2_11.jpg";
			$this->img_tab12_n = "../images/cbois2_12.jpg";
			$this->img_tab13_n = "../images/cbois2_13.jpg";
			$this->img_tab21_n = "../images/cbois2_21.jpg";
			$this->img_tab22_n = "../images/cbois2_22.jpg";
			$this->img_tab23_n = "../images/cbois2_23.jpg";
			$this->img_tab24_n = "../images/cbois2_24.jpg";
			$this->img_tab31_n = "../images/cbois2_31.jpg";
			$this->img_tab32_n = "../images/cbois2_32.jpg";
			$this->img_tab33_n = "../images/cbois2_33.jpg";
			
			$this->img_tab11_h = 27;
			$this->img_tab12_h = 27;
			$this->img_tab13_h = 27;
			$this->img_tab21_h = 28;
			$this->img_tab22_h = 28;
			$this->img_tab23_h = 28;
			$this->img_tab31_h = 19;
			$this->img_tab32_h = 19;
			$this->img_tab33_h = 19;
			
			$this->img_tab11_w = 25;
			$this->img_tab12_w = 29;
			$this->img_tab13_w = 27;
			$this->img_tab21_w = 25;
			$this->img_tab22_w = 29;
			$this->img_tab23_w = 27;
			$this->img_tab31_w = 25;
			$this->img_tab32_w = 29;
			$this->img_tab33_w = 27;
		}
		if ($this->style == "cbleu")
		{
			$this->img_tab11_n = "../images/bcadre_11.jpg";
			$this->img_tab12_n = "../images/bcadre_12.jpg";
			$this->img_tab13_n = "../images/bcadre_13.jpg";
			$this->img_tab21_n = "../images/bcadre_21.jpg";
			$this->img_tab22_n = "../images/bcadre_22.jpg";
			$this->img_tab23_n = "../images/bcadre_23.jpg";
			$this->img_tab24_n = "../images/bcadre_22.jpg";
			$this->img_tab31_n = "../images/bcadre_31.jpg";
			$this->img_tab32_n = "../images/bcadre_32.jpg";
			$this->img_tab33_n = "../images/bcadre_33.jpg";
			
			$this->img_tab11_h = 29;
			$this->img_tab12_h = 29;
			$this->img_tab13_h = 29;
			$this->img_tab21_h = 29;
			$this->img_tab22_h = 29;
			$this->img_tab23_h = 29;
			$this->img_tab31_h = 27;
			$this->img_tab32_h = 27;
			$this->img_tab33_h = 27;
			
			$this->img_tab11_w = 30;
			$this->img_tab12_w = 49;
			$this->img_tab13_w = 30;
			$this->img_tab21_w = 30;
			$this->img_tab22_w = 49;
			$this->img_tab23_w = 30;
			$this->img_tab31_w = 30;
			$this->img_tab32_w = 49;
			$this->img_tab33_w = 30;
		}
		if ($this->style == "yc")
		{
			$this->img_tab11_n = "../images/yc_11.jpg";
			$this->img_tab12_n = "../images/yc_12.jpg";
			$this->img_tab13_n = "../images/yc_13.jpg";
			$this->img_tab21_n = "../images/yc_21.jpg";
			$this->img_tab22_n = "../images/yc_22.jpg";
			$this->img_tab23_n = "../images/yc_23.jpg";
			$this->img_tab24_n = "../images/yc_22.jpg";
			$this->img_tab31_n = "../images/yc_31.jpg";
			$this->img_tab32_n = "../images/yc_32.jpg";
			$this->img_tab33_n = "../images/yc_33.jpg";
			
			$this->img_tab11_h = 37;
			$this->img_tab12_h = 37;
			$this->img_tab13_h = 37;
			$this->img_tab21_h = 40;
			$this->img_tab22_h = 40;
			$this->img_tab23_h = 40;
			$this->img_tab31_h = 33;
			$this->img_tab32_h = 33;
			$this->img_tab33_h = 33;
			
			$this->img_tab11_w = 38;
			$this->img_tab12_w = 40;
			$this->img_tab13_w = 37;
			$this->img_tab21_w = 38;
			$this->img_tab22_w = 40;
			$this->img_tab23_w = 37;
			$this->img_tab31_w = 38;
			$this->img_tab32_w = 40;
			$this->img_tab33_w = 37;
		}
		if ($this->style == "info")
		{
			$this->img_tab11_n = "../images/info_11.jpg";
			$this->img_tab12_n = "../images/info_12.jpg";
			$this->img_tab13_n = "../images/info_13.jpg";
			$this->img_tab21_n = "../images/info_21.jpg";
			$this->img_tab22_n = "../images/info_22.jpg";
			$this->img_tab23_n = "../images/info_23.jpg";
			$this->img_tab24_n = "../images/info_22.jpg";
			$this->img_tab31_n = "../images/info_31.jpg";
			$this->img_tab32_n = "../images/info_32.jpg";
			$this->img_tab33_n = "../images/info_33.jpg";
			
			$this->img_tab11_h = 19;
			$this->img_tab12_h = 19;
			$this->img_tab13_h = 19;
			$this->img_tab21_h = 40;
			$this->img_tab22_h = 40;
			$this->img_tab23_h = 40;
			$this->img_tab31_h = 15;
			$this->img_tab32_h = 15;
			$this->img_tab33_h = 15;
			
			$this->img_tab11_w = 28;
			$this->img_tab12_w = 40;
			$this->img_tab13_w = 14;
			$this->img_tab21_w = 28;
			$this->img_tab22_w = 40;
			$this->img_tab23_w = 14;
			$this->img_tab31_w = 28;
			$this->img_tab32_w = 40;
			$this->img_tab33_w = 14;
		}
	}

	function debut($libelle = "")
	{
		if ($libelle == "") $libelle = "&nbsp;";
		printf("<TR>\n");
		printf("<TD ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 BACKGROUND=".$this->img_tab22_n.">\n");
		printf("<TR>");
		printf("<TD ALIGN=CENTER><IMG SRC=".$this->img_tab11_n." WIDTH=".$this->img_tab11_w." HEIGHT=".$this->img_tab11_h." BORDER=0></IMG></TD>\n");
		printf("<TD ALIGN=CENTER BACKGROUND=".$this->img_tab12_n."><B><FONT COLOR=WHITE>".$libelle."</FONT></B></TD>");
		printf("<TD ALIGN=CENTER><IMG SRC=".$this->img_tab13_n." BORDER=0></IMG></TD>\n");
		printf("<TR VALIGN=TOP>");
		if (isset($this->icon))
			printf("<TD ALIGN=TOP BACKGROUND=".$this->img_tab21_n."><IMG SRC=".$this->icon." BORDER=0></IMG></TD>");
		else
			printf("<TD ALIGN=TOP BACKGROUND=".$this->img_tab21_n."><IMG SRC=".$this->img_tab21_n." WIDTH=".$this->img_tab21_w." BORDER=0></IMG></TD>");
		printf("<TD ALIGN=CENTER>\n");
	}

	function end()
	{
		printf("</TD>\n");
		printf("<TD ALIGN=CENTER BACKGROUND=".$this->img_tab23_n."><IMG SRC=".$this->img_tab23_n." BORDER=0></IMG></TD>\n");
		printf("<TR><TD ALIGN=CENTER><IMG SRC=".$this->img_tab31_n." WIDTH=".$this->img_tab31_w." HEIGHT=".$this->img_tab31_h." BORDER=0></IMG></TD>");
		printf("<TD ALIGN=CENTER BACKGROUND=".$this->img_tab32_n."><IMG SRC=".$this->img_tab32_n." BORDER=0></IMG></TD>");
		printf("<TD ALIGN=CENTER><IMG SRC=".$this->img_tab33_n." BORDER=0></IMG></TD>\n");
		printf("</TABLE></TD>\n");
	}

}

class complex_array
{

	//	 -------------------------------------------------------------
	//	| img_tab11_n |		img_tab12_n		| img_tab13_n |
	//	| ------------+---------------------------------+-------------|
	//	| img_tab21_n |	    img_tab22_n/img_tab24_n	| img_tab23_n |
	//	| ------------+---------------------------------+-------------|
	//	| img_tab31_n |		img_tab32_n		| img_tab33_n |
	//	 -------------------------------------------------------------

	var $img_tab11_n, $img_tab12_n, $img_tab13_n, $img_tab21_n, $img_tab22_n, $img_tab23_n, $img_tab31_n, $img_tab32_n, $img_tab33_n, $img_tab24_n;
	var $img_tab11_h, $img_tab12_h, $img_tab13_h, $img_tab21_h, $img_tab22_h, $img_tab23_h, $img_tab31_h, $img_tab32_h, $img_tab33_h;
	var $img_tab11_w, $img_tab12_w, $img_tab13_w, $img_tab21_w, $img_tab22_w, $img_tab23_w, $img_tab31_w, $img_tab32_w, $img_tab33_w;
	var $nb_col, $nb_ligne = 0;
	var $style;
	var $font       = "fixed";
	var $font_color = "white";
	var $font_size  = 9;

	function complex_array()
	{
		printf("<TR>\n");
		printf("<TD ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>\n");
	}

	function setStyle($style)
	{
		$this->style = $style;
		
		if ($style == "bois")
		{
			$this->img_tab11_n = "../images/tab_11.gif";
			$this->img_tab12_n = "../images/tab_12.gif";
			$this->img_tab13_n = "../images/tab_13.gif";
			$this->img_tab21_n = "../images/tab_21.gif";
			$this->img_tab22_n = "../images/tab_22.gif";
			$this->img_tab23_n = "../images/tab_23.gif";
			$this->img_tab24_n = "../images/tab_24.gif";
			$this->img_tab31_n = "../images/tab_31.gif";
			$this->img_tab32_n = "../images/tab_32.gif";
			$this->img_tab33_n = "../images/tab_33.gif";
			
			$this->img_tab11_h = 24;
			$this->img_tab12_h = 24;
			$this->img_tab13_h = 24;
			$this->img_tab21_h = 24;
			$this->img_tab22_h = 24;
			$this->img_tab23_h = 24;
			$this->img_tab31_h = 16;
			$this->img_tab32_h = 16;
			$this->img_tab33_h = 16;
			
			$this->img_tab11_w = 12;
			$this->img_tab12_w = 44;
			$this->img_tab13_w = 13;
			$this->img_tab21_w = 12;
			$this->img_tab22_w = 12;
			$this->img_tab23_w = 13;
			$this->img_tab31_w = 12;
			$this->img_tab32_w = 44;
			$this->img_tab33_w = 13;
		}
	}

	function setNbCol($nb_col)
	{
		$this->nb_col = $nb_col;
	}
	
	function setFont($font, $font_color, $font_size)
	{
		$this->font       = $font;
		$this->font_color = $font_color;
		$this->font_size  = $font_size;
	}
	
	function barre($bg)
	{
		echo "<TD WIDTH=5 BACKGROUND=".$bg."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
		echo "<TD WIDTH=1 BGCOLOR=#CCCCCC><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
		echo "<TD WIDTH=5 BACKGROUND=".$bg."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
	}

	function echo_ligne($col_val, $bg1, $w1, $h1, $bg2, $w2, $h2, $bg3, $w3, $h3)
	{
		printf("<TR>");
		printf("<TD ALIGN=CENTER BACKGROUND=".$bg1."><IMG SRC=".$bg1." WIDTH=".$w1." HEIGHT=".$h1." BORDER=0></IMG></TD>\n");
	
		foreach($col_val as $val)
		{
			if (strstr($val, "|"))
				list($val, $alg, $nowrap) = explode("|", $val);
			else
				$alg = "CENTER";
		
			$this->barre($bg2);
			if ($val == "")
				printf("<TD BACKGROUND=".$bg2."><IMG SRC=../images/cube1x1.gif WIDTH=1 HEIGHT=1 BORDER=0></IMG></TD>");
			else
				printf("<TD ALIGN=".$alg." BACKGROUND=".$bg2." ".$nowrap."><FONT COLOR=".$this->font_color." STYLE=\"font-size=".$this->font_size."pt; font-familly=".$this->font.";\">".$val."</FONT></TD>");
		}
		
		$this->barre($bg2);
		printf("<TD ALIGN=CENTER BACKGROUND=".$bg3."><IMG SRC=".$bg3." WIDTH=".$w3." HEIGHT=".$h3." BORDER=0></IMG></TD>\n");
	}

	function entete($col_val)
	{
		$this->nb_col = count($col_val);
		$this->echo_ligne($col_val, $this->img_tab11_n, $this->img_tab11_w, $this->img_tab11_h, $this->img_tab12_n, $this->img_tab12_w, $this->img_tab12_h, $this->img_tab13_n, $this->img_tab13_w, $this->img_tab13_h);
	}

	function ligne($col_val)
	{
		// On affiche les lignes avec des couleurs alternées
		$image_n = ($this->nb_ligne % 2) == 0 ? $this->img_tab22_n: $this->img_tab24_n;
		$this->echo_ligne($col_val, $this->img_tab21_n, $this->img_tab21_w, $this->img_tab21_h, $image_n, $this->img_tab22_w, $this->img_tab22_h, $this->img_tab23_n, $this->img_tab23_w, $this->img_tab23_h);
		$this->nb_ligne++;
	}

	function separation($bgcolor = "#CCCCCC")
	{
		printf("<TR HEIGHT=1>");
		printf("<TD ALIGN=RIGHT BGCOLOR=".$bgcolor." HEIGHT=1><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>\n");
	
		for($i = 0; $i < $this->nb_col; $i++)
		{
			echo "<TD WIDTH=5 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
			echo "<TD WIDTH=1 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
			echo "<TD WIDTH=5 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";

			printf("<TD ALIGN=CENTER BGCOLOR=".$bgcolor." HEIGHT=1><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>");
		}
		
		echo "<TD WIDTH=5 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
		echo "<TD WIDTH=1 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
		echo "<TD WIDTH=5 BGCOLOR=".$bgcolor."><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>";
		printf("<TD ALIGN=CENTER BGCOLOR=".$bgcolor." HEIGHT=1><IMG SRC=../images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>\n");
	}


	function end()
	{
		for($i=0; $i < $this->nb_col; $i++) $col_val[$i] = "";
		$this->echo_ligne($col_val, $this->img_tab31_n, $this->img_tab31_w, $this->img_tab31_h, $this->img_tab32_n, $this->img_tab32_w, $this->img_tab32_h, $this->img_tab33_n, $this->img_tab33_w, $this->img_tab33_h);
		printf("</TABLE></TD>\n");
	}
}

class simple_array
{
	var $nb_col;
	var $nb_col_total;
	var $marge_x = 10;
	var $marge_y = 5;
	var $cols_width;
	
	function setColsWidth($cols) {
		$this->cols_width = $cols;
	}
	
	function setMarges($mx, $my)
	{
		$this->marge_x = $mx;
		$this->marge_y = $my;
	}
	
	function simple_array($cols)
	{
		$this->nb_col = $cols;
		$this->nb_col_total = $this->nb_col + 2;

		printf("<LINK REL=\"stylesheet\" HREF=\"../css/Xclasses.css\" TYPE=\"text/css\">");
		printf("<TABLE CLASS=sarray_tab1 CELLPADDING=0 CELLSPACING=0 WIDTH=100%%>");
	
		printf("<TR><TD ALIGN=CENTER><TABLE CLASS=sarray_tab2 BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100%%>");
	}
	
	function entete($entete)
	{
		printf("<TR><TD CLASS=sarray_title COLSPAN=".$this->nb_col_total.">".$entete."</TD>");	
		$this->ligneVide();
	}
	
	function ligne($item)
	{
		printf("<TR>");
		if (is_array($item))
		{
			if (isset($this->cols_width)) reset($this->cols_width);
			echo("<TD WIDTH=".$this->marge_x." NOWRAP></TD>");
			while(list($i, $libelle) = each($item))
			{
				$alg = "LEFT";
				$nowrap = "";
				
				if (strstr($libelle, "|"))
				{
					$options = explode("|", $libelle);
					if (isset($options[0])) $libelle = $options[0];
					if (isset($options[1])) $alg = $options[1];
					if (isset($options[2])) $nowrap = $options[2];
				}

				$td_width = isset($this->cols_width) ? "WIDTH=".current($this->cols_width) : "";

				echo("<TD CLASS=sarray_ligne ".$td_width." ALIGN=".$alg." ".$nowrap.">".$libelle."</TD>");
				if (isset($this->cols_width)) next($this->cols_width);
			}
			echo("<TD WIDTH=".$this->marge_x." NOWRAP></TD>");
		}
		else
		{
			$alg = "CENTER";
			$nowrap = "";
			$res = explode("|", $item);
			if (isset($res[0])) $libelle = $res[0];
			if (isset($res[1])) $alg = $res[1];
			if (isset($res[2])) $nowrap = $res[2];
			echo("<TD WIDTH=".$this->marge_x." NOWRAP></TD><TD CLASS=sarray_ligne ALIGN=".$alg." ".$nowrap." COLSPAN=".$this->nb_col.">".$libelle."</TD><TD WIDTH=".$this->marge_x." NOWRAP></TD>");
		}
	}
	
	function end()
	{
		$this->ligneVide();	
		printf("</TABLE></TD>\n");
		printf("</TABLE>\n");
	}
	
	function separation() {
		printf("<TR><TD CLASS=sarray_separation COLSPAN=".$this->nb_col_total."><IMG SRC=images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>");
	}
	
	function ligneVide() {
		printf("<TR><TD HEIGHT=".$this->marge_y." NOWRAP COLSPAN=".$this->nb_col_total."><IMG SRC=images/cube1x1.gif BORDER=0 HEIGHT=1 WIDTH=1></IMG></TD>");
	}
}

class simple_snack
{
	function simple_snack($libelle, $style = "bleu")
	{
		if ($style == "bleu")
		{
			$img_1 = "../images/title_11.jpg";
			$img_2 = "../images/title_12.jpg";
			$img_3 = "../images/title_13.jpg";
		}
		
		if ($style == "jaune")
		{
			$img_1 = "../images/snack_01.jpg";
			$img_2 = "../images/snack_02.jpg";
			$img_3 = "../images/snack_03.jpg";
		}
	
		echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>";
		echo "<TD><IMG SRC=\"".$img_1."\"></IMG></TD>";
		echo "<TD BACKGROUND=\"".$img_2."\">".$libelle."</TD>";
		echo "<TD><IMG SRC=\"".$img_3."\"></IMG></TD>";
		echo "</TABLE>";
	}
}

class onglet
{
	var $items;
	var $nb_onglet = 0;
	var $selected  = 1; // Par défaut, le premier onglet est sélectionné

	var $img_tab11_n = "../images/Onglet_03.jpg";
	var $img_tab12_n = "../images/Onglet_04.jpg";
	var $img_tab13_n = "../images/Onglet_05.jpg";
	var $img_tab14_n = "../images/Onglet_07.jpg";
	var $img_tab15_n = "../images/Onglet_09.jpg";
	var $img_tab16_n = "../images/Onglet_36.jpg";
	var $img_tab17_n = "../images/Onglet_26.jpg";
	var $img_tab18_n = "../images/Onglet_31.jpg";
	var $img_tab191_n = "../images/Onglet_25.jpg";
	var $img_tab192_n = "../images/Onglet_27.jpg";
	var $img_tab193_n = "../images/Onglet_37.jpg";
	var $img_tab194_n = "../images/Onglet_29.jpg";

	var $img_tab21_n = "../images/Onglet_11.jpg";
	var $img_tab22_n = "../images/Onglet_13.jpg";
	var $img_tab23_n = "../images/Onglet_14.jpg";

	var $img_tab31_n = "../images/Onglet_18.jpg";
	var $img_tab32_n = "../images/Onglet_19.jpg";
	var $img_tab33_n = "../images/Onglet_20.jpg";

	var $img_tab11_h = 35;
	var $img_tab12_h = 35;
	var $img_tab13_h = 35;
	var $img_tab14_h = 35;
	var $img_tab15_h = 35;
	var $img_tab16_h = 35;
	var $img_tab17_h = 35;
	var $img_tab18_h = 35;
	var $img_tab19_h = 35;

	var $img_tab21_h = 15;
	var $img_tab22_h = 15;
	var $img_tab23_h = 15;

	var $img_tab31_h = 8;
	var $img_tab32_h = 8;
	var $img_tab33_h = 8;

	var $img_tab11_w = 9;
	var $img_tab12_w = 110;
	var $img_tab13_w = 22;
	var $img_tab14_w = 22;
	var $img_tab15_w = 10;
	var $img_tab16_w = 9;
	var $img_tab17_w = 110;
	var $img_tab18_w = 22;
	var $img_tab19_w = 22;
	var $img_tab193_w = 24;
	
	var $img_tab21_w = 9;
	var $img_tab22_w = 22;
	var $img_tab23_w = 10;

	var $img_tab31_w = 9;
	var $img_tab32_w = 22;
	var $img_tab33_w = 10;

	function onglet() {
	}
	
	function addFolder($libelle, $url = "")
	{
		$this->items[$libelle]["name"]	= $libelle;
		$this->items[$libelle]["url"]	= $url;
	}
	
	function setSelected($libelle)
	{
		$i = 1;
		while(list($cle, $val) = each($this->items))
		{
			if ($cle == $libelle) $this->selected = $i;
			$i++;
		}
		reset($this->items);
	}

	function debut()
	{
		$this->nb_onglet = sizeof($this->items);
		if ($this->nb_onglet == 0) return;

		// Tableau global
		echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>";

		// Barre des onglets
		echo "<TR>";
		// Le premier onglet est sélectionné
		if ($this->selected == 1)
			echo "<TD WIDTH=$this->img_tab11_w><IMG SRC=$this->img_tab11_n HEIGHT=$this->img_tab11_h WIDTH=$this->img_tab11_w BORDER=0></IMG></TD>";
		else
			echo "<TD WIDTH=$this->img_tab16_w><IMG SRC=$this->img_tab16_n HEIGHT=$this->img_tab16_h WIDTH=$this->img_tab16_w BORDER=0></IMG></TD>";
		
		// Tableau des hauts de onglet
		echo "<TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100%>";
		echo "<TR VALIGN=CENTER>";

		$i = 1;
		while(list($cle, $val) = each($this->items))
		{
			// Si l'onglet en cours est sélectionné
			if ($i == $this->selected)
			{
				$url_lib = "<FONT COLOR=white>".$this->items[$cle]["name"]."</FONT>";
				echo "<TD BACKGROUND=$this->img_tab12_n WIDTH=$this->img_tab12_w ALIGN=CENTER><SMALL>$url_lib</SMALL></TD>";
			}
			else
			{
				$url_lib = "<A HREF=\"javascript:document.forms[0].action='".$this->items[$cle]["url"]."';document.forms[0].submit();\" onClick=\"\"><FONT COLOR=#666666>".$this->items[$cle]["name"]."</FONT></A>";
				echo "<TD BACKGROUND=$this->img_tab17_n WIDTH=$this->img_tab17_w ALIGN=CENTER><SMALL>$url_lib</SMALL></TD>";
			}

			// Est-on sur le dernier onglet
			if ($i == $this->nb_onglet)
			{
				if ($i == $this->selected)
					echo "<TD WIDTH=$this->img_tab13_w><IMG SRC=$this->img_tab13_n HEIGHT=$this->img_tab13_h WIDTH=$this->img_tab13_w BORDER=0></IMG></TD>";
				else
					echo "<TD WIDTH=$this->img_tab18_w><IMG SRC=$this->img_tab18_n HEIGHT=$this->img_tab18_h WIDTH=$this->img_tab18_w BORDER=0></IMG></TD>";
			}
			else
			{
				if ($i == $this->selected)
					echo "<TD WIDTH=$this->img_tab19_w><IMG SRC=$this->img_tab191_n HEIGHT=$this->img_tab19_h WIDTH=$this->img_tab19_w BORDER=0></IMG></TD>";
				else
				{
					// Si le prochain onglet est sélectionné
					if (($i + 1) == $this->selected)
						echo "<TD WIDTH=$this->img_tab193_w><IMG SRC=$this->img_tab193_n HEIGHT=$this->img_tab19_h WIDTH=$this->img_tab193_w BORDER=0></IMG></TD>";
					else
					{
						if ($i > $this->selected)
							echo "<TD WIDTH=$this->img_tab19_w><IMG SRC=$this->img_tab192_n HEIGHT=$this->img_tab19_h WIDTH=$this->img_tab19_w BORDER=0></IMG></TD>";
						else
							echo "<TD WIDTH=$this->img_tab19_w><IMG SRC=$this->img_tab194_n HEIGHT=$this->img_tab19_h WIDTH=$this->img_tab19_w BORDER=0></IMG></TD>";
					}
				}
			}
			
			$i++;
		}

		echo "<TD BACKGROUND=$this->img_tab14_n><IMG SRC=$this->img_tab14_n HEIGHT=$this->img_tab14_h WIDTH=$this->img_tab14_w BORDER=0></IMG></TD>";

		echo "</TABLE></TD>";
		echo "<TD WIDTH=$this->img_tab15_w><IMG SRC=$this->img_tab15_n HEIGHT=$this->img_tab15_h WIDTH=$this->img_tab15_w BORDER=0></IMG></TD>";
		// Fin Barre des onglets

		echo "<TR><TD BACKGROUND=$this->img_tab21_n WIDTH=$this->img_tab21_w><IMG SRC=$this->img_tab21_n HEIGHT=$this->img_tab21_h WIDTH=$this->img_tab21_w BORDER=0></IMG></TD>";
		echo "    <TD BACKGROUND=$this->img_tab22_n ALIGN=CENTER>";
	}

	function end()
	{
		if ($this->nb_onglet == 0) return;

		echo "    &nbsp;</TD>";
		echo "    <TD BACKGROUND=$this->img_tab23_n WIDTH=$this->img_tab23_w><IMG SRC=$this->img_tab23_n HEIGHT=$this->img_tab23_h WIDTH=$this->img_tab23_w BORDER=0></IMG></TD>";

		echo "<TR><TD WIDTH=$this->img_tab31_w><IMG SRC=$this->img_tab31_n HEIGHT=$this->img_tab31_h WIDTH=$this->img_tab31_w BORDER=0></IMG></TD>";
		echo "    <TD BACKGROUND=$this->img_tab32_n><IMG SRC=$this->img_tab32_n HEIGHT=$this->img_tab32_h WIDTH=$this->img_tab32_w BORDER=0></IMG></TD>";
		echo "    <TD WIDTH=$this->img_tab33_w><IMG SRC=$this->img_tab33_n HEIGHT=$this->img_tab33_h WIDTH=$this->img_tab33_w BORDER=0></IMG></TD>";

		echo "</TABLE>";
		// Fin Tableau global
	}
}

?>