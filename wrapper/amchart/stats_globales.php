<?

class Stats_globales {

function year_exists($year) {
	$sql = "SELECT count(*) total FROM stats_globales WHERE year=".$year;
	$res = mysqli_query($sql);
	$row = mysqli_fetch_assoc($res);
	return ($row['total'] == 1 ? true : false);
}

function insert_year($year) {
	$sql = "INSERT INTO stats_globales (year) VALUES (".$year.")";
	$res = mysqli_query($sql);
}

function generateData() {
	$fp = fopen("stats_globales.js", "w+");
	fwrite($fp, "var sg_data = [];\n");

	$res = mysqli_query('SELECT * FROM stats_globales ORDER BY year ASC');

	while ($row = mysqli_fetch_assoc($res)) {

		$t = mktime(0, 0, 0, 1, 1, $row['year']);

		$v = explode(",", $row['visites']);
		$u = explode(",", $row['uniques']);
		$p = explode(",", $row['pages']);

		for($i = 0; $i < count($v); $i++) {
			fwrite($fp, "sg_data.push({ date: new Date(".date("Y", $t).", ".(date("n", $t)-1).", ".date("j", $t)."), visites: ".$v[$i].", uniques: ".floor((isset($u[$i]) ? $u[$i] : 0)/2).", pages: ".(isset($p[$i]) ? $p[$i] : 0)." });\n");
			$t += 24 * 3600;
		}
	}

	$evts = array();
	$evts[] = 'var e0 = { date: new Date(2012, 08, 19), type: "sign", backgroundColor: "#85CDE6", text: "C", description: "Création du championnat" };';
	$evts[] = 'var e1 = { date: new Date(2012, 10, 19), type: "flag", backgroundColor: "#FFFFFF", backgroundAlpha: 0.5, text: "J", description: "50ième Journée" };';
	$evts[] = 'var e2 = { date: new Date(2012, 10, 19), type: "flag", backgroundColor: "#FFFFFF", backgroundAlpha: 0.5, text: "J", description: "50ième Journée" };';

	$str = "";
	for($i=0; $i < count($evts); $i++) { fwrite($fp, $evts[$i]."\n"); $str .= ($str == "" ? "" : ",")."e".$i; }

	fwrite($fp, "var sg_events = [".$str."];");

	fclose($fp);
}

function update_year($year, $visites, $uniques, $pages) {
	if (!$this->year_exists($year)) $this->insert_year($year);

	$sum_visites = 0;
	$tmp = explode(",", $visites);
	for($i = 0; $i < count($tmp); $i++) $sum_visites += $tmp[$i];
	$avg_visites = round($sum_visites / count($tmp));

	$sum_uniques = 0;
	$tmp = explode(",", $uniques);
	for($i = 0; $i < count($tmp); $i++) $sum_uniques += $tmp[$i];
	$avg_uniques = round($sum_uniques / count($tmp));

	$sum_pages = 0;
	$tmp = explode(",", $pages);
	for($i = 0; $i < count($tmp); $i++) $sum_pages += $tmp[$i];
	$avg_pages = round($sum_pages / count($tmp));

	$sql = "UPDATE stats_globales SET sum_visites=".$sum_visites.", avg_visites=".$avg_visites.", visites='".$visites."', sum_uniques=".$sum_uniques.", avg_uniques=".$avg_uniques.", uniques='".$uniques."', sum_pages=".$sum_pages.", avg_pages=".$avg_pages.", pages='".$pages."', last_update=now()";
	$res = mysqli_query($sql);

	$this->generateData();
}

}

?>