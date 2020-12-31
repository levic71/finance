<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;
$jb_langue = "fr";

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$menu = new menu("forum_access");
$menu->debut("");

?>

<div id="pageint" style="margin-bottom: 0px">

<h2>Show portail</h2>

<ul>
<?

$liste = JKCache::getCache("../cache/access_home.txt", 900, "_FLUX_ACCESS_");

foreach($liste as $c)
{
	echo "<li><a href=\"../cache/myhome_".$c['id'].".html\">".htmlspecialchars($c['nom'])."</a></li>";
}

?>
</ul>

</div>

<? $menu->end(); ?>
