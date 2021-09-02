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

$logfile = "./finance.log";
?>

<div class="ui container inverted segment">
    <h2>Cron (<?= filesize($logfile) ?> bytes)</h2>
    <pre style="width: 100%; height: 500px; overflow: scroll;">

<? echo shell_exec( 'tail -n '.$nb_lignes.' ./finance.log'); ?>

    </pre>
</div>

<div class="ui container inverted segment">
    <h2> User Connexions</h2>
    <pre style="width: 100%; height: 500px; overflow: scroll;">

<? 


$req = "SELECT * FROM connexions ORDER BY datetime DESC LIMIT ".$nb_lignes;
$res = dbc::execSql($req);

while($row = mysqli_fetch_array($res)) {
    echo $row['datetime']." | ".sprintf("%-40s", $row['email'])." | ".sprintf("%-64s", $row['ip'])." | ".$row['status']."<br />";
}

?>

    </pre>
</div>

