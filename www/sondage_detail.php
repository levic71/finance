<?include "../include/sess_context.php";ini_set("url_rewriter.tags","input=src");ini_set('arg_separator.output', '&amp;');session_start();$jorkyball_redirect_exception = 1;include "common.php";include "../include/inc_db.php";include "ManagerFXList.php";$db = dbc::connect();if (isset($sess_context) && $sess_context->isChampionnatValide()){	$menu = new menu("full_access");	$menu->debut($sess_context->getChampionnatNom());}else{	$menu = new menu("forum_access");	$menu->debut("");}?><div id="pageint">    <h2>D�tail sondage</h2><?	$sondage_detail = 1;	include ("../include/module_sondage.php");?></div><? $menu->end(); ?>