<?include "../include/sess_context.php";session_start();$jorkyball_redirect_exception = 1;include "common.php";include "../include/inc_db.php";include "ManagerFXList.php";$db = dbc::connect();$no_ads468x60 = 1;if (isset($sess_context) && $sess_context->isChampionnatValide()){	$menu = new menu("full_access");	$menu->debut($sess_context->getChampionnatNom());}else{	$menu = new menu("forum_access");	$menu->debut("");}?><div id="pageint" style="text-align: center;"><h2>Liste de liens/partenaires</h2><div><a href="http://www.php.net/" onclick="window.open(this.href); return false;"><img src="../images/banner_php.gif" title="" alt="" /></a><a href="http://www.mysql.com/" onclick="window.open(this.href); return false;"><img src="../images/banner_mysql.gif" title="" alt="" /></a><a href="http://www.apache.org/" onclick="window.open(this.href); return false;"><img src="../images/banner_apache.gif" title="" alt="" /></a><a href="http://firefox.fr/" onclick="window.open(this.href); return false;" title="Get Firefox - The Browser, Reloaded."><img src="../images/jorkers/images/firefox-blue-62x15.png" width="62" height="15" alt="Get Firefox" /></a></div><div><a href="http://www.french-spider.com/" onclick="window.open(this.href); return false;"><img src="http://www.french-spider.com/images/ban88x31.gif" alt="French-Spider - Moteur de recherche France" width="88" height="31" /></a><a title="Annuaire avec rapports d'indexation Google" href="http://annuaire.yagoort.org"onclick="window.open(this.href); return false;"><img src="http://www.yagoort.org/css/yagblue.gif" title="" alt="" /></a></div><br /><div><script type="text/javascript" src="http://www.addme.com/button.js"></script></div><br /><br /></div><? $menu->end(); ?>