<?include "../include/sess_context.php";ini_set("url_rewriter.tags","input=src");ini_set('arg_separator.output', '&amp;');session_start();session_register("sess_context");if (!isset($jb_langue)){	$jb_langue = "fr";	setcookie("jb_langue", $jb_langue, time()+(3600*24*30*6));}include "../include/lock.php";include "../include/inc_db.php";include "../include/constantes.php";include "../include/cache_manager.php";include "../include/ads_manager.php";include "../include/toolbox.php";include "../include/templatebox.php";include "../include/menu.php";include "../include/HTMLTable.php";include "SQLServices.php";include "ManagerFXList.php";include "StatsBuilder.php";require('../rsslib/rss_fetch.inc');$sess_context = new sess_context();$sess_context->setLangue($jb_langue);include "../lang/nls_".$sess_context->getLangue().".php";$home_page = 1;if ($sess_context->isChampionnatNonValide())	ToolBox::alert('D�sol�, ce championnat n\'existe pas, veuillez en saisir un autre ou utilisez l\'aide ...');// Si on vient ou revient sur cette page, on r�init ...$sess_context->setChampionnatNonValide();$db = dbc::connect();$menu = new menu("home");$menu->debut("-1", "-1", "");// Remise � vide de l'antispamunset($_SESSION['antispam']);?><form action="championnat_acces.php" method="post"><div>	<input type="hidden" name="ref_champ"  value="8" />	<input type="hidden" name="id_sondage" value="0" />	<input type="hidden" name="reponse1"   value="0" /></div><?	include ("../include/module_bienvenue.php");	include ("../include/module_direct.php");//	include ("../include/module_news.php");//	include ("../include/module_newsletter_home.php");?></form><?if (isset($once_sondage) && $once_sondage == 1)	ToolBox::alert('D�sol�, on ne vote qu\'une fois ...');$menu->end(); ?>