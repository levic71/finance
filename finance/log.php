<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$db = dbc::connect();

$nb_lignes = 200;

foreach(['nb_lignes'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if (!is_dir("cache/")) mkdir("cache/");

?>

<div class="ui container inverted segment">
    <h2>Log <button id="lst_filter1_bt" class="mini ui blue button"><?= number_format(filesize("./finance.log") / 1048576, 2) . ' MB' ?> bytes</button></h2>
    <pre style="width: 100%; height: 300px; overflow: scroll;">

<? echo shell_exec( 'tail -n '.$nb_lignes.' ./finance.log'); ?>

    </pre>
</div>

<div class="ui container inverted segment">
    <h2> User Connexions</h2>
    <pre style="width: 100%; height: 300px; overflow: scroll;">

<? 


$req = "SELECT * FROM connexions ORDER BY datetime DESC LIMIT ".$nb_lignes;
$res = dbc::execSql($req);

while($row = mysqli_fetch_array($res)) {
    echo $row['datetime']." | ".sprintf("%-40s", $row['email'])." | ".sprintf("%-64s", $row['ip'])." | ".$row['status']."<br />";
}

?>

    </pre>
</div>

<? 

$searchthis = "ALPHAV";
$matches = array();
$matches_Tentative = array();
$matches_Note = array();
$matches_Error = array();

$handle = @fopen("./finance.log", "r");
if ($handle)
{
    while (!feof($handle))
    {
        $buffer = fgets($handle);
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && !stripos($buffer, "[NOTE]") && !stripos($buffer, "[ERROR]") && !stripos($buffer, "Thank you"))
            $matches[] = $buffer;
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && !stripos($buffer, "[NOTE]") && !stripos($buffer, "[ERROR]") && stripos($buffer, "Thank you"))
            $matches_Tentative[] = $buffer;
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && stripos($buffer, "[NOTE]"))
            $matches_Note[] = $buffer;
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && stripos($buffer, "[ERROR]"))
            $matches_Error[] = $buffer;
    }
    fclose($handle);
}

?>

<div class="ui container inverted segment">
    <h2> Alphavantage
        <button id="lst_filter1_bt" class="mini ui blue button"><?= count($matches) ?></button>
        <button id="lst_filter1_bt" class="mini ui orange button"><?= count($matches_Tentative) ?></button>
        <button id="lst_filter1_bt" class="mini ui orange button"><?= count($matches_Note) ?></button>
        <button id="lst_filter1_bt" class="mini ui red button"><?= count($matches_Error) ?></button>
    </h2>
    <pre style="width: 100%; height: 300px; overflow: scroll;">

<? 

var_dump($matches);
var_dump($matches_Tentative);
var_dump($matches_Note);
var_dump($matches_Error);


?>

    </pre>
</div>

