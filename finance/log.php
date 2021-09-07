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

$searchthis = "ALPHAV";
$matches_info = array();
$matches_warn = array();
$matches_error = array();

$handle = @fopen("./finance.log", "r");
if ($handle)
{
    while (!feof($handle))
    {
        $buffer = fgets($handle);
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "[OK]") && stripos($buffer, "INFO") && stripos($buffer, "getData"))
            $matches_info[] = $buffer;
        else if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "WARN"))
            $matches_warn[] = $buffer;
        else if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "ERROR"))
            $matches_error[] = $buffer;
    }
    fclose($handle);
}

?>

<div class="ui container inverted segment">
    <h2>Log
        <button id="lst_filter1_bt" class="mini ui blue button"><?= number_format(filesize("./finance.log") / 1048576, 2) . ' MB' ?> bytes</button>
        <button id="lst_filter1_bt" class="mini ui green button"><?= count($matches_info) ?></button>
        <button id="lst_filter1_bt" class="mini ui orange button"><?= count($matches_warn) ?></button>
        <button id="lst_filter1_bt" class="mini ui red button"><?= count($matches_error) ?></button>
    </h2>
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


<div class="ui container inverted segment">
    <h2> Alphavantage</h2>
    <pre style="width: 100%; height: 300px; overflow: scroll;">

<? 

echo "info : <br />";
foreach($matches_info as $key => $val) echo $val;
echo "warn : <br />";
foreach($matches_warn as $key => $val) echo $val;
echo "error : <br />";
foreach($matches_error as $key => $val) echo $val;

?>

    </pre>
</div>

