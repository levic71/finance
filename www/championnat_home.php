<?

include "../include/sess_context.php";

session_start();

if (isset($sess_context)) setcookie("mon_id_championnat", $sess_context->getRealChampionnatId(), time()+(3600*24*30*6));

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

if (isset($sess_context) && !isset($sess_context->championnat['saison_id']))
{
	$sess_context->setChampionnatNonValide();
    ToolBox::do_redirect("home.php");
}

$db = dbc::connect();

$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$sfs = new SQLForumServices($sess_context->getRealChampionnatId());
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), -1);
$sms = new SQLMatchsServices($sess_context->getChampionnatId(), -1, -1);
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());

// Récupération des infos du championnat
$row = $scs->getChampionnat();
if (!$row)
{
	$sess_context->setChampionnatNonValide();
    ToolBox::do_redirect("home.php");
}

// Récup des infos de la saison
$saison = $sss->getSaison();

$news = $row['news'];
$options = $row['options'] == "" ? "0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0" : $row['options'];
$opt = explode('|', $options);
if (!isset($opt[9])) $opt[9] = 0;
if (!isset($opt[10])) $opt[10] = 0;

if (isset($msgno) && $msgno == 1) ToolBox::alert("Inscription validée, vous êtes maintenant dans votre championnat");

$championnat_home = 1;

$menu = new menu("championnat_home");
$menu->debut($sess_context->getChampionnatNom());

// Récupération de la liste des équipes
$equipe = $ses->getListeEquipes();

?>

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">
<tr><td>

<?

echo "<div class=\"box\"><div class=\"box1\">";

if ($opt[0] == 1)
{

	$news = "<div id=\"newsint\">".($news == "" || $news == " " ? "No news." : nl2br($news))."</div>";

	echo "<div id=\"newsbox\">";
	$fxlist = new FXListPresentation(array(array($news)));
	$fxlist->FXSetTitle("News", "left");
	$fxlist->FXSetColumnsAlign(array("left", "left"));
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";

	if ($opt[1] == 1)
	{
		$forum = "";
		$liste = $sfs->getListeMessages("LIMIT 0,13");
        $forum .= "<div id=\"forumint\" class=\"corps\">";
	    if ($liste)
	    {
	        foreach($liste as $row)
			{
				$forum .= "<div class=\"date\">".Toolbox::mysqldate2smalldatetime($row['last_reponse'])."</div>";
				$forum .= "<div class=\"auteur\">".$row['nom']."</div>";
				if (strlen($row['title']) > 50)
					$forum .= "<div class=\"title\"><a href=\"#\" onclick=\"javascript:launch('forum_message.php?id_msg=".$row['id']."#bottom');\">".substr($row['title'], 0, 20)."</a></div>";
				else
					$forum .= "<div class=\"title\"><a href=\"#\" onclick=\"javascript:launch('forum_message.php?id_msg=".$row['id']."#bottom');\">".$row['title']."</a></div>";
				$forum .= "<div class=\"lecture\">".$row['nb_lectures']."</div>";
				$forum .= "<div class=\"reponse\">".$row['nb_reponses']."</div>";
			}
	    }
	    else
	    	$forum .= "Pas de messages";
        $forum .= "</div>";

		echo "<div id=\"forum\">";
		$fxlist = new FXListPresentation(array(array($forum)));
		$fxlist->FXReplaceTitle("<div><div style=\"float: left;\">Forum</div><div style=\"float: right;\">&nbsp;[L] [R]</div></div>", "left");
		$fxlist->FXSetColumnsAlign(array("left"));
		$fxlist->FXSetMouseOverEffect(false);
		$fxlist->FXDisplay();
		echo "</div>";
	}
}

// box1
echo "</div>";

echo "<div class=\"box2\">";
echo "<p class=\"pub250x250\">";
JKAds::getAds250x250();
echo "</p>";

$options = explode('|', $sess_context->getChampionnatOptions());

if ($opt[5] == 1)
{
	if ((isset($opt[6]) && $opt[6] == 1))
	{
		$focus = "";
		$sp = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

		echo "<div class=\"focus\">";
		$focus .= "<div class=\"focusint\">";

		$titre = "Focus joueur";
		$liste = $sp->getStatsPlayers();
	    if ($liste)
	    {
			$rand_key = array_rand($liste, 1);
			$xj = $liste[$rand_key];
			$img = $xj->photo == "" ? "../images/templates/defaut/linconnu.gif" : $xj->photo;
			$focus .= "<div class=\"photo\"><a href=\"../www/stats_detail_joueur.php?id_detail=".$xj->id."\" onmouseover=\"show_info_upleft('<img src=".$img.">', event);\" onmouseout=\"close_info();\"><img src=\"".$img."\" alt=\"Photo joueur\" /></a></div>";
			$focus .= "<div class=\"nom\">".$xj->nom." ".$xj->prenom."<br />as<br />".$xj->pseudo."</div>";
			$focus .= "<div class=\"opt1\">";
			$focus .= "<table border=\"0\" summary=\"\" cellspacing=\"0\" cellpadding=\"0\">";
			$focus .= "<tr><td class=\"label_r\">Participation : </td><td>".$xj->pourc_joues." %</td></tr>";
			$focus .= "<tr><td class=\"label_r\">Match gagnés : </td><td>".sprintf("%2.2f", $xj->pourc_gagnes)." %"."</td></tr>";
			$focus .= "<tr><td class=\"label_r\">1ère Place sur podium : </td><td>".$xj->podium." fois </td></tr>";
			$focus .= "<tr><td class=\"label_r\">Forme du moment : </td><td>".$xj->forme_indice."</td></tr>";
			$focus .= "<tr><td class=\"label_r\">Forme dernière journée jouée : </td><td>".$xj->forme_last_indice."</td></tr>";
			$focus .= "</table>";
			$focus .= "</div>";
	    }
	    else
	        $focus .= "Pas de joueurs";

		$focus .= "</div>";
		$fxlist = new FXListPresentation(array(array($focus)));
		$fxlist->FXSetTitle($titre, "left");
		$fxlist->FXSetColumnsAlign(array("left"));
		$fxlist->FXSetMouseOverEffect(false);
		$fxlist->FXDisplay();
		echo "</div>";
	}
}

if ($opt[10] == 1)
{
	$focus = "";
	$sp = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

	echo "<div class=\"focus\">";
	$focus .= "<div class=\"focusint\">";

	$titre = "Focus équipe";
	$liste = $sp->getStatsTeams();
    if ($liste)
    {
		$rand_key = array_rand($liste, 1);
		$xt = $liste[$rand_key];
		$img = "../images/templates/defaut/linconnu.gif";
		$focus .= "<div class=\"photo\"><a href=\"../www/stats_detail_equipe.php?id_detail=".$xt->id."\" onmouseover=\"show_info_upright('<img src=".$img." />', event);\" onmouseout=\"close_info();\"><img src=\"".$img."\" alt=\"Photo équipe\" /></a></div>";
		$focus .= "<div class=\"nom\">".$xt->nom." <br /> Nb joueurs : ".$xt->nb_joueurs."</div>";
		$focus .= "<div class=\"opt1\">";
		$focus .= "<table border=\"0\" summary=\"\" cellspacing=\"0\" cellpadding=\"0\">";
		$focus .= "<tr><td class=\"label_r\">Nb Participations : </td><td>".$xt->tournoi_nb_participation."</td></tr>";
		$focus .= "<tr><td class=\"label_r\">Match gagnés : </td><td>".sprintf("%2.2f", $xt->pourc_gagnes)." %"."</td></tr><tr><td class=\"label_r\">Moy classement : </td><td>".$xt->tournoi_classement_moy."</td></tr>";
		$focus .= "<tr><td class=\"label_r\">Stat attaque : </td><td>".$xt->stat_attaque." buts/match</td></tr>";
		$focus .= "<tr><td class=\"label_r\">Stat défense : </td><td>".$xt->stat_defense." buts/match</td></tr>";
		$focus .= "</table>";
		$focus .= "</div>";
    }
    else
        $focus .= "Pas d'équipes";

	$focus .= "</div>";
	$fxlist = new FXListPresentation(array(array($focus)));
	$fxlist->FXSetTitle($titre, "left");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";
}

// box2
echo "</div>";

// box
echo "</div>";

echo "<div class=\"box3cols\">";

if ($opt[3] == 1 || $opt[4] == 1)
{
	$lib1  = "";
	$liste = $sjs->getListeLast4Journees(date("Y-m-d"));
    if ($liste)
    {
        $lib1 .= "<div class=\"lastj\">";
        foreach($liste as $row)
		{
			$url = $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? ($row['virtuelle'] == 1 ? "journees_virtuelles_ajouter.php" : "matchs_tournoi.php") : "matchs.php";
            $lib1 .= "<div class=\"date\">".ToolBox::reformatDate($row['date'])."</div><div class=\"lib\"><a href=\"".$url."?pkeys_where_jb_journees=+WHERE+id=".$row['id']."\" class=\"blue\">".$row['nom']."</a></div>";
		}
        $lib1 .= "</div>";
    }
    else
        $lib1 .= "<div class=\"lastj\"><div class=\"lib\">Pas de journées planifiées</div></div>";

	$lib2 = "";
	$liste = $sjs->getListeNext4Journees(date("Y-m-d"));
    if ($liste)
    {
        $lib2 .= "<div class=\"nextj\">";
        foreach($liste as $row)
		{
			$url = $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? ($row['virtuelle'] == 1 ? "journees_virtuelles_ajouter.php" : "matchs_tournoi.php") : "matchs.php";
            $lib2 .= "<div class=\"date\">".ToolBox::reformatDate($row['date'])."</div><div class=\"lib\"><a href=\"".$url."?pkeys_where_jb_journees=+WHERE+id=".$row['id']."\" class=\"blue\">".$row['nom']."</a></div>";
		}
        $lib2 .= "</div>";
    }
    else
        $lib2 .= "<div class=\"nextj\"><div class=\"lib\">Pas de journées planifiées</div></div>";

	echo "<div class=\"col1\">";
	$fxlist = new FXListPresentation(array(array($opt[3] == 1 ? $lib1 : "&nbsp;")));
	$fxlist->FXSetTitle($opt[3] == 1 ? "Dernières Journées" : "", "left");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetNbCols(1);
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";

	echo "<div class=\"col2\">";
	$fxlist = new FXListPresentation(array(array($opt[4] == 1 ? $lib2 : "&nbsp;")));
	$fxlist->FXSetTitle($opt[4] == 1 ? "Prochaines Journées" : "", "left");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetNbCols(1);
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";
}

if ($opt[2] == 1)
{
	$fanny = "";
	$liste = $sms->getListeFannys();

    $fanny .= "<div class=\"fanny\">";
	if ($liste)
    {
         $i = 0;
         foreach($liste as $row)
         {
         	if (!isset($equipe[$row['id_equipe1']]['nom']) || !isset($equipe[$row['id_equipe2']]['nom'])) continue;

         	$score = explode(',', $row['resultat']);
         	$fanny .= "<div class=\"fline\"><div class=\"date\">".ToolBox::reformatDate($row['date'])."</div><div class=\"equipe1\">".$equipe[$row['id_equipe1']]['nom']."</div><div class=\"score\"><a href=\"".($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php")."?pkeys_where_jb_journees=+WHERE+id=".$row['id_journee']."\" class=\"blue\">".$score[0]."</a></div><div class=\"equipe2\">".$equipe[$row['id_equipe2']]['nom']."</div></div>";
            if ($i++ > 2) break;
         }
         $fanny .= "<div class=\"allaccess fline allf\"><a href=\"stats_joueurs_fannys.php\"> Tous les fannys </a></div>";
	}
    else
        $fanny .= "Pas de fannys";
    $fanny .= "</div>";

	echo "<div class=\"col3\">";
	$fxlist = new FXListPresentation(array(array($fanny)));
	$fxlist->FXSetTitle("Derniers Fannys", "left");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";
}

if ($opt[9] == 1)
{
	$lib = "";
	$liste = $sms->getLastMatchs();

    $lib .= "<div class=\"fanny\">";
	if (count($liste) > 0)
    {
         $i = 0;
         foreach($liste as $row)
         {
         	if (!isset($equipe[$row['id_equipe1']]['nom']) || !isset($equipe[$row['id_equipe2']]['nom'])) continue;

         	$score = explode(',', $row['resultat']);
         	$lib .= "<div class=\"fline\"><div class=\"date\">".ToolBox::reformatDate($row['date'])."</div><div class=\"equipe1\">".$equipe[$row['id_equipe1']]['nom']."</div><div class=\"score\"><a href=\"".($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php")."?pkeys_where_jb_journees=+WHERE+id=".$row['id_journee']."\" class=\"blue\">".$score[0]."</a></div><div class=\"equipe2\">".$equipe[$row['id_equipe2']]['nom']."</div></div>";
            if ($i++ > 3) break;
         }
	}
    else
        $lib .= "<p>Pas de matchs</p>";
    $lib .= "</div>";

	echo "<div class=\"col3\">";
	$fxlist = new FXListPresentation(array(array($lib)));
	$fxlist->FXSetTitle("Derniers Matchs Joués", "left");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</div>";
}


// 3cols
echo "</div>";

?>

</td></tr>
</table>

<br />

<!-- AddToAny BEGIN -->
<a class="a2a_dd" href="http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Fwww.jorkers.com&amp;linkname="><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" border="0" alt="Share"/></a>
<script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = "Jorkers - <?= $sess_context->getChampionnatNom() ?>";
a2a_config.show_title = 1;
a2a_config.linkurl = "http://www.jorkers.com/www/championnat_redirect.php?champ=<?= $sess_context->getRealChampionnatId() ?>";
</script>
<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
<!-- AddToAny END -->

<? $menu->end(); ?>
