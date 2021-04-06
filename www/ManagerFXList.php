<?

include "../include/FXList.php";


// /////////////////////////////////////////////////////////////////////////////////////
// FXList PRESENTATION SIMPLE
// /////////////////////////////////////////////////////////////////////////////////////
class FXListPresentation extends FXList
{
	function __construct($tab)
	{
        $fxbody = new FXBodyArray($tab, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetNumerotation(false);
	}

	function FXSetTitle($title, $alg = "center")
	{
		parent::FXSetTitle($title, $alg);
	}

	function FXReplaceTitle($title, $alg)
	{
		parent::FXSetTitle($title, $alg);
	}

	function FXSetTitleWithIcons($title, $iconL, $iconR, $width)
	{
		parent::FXSetTitle("<div class=\"fxl_title_box\"><div style=\"width:".$width."\"></div><div class=\"fxl_title_box_left\">".$title."</td><td width=\"".$width."\">".$icon."</div></div>", "center");
	}

	function FXSetTitleWithRightIconOnly($title, $icon, $width)
	{
		parent::FXSetTitle("<div class=\"fxl_title_box\"><div class=\"fxl_title_box_left\">".$title."</div><div class=\"fxl_title_box_right\" style=\"width:".$width."\">".$icon."</div></div>", "center");
	}

	function FXSetTitleWithLeftIconOnly($title, $icon, $width)
	{
		parent::FXSetTitle("<div class=\"fxl_title_box\"><div class=\"fxl_title_box_left\" style=\"width:".$width."\">".$icon."</div><div class=\"fxl_title_box_right\">".$title."</div></div>", "center");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES CHAMPIONNATS ACTIFS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListChampionnatsActifs extends FXList
{
	function __construct($only_nul_points = false, $only_speciaux = false, $only_pronostics = false)
	{
		$most_active = JKCache::getCache("../cache/most_active_home.txt", 900, "_FLUX_MOST_ACTIVE_");
		$tab = array();
		foreach($most_active as $c)
		{
			$lib = "<a class=\"blue icon_".$c['type']."\" href=\"championnat_acces.php?ref_champ=".$c['id']."\">".$c['nom']."</a>";
			$actif = "<input type=radio name=\"c_".$c['id']."\" ".($c['actif'] == 1 ? "checked=\"checked\"" : "")." value=\"1\" /> Yes <input type=radio name=\"c_".$c['id']."\" ".($c['actif'] == 0 ? "checked=\"checked\"" : "")." value=\"0\" /> No";
			$special = "<input type=radio name=\"s_".$c['id']."\" ".($c['special'] == 1 ? "checked=\"checked\"" : "")." value=\"1\" /> Yes <input type=radio name=\"s_".$c['id']."\" ".($c['special'] == 0 ? "checked=\"checked\"" : "")." value=\"0\" /> No";
			$pronostic = "<input type=radio name=\"p_".$c['id']."\" ".($c['pronostic'] == 1 ? "checked=\"checked\"" : "")." value=\"1\" /> Yes <input type=radio name=\"p_".$c['id']."\" ".($c['pronostic'] == 0 ? "checked=\"checked\"" : "")." value=\"0\" /> No";
			$ref_champ = "<input type=text name=\"r_".$c['id']."\" value=\"".$c['ref_champ']."\" size=\"3\" />";

			$display = 0;
			if ($only_nul_points)
			{
				if ($c['points'] == 0)	$display = 1;
			}
			else if ($only_speciaux)
			{
				if ($c['special'] == 1)	$display = 1;
			}
			else if ($only_pronostics)
			{
				if ($c['pronostic'] == 1)	$display = 1;
			}
			else
				$display = 1;

			if ($display == 1) $tab [] = array($c['dt_creation'], $lib, $c['email'], $c['points']." points", $actif, $special, $pronostic, $ref_champ);
		}
        $fxbody = new FXBodyArray($tab, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsName(array("Date", "Nom", "Email", "Points", "Actif", "Spécial", "Pronostic", "Ref champ"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "left", "center", "center", "center", "left"));
	    $this->FXSetColumnsColor(array("", "", "", $this->c2_column, $this->color_action_column, $this->color_action_column, $this->color_action_column, $this->color_action_column));
        $this->FXSetColumnsWidth(array("", "5%", "5%", "5%", "9%", "9%", "9%", "9%"));
        $this->FXSetTitle("Liste des championnats");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES PHOTOS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListPhotos extends FXList
{
	function __construct()
	{
        $sfs = new SQLForumServices(-1);
		$lst = $sfs->getListePhotosFull();
		$tab = array();
		foreach($lst as $m)
			$tab [] = array("date" => $m['date'], "nom" => $m['nom'], "title" => $m['title'], "nb_lectures" => $m['nb_lectures'], "nb_reponses" => $m['nb_reponses'], "last_reponse" => $m['last_reponse'], "action" => "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_edit2.gif\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_poubelle.gif\" /></a></td></table>");
        $fxbody = new FXBodyArray($tab);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("date", "nom", "title", "nb_lectures", "nb_reponses", "last_reponse", "action"));
        $this->FXSetColumnsName(array("Date", "Auteur", "Titre", "(L)", "(R)", "Last Post", "Action"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "right", "right", "center"));
        $this->FXSetColumnsWidth(array("", "", "45%", "", "", "", ""));
	    $this->FXSetColumnsColor(array("", "", "", "", "", "", $this->color_action_column));
        $this->FXSetTitle("Liste des Photos");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES CHRONIQUES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListChroniques extends FXList
{
	function __construct()
	{
        $sfs = new SQLForumServices(-1);
		$lst = $sfs->getListeChroniques();
		$tab = array();
		foreach($lst as $m)
			$tab [] = array("date" => $m['date'], "nom" => $m['nom'], "title" => $m['title'], "nb_lectures" => $m['nb_lectures'], "nb_reponses" => $m['nb_reponses'], "last_reponse" => $m['last_reponse'], "action" => "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_edit2.gif\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_poubelle.gif\" /></a></td></table>");
        $fxbody = new FXBodyArray($tab);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("date", "nom", "title", "nb_lectures", "nb_reponses", "last_reponse", "action"));
        $this->FXSetColumnsName(array("Date", "Auteur", "Titre", "(L)", "(R)", "Last Post", "Action"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "right", "right", "center"));
        $this->FXSetColumnsWidth(array("", "", "45%", "", "", "", ""));
	    $this->FXSetColumnsColor(array("", "", "", "", "", "", $this->color_action_column));
        $this->FXSetTitle("Liste des Chroniques");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES ASTUCES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListAstuces extends FXList
{
	function __construct()
	{
        $sfs = new SQLForumServices(-1);
		$lst = $sfs->getListeMessagesLeSaviezVousFull();
		$tab = array();
		foreach($lst as $m)
			$tab [] = array("date" => $m['date'], "nom" => $m['nom'], "title" => $m['title'], "nb_lectures" => $m['nb_lectures'], "nb_reponses" => $m['nb_reponses'], "last_reponse" => $m['last_reponse'], "action" => "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_edit2.gif\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_item('+WHERE+id%3D".$m['id']."');\"><img src=\"../images/small_poubelle.gif\" /></a></td></table>");
        $fxbody = new FXBodyArray($tab);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("date", "nom", "title", "nb_lectures", "nb_reponses", "last_reponse", "action"));
        $this->FXSetColumnsName(array("Date", "Auteur", "Titre", "(L)", "(R)", "Last Post", "Action"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "right", "right", "center"));
        $this->FXSetColumnsWidth(array("", "", "45%", "", "", "", ""));
	    $this->FXSetColumnsColor(array("", "", "", "", "", "", $this->color_action_column));
        $this->FXSetTitle("Liste des Astuces");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES VIDEOS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListVideos extends FXList
{
	function __construct()
	{
       	$requete = "SELECT *, CONCAT('<div style=\"width: 120px; overflow: hidden;\">', url, '</div>') url, CONCAT('<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_item(\'+WHERE+id%3D',id,'\');\"><img src=\"../images/small_edit2.gif\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_item(\'+WHERE+id%3D',id,'\');\"><img src=\"../images/small_poubelle.gif\" /></a></td></table>') action FROM jb_videos ORDER BY date DESC";
        $fxbody = new FXBodySQL($requete);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("date", "titre", "description", "url", "action"));
        $this->FXSetColumnsName(array("Date", "Titre", "Description", "Url", "Action"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "left", "center"));
        $this->FXSetColumnsWidth(array("10%", "30%", "30%", "20%", "10%"));
	    $this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
        $this->FXSetTitle("Liste des vidéos");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES ACTUALITES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListActualites extends FXList
{
	function __construct()
	{
       	$requete = "SELECT *, CONCAT('<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_item(\'+WHERE+id%3D',id,'\');\"><img src=\"../images/small_edit2.gif\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_item(\'+WHERE+id%3D',id,'\');\"><img src=\"../images/small_poubelle.gif\" /></a></td></table>') action FROM jb_actualites ORDER BY date DESC";
        $fxbody = new FXBodySQL($requete);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("date", "resume", "lien", "alaune", "action"));
        $this->FXSetColumnsName(array("Date", "Résumé", "Lien", "A la Une", "Action"));
		$this->FXSetColumnsAlign(array("center", "left", "left", "center", "center"));
        $this->FXSetColumnsWidth(array("10%", "60%", "10%", "10%", "10%"));
	    $this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
        $this->FXSetTitle("Liste des actualités");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// FORUM
// /////////////////////////////////////////////////////////////////////////////////////
class FXListForum extends FXList
{
	function __construct($championnat, $admin = false, $dual = 0)
	{
		$filtre = "";
		if ($dual == 0 || $dual == 5) $filtre = " AND title NOT LIKE '{%}%' AND in_response=0";
		if ($dual == 2) $filtre = " AND title LIKE '{PHOTO}%' AND in_response=0";
		if ($dual == 3) $filtre = " AND title LIKE '{%HELP%}%' AND in_response=0";
		if ($dual == 6) $filtre = " AND title LIKE '{%chronique%}%' AND in_response=0";
		$orderby = "ORDER BY last_reponse DESC";
		if ($dual == 3) $orderby = "ORDER BY title ASC";
		if ($dual == 5) $championnat = 0;

		if ($dual != 0 || $championnat == 0)
	       	$action_col ="CONCAT('<table border=0 cellpadding=0 cellspacing=0 summary=\"\"><tr><td><a href=# onclick=\"javascript:modifier_message(',id,');\"><img src=\"../images/small_edit2.gif\" border=\"0\" alt=\"\" /></a></td><td><a href=\"#\" onclick=\"javascript:supprimer_msg(', id, ');\"><img src=\"../images/small_poubelle.gif\" alt=\"\" /></a></td></tr></table>') action";
		else
			$action_col ="CONCAT('<a href=\"#\" onclick=\"javascript:supprimer_msg(', id, ')\"><img src=\"../images/small_poubelle.gif\" alt=\"\" /></a>') action";

		if ($admin)
		{
    		$select = "SELECT *, CONCAT('<A HREF=\"#\" class=\"blue_none\" title=\"[',ip, '][', agent, ']\">', nom, '<br><span class=\"classic\">', date, '</span></A>') auteur, CONCAT(last_user, '<br><span class=\"classic\">', last_reponse, '</span>') last_post, ".$action_col.", CONCAT('<DIV CLASS=titre_forum><A HREF=forum_message.php?dual=".$dual."&amp;id_msg=', id, '#bottom CLASS=blue>', SUBSTRING_INDEX(title, '}', -1), '</A></DIV>') libelle, CONCAT('<IMG SRC=', smiley, ' BORDER=0  ALT=\"\" />') imgs FROM jb_forum WHERE id_champ=".$championnat." AND del=0 ".$filtre." ".$orderby;
            $fxbody = new FXBodySQL($select);
//			$this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed(array("imgs", "auteur", "libelle", "nb_lectures", "nb_reponses", "last_post", "action"));
            $this->FXSetColumnsName(array("Smiley", "Auteur", "Titre", "(L)", "(R)", "Last Post", "Action"));
            $this->FXSetColumnsWidth(array("", "20%", "50%", "", "", "20%", ""));
            $this->FXSetColumnsAlign(array("", "", "left", "center", "center", ""));
	        $this->FXSetColumnsColor(array("", "", "", "", "", $this->color_action_column, $this->color_action_column));
		}
		else
		{
			$select_smiley = $dual == 2 ? "CONCAT('<img src=\"', image,'\" height=\"64\" width=\"64\" border=\"1\" alt=\"\" />') imgs" : "CONCAT('<img src=\"',smiley,'\" alt=\"\" class=\"smiley_forum\" />') imgs";
			$column_smiley = $dual == 2 ? "Photo" : "Smiley";
    		$select = "SELECT *, CONCAT('<div class=\"auteur_forum\"><a href=\"#\" class=\"blue_none\" title=\"[',ip, '][', agent, ']\"><span>', nom, '</span><br /><span class=\"classic\">', date, '</span></a></div>') auteur, CONCAT('<div class=\"last_post_forum\">', last_user, '<br /><span class=\"classic\">', last_reponse, '</span></div>') last_post, CONCAT('<div class=\"titre_forum\"><a href=\"forum_message.php?dual=".$dual."&amp;id_msg=', id, '#bottom\" class=\"blue\">', SUBSTRING_INDEX(title, '}', -1), '</a></div>') libelle, ".$select_smiley." FROM jb_forum WHERE id_champ=".$championnat." AND del=0 ".$filtre." ".$orderby;
            $fxbody = new FXBodySQL($select);
//			$this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed(array("imgs", "auteur", "libelle", "nb_lectures", "nb_reponses", "last_post"));
            $this->FXSetColumnsName(array($column_smiley, "Auteur", "Titre", "(L)", "(R)", "Last Post"));
            $this->FXSetColumnsWidth(array("", "", "", "", "", ""));
            $this->FXSetColumnsAlign(array("", "", "left", "center", "center", ""));
	        $this->FXSetColumnsColor(array("", "", "", "", "", $this->color_action_column));
// Le tri inversé n'est pas opérationnel !!!
//			$this->FXSetSortable(true);
		}
        $this->FXSetTitle("Forum");
		$this->FXSetNumeroInverse(true);
		$this->FXSetPagination("forum.php?dual=".$dual);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// Who is ?
// /////////////////////////////////////////////////////////////////////////////////////
class FXListForumWhois extends FXList
{
	function initDatas($championnat)
	{
		$tab = array();
		$select = "SELECT * FROM jb_forum WHERE id_champ=".$championnat." AND ip != '' ORDER BY date DESC";
	   	$res = dbc::execSql($select);
	   	while($row = mysqli_fetch_array($res))
			$tab[$row['ip']][] = "<a href=\"http://www.jorkers.com/www/forum_redirect.php?champ=".$championnat."&amp;id_msg=".$row['id']."#ITEM_".$row['in_response']."\" class=\"blue\">".$row['nom']."</a> <span style=\"font-weight:normal;\">[".ToolBox::mysqldate2date($row['date'])."] [".$row['title']."]</span>";

		$rows = array();
		while(list($ip, $val) = each($tab))
		{
			$pseudos = "";
			while(list($cle, $val2) = each($tab[$ip]))
			    $pseudos .= ($pseudos == "" ? "" : "<br />").$val2;

			$rows[] = array("ip" => $ip, "nom" => $pseudos);
		}

		return $rows;
	}

	function __construct($championnat)
	{
        $fxbody = new FXBodyArray($this->initDatas($championnat));
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetColumnsDisplayed(array("nom"));
        $this->FXSetColumnsName(array("Auteur"));
        $this->FXSetColumnsWidth(array("", "", ""));
        $this->FXSetColumnsAlign(array("left", "left", ""));
        $this->FXSetColumnsColor(array("", "", ""));
        $this->FXSetTitle("Who is ?");
		$this->FXSetPagination("whois.php");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES SAISONS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListSaisons extends FXList
{
	function __construct($championnat, $admin = false)
	{
		if ($admin)
		{
	       	$requete = "SELECT *, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_saison(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_saison(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action FROM jb_saisons WHERE id_champ=".$championnat." ORDER BY date_creation";
	        $fxbody = new FXBodySQL($requete);
//	        $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed(array("nom", "date_creation", "active", "action"));
	        $this->FXSetColumnsName(array("Nom", "Date de création", "Active", "Action"));
            $this->FXSetColumnsAlign(array("left", "center", "center", "center"));
	        $this->FXSetColumnsWidth(array("40%", "", "", "5%"));
	        $this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
//			$this->FXSetExtraIcons("<A HREF=\"#\" onMouseover=\"javascript:showmenuwithtitle(event, linkset, 280, 'MENU');\" onMouseout=\"javascript:delayhidemenu();\"><IMG SRC=../images/extra_icon.gif BORDER=0 /></A>");
		}
		else
		{
	       	$requete = "SELECT * FROM jb_saisons WHERE id_champ=".$championnat." ORDER BY date_creation";
	        $fxbody = new FXBodySQL($requete);
//	        $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed(array("nom", "date_creation", "active"));
	        $this->FXSetColumnsName(array("Nom", "Date de création", "Active"));
            $this->FXSetColumnsAlign(array("left", "center", "center"));
	        $this->FXSetColumnsWidth(array("40%", "", ""));
	        $this->FXSetColumnsColor(array("", "", ""));
		}
        $this->FXSetTitle("Liste des saisons");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListPlayers extends FXList
{
	function __construct($championnat, $type_championnat, $id_saison, $admin = false, $stat_access = true, $delta = "")
	{
		$filtre = "";

		// Pour les tournois LIBRE on récupère la liste des joueurs à partir du champ "joueurs" de la saison en cours
		$req = "SELECT * FROM jb_saisons WHERE id=".$id_saison;
		$res = dbc::execSql($req);
		$saison = mysqli_fetch_array($res);
		if ($type_championnat == _TYPE_LIBRE_)
		{
			$filtre = (isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".$saison['joueurs'].")" : "");
		}
		// Pour les tournois ou championnats on récupère la liste des joueurs à partir de la liste des équipes de la saison en cours
		else
		{
			/*
			$lst = "";
			$req = "SELECT * FROM jb_equipes WHERE id IN (".$saison['equipes'].")";
			$res = dbc::execSql($req);
			while ($equipes = mysqli_fetch_array($res))
				if ($equipes['joueurs'] != "") $lst .= ($lst == "" ? "" : ",").$equipes['joueurs'];

			$filtre = ($lst != "" ? " AND id IN (".str_replace("|", ",", $lst).")" : "");
			*/
			$j = "";
			if ($saison['joueurs'] != "")
			{
				$tmp = explode(",", $saison['joueurs']);
				foreach($tmp as $item)
					if ($item != "") $j .= ($j != "" ? "," : "").$item;
			}
			$filtre = ($j != "" ? " AND id IN (".$j.")" : "");
		}

		if ($admin)
		{
	       	$requete = "SELECT ELT(etat+1, 'Actif', '<IMG SRC=../images/fleches/blesse.gif BORDER=0 ALT=\"blessé\" />', '<IMG SRC=../images/fleches/vacances.gif BORDER=0 ALT=\"vacances\" />') etat, ELT(presence+1, 'Non', 'Oui') presence, pseudo, CONCAT('<a HREF=\"#\"  onmouseover=\"show_info_upright(\'<IMG SRC=', IF(STRCMP(photo, ''), photo, '../uploads/linconnu.gif'), ' />\', event);\" onmouseout=\"close_info();\"><IMG SRC=', IF(STRCMP(photo, ''), photo, '../uploads/linconnu.gif'), ' style=\"border: 1px solid #AAAAAA;\" HEIGHT=20 WIDTH=20 ALT=\"Photo joueur\" /></A>') photo, CONCAT(nom, ' ', prenom) nom, CONCAT('<A HREF=\"stats_detail_joueur.php?id_detail=', id, '\"><IMG SRC=../images/stats.gif BORDER=0 ALT=\"\" /></A>') stats, CONCAT('<TABLE BORDER=0 SUMMARY=\"\" CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_joueur(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 ALT=\"\" /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_joueur(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 ALT=\"\" /></A></TD></TABLE>') action FROM jb_joueurs WHERE id_champ=".$championnat." ".$filtre." ORDER BY nom";
	        $fxbody = new FXBodySQL($requete, $delta);
//	        $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed($stat_access ? array("photo", "nom", "pseudo", "presence", "etat", "stats", "action") : array("photo", "nom", "pseudo", "presence", "etat", "action"));
	        $this->FXSetColumnsName($stat_access ? array("Photo", "Nom", "Pseudo", "Régulier", "Etat", "Statistiques", "Action") : array("Photo", "Nom", "Pseudo", "Régulier", "Etat", "Action"));
            $this->FXSetColumnsAlign(array("", "left", "left", "", "", "", "", ""));
	        $this->FXSetColumnsWidth(array("5%", "40%", "", "7%", "7%", "7%", "5%"));
	        $this->FXSetColumnsColor($stat_access ? array("", "", "", "", "", "", $this->color_action_column) : array("", "", "", "", "", $this->color_action_column));
//			$this->FXSetExtraIcons("<A HREF=\"#\" onMouseover=\"javascript:showmenuwithtitle(event, linkset, 280, 'MENU');\" onMouseout=\"javascript:delayhidemenu();\"><IMG SRC=../images/extra_icon.gif BORDER=0 /></A>");
		}
		else
		{
	       	$requete = "SELECT ELT(etat+1, 'Actif', '<img src=\"../images/fleches/blesse.gif\" alt=\"blessé\" />', '<img src=\"../images/fleches/vacances.gif\" alt=\"vacances\" />') etat, ELT(presence+1, 'Non', 'Oui') presence, pseudo, CONCAT('<a href=\"#\" onmouseover=\"show_info_upright(\'<img src=', IF(STRCMP(photo, ''), photo, '../uploads/linconnu.gif'), '>\', event);\" onmouseout=\"close_info();\"><img src=\"', IF(STRCMP(photo, ''), photo, '../uploads/linconnu.gif'), '\" style=\"border: 1px solid #AAAAAA;\" height=\"20\" WIDTH=\"20\" alt=\"Photo joueur\" /></A>') photo, CONCAT(nom, ' ', prenom) nom, CONCAT('<a href=\"stats_detail_joueur.php?id_detail=', id, '\"><img src=\"../images/stats.gif\" alt=\"\" /></a>') stats FROM jb_joueurs WHERE id_champ=".$championnat." ".$filtre." ORDER BY nom";
	        $fxbody = new FXBodySQL($requete, $delta);
//	        $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetColumnsDisplayed($stat_access ? array("photo", "nom", "pseudo", "presence", "etat", "stats") : array("photo", "nom", "pseudo", "presence", "etat"));
	        $this->FXSetColumnsName($stat_access ? array("Photo", "Nom", "Pseudo", "Régulier", "Etat", "Statistiques") :  array("Photo", "Nom", "Pseudo", "Régulier", "Etat"));
            $this->FXSetColumnsAlign(array("", "left", "left", "", "", "", ""));
	        $this->FXSetColumnsWidth(array("5%", "40%", "", "7%", "7%", "7%"));
			$this->FXSetColumnsSort(array("istr", "istr", "istr", "istr", "istr"));
//			$this->FXSetSortable(true);
		}
        $this->FXSetTitle("Liste des joueurs");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MEILLEURES JOUEURS ATTAQUANTS/DEFENSEURS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListBestPlayers extends FXList
{
	function __construct($best_teams, $option = "0")
	{
        $fxbody = new FXBodyArray($best_teams);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetTitle($option == 0 ? "Meilleurs défenseurs" :"Meilleurs attaquants");
        $this->FXSetColumnsDisplayed(array("nom", "moy_marquesA", "moy_encaissesD"));
        $this->FXSetColumnsName(array("Joueur", "Moy Buts Marqués", "Moy Buts encaissés"));
        $this->FXSetColumnsWidth(array("50%", "", ""));
        $this->FXSetColumnsColor(array("", $option == 0 ? "" : $this->color_action_column, $option == 0 ? $this->color_action_column : ""));
        $this->FXSetNbCols(7);
	}
}



// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListTeams extends FXList
{
	var $championnat;
	var $type;
	var $id_saison;
	var $fxbody;
	var $datas;

	function initDatas($requete)
	{
		$sss = new SQLSaisonsServices($this->championnat, $this->id_saison);
		$saison = $sss->getSaison();
		$lst2 = explode(',', $saison['joueurs']);
		foreach($lst2 as $js) $joueurs_saison[$js] = $js;

		$this->datas = array();
		$res = dbc::execSql($requete);
		while($row = mysqli_fetch_array($res))
		{
			// Compteur pour championnat libre pour savoir si on affiche l'equipe (au moins 2 joueurs de l'equipe qui sont dans la saison
			$nb_joueurs_in_saison = 0;

			$items = explode('@', $row['nb_joueurs']);
			if (isset($items[1]) && $items[1] > 0)
			{
				$joueurs = explode('|', $items[1]);
				foreach($joueurs as $j)
				{
					if (isset($joueurs_saison[$j])) $nb_joueurs_in_saison++;
				}
			}

			if ($this->type == _TYPE_LIBRE_ && $nb_joueurs_in_saison < 2) continue;

			$this->datas[] = $row;
		}
	}

	function formatForDisplay()
	{
		if (count($this->fxbody->tab) == 0) return;

		$sjs = new SQLJoueursServices($this->championnat);
		$lst = $sjs->getListeJoueurs();

		while(list($cle, $val) = each($this->fxbody->tab))
		{
			$items = explode('@', $this->fxbody->tab[$cle]['nb_joueurs']);
			if (isset($items[1]) && $items[1] > 0)
			{
				$joueurs = explode('|', $items[1]);
				$tag_joueurs = "[";
				$img_joueurs = "";
				foreach($joueurs as $j)
				{
					$tag_joueurs .= (strlen($tag_joueurs) > 1 ? ", " : "").$lst[$j]['pseudo'];
					$img_joueurs .= "<img height=100 width=100 src=".($lst[$j]['photo'] != "" ? $lst[$j]['photo'] : "../uploads/linconnu.gif")." />";
				}
				$tag_joueurs .= "]";
				if ($this->fxbody->tab[$cle]['photo'] != "") $img_joueurs = "<img height=200 width=200 src=".$this->fxbody->tab[$cle]['photo']." />";

				$this->fxbody->tab[$cle]['nb_joueurs'] = "<ul class=\"nb_joueurs\"><li class=\"numb\">".$items[0]."</li><li><a href=\"#\" onmouseover=\"show_info_upleft('".$img_joueurs."', event);\" onmouseout=\"close_info();\"><img src=\"../images/player_choice.gif\" alt=\"".$tag_joueurs."\" title=\"".$tag_joueurs."\" /></a></li></ul>";
			}
			else
				$this->fxbody->tab[$cle]['nb_joueurs'] = $items[0];
		}
	}

	function __construct($championnat, $type, $id_saison, $admin = false)
	{
		$this->championnat = $championnat;
		$this->type        = $type;
		$this->id_saison   = $id_saison;

		$filtre = "";

		// Pour les tournois/Championnat classiques on peut les equipes issues du champ 'equipes' de la saison en cours
		if ($type != _TYPE_LIBRE_)
		{
			$req = "SELECT * FROM jb_saisons WHERE id=".$id_saison;
			$res = dbc::execSql($req);
			$saison = mysqli_fetch_array($res);
			$filtre = $saison['equipes'] == "" ? " AND id IN (-1) " : " AND id IN (".$saison['equipes'].")";
		}

		if ($admin)
		{
			$requete = "SELECT photo, CONCAT('<A HREF=stats_detail_equipe.php?id_detail=', id, '><IMG SRC=../images/stats.gif BORDER=0 ALT=\"\" /></A>') stats, nom, CONCAT(nb_joueurs, '@', joueurs) nb_joueurs, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_equipe(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_equipe(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action FROM jb_equipes WHERE id_champ=".$championnat." ".$filtre." ORDER BY nom";
//	        $this->fxbody = new FXBodySQL($requete);
			$this->initDatas($requete);
	        $this->fxbody = new FXBodyArray($this->datas);
			$this->formatForDisplay();
	        $this->FXList($this->fxbody);
	        $this->FXSetTitle("Liste des équipes");
	        $this->FXSetColumnsDisplayed(array("nom", "nb_joueurs", "stats", "action"));
	        $this->FXSetColumnsName(array("Nom", "Nb joueurs", "Statistiques", "Action"));
	        $this->FXSetColumnsWidth(array("65%", "", "", ""));
	        $this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
//			$this->FXSetExtraIcons("<A HREF=\"#\" onMouseover=\"javascript:showmenuwithtitle(event, linkset, 280, 'MENU');\" onMouseout=\"javascript:delayhidemenu();\"><IMG SRC=../images/extra_icon.gif BORDER=0 /></A>");
		}
		else
		{
			$requete = "SELECT photo, nom, CONCAT(nb_joueurs, '@', joueurs) nb_joueurs, CONCAT('<A HREF=stats_detail_equipe.php?id_detail=', id, '><IMG SRC=../images/stats.gif BORDER=0 ALT=\"\" /></A>') stats FROM jb_equipes WHERE id_champ=".$championnat." ".$filtre." ORDER BY nom";
//	        $this->fxbody = new FXBodySQL($requete);
			$this->initDatas($requete);
	        $this->fxbody = new FXBodyArray($this->datas);
			$this->formatForDisplay();
	        $this->FXList($this->fxbody);
	        $this->FXSetTitle("Liste des équipes");
	        $this->FXSetColumnsDisplayed(array("nom", "nb_joueurs", "stats"));
	        $this->FXSetColumnsName(array("Equipe", "Nb joueurs", "Statistiques"));
	        $this->FXSetColumnsWidth(array("65%", "", ""));
//			$this->FXSetSortable(true);
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES JOURNEES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListJournees extends FXList
{
	var $admin;
	var $fxbody;

	function formatForDisplay()
	{
		while(list($cle, $val) = each($this->fxbody->tab))
		{
			$this->fxbody->tab[$cle]['nom'] = ToolBox::conv_lib_journee($this->fxbody->tab[$cle]['nom']);
			if ($this->fxbody->tab[$cle]['virtuelle'] == 1)
				$this->fxbody->tab[$cle]['nom'] .= "[Virtuelle]";
			if ($this->admin && $this->fxbody->tab[$cle]['id_journee_mere'] != 0)
				$this->fxbody->tab[$cle]['action'] = "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR></TD><TD><A HREF=# onClick=\"javascript:supprimer_journee_alias('".$this->fxbody->tab[$cle]['id']."');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>";
		}
	}

	function __construct($championnat, $type_championnat, $admin = false)
	{
		$this->admin = $admin;
		if ($admin)
		{
	       	$requete = "SELECT id, id_champ, IF(id_journee_mere > 0, 'Oui', 'Non') id_journee_mere, CONCAT('<A HREF=', ELT(virtuelle+1, '".($type_championnat == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php")."', 'journees_virtuelles_ajouter.php'), '?pkeys_where_jb_journees=+WHERE+id%3D', id,'><IMG BORDER=0 SRC=../images/small_ball.gif ALT=\'Gestion des matchs\' />') matchs, nom, ELT(virtuelle+1, 'Non', 'Oui') virtuelle, date, heure, duree, IF(id_journee_mere > 0, CONCAT('<A HREF=# onClick=\"javascript:supprimer_journee_alias(',id,');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A>'), CONCAT('<A HREF=# onClick=\"javascript:supprimer_journee(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A>')) action FROM jb_journees WHERE id_champ IN (".$championnat.") ORDER BY date DESC, nom DESC";
	        $this->fxbody = new FXBodySQL($requete);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetColumnsDisplayed(array("nom", "date", "virtuelle", "id_journee_mere", "heure", "duree", "matchs", "action"));
	        $this->FXSetColumnsName(array("Nom", "Date", "Virtuelle", "Alias", "Heure", "Durée", "Matchs", "Action"));
	        $this->FXSetColumnsWidth(array("40%", "", "", "", "", ""));
	        $this->FXSetColumnsColor(array("", "", "", "", "", $this->color_action_column));
//			$this->FXSetExtraIcons("<A HREF=\"#\" onMouseover=\"javascript:showmenuwithtitle(event, linkset, 280, 'MENU');\" onMouseout=\"javascript:delayhidemenu();\"><IMG SRC=../images/extra_icon.gif BORDER=0 /></A>");
		}
		else
		{
	       	$requete = "SELECT id_champ, id_journee_mere, CONCAT('<A HREF=', ELT(virtuelle+1, '".($type_championnat == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php")."', 'journees_virtuelles_ajouter.php'), '?pkeys_where_jb_journees=+WHERE+id%3D', id,'><IMG BORDER=0 SRC=../images/small_ball.gif ALT=\'Gestion des matchs\' /></A>') matchs, nom, virtuelle, date, heure, duree FROM jb_journees WHERE id_champ IN (".$championnat.") ORDER BY date DESC, nom DESC";
	        $this->fxbody = new FXBodySQL($requete);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetColumnsDisplayed(array("nom", "date", "heure", "duree", "matchs"));
	        $this->FXSetColumnsName(array("Nom", "Date", "Heure", "Durée", "Matchs"));
	        $this->FXSetColumnsWidth(array("40%", "", "", "", ""));
//			$this->FXSetSortable(true);
		}
        $this->FXSetTitle("Liste des journées");
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT PAR POURCENTAGE DE VICTOIRES DES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsTeams extends FXList
{
	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan("N°",           $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("% Matchs Gagnés", $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, 3);
        HTMLTable::printCellWithColSpan("Sets",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 4);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("J",         $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($best_teams)
	{
		$t = array();
		reset($best_teams);
		foreach($best_teams as $team)
		{
			$team->nom = "<A HREF=stats_detail_equipe.php?id_detail=".$team->id." CLASS=blue>".$team->nom."</A>";
			if ($team->sets_diff > 0) $team->sets_diff = "+".$team->sets_diff;
			if ($team->diff > 0) $team->diff = "+".$team->diff;
			$t[] = $team;
		}
        $fxbody = new FXBodyArray($t);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetTitle("Statistiques Equipes");
        $this->FXSetColumnsDisplayed(array("nom", "pourc_gagnes", "matchs_joues", "matchs_gagnes", "matchs_perdus", "sets_joues", "sets_gagnes", "sets_perdus", "sets_diff", "buts_marques", "buts_encaisses", "diff"));
        $this->FXSetColumnsName(array("Equipe", "% Gagnés", "Matchs Joués", "Matchs Gagnés", "Matchs Perdus", "Sets Joués", "Sets Gagnés", "Sets Perdus", "Sets Diff", "Buts Marques", "Buts Encaisses", "Diff"));
        $this->FXSetColumnsWidth(array("30%", "", "", "", "", "", ""));
        $this->FXSetColumnsColor(array("", $this->c1_column, $this->c2_column, $this->c2_column, $this->c2_column, "", "", "", $this->color_action_column, "", "", $this->color_action_column));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT PAR POURCENTAGE DE VICTOIRES DES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsAttDef extends FXList
{
	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan("N°",           $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Moy buts",     $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Matchs Joués", $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithRowSpan("Sets joués",   $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);

        echo "<TR>";
        HTMLTable::printCell("Att",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Def",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($best_att_def, $attaque = 0)
	{
		$t = array();
		reset($best_att_def);
		foreach($best_att_def as $team)
		{
			$team->nom = "<A HREF=stats_detail_equipe.php?id_detail=".$team->id." CLASS=blue>".$team->nom."</A>";
			$t[] = $team;
		}
        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetTitle("Statistiques Equipes");

		$mytab = array();
		$i = 0;
		$mytab[$i++] = "nom";
		$mytab[$i++] = "stat_attaque";
		$mytab[$i++] = "stat_defense";
		$mytab[$i++] = "matchs_joues";
		if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_joues";
		$this->FXSetColumnsDisplayed($mytab);

        $this->FXSetColumnsWidth(array("50%", "10%", "10%", "10%", "10%"));
        $this->FXSetColumnsAlign(array("left", "", ""));
        $this->FXSetColumnsColor(array("", $attaque == 1 ? $this->c1_column : "",  $attaque == 1 ? "" : $this->c2_column));
        $this->FXSetNbCols(7);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT PAR POINTS DES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsTeamsII extends FXList
{
	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan("N°",           $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Points",       $this->c1_title,           "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title,           "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 0 ? 3 : 4);
        if (sess_context::getGestionSets() == 1)
	        HTMLTable::printCellWithColSpan("Sets",         $this->c3_title,           "", "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 4);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);

        if (sess_context::getGestionSets() == 1)
        {
	        HTMLTable::printCell("J",         $this->c3_title, "", "center", _CELLBORDER_SE_);
	        HTMLTable::printCell("G",         $this->c3_title, "", "center", _CELLBORDER_SE_);
			if (sess_context::getGestionMatchsNul() == 1)         HTMLTable::printCell("N",      $this->c3_title, "", "center", _CELLBORDER_SE_);
	        HTMLTable::printCell("P",         $this->c3_title, "", "center", _CELLBORDER_SE_);
			if (sess_context::getGestionMatchsNul() != 1) HTMLTable::printCell("Diff",      $this->c3_title, "", "center", _CELLBORDER_SE_);
	    }
        HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($best_teams, $xmloutput = false)
	{
		$t = array();
		reset($best_teams);
		foreach($best_teams as $team)
		{
			if (!$xmloutput)
				$team->nom = "<A HREF=stats_detail_equipe.php?id_detail=".$team->id." CLASS=blue>".$team->nom."</A>";
			if ($team->sets_diff > 0) $team->sets_diff = "+".$team->sets_diff;
			if ($team->diff > 0) $team->diff = "+".$team->diff;
			$t[] = $team;
		}
        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetTitle("Statistiques Equipes");

		$i=0;
		$cols = array();
		$cols[$i++] = "nom";
		$cols[$i++] = "points";
		$cols[$i++] = "matchs_joues";
		$cols[$i++] = "matchs_gagnes";
		if (sess_context::getGestionMatchsNul() == 1) $cols[$i++] = "matchs_nuls";
		$cols[$i++] = "matchs_perdus";
        if (sess_context::getGestionSets() == 1)
        {
			$cols[$i++] = "sets_joues";
			$cols[$i++] = "sets_gagnes";
			if (sess_context::getGestionMatchsNul() == 1) $cols[$i++] = "sets_nuls";
			$cols[$i++] = "sets_perdus";
			if (sess_context::getGestionMatchsNul() != 1) $cols[$i++] = "sets_diff";
		}
		$cols[$i++] = "buts_marques";
		$cols[$i++] = "buts_encaisses";
		$cols[$i++] = "diff";
		$this->FXSetColumnsDisplayed($cols);
		$i=0;
		$colors = array();
		$colors[$i++] = "";
		$colors[$i++] = $this->c1_column;
		$colors[$i++] = $this->c2_column;
		$colors[$i++] = $this->c2_column;
		if (sess_context::getGestionMatchsNul() == 1) $colors[$i++] = $this->c2_column;
		$colors[$i++] = $this->c2_column;
        if (sess_context::getGestionSets() == 1)
        {
			$colors[$i++] = $this->c3_column;
			$colors[$i++] = $this->c3_column;
			if (sess_context::getGestionMatchsNul() == 1) $colors[$i++] = $this->c3_column;
			$colors[$i++] = $this->c3_column;
			if (sess_context::getGestionMatchsNul() != 1) $colors[$i++] = $this->c3_column;
		}
		$colors[$i++] = "";
	    $this->FXSetColumnsColor($colors);

        $this->FXSetColumnsWidth(array("40%", "", ""));
	}
	function getXmlClassement()
	{
		// Synthèse des stats avec tri sur tournoi_points
		reset($this->body->tab);
		while(list($cle, $val) = each($this->body->tab))
		{
			$elt = $this->body->tab[$cle];
			echo "<EQUIPE CLASSEMENT=\"".($cle+1)."\" NOM=\"".$elt['nom']."\" POINTS=\"".$elt['points']."\" MATCHS=\"".$elt['matchs_joues']."|".$elt['matchs_gagnes']."|".$elt['matchs_perdus']."\" SETS=\"".$elt['sets_joues']."|".$elt['sets_gagnes']."|".$elt['sets_perdus']."|".$elt['sets_diff']."\" BUTS=\"".$elt['buts_marques']."|".$elt['buts_encaisses']."|".$elt['diff']."\">";
			echo "</EQUIPE>\n";
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MEILLEURES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListBestTeams extends FXList
{
	function __construct($best_teams, $delta = "")
	{
        $fxbody = ($delta == "") ? new FXBodyArray($best_teams) : new FXBodyArray($best_teams, $delta);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetTitle("Equipes les + performantes");
        $this->FXSetColumnsDisplayed(array("nom", "matchs_joues", "pourc_gagnes"));
        $this->FXSetColumnsName(array("Equipe", "Matchs Joués", "Matchs Gagnés"));
        $this->FXSetColumnsPadAfter(array("", "", " %"));
        $this->FXSetColumnsWidth(array("50%", "", ""));
        $this->FXSetColumnsColor(array("", "", $this->color_action_column));
        $this->FXSetNbCols(7);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES EQUIPES LE + SUR LE TERRAIN
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMostOnGround extends FXList
{
	function __construct($most_matchs, $delta = "")
	{
        $fxbody = ($delta == "") ? new FXBodyArray($most_matchs) : new FXBodyArray($most_matchs, $delta);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
        $this->FXSetTitle("Equipes les + sur le terrain");
        $this->FXSetColumnsDisplayed(array("nom", "matchs_joues", "pourc_gagnes"));
        $this->FXSetColumnsName(array("Equipe", "Matchs Joués", "Matchs Gagnés"));
        $this->FXSetColumnsPadAfter(array("", "", " %"));
        $this->FXSetColumnsWidth(array("50%", "", ""));
        $this->FXSetNbCols(4);
        $this->FXSetColumnsColor(array("", $this->color_action_column, ""));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE GENERIQUE DE MATCHS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsGen extends FXList
{
	var $fxbody;

	function getClassScore($score1, $score2, $eq)
	{
		$ret = "";

		if ($score1 == "" || $score2 == "") return $ret;

		if ($eq == 1)
			$ret = ($score1 > $score2) ? "score_gagne" : "score_perdu";

		if ($eq == 2)
			$ret = ($score2 > $score1) ? "score_gagne" : "score_perdu";

		return $ret;
	}

	function formatForDisplay($fannys = true)
	{
		$counts = array();
		while(list($cle, $val) = each($this->fxbody->tab))
		{
			if ($val == _FXSEPARATORWITHINIT_) continue;
			if ($val == _FXSEPARATOR_) continue;
			if ($val == _FXLINESEPARATOR_) continue;

			if (!isset($match['nbset']) || $match['nbset'] < 1) continue;

			if (isset($val['id_equipe1']))
			{
				if (!isset($counts[$val['id_equipe1']])) $counts[$val['id_equipe1']] = 1;
				if (!isset($counts[$val['id_equipe2']])) $counts[$val['id_equipe2']] = 1;
			}

			if (isset($val['mdate']))
				$this->fxbody->tab[$cle]['mdate'] = "<font class=\"equipe_perdu\">".Toolbox::mysqldate2date($val['mdate'])."</font>";

			// Choix du vainqueur
			$vainqueur = StatsJourneeBuilder::kikiGagne($val);

			// Mise en forme du score
			$sm = new StatMatch($val['resultat'], $val['nbset']);
			$score = $sm->getScore();

			if (!isset($score[0][0])) $score[0][0] = "";
			if (!isset($score[0][1])) $score[0][1] = "";
			if (!isset($score[0][2])) $score[0][2] = "";
			if (!isset($score[0][3])) $score[0][3] = "";
			if (!isset($score[0][4])) $score[0][4] = "";
			if (!isset($score[1][0])) $score[1][0] = "";
			if (!isset($score[1][1])) $score[1][1] = "";
			if (!isset($score[1][2])) $score[1][2] = "";
			if (!isset($score[1][3])) $score[1][3] = "";
			if (!isset($score[1][4])) $score[1][4] = "";

			$lib = "<TABLE CELLPADDING=0 CELLSPACING=0 CLASS=score SUMMARY=\"\">";
			$lib .= "<TR>";

			// Gestion des forfaits d'équipes pour l'affichage du score
			if ($score == -1 || $score == -2)
			{
				$lib .= "<TD CLASS=score_perdu><FONT CLASS=score_perdu>forfait</FONT></TD>";
			}
			else
			{
				$lib .= "<TD CLASS=\"".$this->getClassScore($score[0][0], $score[0][1], 1)."\"><FONT CLASS=\"".$this->getClassScore($score[0][0], $score[0][1], 1)."\">".$score[0][0]."</FONT></TD>";
				if ($val['nbset'] >= 2) $lib .= "<TD CLASS=\"".$this->getClassScore($score[1][0], $score[1][1], 1)."\"><FONT CLASS=\"".$this->getClassScore($score[1][0], $score[1][1], 1)."\">".$score[1][0]."</FONT></TD>";
				if ($val['nbset'] >= 3) $lib .= "<TD CLASS=\"".$this->getClassScore($score[2][0], $score[2][1], 1)."\"><FONT CLASS=\"".$this->getClassScore($score[2][0], $score[2][1], 1)."\">".$score[2][0]."</FONT></TD>";
				if ($val['nbset'] >= 4) $lib .= "<TD CLASS=\"".$this->getClassScore($score[3][0], $score[3][1], 1)."\"><FONT CLASS=\"".$this->getClassScore($score[3][0], $score[3][1], 1)."\">".$score[3][0]."</FONT></TD>";
				if ($val['nbset'] >= 5) $lib .= "<TD CLASS=\"".$this->getClassScore($score[4][0], $score[4][1], 1)."\"><FONT CLASS=\"".$this->getClassScore($score[4][0], $score[4][1], 1)."\">".$score[4][0]."</FONT></TD>";
				if ($val['nbset'] >  1) $lib .= "<TR>";
				$lib .= "<TD CLASS=\"".$this->getClassScore($score[0][0], $score[0][1], 2)."\"><FONT CLASS=\"".$this->getClassScore($score[0][0], $score[0][1], 2)."\">".$score[0][1]."</FONT></TD>";
				if ($val['nbset'] >= 2) $lib .= "<TD CLASS=\"".$this->getClassScore($score[1][0], $score[1][1], 2)."\"><FONT CLASS=\"".$this->getClassScore($score[1][0], $score[1][1], 2)."\">".$score[1][1]."</FONT></TD>";
				if ($val['nbset'] >= 3) $lib .= "<TD CLASS=\"".$this->getClassScore($score[2][0], $score[2][1], 2)."\"><FONT CLASS=\"".$this->getClassScore($score[2][0], $score[2][1], 2)."\">".$score[2][1]."</FONT></TD>";
				if ($val['nbset'] >= 4) $lib .= "<TD CLASS=\"".$this->getClassScore($score[3][0], $score[3][1], 2)."\"><FONT CLASS=\"".$this->getClassScore($score[3][0], $score[3][1], 2)."\">".$score[3][1]."</FONT></TD>";
				if ($val['nbset'] >= 5) $lib .= "<TD CLASS=\"".$this->getClassScore($score[4][0], $score[4][1], 2)."\"><FONT CLASS=\"".$this->getClassScore($score[4][0], $score[4][1], 2)."\">".$score[4][1]."</FONT></TD>";
			}
			$lib .= "</TABLE>";
			$this->fxbody->tab[$cle]['resultat'] = $lib;

			$anchor1_debut = "";
			$anchor2_debut = "";
			$anchor_fin   = "";
			if (isset($val['id_equipe1']))
			{
				$anchor1_debut = "<DIV CLASS=div_equipe ID=\"M".$val['id_equipe1'].$counts[$val['id_equipe1']]."\" onmouseover=\"highlight_equipe('".$val['id_equipe1']."')\">";
				$anchor2_debut = "<DIV CLASS=div_equipe ID=\"M".$val['id_equipe2'].$counts[$val['id_equipe2']]."\" onmouseover=\"highlight_equipe('".$val['id_equipe2']."')\">";
				$anchor_fin   = "</DIV>";
			}

			if (sess_context::getGestionFanny() == 1 && $val['fanny'] == 1 &&  $fannys)
				$lib = $anchor1_debut."<TABLE BORDER=0 SUMMARY=\"\"><TR><TD><IMG SRC=../images/animated/20anidot1a.gif ALT=\"Fanny in your face\" /></TD><TD><FONT CLASS=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne")."\">".$val['nom1']."</FONT></TD></TABLE>".$anchor_fin;
			else
				$lib = $anchor1_debut."<FONT CLASS=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne")."\">".$val['nom1']."</FONT>".$anchor_fin;
			$this->fxbody->tab[$cle]['nom1'] = $lib;

			if (sess_context::getGestionFanny() == 1 && $val['fanny'] == 1 && $fannys)
				$lib = $anchor2_debut."<TABLE BORDER=0><TR><TD><IMG SRC=../images/animated/20anidot1a.gif ALT=\"Fanny in your face\" /></TD><TD><FONT CLASS=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne")."\">".$val['nom2']."</FONT></TD></TABLE>".$anchor_fin;
			else
				$lib = $anchor2_debut."<FONT CLASS=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne")."\">".$val['nom2']."</FONT>".$anchor_fin;
			$this->fxbody->tab[$cle]['nom2'] = $lib;

			if (isset($val['id_equipe1']))
			{
				$counts[$val['id_equipe1']]++;
				$counts[$val['id_equipe2']]++;
			}
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES FANNYS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListFannys extends FXListMatchsGen
{
	function __construct($championnat, $requete, $delta = "")
	{
        $this->fxbody = ($delta == "") ? new FXBodySQL($requete) : new FXBodySQL($requete, $delta);
		$this->formatForDisplay(false);
//		$this->FXList($this->fxbody);
		FXList::__construct($this->fxbody);
        $this->FXSetTitle("Fannys");
        $this->FXSetColumnsDisplayed(array("date", "nom1", "resultat", "nom2"));
        $this->FXSetColumnsName(array("Date", "Equipe1", "Résultat", "Equipe2"));
        $this->FXSetColumnsWidth(array("15%", "", "10%", ""));
        $this->FXSetColumnsColor(array("", "", $this->color_title_column, ""));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES FANNYS POUR UN JOUEUR
// /////////////////////////////////////////////////////////////////////////////////////
class FXListFannysJoueur extends FXListFannys
{
	function __construct($championnat, $id_joueur = "", $delta = "")
	{
		if ($id_joueur == "")
        	$requete = "SELECT m.fanny, m.nbset, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_champ=".$championnat." ORDER BY date DESC";
		else
        	$requete = "SELECT m.fanny, m.nbset, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (e1.joueurs like '".$id_joueur."|%' OR e1.joueurs like '%|".$id_joueur."|%' OR e1.joueurs like '%|".$id_joueur."' OR e2.joueurs like '".$id_joueur."|%' OR e2.joueurs like '%|".$id_joueur."|%' OR e2.joueurs like '%|".$id_joueur."') AND m.id_champ=".$championnat." ORDER BY date DESC";

//		$this->FXListFannys($championnat, $requete, $delta);
		FXListFannys::__construct($championnat, $requete, $delta);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES FANNYS POUR UNE EQUIPE
// /////////////////////////////////////////////////////////////////////////////////////
class FXListFannysEquipe extends FXListFannys
{
	function __construct($championnat, $id_equipe = "", $delta = "")
	{
		if ($id_equipe == "")
        	$requete = "SELECT m.fanny, m.nbset, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_champ=".$championnat." ORDER BY date DESC";
		else
        	$requete = "SELECT m.fanny, m.nbset, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (m.id_equipe1=".$id_equipe." OR m.id_equipe2=".$id_equipe.") AND m.id_champ=".$championnat." ORDER BY date DESC";

//		$this->FXListFannys($championnat, $requete, $delta);
		FXListFannys::__construct($championnat, $requete, $delta);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MATCHS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchs extends FXListMatchsGen
{
	function __construct($championnat, $id_journee = "", $admin = false, $filtre = "")
	{
		if ($admin)
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.id match_id, j.date mdate, m.match_joue, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre;
			$this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("nom1", "resultat", "nom2", "action"));
	        $this->FXSetColumnsName(array("Equipe1", "Score", "Equipe2", "Action"));
	        $this->FXSetColumnsWidth(array("42%", "10%", "42%", "6%"));
	        $this->FXSetColumnsColor(array("", $this->color_action_column, "", $this->color_action_column));
		}
		else
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.id match_id, j.date mdate, m.match_joue, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre;
	        $this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("nom1", "resultat", "nom2"));
	        $this->FXSetColumnsName(array("Equipe1", "Score", "Equipe2"));
	        $this->FXSetColumnsWidth(array("45%", "10%", "45%"));
	        $this->FXSetColumnsColor(array("", $this->color_action_column, ""));
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MATCHS DE POULE D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsPoules extends FXListMatchsGen
{
	function __construct($championnat, $id_journee = "", $admin = false, $filtre = "")
	{
		if ($admin)
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.id match_id, IF(LENGTH(play_date) > 0, CONCAT(SUBSTRING(play_date,7,4), '-', SUBSTRING(play_date,4,2), '-', SUBSTRING(play_date,1,2)), j.date) mdate, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, IF(LENGTH(play_date) > 0, CONCAT(SUBSTRING(play_date,7,4), SUBSTRING(play_date,4,2),SUBSTRING(play_date,1,2)), '00000000') dt2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.id_journee=".$id_journee." AND m.id_champ=".$championnat." AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id ".$filtre." ORDER BY niveau DESC";
			$this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->preformat($championnat, $id_journee);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("mdate", "nom1", "resultat", "nom2", "action"));
	        $this->FXSetColumnsName(array("Date", "Equipe1", "Score", "Equipe2", "Action"));
	        $this->FXSetColumnsWidth(array("12%", "37%", "9%", "37%", "5%"));
	        $this->FXSetColumnsColor(array("", "", $this->color_action_column, "", $this->color_action_column));
		}
		else
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.id match_id, IF(LENGTH(play_date) > 0, CONCAT(SUBSTRING(play_date,7,4), '-', SUBSTRING(play_date,4,2), '-', SUBSTRING(play_date,1,2)), j.date) mdate, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre." ORDER BY niveau DESC";
	        $this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->preformat($championnat, $id_journee);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("mdate", "nom1", "resultat", "nom2"));
	        $this->FXSetColumnsName(array("Date", "Equipe1", "Score", "Equipe2"));
	        $this->FXSetColumnsWidth(array("12%", "40%", "8%", "40%"));
	        $this->FXSetColumnsColor(array("", "", $this->color_action_column, ""));
		}

	}

	function preformat($championnat, $id_journee_mere)
	{
		// On récupère les journees alias pour changer les dates des matchs
		$sjs = new SQLJourneesServices($championnat, $id_journee_mere);
		$all = $sjs->getAllAliasJournee();

		$date_matchs = array();
		foreach($all as $item)
		{
			$tmp = explode ('|', $item['id_matchs']);
			if (isset($tmp[1]) && $tmp[1] != "")
			{
				$ids_matchs = explode(',', $tmp[1]);
				foreach($ids_matchs as $m)
					$date_matchs[$m] = $item['date'];
			}
		}

		$tri = array();
		while(list($cle, $val) = each($this->fxbody->tab))
		{
			if (isset($date_matchs[$this->fxbody->tab[$cle]['match_id']]))
				$this->fxbody->tab[$cle]['mdate'] = $date_matchs[$this->fxbody->tab[$cle]['match_id']];
			$tri[] = $this->fxbody->tab[$cle]['mdate'];
		}

		array_multisort($tri, SORT_ASC, $this->fxbody->tab);

		reset($this->fxbody->tab);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MATCHS DE CLASSEMENT D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsPlayOff extends FXListMatchsGen
{
	var $championnat;
	var $journee;
	var $type_matchs;

	function __construct($championnat, $id_journee = "", $phase_finale = 16, $admin = false, $type_matchs)
	{
		$this->championnat  = $championnat;
		$this->journee      = $id_journee;
		$this->type_matchs  = $type_matchs;

		if ($admin)
		{
	        $requete = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." AND niveau LIKE '".$type_matchs."|%' ORDER BY niveau DESC";
			$this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $phase_finale, $type_matchs);
			$this->formatForDisplay();
//	        $this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2", "action"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2", "Action"));
	        $this->FXSetColumnsWidth(array("15%", "35%", "10%", "35%", "5%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, "", $this->color_action_column));
			$this->FXSetNumerotation(false);
		}
		else
		{
	        $requete = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." AND niveau LIKE '".$type_matchs."|%' ORDER BY niveau DESC";
	        $this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $phase_finale, $type_matchs);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2"));
	        $this->FXSetColumnsWidth(array("15%", "37%", "11%", "37%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, ""));
			$this->FXSetNumerotation(false);
		}
	}

	function completeList($admin, $phase_finale, $type_matchs)
	{
		global $libelle_phase_finale;

		$res = array();

		// Initialisation
		$i = $phase_finale;
		while($i >= 1)
		{
			for($j=1; $j <= $i; $j++)
			{
				$action = $admin ? "<A HREF=\"matchs_ajouter.php?options_type_matchs=".$type_matchs."|".$i."|".$j."\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A>" : "";
				$res[$type_matchs."|".$i."|".$j] = array("niveau" => $type_matchs."|".$i."|".$j, "libelle_niveau" => $libelle_phase_finale[$i], "nom1" => "-", "resultat" => "0/0", "res2" => "-/-", "nom2" => "-", "action" => $action, "nbset" => 1, "fanny" => 0);
			}
			if ($i > 1) $res["F|".$i] = _FXLINESEPARATOR_;
			$i = $i / 2;
		}

		// Réaffectation des matchs saisis
		foreach($this->fxbody->tab as $elt)
		{
			$tmp = $elt['niveau'];
			$items = explode('|', $elt['niveau']);
			$elt['libelle_niveau'] = $libelle_phase_finale[$items[1]];
			$elt['res2'] = $elt['resultat'];
			$res[$tmp] = $elt;
		}

		// On récupère les matchs de la journée avec une seule équipe de configurée
        $sql1 = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, '-------' nom2 FROM jb_matchs m, jb_equipes e1, jb_journees j WHERE e1.id=m.id_equipe1 AND m.id_equipe2=0 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res1 = dbc::execSQL($sql1);
		while($row_matchs = mysqli_fetch_array($res1)) { $items = explode('|', $row_matchs['niveau']); $row_matchs['libelle_niveau'] = $libelle_phase_finale[$items[1]]; $row_matchs["res2"] = $row_matchs["resultat"]; unset($row_matchs[6]); unset($row_matchs[8]); $res[$row_matchs['niveau']] = $row_matchs; }

        $sql2 = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action, j.date date, '-------' nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e2, jb_journees j WHERE e2.id=m.id_equipe2 AND m.id_equipe1=0 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res2 = dbc::execSQL($sql2);
		while($row_matchs = mysqli_fetch_array($res2)) { $items = explode('|', $row_matchs['niveau']); $row_matchs['libelle_niveau'] = $libelle_phase_finale[$items[1]]; $row_matchs["res2"] = $row_matchs["resultat"]; unset($row_matchs[6]); unset($row_matchs[8]); $res[$row_matchs['niveau']] = $row_matchs; }

		$this->fxbody->tab = $res;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// DIAGRAMME PHASE FINALE D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsPlayOffII extends FXListPresentation
{
	var $championnat;
	var $journee;
	var $phase_finale;
	var $admin;
	var $type_matchs;

	function __construct($championnat, $id_journee = "", $phase_finale = 16, $admin = false, $type_matchs)
	{
		$this->championnat  = $championnat;
		$this->journee      = $id_journee;
		$this->phase_finale = $phase_finale;
		$this->admin        = $admin;
		$this->type_matchs  = $type_matchs;

//		$this->FXListPresentation($this->initData($phase_finale));
		FXListPresentation::__construct($this->initData($phase_finale));
        $this->FXSetColumnsName(array("&nbsp;"));
	    $this->FXSetColumnsAlign(array("center"));
		$this->FXSetMouseOverEffect(false);
	}

	function initData()
	{
		// Initialisation des matchs à vide
		$tab = array();
		$i = $this->phase_finale;
		while($i >= 1)
		{
			for($j=1; $j <= $i; $j++)
				$tab[$this->type_matchs."|".$i."|".$j] = array("prolongation" => "0", "penaltys" => "", "match_joue" => 0, "today" => 0, "id" => -1, "niveau" => $this->type_matchs."|".$i."|".$j, "nom1" => "----------", "resultat" => "0/0", "nom2" => "----------", "nbset" => 1, "fanny" => 0);
			$i = $i / 2;
		}

		// On récupère les matchs de la journée
		$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 1 today, m.id id, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res = dbc::execSQL($select);
		while($row_matchs = mysqli_fetch_array($res))
			$tab[$row_matchs['niveau']] = $row_matchs;

		// Récupération des matchs de journée alias
		$sjs = new SQLJourneesServices($this->championnat, $this->journee);
		$journee = $sjs->getJournee();
		$ref_journee = $journee['id_journee_mere'] == 0 ? $this->journee : $journee['id_journee_mere'];
		$liste_alias = $sjs->getAllAliasJournee($ref_journee);
		$all_journees[$ref_journee] = $ref_journee;
		foreach($liste_alias as $item)
			$all_journees[$item['id']] = $item['id'];
		// Toutes les journées sauf celle en cours
		unset($all_journees[$this->journee]);
		// Récupération des matchs
		foreach($all_journees as $item)
		{
			$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 0 today, m.id id, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$item." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
			$res = dbc::execSQL($select);
			while($row_matchs = mysqli_fetch_array($res))
				$tab[$row_matchs['niveau']] = $row_matchs;
		}
		$cellheight = 15;
		$contenu = "";
		$contenu .= "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=100% SUMMARY=\"\">";
		$i = 0;
		$puissance2 = ToolBox::getPuissance2($this->phase_finale);
		while($i < $this->phase_finale)
		{
			$contenu .= "<TR>";
			for($j=0; $j <= $puissance2; $j++)
			{
				$indice = $this->type_matchs."|".pow(2, ($puissance2-$j))."|".(($i / pow(2, $j))+1);
				if (($i % pow(2, $j)) == 0)
				{
					$vainqueur = StatsJourneeBuilder::kikiGagne($tab[$indice]);
					$sm = new StatMatch($tab[$indice]['resultat'], $tab[$indice]['nbset']);
					$score = $sm->getScore();

					$style1 = $tab[$indice]['today'] == 1 ? "color:#003366;" : "color:#003366;";
					$style2 = $tab[$indice]['today'] == 1 ? "font-weight:normal;" : "color:#DDDDDD;font-weight:normal;";
					$stl1 = "color:#FFFFCC;background-color:#003366;padding-left:3;padding-right:3;";
					$stl2 = "font-weight:normal;padding-left:3;padding-right:3;";

					if ($j > 0)
					{
						$contenu .= "<TD ROWSPAN=".pow(2, $j)." ALIGN=center HEIGHT=100%><TABLE BORDER=0 SUMMARY=\"\" CELLSPACING=0 CELLPADDING=0 HEIGHT=".(115*pow(2, ($j-1)))." WIDTH=".($cellheight+$cellheight+1).">";
						$contenu .= "<TR><TD HEIGHT=25% COLSPAN=2><IMG SRC=../images/1x1.gif ALT=\"\" /></TD>";
						$contenu .= "<TR><TD HEIGHT=25% WIDTH=".$cellheight." CLASS=crochet1><IMG SRC=../images/1x1.gif ALT=\"\" /></TD><TD WIDTH=".$cellheight." CLASS=crochet2><IMG SRC=../images/1x1.gif ALT=\"\" /></TD>";
						$contenu .= "<TR><TD HEIGHT=25% WIDTH=".$cellheight." CLASS=crochet3><IMG SRC=../images/1x1.gif ALT=\"\" /></TD><TD WIDTH=".$cellheight."><IMG SRC=../images/1x1.gif ALT=\"\" /></TD>";
						$contenu .= "<TR><TD HEIGHT=25% COLSPAN=2><IMG SRC=../images/1x1.gif ALT=\"\" /></TD>";
						$contenu .= "</TABLE></TD>";
					}

					$contenu .= "<TD ROWSPAN=".pow(2, $j)." HEIGHT=60><TABLE SUMMARY=\"\" CELLPADDING=1 CELLSPACING=0 BORDER=0 CLASS=score>";
					$contenu .= "<TR>";
					if ($this->admin)
					{
						if ($tab[$indice]['id'] == -1)
							$contenu .= "<TD CLASS=match1 ROWSPAN=2><A HREF=\"matchs_ajouter.php?options_type_matchs=".$indice."\"><IMG SRC=../images/small_edit2.gif BORDER=0 ALT=\"\" /></A></TD>";
						else
							$contenu .= "<TD CLASS=match1 ROWSPAN=2> <TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match('+WHERE+id%3D".$tab[$indice]['id']."', '', '".$tab[$indice]['niveau']."');\"><IMG SRC=../images/small_edit2.gif BORDER=0 ALT=\"\" /></A></TD><TR><TD><A HREF=# onClick=\"javascript:supprimer_match('+WHERE+id%3D".$tab[$indice]['id']."');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 ALT=\"\" /></A></TD></TABLE> </TD>";
					}
					$contenu .= "<TD NOWRAP CLASS=match2><FONT STYLE=\"".($vainqueur == 2 ? $style2 : $style1)."\">".$tab[$indice]['nom1']."</FONT></TD>";
					$contenu .= "<TD CLASS=match3> &nbsp; </TD>";
					$contenu .= "<TD CLASS=\"".($score[0][0] > $score[0][1] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[0][0] > $score[0][1] ? "score_gagne" : "score_perdu")."\">".$score[0][0]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 2) $contenu .= "<TD CLASS=\"".($score[1][0] > $score[1][1] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[1][0] > $score[1][1] ? "score_gagne" : "score_perdu")."\">".$score[1][0]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 3) $contenu .= "<TD CLASS=\"".($score[2][0] > $score[2][1] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[2][0] > $score[2][1] ? "score_gagne" : "score_perdu")."\">".$score[2][0]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 4) $contenu .= "<TD CLASS=\"".($score[3][0] > $score[3][1] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[3][0] > $score[3][1] ? "score_gagne" : "score_perdu")."\">".$score[3][0]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 5) $contenu .= "<TD CLASS=\"".($score[4][0] > $score[4][1] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[4][0] > $score[4][1] ? "score_gagne" : "score_perdu")."\">".$score[4][0]."</FONT></TD>";
					$contenu .= "<TR>";
					$contenu .= "<TD NOWRAP CLASS=match4><FONT STYLE=\"".($vainqueur == 1 ? $style2 : $style1)."\">".$tab[$indice]['nom2']."</FONT></TD>";
					$contenu .= "<TD CLASS=match1> &nbsp; </TD>";
					$contenu .= "<TD CLASS=\"".($score[0][1] > $score[0][0] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[0][1] > $score[0][0] ? "score_gagne" : "score_perdu")."\">".$score[0][1]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 2) $contenu .= "<TD CLASS=\"".($score[1][1] > $score[1][0] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[1][1] > $score[1][0] ? "score_gagne" : "score_perdu")."\">".$score[1][1]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 3) $contenu .= "<TD CLASS=\"".($score[2][1] > $score[2][0] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[2][1] > $score[2][0] ? "score_gagne" : "score_perdu")."\">".$score[2][1]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 4) $contenu .= "<TD CLASS=\"".($score[3][1] > $score[3][0] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[3][1] > $score[3][0] ? "score_gagne" : "score_perdu")."\">".$score[3][1]."</FONT></TD>";
					if ($tab[$indice]['nbset'] >= 5) $contenu .= "<TD CLASS=\"".($score[4][1] > $score[4][0] ? "score_gagne" : "score_perdu")."\"><FONT CLASS=\"".($score[4][1] > $score[4][0] ? "score_gagne" : "score_perdu")."\">".$score[4][1]."</FONT></TD>";
					$contenu .= "</TABLE></TD>";
				}
			}
			$i++;
		}
		$contenu .= "</TABLE>";

		return array(array($contenu));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// DIAGRAMME PHASE FINALE D'UN TOURNOI (pour nouvelle version jorkers)
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsPlayOffIII extends FXListPresentation
{
	var $championnat;
	var $journee;
	var $phase_finale;
	var $admin;
	var $type_matchs;

	function __construct($championnat, $id_journee = "", $phase_finale = 16, $admin = false, $type_matchs)
	{
		$this->championnat  = $championnat;
		$this->journee      = $id_journee;
		$this->phase_finale = $phase_finale;
		$this->admin        = $admin;
		$this->type_matchs  = $type_matchs;

//		$this->FXListPresentation($this->initData($phase_finale));
		FXListPresentation::__construct($this->initData($phase_finale));
        $this->FXSetColumnsName(array("&nbsp;"));
	    $this->FXSetColumnsAlign(array("center"));
		$this->FXSetMouseOverEffect(false);
	}

	function initData()
	{
		// Initialisation des matchs à vide
		$tab = array();
		$i = $this->phase_finale;
		while($i >= 1)
		{
			for($j=1; $j <= $i; $j++)
				$tab[$this->type_matchs."|".$i."|".$j] = array("prolongation" => "0", "penaltys" => "", "match_joue" => 0, "today" => 0, "id" => -1, "niveau" => $this->type_matchs."|".$i."|".$j, "nom1" => "----------", "resultat" => "-/-", "nom2" => "----------", "nbset" => 1, "fanny" => 0);
			$i = $i / 2;
		}

		// On récupère les matchs de la journée
		$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 1 today, m.id id, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res = dbc::execSQL($select);
		while($row_matchs = mysqli_fetch_array($res)) $tab[$row_matchs['niveau']] = $row_matchs;

		// On récupère les matchs de la journée avec une seule équipe de configurée
		$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 1 today, m.id id, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, '----------' nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_journees j WHERE e1.id=m.id_equipe1 AND m.id_equipe2=0 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res = dbc::execSQL($select);
		while($row_matchs = mysqli_fetch_array($res)) $tab[$row_matchs['niveau']] = $row_matchs;

		$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 1 today, m.id id, m.niveau niveau, j.date date, '----------' nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e2, jb_journees j WHERE e2.id=m.id_equipe2 AND m.id_equipe1=0 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
		$res = dbc::execSQL($select);
		while($row_matchs = mysqli_fetch_array($res)) $tab[$row_matchs['niveau']] = $row_matchs;

		// Récupération des matchs de journée alias
		$sjs = new SQLJourneesServices($this->championnat, $this->journee);
		$journee = $sjs->getJournee();
		$ref_journee = $journee['id_journee_mere'] == 0 ? $this->journee : $journee['id_journee_mere'];
		$liste_alias = $sjs->getAllAliasJournee($ref_journee);
		$all_journees[$ref_journee] = $ref_journee;
		foreach($liste_alias as $item)
			$all_journees[$item['id']] = $item['id'];
		// Toutes les journées sauf celle en cours
		unset($all_journees[$this->journee]);
		// Récupération des matchs
		foreach($all_journees as $item)
		{
			$select = "SELECT m.penaltys, m.prolongation, m.match_joue, 0 today, m.id id, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$item." AND m.id_champ=".$this->championnat." AND niveau LIKE '".$this->type_matchs."|%' ORDER BY niveau DESC";
			$res = dbc::execSQL($select);
			while($row_matchs = mysqli_fetch_array($res))
				$tab[$row_matchs['niveau']] = $row_matchs;
		}
		$cellheight = 15;
		$contenu = "";
		$contenu .= "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">";
		$i = 0;
		$puissance2 = ToolBox::getPuissance2($this->phase_finale);
		while($i < $this->phase_finale)
		{
			$contenu .= "<tr>";
			for($j=0; $j <= $puissance2; $j++)
			{
				$indice = $this->type_matchs."|".pow(2, ($puissance2-$j))."|".(($i / pow(2, $j))+1);
				if (($i % pow(2, $j)) == 0)
				{
					$vainqueur = StatsJourneeBuilder::kikiGagne($tab[$indice]);
					$sm = new StatMatch($tab[$indice]['resultat'], $tab[$indice]['nbset']);
					$score = $sm->getScore();

					$style1 = $tab[$indice]['today'] == 1 ? "color:#003366;" : "color:#003366;";
					$style2 = $tab[$indice]['today'] == 1 ? "font-weight:normal;" : "color:#DDDDDD;font-weight:normal;";
					$stl1 = "color:#FFFFCC;background-color:#003366;padding-left:3;padding-right:3;";
					$stl2 = "font-weight:normal;padding-left:3;padding-right:3;";

					if ($j > 0)
					{
						$contenu .= "<td rowspan=\"".pow(2, $j)."\" align=\"center\" height=\"100%\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" height=\"".(115*pow(2, ($j-1)))."\" width=\"".($cellheight+$cellheight+1)."\">";
						$contenu .= "<tr><td height=\"25%\" colspan=\"2\"><img src=\"../images/1x1.gif\" /></td></tr>";
						$contenu .= "<tr><td height=\"25%\" width=\"".$cellheight."\" class=\"crochet1\"><img src=\"../images/1x1.gif\" /></td><td width=\"".$cellheight."\" class=\"crochet2\"><img src=\"../images/1x1.gif\" /></td></tr>";
						$contenu .= "<tr><td height=\"25%\" width=\"".$cellheight."\" class=\"crochet3\"><img src=\"../images/1x1.gif\" /></td><td width=\"".$cellheight."\"><img src=\"../images/1x1.gif\" /></td></tr>";
						$contenu .= "<tr><td height=\"25%\" colspan=\"2\"><img src=\"../images/1x1.gif\" /></td></tr>";
						$contenu .= "</table></td>";
					}

					$contenu .= "<td rowspan=\"".pow(2, $j)."\" height=\"60\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\" class=\"score\">";
					$contenu .= "<tr>";
					if ($this->admin)
					{
						if ($tab[$indice]['id'] == -1)
							$contenu .= "<td class=\"match1\" rowspan=\"2\"><a href=\"#\" onclick=\"ajouter_match('".$indice."')\"><img src=\"img/pencil_16.png\" class=\"full-circle\" /></a></td>";
						else
							$contenu .= "<td class=\"match1\" rowspan=\"2\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><a href=\"#\" onclick=\"javascript:modifier_match('".$tab[$indice]['id']."', '', '".$tab[$indice]['niveau']."');\"><img src=\"img/pencil_16.png\" border=\"0\" class=\"full-circle\" /></a></td></tr><tr><td><a href=\"#\" onclick=\"javascript:supprimer_match('".$tab[$indice]['id']."');\"><img src=\"img/trash_16.png\" border=\"0\" class=\"full-circle\" /></a></td></tr></table></td>";
					}
					$contenu .= "<td nowrap=\"nowrap\" class=\"match2 ".($vainqueur == 2 ? "" : "winner")."\">".$tab[$indice]['nom1']."</td>";
					$contenu .= "<td class=\"match3\"> &nbsp; </td>";
					$contenu .= "<td class=\"".($score[0][0] > $score[0][1] ? "score_gagne" : "score_perdu")."\">".($score[0][0] == "" ? "-" : $score[0][0])."</td>";
					if ($tab[$indice]['nbset'] >= 2) $contenu .= "<td class=\"".($score[1][0] > $score[1][1] ? "score_gagne" : "score_perdu")."\">".($score[1][0] == "" ? "-" : $score[1][0])."</td>";
					if ($tab[$indice]['nbset'] >= 3) $contenu .= "<td class=\"".($score[2][0] > $score[2][1] ? "score_gagne" : "score_perdu")."\">".($score[2][0] == "" ? "-" : $score[2][0])."</td>";
					if ($tab[$indice]['nbset'] >= 4) $contenu .= "<td class=\"".($score[3][0] > $score[3][1] ? "score_gagne" : "score_perdu")."\">".($score[3][0] == "" ? "-" : $score[3][0])."</td>";
					if ($tab[$indice]['nbset'] >= 5) $contenu .= "<td class=\"".($score[4][0] > $score[4][1] ? "score_gagne" : "score_perdu")."\">".($score[4][0] == "" ? "-" : $score[4][0])."</td>";
					$contenu .= "</tr>";
					$contenu .= "<tr>";
					$contenu .= "<td nowrap=\"nowrap\" class=\"match4 ".($vainqueur == 1 ? "" : "winner")."\">".$tab[$indice]['nom2']."</td>";
					$contenu .= "<td class=\"match3\"> &nbsp; </td>";
					$contenu .= "<td class=\"".($score[0][1] > $score[0][0] ? "score_gagne" : "score_perdu")."\">".($score[0][1] == "" ? "-" : $score[0][1])."</td>";
					if ($tab[$indice]['nbset'] >= 2) $contenu .= "<td class=\"".($score[1][1] > $score[1][0] ? "score_gagne" : "score_perdu")."\">".($score[1][1] == "" ? "-" : $score[1][1])."</td>";
					if ($tab[$indice]['nbset'] >= 3) $contenu .= "<td class=\"".($score[2][1] > $score[2][0] ? "score_gagne" : "score_perdu")."\">".($score[2][1] == "" ? "-" : $score[2][1])."</td>";
					if ($tab[$indice]['nbset'] >= 4) $contenu .= "<td class=\"".($score[3][1] > $score[3][0] ? "score_gagne" : "score_perdu")."\">".($score[3][1] == "" ? "-" : $score[3][1])."</td>";
					if ($tab[$indice]['nbset'] >= 5) $contenu .= "<td class=\"".($score[4][1] > $score[4][0] ? "score_gagne" : "score_perdu")."\">".($score[4][1] == "" ? "-" : $score[4][1])."</td>";
					$contenu .= "</tr>";
					$contenu .= "</table></td>";
				}
			}
			$i++;
		}
		$contenu .= "</tr></table>";

		return array(array($contenu));
	}
}


// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MATCHS DE CLASSEMENT D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsClassementTournoi extends FXListMatchsGen
{
	function __construct($championnat, $id_journee = "", $nb_equipes = 0, $admin = false, $filtre = "")
	{
		if ($admin)
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre." ORDER BY niveau";
			$this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $nb_equipes);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2", "action"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2", "Action"));
	        $this->FXSetColumnsWidth(array("15%", "35%", "10%", "35%", "5%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, "", $this->color_action_column));
			$this->FXSetNumerotation(false);
		}
		else
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre." ORDER BY niveau";
	        $this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $nb_equipes);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2"));
	        $this->FXSetColumnsWidth(array("15%", "37%", "11%", "37%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, ""));
			$this->FXSetNumerotation(false);
		}
	}

	function completeList($admin, $nb_equipes)
	{
		$res = array();

		// Initialisation
		for($i=3; $i <= $nb_equipes; $i++)
		{
			if (($i % 2) != 0)
			{
				$action = $admin ? "<A HREF=\"matchs_ajouter.php?options_type_matchs=C|".$i."\"><IMG SRC=../images/small_edit2.gif BORDER=0></A>" : "";
				$res["C|".$i] = array("prolongation" => "0", "penaltys" => "", "match_joue" => "0", "niveau" => $i." ième place", "nom1" => "-", "resultat" => "0/0", "res2" => "0/0", "nom2" => "-", "action" => $action, "nbset" => 1, "fanny" => 0);
			}
		}

		// Réaffectation des matchs saisis
		foreach($this->fxbody->tab as $elt)
		{
			$tmp = $elt['niveau'];
			$items = explode('|', $elt['niveau']);
			$elt['niveau'] = $items[1] == -1 ? "Barrage" : $items[1]." ième place";
			$elt['res2'] = $elt['resultat'];
			$res[$tmp] = $elt;
		}

		$this->fxbody->tab = $res;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES MATCHS DE CLASSEMENT D'UN TOURNOI (Nouvelle version pour jorkers v3)
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsClassementTournoiIII extends FXListMatchsGen
{
	var $championnat;
	var $journee;
	var $consolante;
	var $phase_finale;

	function __construct($championnat, $id_journee = "", $nb_equipes = 0, $admin = false, $filtre = "", $consolante, $phase_finale)
	{
		$this->championnat  = $championnat;
		$this->journee      = $id_journee;
		$this->consolante   = $consolante;
		$this->phase_finale = $phase_finale;

		if ($admin)
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre." ORDER BY niveau";
			$this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $nb_equipes);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2", "action"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2", "Action"));
	        $this->FXSetColumnsWidth(array("15%", "35%", "10%", "35%", "5%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, "", $this->color_action_column));
			$this->FXSetNumerotation(false);
		}
		else
		{
	        $requete = "SELECT m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$id_journee." AND m.id_champ=".$championnat." ".$filtre." ORDER BY niveau";
	        $this->fxbody = new FXBodySQL($requete, _FXLIST_FULL_);
			$this->completeList($admin, $nb_equipes);
			$this->formatForDisplay();
//			$this->FXList($this->fxbody);
			FXList::__construct($this->fxbody);
	        $this->FXSetTitle("Matchs");
	        $this->FXSetColumnsDisplayed(array("niveau", "nom1", "resultat", "nom2"));
	        $this->FXSetColumnsName(array("Enjeu", "Equipe1", "Score", "Equipe2"));
	        $this->FXSetColumnsWidth(array("15%", "37%", "11%", "37%"));
	        $this->FXSetColumnsColor(array($this->color_action_column, "", $this->color_action_column, ""));
			$this->FXSetNumerotation(false);
		}
	}

	function completeList($admin, $nb_equipes)
	{
		$res = array();

		// Création manuelle du match de la finale
		$res["F|1|1"] = array("prolongation" => "0", "penaltys" => "", "match_joue" => "0", "niveau" => "1 ère place", "nom1" => "-", "resultat" => "0/0", "res2" => "0/0", "nom2" => "-", "action" => "", "nbset" => 1, "fanny" => 0);
        $sql1 = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau='F|1|1' ORDER BY niveau";
		$res1 = dbc::execSQL($sql1);
		if ($match = mysqli_fetch_array($res1)) {
			$res["F|1|1"][10] = $match['nom1']; // Pas affichage correct
			$res["F|1|1"]["nom1"] = $match['nom1'];
			$res["F|1|1"]["nom2"] = $match['nom2'];
			$res["F|1|1"]["res2"] = $match['resultat'];
			$res["F|1|1"]["resultat"] = $match['resultat'];
       		$res["F|1|1"]["action"] = "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match('+WHERE+id%3D".$match['match_id']."', '', 'F|1|1');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match('+WHERE+id%3D".$match['match_id']."');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>";
		}

		// Initialisation
		for($i=3; $i <= $nb_equipes; $i++)
		{
			if (($i % 2) != 0)
			{
				$action = $admin ? "<A HREF=\"matchs_ajouter.php?options_type_matchs=C|".$i."\"><IMG SRC=../images/small_edit2.gif BORDER=0></A>" : "";
				$res["C|".$i] = array("prolongation" => "0", "penaltys" => "", "match_joue" => "0", "niveau" => $i." ième place", "nom1" => "-", "resultat" => "0/0", "res2" => "0/0", "nom2" => "-", "action" => $action, "nbset" => 1, "fanny" => 0);
			}
		}

		// Réaffectation des matchs saisis
		foreach($this->fxbody->tab as $elt)
		{
			$tmp = $elt['niveau'];
			$items = explode('|', $elt['niveau']);
			$elt['niveau'] = $items[1] == -1 ? "Barrage" : $items[1]." ième place";
			$elt['res2'] = $elt['resultat'];
			$res[$tmp] = $elt;
		}

		// Création manuelle du match de la finale de la consolante
		if ($this->consolante > 0) {
	        $sql2 = "SELECT m.id match_id, m.penaltys, m.prolongation, m.match_joue, m.id_equipe1, m.id_equipe2, m.niveau niveau, m.fanny, m.nbset, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match(\'+WHERE+id%3D',m.id,'\', \'\', \'', m.niveau,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match(\'+WHERE+id%3D',m.id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>') action, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$this->journee." AND m.id_champ=".$this->championnat." AND niveau='Y|1|1' ORDER BY niveau";
			$res2 = dbc::execSQL($sql2);

			if ($match = mysqli_fetch_array($res2)) {
				$res["C|".(($this->phase_finale*2)+1)][10] = $match['nom1']; // Pas affichage correct
				$res["C|".(($this->phase_finale*2)+1)]["nom1"] = $match['nom1'];
				$res["C|".(($this->phase_finale*2)+1)]["nom2"] = $match['nom2'];
				$res["C|".(($this->phase_finale*2)+1)]["res2"] = $match['resultat'];
				$res["C|".(($this->phase_finale*2)+1)]["resultat"] = $match['resultat'];
				$res["C|".(($this->phase_finale*2)+1)]["master_niveau"] = $match['niveau'];
				$res["C|".(($this->phase_finale*2)+1)]["action"] = "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_match('+WHERE+id%3D".$match['match_id']."', '', 'Y|1|1');\"><IMG SRC=../images/small_edit2.gif BORDER=0></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_match('+WHERE+id%3D".$match['match_id']."');\"><IMG SRC=../images/small_poubelle.gif BORDER=0></A></TD></TABLE>";
			}
			else
			{
				$res["C|".(($this->phase_finale*2)+1)]["master_niveau"] = "Y|1|1";
			}
		}

		$this->fxbody->tab = $res;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SAISIE D'UNE JOURNEE VIRTUELLE
// /////////////////////////////////////////////////////////////////////////////////////
class FXListJourneeVirtuelle extends FXListPresentation
{
	var $championnat;
	var $saison;
	var $datas;
	var $eq_liste;
	var $admin;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan($this->admin ? "Equipe" : "Podium", $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan($this->admin ? "Podium" : "Equipe", $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Points",       $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, $this->admin ? 2 : 3);
        HTMLTable::printCellWithColSpan("Sets",         $this->c3_title, "", "center", _CELLBORDER_SE_, $this->admin ? 2 : 4);
        HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, $this->admin ? 2 : 3);

        echo "<TR>";
		if (!$this->admin) HTMLTable::printCell("J", $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (!$this->admin) HTMLTable::printCell("J", $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c3_title, "", "center", _CELLBORDER_SE_);
		if (!$this->admin) HTMLTable::printCell("Diff", $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
		if (!$this->admin) HTMLTable::printCell("Diff", $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($championnat, $saison, $datas = "", $admin = false)
	{
		$this->championnat = $championnat;
		$this->saison      = $saison;
		$this->datas       = array();
		$this->admin       = $admin;

		// Initialisation des équipes
		$sss = new SQLSaisonsServices($this->championnat, $this->saison);
		$this->eq_liste = $sss->getListeEquipes();
		$this->initDatas($datas);

		// Formattage de l'affichage
//		$this->FXListPresentation($this->datas);
		FXListPresentation::__construct($this->datas);
		$this->FXSetNbCols($this->admin ? 9 : 13);
		$this->FXSetColumnsAlign(array($this->admin ? "left" : "center", $this->admin ? "center" : "left", "center"));
		if ($this->admin)
			$this->FXSetColumnsColor(array($this->color_action_column, "", $this->c1_column, $this->c2_column, $this->c2_column, $this->c3_column, $this->c3_column, "", ""));
		else
			$this->FXSetColumnsColor(array($this->color_action_column, "", $this->c1_column, $this->c2_column, $this->c2_column, $this->c2_column, $this->c3_column, $this->c3_column, $this->c3_column, $this->c3_column, "", "", $this->color_action_column));
		$this->FXSetColumnsWidth(array($this->admin ? "30%" : "5%", $this->admin ? "5%" : "30%", "", "", "", "", "", "", "", ""));
	}

	function getHtmlSelect($name, $value, $min, $max)
	{
		$ret = "<SELECT NAME=".$name.">";
		for($i=$min; $i <= $max; $i++) $ret .= "<OPTION VALUE=".$i." ".($i == $value ? "SELECTED" : "")."> ".$i;
		$ret .= "</SELECT>";

		return $ret;
	}

	function initDatas($datas)
	{
		// Valeurs par défaut
		$stats_equipes = array();
		if ($datas != "")
		{
			$items = explode('|', $datas);
			foreach($items as $item)
			{
				$tmp = explode('@', $item);
				$stats_equipes[$tmp[0]] = $item;
			}
		}

		$hidden = "<INPUT TYPE=HIDDEN NAME=equipes_stats VALUE=\"";

		$i=0;
		$tri = array();
		foreach($this->eq_liste as $equipe)
		{
			// Récup valeur par défaut
			$sjt = new StatJourneeTeam();
			if (isset($stats_equipes[$equipe['id']])) $sjt->init($stats_equipes[$equipe['id']]);

			if ($this->admin)
			{
				$this->datas[] = array(
					$equipe['nom'],
					$this->getHtmlSelect("podium_".$equipe['id'], $sjt->tournoi_classement, 0, count($this->eq_liste)),
					$this->getHtmlSelect("points_".$equipe['id'], $sjt->tournoi_points, 0, 32),
					$this->getHtmlSelect("gagnes_".$equipe['id'], $sjt->matchs_gagnes, 0, 32),
					$this->getHtmlSelect("perdus_".$equipe['id'], $sjt->matchs_perdus, 0, 32),
					"<INPUT TYPE=TEXT NAME=setsg_".$equipe['id']." VALUE=".$sjt->sets_gagnes." SIZE=3>",
					"<INPUT TYPE=TEXT NAME=setsp_".$equipe['id']." VALUE=".$sjt->sets_perdus." SIZE=3>",
					"<INPUT TYPE=TEXT NAME=marques_".$equipe['id']." VALUE=".$sjt->buts_marques." SIZE=3>",
					"<INPUT TYPE=TEXT NAME=encaisses_".$equipe['id']." VALUE=".$sjt->buts_encaisses." SIZE=3>"
					);
			}
			else
			{
				if ($sjt->tournoi_classement > 0)
				{
					$this->datas[$i] = array(
						$sjt->tournoi_classement,
						$equipe['nom'],
						$sjt->tournoi_points,
						$sjt->matchs_gagnes + $sjt->matchs_perdus,
						$sjt->matchs_gagnes,
						$sjt->matchs_perdus,
						$sjt->sets_gagnes + $sjt->sets_perdus,
						$sjt->sets_gagnes,
						$sjt->sets_perdus,
						$sjt->sets_gagnes - $sjt->sets_perdus,
						$sjt->buts_marques,
						$sjt->buts_encaisses,
						(($sjt->buts_marques - $sjt->buts_encaisses) >= 0 ? "+": "").($sjt->buts_marques - $sjt->buts_encaisses)
						);
					$tri[$i] = $sjt->tournoi_classement;
				}
			}

			$hidden .= ($i == 0 ? "" : ",").$equipe['id'];

			$i++;
		}

		if (!$this->admin)
		{
			array_multisort($tri, SORT_ASC, $this->datas);
			$res = array();
			$i = 1;
			foreach($this->datas as $elt)
			{
				$res[] = $elt;
				if ($i == 1 || $i == 4 || $i == 8 || $i == 16) $res[] = _FXLINESEPARATOR_;

				$i++;
			}
			$this->datas = $res;
		}

		$hidden .= "\">\n";

		echo $hidden;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT D'UNE JOURNEE D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListClassementJourneeTournoi extends FXListPresentation
{
	var $championnat;
	var $id_saison;
	var $journee;
	var $datas;
	var $eq_liste;
	var $sjs;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan("Podium",       $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Points",       $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 1 ? 4 : 3);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithColSpan("Sets",         $this->c3_title, "", "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 3);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("J",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("G",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("P",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("Diff",      $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($championnat, $id_saison, $journee)
	{
		$this->championnat = $championnat;
		$this->id_saison   = $id_saison;
		$this->journee     = $journee;
		$this->datas       = array();
		$this->sjs         = new SQLJourneesServices($this->id_saison, $this->journee);

		// Initialisation des équipes
		$ses = new SQLEquipesServices($this->championnat);
		$this->eq_liste = $ses->getListeEquipes();

		// Récupération des données à afficher
		$this->initDatas();

		// Formatage de l'affichage
		$this->formatForDisplay();

		// Formattage de l'affichage
//		$this->FXListPresentation($this->datas);
		FXListPresentation::__construct($this->datas);
		$nbCols = 14;
		if (sess_context::getGestionMatchsNul() == 0) $nbCols -= 1;
		if (sess_context::getGestionSets() == 0)      $nbCols -= 4;
		$this->FXSetNbCols($nbCols);
		$this->FXSetColumnsAlign(array("center", "left", "center"));
		$this->FXSetColumnsWidth(array("8%", "30%", "", "", "", "", "", "", "", "", "", "", ""));

		$mytab = array();
		$i = 0;
		$mytab[$i++] = $this->color_action_column;
		$mytab[$i++] = "";
		$mytab[$i++] = $this->c1_column;
		$mytab[$i++] = $this->c2_column;
		$mytab[$i++] = $this->c2_column;
		if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $this->c2_column;
		$mytab[$i++] = $this->c2_column;
		if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c3_column;
		if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c3_column;
		if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c3_column;
		if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c3_column;
		$mytab[$i++] = "";
		$mytab[$i++] = "";
		$mytab[$i++] = $this->color_action_column;
		$mytab[$i++] = $this->color_action_column;
		$this->FXSetColumnsColor($mytab);
	}

	function formatForDisplay()
	{
		$i = 1;
		$res = array();
		while(list($cle, $val) = each($this->datas))
		{
			$res[] = $val;
			if ($i == 1 || $i == 4 || $i == 8 || $i == 16 || $i == 32 || $i == 64) $res[] = _FXLINESEPARATOR_;
			$i++;
		}

		$this->datas = $res;
	}

	function initDatas()
	{
		// Récupération des infos de la journée
		$journee = $this->sjs->getJournee();

		// Si pas de joueurs ...
		if (!isset($journee['equipes']) || !is_array($journee['equipes']) || count(array($journee['equipes'])) == 0) return;

		// Recherche du nb d'equipes
		$tmp = str_replace('|', ',', $journee['equipes']);
		$lst = explode(',', $tmp);
		$nb_equipes = count($lst);

		// Initialisation des stats
		for($k = 1; $k <= $nb_equipes; $k++)
		{
			$mytab = array();
			$i = 0;
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = "-";
			$mytab[$i++] = "-";
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = "-";
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = "-";
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = "-";
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$mytab[$i++] = "-";
			$this->datas[$k] = $mytab;
		}

		// Gestion du classement tournoi
		if ($journee['classement_equipes'] != "")
		{
			// Gestion des bonus [gérer bizarre globalement, déduit en live sur classement général et donc à faire en live egalement sur classement journee]
			$bonus = array();
			$bonustab = explode(',', $journee['bonus']);
			foreach($bonustab as $b) {
				$toto = explode('=', $b);
				$bonus[$toto[0]] = isset($toto[1]) ? $toto[1] : 0;
			}

			// Affectation des valeurs issues des stats
			$items = explode('|', $journee['classement_equipes']);
			foreach($items as $stat)
			{

				$st = new StatJourneeTeam();
				$st->init($stat);

				// Si =0 alors le classement de cette équipe n'est pas encore connu
				if ($st->tournoi_classement == 0) continue;

				$mytab = array();
				$i = 0;
				$mytab[$i++] = $st->tournoi_classement;
				$mytab[$i++] = "<a href=\"stats_detail_equipe.php?id_detail=".$st->id."\" class=\"blue\">".$this->eq_liste[$st->id]['nom']."</a>";
				$mytab[$i++] = $st->tournoi_points + (isset($bonus[$st->id]) ? $bonus[$st->id] : 0);
				$mytab[$i++] = $st->matchs_joues;
				$mytab[$i++] = $st->matchs_gagnes;
				if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $st->matchs_nuls;
				$mytab[$i++] = $st->matchs_perdus;
				if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_joues;
				if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_gagnes;
				if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_perdus;
				if (sess_context::getGestionSets() == 1) $mytab[$i++] = ($st->sets_diff > 0 ? "+" : "").$st->sets_diff;
				$mytab[$i++] = $st->buts_marques;
				$mytab[$i++] = $st->buts_encaisses;
				$mytab[$i++] = ($st->diff > 0 ? "+" : "").$st->diff;
				$this->datas[$st->tournoi_classement] =	$mytab;
			}
			ksort($this->datas);
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT GENERAL D'UN TOURNOI
// /////////////////////////////////////////////////////////////////////////////////////
class FXListClassementGeneralTournoi extends FXListPresentation
{
	var $championnat;
	var $id_saison;
	var $journee;
	var $datas;
	var $eq_liste;
	var $ses;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
        HTMLTable::printCellWithRowSpan("Podium",       $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Points",       $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 1 ? 4 : 3);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithColSpan("Sets",         $this->c3_title, "", "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 3);
        HTMLTable::printCellWithRowSpan("Moy Class.",	$this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Moy buts",     $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("J",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("G",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("P",         $this->c3_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("Diff",      $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Att",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Def",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($championnat, $id_saison, $classement)
	{
		$this->championnat = $championnat;
		$this->id_saison   = $id_saison;
		$this->classement  = $classement;
		$this->datas       = array();
		$this->ses         = new SQLEquipesServices($championnat);

		// Initialisation des équipes
		$this->eq_liste = $this->ses->getListeEquipes();

		// Récupération des données à afficher
		$this->initDatas();

		// Formatage de l'affichage
		$this->formatForDisplay();

		// Formattage de l'affichage
//		$this->FXListPresentation($this->datas);
		FXListPresentation::__construct($this->datas);

		$this->FXSetColumnsAlign(array("center", "left", "center"));

		$nbCols = 17;
		if (sess_context::getGestionMatchsNul() == 0) $nbCols -= 1;
		if (sess_context::getGestionSets() == 0)      $nbCols -= 4;
		$this->FXSetNbCols($nbCols);

		$mytab = array();
		$i = 0;
		$mytab[$i++] = $this->color_action_column;
		$mytab[$i++] = "";
		$mytab[$i++] = $this->c1_column;
		$mytab[$i++] = $this->c2_column;
		$mytab[$i++] = $this->c2_column;
		if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $this->c2_column;
		$mytab[$i++] = $this->c2_column;
		$mytab[$i++] = $this->c3_column;
		$mytab[$i++] = $this->c3_column;
		$mytab[$i++] = $this->c3_column;
		$mytab[$i++] = $this->c3_column;
		$mytab[$i++] = "";
		$mytab[$i++] = "";
		$mytab[$i++] = $this->color_action_column;
		$mytab[$i++] = $this->color_action_column;
		$this->FXSetColumnsColor($mytab);

		$this->FXSetColumnsWidth(array("8%", "30%", "", "", "", "", "", "", "", "", "", "", "", ""));
//		$this->FXSetSortable(true);
	}

	function formatForDisplay()
	{
		$i = 1;
		$res = array();
		while(list($cle, $val) = each($this->datas))
		{
			$res[] = $val;
			if ($i == 1 || $i == 4 || $i == 8 || $i == 16 || $i == 32 || $i == 64) $res[] = _FXLINESEPARATOR_;
			$i++;
		}

		$this->datas = $res;
	}

	function initDatas()
	{
		$select = "SELECT * FROM jb_joueurs WHERE id_champ = ".$this->championnat;
		$res = dbc::execSQL($select);
		while($joueur = mysqli_fetch_array($res))
			$tab[$joueur['id']] = $joueur;

		// Synthèse des stats avec tri sur tournoi_points
		reset($this->classement);
		foreach($this->classement as $st)
		{
			$img_joueurs = "../uploads/linconnu.gif";
			if ($st->nb_joueurs > 0)
			{
				$img_joueurs = "";
				$t = explode('|', $st->joueurs);
				foreach($t as $j)
					$img_joueurs .= "<IMG HEIGHT=100 WIDTH=100 SRC=".($tab[$j]['photo'] != "" ? $tab[$j]['photo'] : "../uploads/linconnu.gif")." />";
			}

			$mytab = array();
			$i=0;
			$mytab[$i++] = $st->tournoi_classement;
			$mytab[$i++] = "<A HREF=stats_detail_equipe.php?id_detail=".$st->id." onmouseover=\"show_info_upright('".$img_joueurs."', event);\" onmouseout=\"close_info();\" CLASS=blue>".$this->eq_liste[$st->id]['nom']."</A>";
			$mytab[$i++] = $st->tournoi_points;
			$mytab[$i++] = $st->matchs_joues;
			$mytab[$i++] = $st->matchs_gagnes;
			if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $st->matchs_nuls;
			$mytab[$i++] = $st->matchs_perdus;
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_joues;
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_gagnes;
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = $st->sets_perdus;
			if (sess_context::getGestionSets() == 1) $mytab[$i++] = ($st->sets_diff > 0 ? "+" : "").$st->sets_diff;
			$mytab[$i++] = $st->buts_marques;
			$mytab[$i++] = $st->buts_encaisses;
			$mytab[$i++] = ($st->diff > 0 ? "+" : "").$st->diff;
			$mytab[$i++] = $st->tournoi_classement_moy;
			$mytab[$i++] = $st->stat_attaque;
			$mytab[$i++] = $st->stat_defense;
			$tmp[$st->id] = $mytab;
		}

		$i = 1;
		foreach($tmp as $data)
		{
			$data[0] = $i;
			$this->datas[$i++] = $data;
		}
	}
	function getXmlClassement()
	{
		// Synthèse des stats avec tri sur tournoi_points
		reset($this->classement);
		while(list($cle, $st) = each($this->classement))
		{
			echo "<EQUIPE CLASSEMENT=\"".($cle+1)."\" NOM=\"".$st->nom."\" POINTS=\"".$st->tournoi_points."\" MATCHS=\"".$st->matchs_joues."|".$st->matchs_gagnes."|".$st->matchs_perdus."\" SETS=\"".$st->sets_joues."|".$st->sets_gagnes."|".$st->sets_perdus."|".($st->sets_diff > 0 ? "+" : "").$st->sets_diff."\" BUTS=\"".$st->buts_marques."|".$st->buts_encaisses."|".($st->diff > 0 ? "+" : "").$st->diff."\" MOYENNE=\"".$st->tournoi_classement_moy."\">";
			echo "</EQUIPE>\n";
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT MATCHS JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsStatsJoueurs extends FXList
{
	function FXDisplayColumnsName()
	{
    	echo "<TR>";
       	HTMLTable::printCellWithRowSpan("N°",             $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
       	HTMLTable::printCellWithRowSpan("Joueur",         $this->color_title_column, "", "center", _CELLBORDER_SE_,  2);
        HTMLTable::printCellWithColSpan("Matchs",         $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
	    HTMLTable::printCellWithColSpan("Sets",           $this->c2_title, "", "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Buts attaquant", $this->color_title_column, "", "center", _CELLBORDER_SE_, 3);
        HTMLTable::printCellWithColSpan("Buts défenseur", $this->color_title_column, "", "center", _CELLBORDER_SE_, 3);
        HTMLTable::printCellWithRowSpan("Goal Average",   $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Fanny",          $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);

    	echo "<TR>";
        HTMLTable::printCell("J",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c1_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("Diff",      $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marq.",     $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Enc.",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Marq.",     $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Enc.",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("In",        $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Out",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($classement, $joueurs)
	{
		$t = array();

		$cl = explode('|', $classement);
	    foreach($cl as $c)
	    {
			$sjj = new StatJourneeJoueur();
			$sjj->init($c);

			$obj = get_object_vars($sjj);
			$obj['joueur'] = $joueurs[$obj['id']];
			$obj['matchs_joues']  = $obj['matchs_jouesA'] + $obj['matchs_jouesD'];
			if ($obj['diff_attaquant'] > 0) $obj['diff_attaquant'] = "+".$obj['diff_attaquant'];
			if ($obj['diff_defenseur'] > 0) $obj['diff_defenseur'] = "+".$obj['diff_defenseur'];
			if ($obj['diff'] > 0) $obj['diff'] = "+".$obj['diff'];

			$t[] = $obj;
		}

        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
        $this->FXSetTitle("Classement Joueurs");
        $this->FXSetColumnsDisplayed(array("joueur", "matchs_joues", "matchs_gagnes", "sets_joues", "sets_gagnes", "sets_perdus", "sets_diff", "buts_marques", "buts_encaisses_attaquant", "diff_attaquant", "buts_marques_defenseur", "buts_encaisses", "diff_defenseur", "diff", "fanny_in", "fanny_out"));
        $this->FXSetColumnsWidth(array("40%", "10%", "10%", "", "", "", "", "", "", "", "", "", "", "", "4%", "4%"));
        $this->FXSetColumnsAlign(array("left", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
        $this->FXSetColumnsColor(array("", $this->c1_column, $this->c1_title, $this->c2_column, $this->c2_column, $this->c2_column, $this->c2_column, "", "", $this->color_action_column, "", "", $this->color_action_column, $this->color_action_column, "", ""));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT MATCHS EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsStatsEquipes extends FXList
{
	function FXDisplayColumnsName()
	{
		echo "<TR>";
		HTMLTable::printCellWithRowSpan("N°",           $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
	   	HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_,  2);
	    HTMLTable::printCellWithRowSpan("Points",       $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
	    HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 0 ? 3 : 4);
	    if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithColSpan("Sets",         $this->c3_title, "", "center", _CELLBORDER_SE_, 4);
	    HTMLTable::printCellWithColSpan("Buts",         $this->color_title_column, "", "center", _CELLBORDER_SE_, 4);

		echo "<TR>";
	    HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    if (sess_context::getGestionSets() == 1) HTMLTable::printCell("J",         $this->c3_title, "", "center", _CELLBORDER_SE_);
	    if (sess_context::getGestionSets() == 1) HTMLTable::printCell("G",         $this->c3_title, "", "center", _CELLBORDER_SE_);
	    if (sess_context::getGestionSets() == 1) HTMLTable::printCell("P",         $this->c3_title, "", "center", _CELLBORDER_SE_);
	    if (sess_context::getGestionSets() == 1) HTMLTable::printCell("Diff",      $this->c3_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("Marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("Encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("Diff",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
	}

	function __construct($classement, $equipes)
	{
		$t = array();

		$cl = explode('|', $classement);
	    foreach($cl as $c)
	    {
			$sjj = new StatJourneeTeam();
			$sjj->init($c);

			$obj = get_object_vars($sjj);
			if (isset($equipes[$obj['id']]))
			{
				$obj['equipe'] = $equipes[$obj['id']];
				if ($obj['sets_diff'] > 0) $obj['sets_diff'] = "+".$obj['sets_diff'];
				if ($obj['diff'] > 0) $obj['diff'] = "+".$obj['diff'];

				$t[] = $obj;
			}
		}
        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
        $this->FXSetTitle("Classement Equipes");

		$nbCols = 13;
		if (sess_context::getGestionMatchsNul() == 0) $nbCols -= 1;
		if (sess_context::getGestionSets() == 0)      $nbCols -= 4;
		$this->FXSetNbCols($nbCols);

       	$mytab = array();
       	$i = 0;
       	$mytab[$i++] = "equipe";
       	$mytab[$i++] = "points";
       	$mytab[$i++] = "matchs_joues";
       	$mytab[$i++] = "matchs_gagnes";
       	if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = "matchs_nuls";
       	$mytab[$i++] = "matchs_perdus";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_joues";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_gagnes";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_perdus";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_diff";
       	$mytab[$i++] = "buts_marques";
       	$mytab[$i++] = "buts_encaisses";
       	$mytab[$i++] = "diff";
       	$this->FXSetColumnsDisplayed($mytab);

        $mytab = array();
        $i = 0;
        $mytab[$i++] = "";
        $mytab[$i++] = $this->c1_column;
        $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c2_column;
        if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        $mytab[$i++] = $this->color_action_column;
        $this->FXSetColumnsColor($mytab);

        $this->FXSetColumnsWidth(array("30%", "", ""));
		$this->FXSetColumnsAlign(array("left", "center", "center"));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT MATCHS EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListMatchsStatsEquipesLight extends FXList
{
	function FXDisplayColumnsName()
	{
		echo "<TR>";
		HTMLTable::printCellWithRowSpan("N°",           $this->color_title_column, "", "center", _CELLBORDER_U_,  2);
	   	HTMLTable::printCellWithRowSpan("Equipe",       $this->color_title_column, "", "center", _CELLBORDER_SE_,  2);
	    HTMLTable::printCellWithRowSpan("Points",       $this->c1_title, "", "center", _CELLBORDER_SE_, 2);
	    HTMLTable::printCellWithColSpan("Matchs",       $this->c2_title, "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 1 ? 4 : 3);
	    HTMLTable::printCellWithRowSpan("Goal<BR>average", $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);

		echo "<TR>";
	    HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	    HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
		HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
	}

	function __construct($classement, $equipes)
	{
		$t = array();

		$cl = explode('|', $classement);
	    foreach($cl as $c)
	    {
			$sjj = new StatJourneeTeam();
			$sjj->init($c);

			$obj = get_object_vars($sjj);
			if (isset($equipes[$obj['id']]))
			{
				$obj['equipe'] = $equipes[$obj['id']];
				if ($obj['sets_diff'] > 0) $obj['sets_diff'] = "+".$obj['sets_diff'];
				if ($obj['diff'] > 0) $obj['diff'] = "+".$obj['diff'];

				$t[] = $obj;
			}
		}
        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);

        $mytab = array();
        $i = 0;
        $mytab[$i++] = "equipe";
        $mytab[$i++] = "points";
        $mytab[$i++] = "matchs_joues";
        $mytab[$i++] = "matchs_gagnes";
        if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = "matchs_nuls";
        $mytab[$i++] = "matchs_perdus";
        $mytab[$i++] = "diff";
        $this->FXSetColumnsDisplayed($mytab);

		$nbCols = 12;
		if (sess_context::getGestionMatchsNul() == 0) $nbCols -= 1;
		$this->FXSetNbCols($nbCols);

        $mytab = array();
        $i = 0;
        $mytab[$i++] = "";
        $mytab[$i++] = $this->c1_column;
        $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c2_column;
        if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        $mytab[$i++] = $this->color_action_column;
        $this->FXSetColumnsColor($mytab);

        $this->FXSetColumnsWidth(array("42%", "", ""));
		$this->FXSetColumnsAlign(array("left", "center", "center"));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// STATS JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsJoueurs extends FXList
{
	var $sgb;
	var $id_joueurs;
	var $nom_joueurs;
	var $stats_joueurs;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
		HTMLTable::printCellWithRowSpan("N°",       $this->color_title_column, "",    "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Joueur",   $this->color_title_column, "30%", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithRowSpan("Présence", $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",   $this->c1_title, "",    "center", _CELLBORDER_SE_, 2);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithColSpan("Sets",     $this->c2_title, "",    "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Forme",    $this->c3_title, "",    "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Podium",   $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Moy buts", $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);
        if (sess_context::getGestionFanny() == 1) HTMLTable::printCellWithColSpan("Fanny",    $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("J",      $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("G",      $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("P",      $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("Diff",   $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Etat",      $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("Last",      $this->c3_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("1er",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("2me",       $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("marq.",     $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("enc.",      $this->color_title_column, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionFanny() == 1) HTMLTable::printCell("In",        $this->color_title_column, "4%", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionFanny() == 1) HTMLTable::printCell("Out",       $this->color_title_column, "4%", "center", _CELLBORDER_SE_);
	}

	function __construct($sgb, $id_joueur = "-1")
	{
		$this->sgb = $sgb;
		$this->id_joueurs    = $sgb->getIdPlayers();
		$this->nom_joueurs   = $sgb->getPlayersName();
		$this->stats_joueurs = $sgb->getStatsPlayers();

		if (count($this->nom_joueurs) == 0)
		{
	        $this->FXSetTitle("Statistiques Joueurs");
	        $this->FXSetColumnsWidth(array("30%", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
//			$this->FXList("");
			FXList::__construct("");
			$this->FXSetNbCols(17);
			return;
		}

		$t = array();

		$items = $id_joueur == "-1" ? $this->getStatsAllJoueurs() : $this->getStatsJoueur($id_joueur);
		if ($items) while(list($cle, $val) = each($items))
        {
			if ($val == _FXSEPARATOR_ || $val == _FXSEPARATORWITHINIT_)
			{
				$t[] = $val;
				continue;
			}
        	$id = $this->id_joueurs[$val];
			$obj = get_object_vars($this->stats_joueurs[$id]);

        	$or  = $sgb->isBestMedaille($id)   ? "<TD><IMG SRC=../images/etoiles/etoile_or.gif     BORDER=0 ALT=\"Meilleur médaillé\" /></TD>"   : "";
        	$ag  = $sgb->isBestPerformeur($id) ? "<TD><IMG SRC=../images/etoiles/etoile_argent.gif BORDER=0 ALT=\"Meilleur performeur\" /></TD>" : "";
        	$lib = "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 SUMMARY=\"\"><TR><TD><A HREF=stats_detail_joueur.php?id_detail=".$id." CLASS=blue>".$val."</A></TD>".$or.$ag."</TABLE>";
			$obj['joueur'] = $lib;
			$obj['pourc_joues']  = "<TABLE BORDER=0 CELLPADDING=1 SUMMARY=\"\"><TR><TD NOWRAP>".$obj['pourc_joues']." %</TD></TABLE>";
			$obj['pourc_gagnes'] = sprintf("<TABLE BORDER=0 CELLPADDING=1 SUMMARY=\"\"><TR><TD NOWRAP>%2.2f %%</TD></TABLE>", $obj['pourc_gagnes']);

			if ($obj['etat'] == _ETAT_JOUEUR_BLESSE_)   $obj['forme_indice'] = "<IMG SRC=../images/fleches/blesse.gif   BORDER=0 ALT=\"blessé\" />";
			if ($obj['etat'] == _ETAT_JOUEUR_VACANCES_) $obj['forme_indice'] = "<IMG SRC=../images/fleches/vacances.gif BORDER=0 ALT=\"vacances\" />";

			$t[] = $obj;
		}

        $fxbody = new FXBodyArray($t, _FXLIST_FULL_);
		parent::__construct($fxbody);

        $mytab = array();
        $i = 0;
        $mytab[$i++] = "joueur";
        $mytab[$i++] = "lib_presence";
        $mytab[$i++] = "pourc_joues";
        $mytab[$i++] = "pourc_gagnes";
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_joues";
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_gagnes";
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_perdus";
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_diff";
        $mytab[$i++] = "forme_indice";
        $mytab[$i++] = "forme_last_indice";
        $mytab[$i++] = "podium";
        $mytab[$i++] = "polidor";
        $mytab[$i++] = "moy_marquesA";
        $mytab[$i++] = "moy_encaissesD";
        if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "fanny_in";
        if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "fanny_out";
        $this->FXSetColumnsDisplayed($mytab);

        $mytab = array();
        $i = 0;
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        $mytab[$i++] = $this->c1_column;
        $mytab[$i++] = $this->c1_title;
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c2_column;
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c2_column;
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c2_column;
        if (sess_context::getGestionSets() == 1) $mytab[$i++] = $this->c2_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = $this->c3_column;
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        $mytab[$i++] = "";
        if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "";
        if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "";
        $this->FXSetColumnsColor($mytab);

        $this->FXSetTitle("Statistiques Joueurs");
        $this->FXSetColumnsAlign(array("left", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
        $this->FXSetColumnsWidth(array("30%", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));

		$nbCols = 18;
		if (sess_context::getGestionMatchsNul() == 0) $nbCols -= 1;
		if (sess_context::getGestionSets() == 0)      $nbCols -= 3;
		if (sess_context::getGestionFanny() == 0)     $nbCols -= 2;
		$this->FXSetNbCols($nbCols);
	}

	function getStatsJoueur($id_joueur)
	{
		$res = array();

		sort($this->nom_joueurs);
		while(list($cle, $val) = each($this->nom_joueurs))
		{
        	if ($this->id_joueurs[$val] == $id_joueur)
			{
				$res[$cle] = $val;
				break;
			}
		}

		return $res;
	}

	function getStatsAllJoueurs()
	{
		$regulier = array();
		$okaz     = array();

		sort($this->nom_joueurs);
		while(list($cle, $val) = each($this->nom_joueurs))
		{
        	$id = $this->id_joueurs[$val];
			if ($this->stats_joueurs[$id]->presence == 0) $okaz[$cle] = $val;
			if ($this->stats_joueurs[$id]->presence == 1) $regulier[$cle] = $val;
		}

		if (count($regulier) == 0)
			return array_merge($okaz);
		else if (count($okaz) == 0)
			return array_merge($regulier);
		else
			return array_merge($regulier, $okaz);
	}
	function getXmlClassement()
	{
        // Synthèse des stats avec tri sur tournoi_points
		reset($this->fxbody->tab);
		while(list($cle, $val) = each($this->fxbody->tab))
		{
			$elt = $this->fxbody->tab[$cle];
			echo "<JOUEUR CLASSEMENT=\"".($cle+1)."\" NOM=\"".$elt['joueur']."\" PRESENCE=\"".$elt['lib_presence']."\" MATCHS=\"".$elt['pourc_joues']."|".$elt['pourc_gagnes']."\" SETS=\"".$elt['sets_joues']."|".$elt['sets_gagnes']."|".$elt['sets_perdus']."|".($elt['sets_diff'] > 0 ? "+" : "").$elt['sets_diff']."\" FORME= BUTS=\"".$elt['moy_marquesA']."|".$elt['moy_encaissesD']."\">";
			echo "</JOUEUR>\n";
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// STATS JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsTournoiJoueurs extends FXList
{
	var $sgb;
	var $id_joueurs;
	var $nom_joueurs;
	var $stats_joueurs;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
		HTMLTable::printCellWithRowSpan("N°",       $this->color_title_column, "",    "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Joueur",   $this->color_title_column, "30%", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",   $this->c1_title, "",    "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 1 ? 4 : 3);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCellWithColSpan("Sets",     $this->c2_title, "",    "center", _CELLBORDER_SE_, 4);
        HTMLTable::printCellWithColSpan("Moy buts", $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);
        if (sess_context::getGestionFanny() == 1) HTMLTable::printCellWithColSpan("Fanny",    $this->color_title_column, "",    "center", _CELLBORDER_SE_, 2);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c1_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("J",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("G",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("P",         $this->c2_title, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionSets() == 1) HTMLTable::printCell("Diff",      $this->c2_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("marqués",   $this->color_title_column, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("encaissés", $this->color_title_column, "", "center", _CELLBORDER_SE_);
        if (sess_context::getGestionFanny() == 1)
        {
        	HTMLTable::printCell("In",        $this->color_title_column, "4%", "center", _CELLBORDER_SE_);
        	HTMLTable::printCell("Out",       $this->color_title_column, "4%", "center", _CELLBORDER_SE_);
        }
	}

	function __construct($sgb, $id_joueur = "-1")
	{
		$this->sgb = $sgb;
		$this->nom_joueurs   = $sgb->getPlayersName();
		$this->stats_joueurs = $sgb->getStatsPlayers();

		if (count($this->nom_joueurs) == 0)
		{
	        $this->FXSetTitle("Statistiques Joueurs");
	        $this->FXSetColumnsWidth(array("30%", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
			$this->FXSetNbCols(17);
			return;
		}

		$tri = array();
		$tab = array();

		$items = $id_joueur == "-1" ? $this->nom_joueurs : array($id_joueur => $this->nom_joueurs[$id_joueur]);
		while(list($id, $val) = each($items))
        {
			$obj = get_object_vars($this->stats_joueurs[$id]);

        	$or  = $sgb->isBestMedaille($id)   ? "<TD><IMG SRC=../images/etoiles/etoile_or.gif     BORDER=0 ALT=\"Meilleur médaillé\" /></TD>"   : "";
        	$ag  = $sgb->isBestPerformeur($id) ? "<TD><IMG SRC=../images/etoiles/etoile_argent.gif BORDER=0 ALT=\"Meilleur performeur\" /></TD>" : "";
        	$lib = "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1><TR><TD><A HREF=stats_detail_joueur.php?id_detail=".$id." CLASS=blue>".$val."</A></TD>".$or.$ag."</TABLE>";
			$obj['perdus'] = $obj['joues'] - $obj['gagnes'];
			$obj['joueur'] = $lib;
			$obj['pourc_gagnes'] = sprintf("<TABLE BORDER=0 CELLPADDING=1><TR><TD NOWRAP>%2.2f %%</TD></TABLE>", $obj['pourc_gagnes']);

			$tab[$id] = $obj;
			$tri[$id] = $val;
		}

		// Tri sur le nom
		array_multisort($tri, SORT_ASC, $tab);

        $fxbody = new FXBodyArray($tab, _FXLIST_FULL_);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);

		$this->FXSetTitle("Statistiques Joueurs");

		$nbcols = 14;
		if (sess_context::getGestionMatchsNul() == 0) $nbcols -= 1;
		if (sess_context::getGestionFanny() == 0)     $nbcols -= 2;
		if (sess_context::getGestionSets() == 0)      $nbcols -= 3;
		$this->FXSetNbCols($nbcols);

       	$mytab = array();
       	$i = 0;
       	$mytab[$i++] = "joueur";
       	$mytab[$i++] = "joues";
       	$mytab[$i++] = "gagnes";
       	if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = "nuls";
       	$mytab[$i++] = "perdus";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_joues";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_gagnes";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_perdus";
       	if (sess_context::getGestionSets() == 1) $mytab[$i++] = "sets_diff";
       	$mytab[$i++] = "moy_marquesA";
       	$mytab[$i++] = "moy_encaissesD";
       	if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "fanny_in";
       	if (sess_context::getGestionFanny() == 1) $mytab[$i++] = "fanny_out";
       	$this->FXSetColumnsDisplayed($mytab);

       	$mytab = array();
       	$i = 0;
       	$mytab[$i++] = "";
       	$mytab[$i++] = $this->c1_column;
       	if (sess_context::getGestionMatchsNul() == 1) $mytab[$i++] = $this->c1_column;
       	$mytab[$i++] = $this->c1_column;
       	$mytab[$i++] = $this->c2_column;
       	$mytab[$i++] = $this->c2_column;
       	$mytab[$i++] = $this->c2_column;
       	$mytab[$i++] = $this->c2_column;
       	$this->FXSetColumnsColor($mytab);

       	$this->FXSetColumnsWidth(array("30%", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES CONFRONTATIONS D'UNE EQUIPE AVEC LES AUTRES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class FXListStatsConfrontations extends FXList
{
	var $championnat;
	var $id_equipe;

	function FXDisplayColumnsName()
	{
        echo "<TR>";
		HTMLTable::printCellWithRowSpan("N°",       $this->color_title_column, "3%", "center", _CELLBORDER_U_,  2);
        HTMLTable::printCellWithRowSpan("Equipe",   $this->color_title_column, "", "center", _CELLBORDER_SE_, 2);
        HTMLTable::printCellWithColSpan("Matchs",   $this->c1_title, "", "center", _CELLBORDER_SE_, sess_context::getGestionMatchsNul() == 1 ? 4 : 3);

        echo "<TR>";
        HTMLTable::printCell("J",         $this->c1_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("G",         $this->c1_title, "", "center", _CELLBORDER_SE_);
		if (sess_context::getGestionMatchsNul() == 1) HTMLTable::printCell("N", $this->c1_title, "", "center", _CELLBORDER_SE_);
        HTMLTable::printCell("P",         $this->c1_title, "", "center", _CELLBORDER_SE_);
	}

	function __construct($championnat, $id_equipe, $delta = "")
	{
		$this->championnat = $championnat;
		$this->id_equipe   = $id_equipe;

		$ses = new SQLEquipesServices($championnat);

        $fxbody = new FXBodyArray($ses->getStatsConfrontations($id_equipe), $delta);
//		$this->FXList($fxbody);
		FXList::__construct($fxbody);
		$this->FXSetNbCols(sess_context::getGestionMatchsNul() == 1 ? 5 : 4);
		if (sess_context::getGestionMatchsNul() == 1)
	        $this->FXSetColumnsDisplayed(array("equipe", "joues", "gagnes", "nuls", "perdus"));
	    else
	        $this->FXSetColumnsDisplayed(array("equipe", "joues", "gagnes", "perdus"));
        $this->FXSetTitle("Confrontations");
        $this->FXSetColumnsWidth(array("50%", "", "", ""));
        $this->FXSetColumnsColor(array("", $this->c1_column, $this->c1_column, $this->c1_column, $this->c1_column));
		$this->FXSetColumnsAlign(array("left", "", "", ""));
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES THEMES POUR LES ALBUMS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListAlbumsThemes extends FXList
{
	var $championnat;
	var $admin;

	function __construct($championnat, $admin = false)
	{
		$this->championnat = $championnat;
		$this->admin       = $admin;

		if ($admin)
		{
	       	$requete = "SELECT CONCAT('<A HREF=albums.php?id_theme=', id, ' CLASS=blue>', nom, '</A>') nom, nb_photos, date, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_theme(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_theme(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action FROM jb_albums_themes WHERE id_champ=".$championnat." ORDER BY date";
	        $fxbody = new FXBodySQL($requete);
//    	    $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetNbCols(4);
	        $this->FXSetColumnsDisplayed(array("nom", "nb_photos", "date", "action"));
       		$this->FXSetColumnsName(array("Thème", "Nb photos", "Date", "Action"));
        	$this->FXSetTitle("Thèmes Albums");
        	$this->FXSetColumnsWidth(array("50%", "", "", ""));
        	$this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
			$this->FXSetColumnsAlign(array("left", "", "", ""));
		}
		else
		{
	       	$requete = "SELECT CONCAT('<A HREF=albums.php?id_theme=', id, ' CLASS=blue>', nom, '</A>') nom, nb_photos, date FROM jb_albums_themes WHERE id_champ=".$championnat." ORDER BY date";
	        $fxbody = new FXBodySQL($requete);
//    	    $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetNbCols(3);
	        $this->FXSetColumnsDisplayed(array("nom", "nb_photos", "date"));
       		$this->FXSetColumnsName(array("Thème", "Nb photos", "Date"));
        	$this->FXSetTitle("Thèmes Albums");
        	$this->FXSetColumnsWidth(array("50%", "", "", ""));
        	$this->FXSetColumnsColor(array("", "", ""));
			$this->FXSetColumnsAlign(array("left", "", ""));
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// LISTE DES PHOTOS DES ALBUMS
// /////////////////////////////////////////////////////////////////////////////////////
class FXListAlbums extends FXList
{
	var $championnat;
	var $admin;
	var $theme;

	function __construct($championnat, $theme, $admin = false)
	{
		$this->championnat = $championnat;
		$this->admin       = $admin;
		$this->theme       = $theme;

		if ($admin)
		{
	       	$requete = "SELECT *, CONCAT('<IMG SRC=', photo, ' BORDER=0 WIDTH=45 HEIGHT=45 />') photo, CONCAT('<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><A HREF=# onClick=\"javascript:modifier_photo(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_edit2.gif BORDER=0 /></A></TD><TD><A HREF=# onClick=\"javascript:supprimer_photo(\'+WHERE+id%3D',id,'\');\"><IMG SRC=../images/small_poubelle.gif BORDER=0 /></A></TD></TABLE>') action FROM jb_albums WHERE id_champ=".$championnat." AND id_theme=".$theme;
	        $fxbody = new FXBodySQL($requete);
//    	    $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetNbCols(4);
	        $this->FXSetColumnsDisplayed(array("commentaire", "photo", "date", "action"));
       		$this->FXSetColumnsName(array("Commentaire", "Photo", "Date", "Action"));
        	$this->FXSetTitle("Photos");
        	$this->FXSetColumnsWidth(array("50%", "", "", ""));
        	$this->FXSetColumnsColor(array("", "", "", $this->color_action_column));
			$this->FXSetColumnsAlign(array("left", "", "", ""));
		}
		else
		{
	       	$requete = "SELECT * FROM jb_albums WHERE id_champ=".$championnat." AND id_theme=".$theme;
	        $fxbody = new FXBodySQL($requete);
//    	    $this->FXList($fxbody);
			FXList::__construct($fxbody);
			$this->FXSetNbCols(3);
	        $this->FXSetColumnsDisplayed(array("commentaire", "photo", "date"));
       		$this->FXSetColumnsName(array("Commentaire", "Photo", "Date"));
        	$this->FXSetTitle("Photos");
        	$this->FXSetColumnsWidth(array("50%", "", "", ""));
        	$this->FXSetColumnsColor(array("", "", ""));
			$this->FXSetColumnsAlign(array("left", "", ""));
		}
	}
}


?>
