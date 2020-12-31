<?

if (isset($refChampForm) && $refChampForm != "" && isset($refJourneeForm) && $refJourneeForm != "")
{
//	$url = "http://127.0.0.1/Sites/jorkyball/www/journee.xml";
	$url = "http://www.myfreesport.fr/stats/football-live/loadmodule.php?module=main&type=xml&args=ref_group=".$refChampForm."&ref_day=&num_matchday=".$refJourneeForm."&ref_match=";

	if (!($fp = fopen($url,"r"))) die ("could not open file for input");

	$data = "";
	while(!feof($fp))
		$data .= fread ($fp, 4096);

	fclose($fp);

	echo trim(ereg_replace ("<\?xml.*\"\?>", "", $data));
}
else
{
	header('Content-Type: text/html; charset=ISO-8859-15');
	echo "Pb paramatrères ...";
}

?>