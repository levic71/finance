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

$liste = JKCache::getCache("../cache/access_home.txt", 900, "_FLUX_ACCESS_");

foreach($liste as $c)
{
//	echo $c['id'].":".htmlspecialchars($c['nom']);

	if (!($fp = fopen("http://www.jorkers.com/www/generate_home.php?championnat_id=".$c['id'],"r"))) die ("could not open file for input");

	$data = "";
	while(!feof($fp))
		$data .= fread ($fp, 4096);

	fclose($fp);

	$fichier = fopen("../cache/myhome_".$c['id'].".html", "w");
	fputs($fichier, $data);
	fclose($fichier);

}


?>