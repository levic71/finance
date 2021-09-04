<?

require_once "sess_context.php";

session_start();

// toto

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$db = dbc::connect();

$nb_lignes = 200;

foreach(['nb_lignes'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if (!is_dir("cache/")) mkdir("cache/");

?>

<div class="ui container inverted segment">
<<<<<<< HEAD
    <h2>Cron (<?= number_format(filesize("./finance.log") / 1048576, 2) . ' MB' ?> bytes)</h2>
    <pre style="width: 100%; height: 500px; overflow: scroll;">
=======
    <h2>Cron (<?= filesize($logfile) ?> bytes)</h2>
    <pre style="width: 100%; height: 300px; overflow: scroll;">
>>>>>>> develop

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

$searchthis = "ALPHAV";
$matches = array();
$matches_Note = array();
$matches_Error = array();

$handle = @fopen("./finance.log", "r");
if ($handle)
{
    while (!feof($handle))
    {
        $buffer = fgets($handle);
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && !stripos($buffer, "[NOTE]") && !stripos($buffer, "[ERROR]"))
            $matches[] = $buffer;
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && stripos($buffer, "[NOTE]"))
            $matches_Note[] = $buffer;
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && !stripos($buffer, "No Update") && stripos($buffer, "[ERROR]"))
            $matches_Error[] = $buffer;
    }
    fclose($handle);
}

echo "Calls = ".count($matches)."<br />";
echo "Note = ".count($matches_Note)."<br />";
echo "Error = ".count($matches_Error)."<br />";
var_dump($matches);
var_dump($matches_Note);
var_dump($matches_Error);

?>

    </pre>
</div>

