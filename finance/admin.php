<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$db = dbc::connect();

$nb_lignes = 400;

foreach(['nb_lignes', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if (!is_dir("cache/")) mkdir("cache/");

if ($action == "reset") {
    cacheData::deleteTMPFiles();
}

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
        if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "[OK]") && stripos($buffer, "INFO") && stripos($buffer, "ALPHAV") && !stripos($buffer, "[No update]"))
            $matches_info[] = $buffer;
        else if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "WARN") && !stripos($buffer, "[No update]"))
            $matches_warn[] = $buffer;
        else if(stripos($buffer, date("d-M-Y")) && stripos($buffer, $searchthis) && stripos($buffer, "ERROR") && !stripos($buffer, "[No update]"))
            $matches_error[] = $buffer;
    }
    fclose($handle);
}

?>

<div class="ui container inverted segment">
    <h2><i class="inverted black code icon"></i>&nbsp;&nbsp;Log
        <button id="lst_filter1_bt" class="mini ui blue button"><?= number_format(filesize("./finance.log") / 1048576, 2) . ' MB' ?> bytes</button>
        <button id="lst_filter1_bt" class="mini ui green button"><?= count($matches_info) ?></button>
        <button id="lst_filter1_bt" class="mini ui orange button"><?= count($matches_warn) ?></button>
        <button id="lst_filter1_bt" class="mini ui red button"><?= count($matches_error) ?></button>
        <button id="log_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chevron down icon"></i></button>
    </h2>
    <pre id="log_view" style="width: 100%; overflow: scroll;">

<?
    $text = shell_exec( 'tail -n '.$nb_lignes.' ./finance.log'); 

    foreach(preg_split('~[\r\n]+~', $text) as $line){
        if(empty($line) or ctype_space($line)) continue; // skip only spaces
        // if(!strlen($line = trim($line))) continue; // or trim by force and skip empty
        if (strstr($line, "ERROR") || strstr($line, "WARN") || strstr($line, "###"))
            echo "<mark style=\"background-color: ".(strstr($line, "ERROR") ? "red" : (strstr($line, "WARN") ? "orange" : (strstr($line, "###") ? "cyan" : "#222")))."\">".$line."</mark><br />";
        else
            echo $line."<br />";
    }
?>

    </pre>
</div>

<div class="ui container inverted segment">
    <h2><i class="inverted black users icon"></i>&nbsp;&nbsp;User Connexions <button id="users_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chevron down icon"></i></button></h2>
    <pre id="users_view" style="width: 100%; overflow: scroll;">

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
    <h2><i class="inverted black dashboard icon"></i>&nbsp;&nbsp;Alphavantage <button id="alpha_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chevron down icon"></i></button></h2>
    <pre id="alpha_view" style="width: 100%; overflow: scroll;">

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

<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header">
    <button id="admin_cron_bt" class="circular ui icon very small right floated pink labelled button">Cron</button>
    <button id="admin_refresh_bt" class="circular ui icon very small right floated pink labelled button">Refresh GSheet</button>
    <button id="admin_reset_bt" class="circular ui icon very small right floated pink labelled button">Reset Cache</button>
    </h2>
</div>


<script>
hideAllView = function() {
    hide('log_view');
    hide('users_view');
    hide('alpha_view');
}
hideAllView();
Dom.addListener(Dom.id('admin_reset_bt'), Dom.Event.ON_CLICK, function(event) { hideAllView(); go({ action: 'admin', id: 'main', url: 'admin.php?action=reset', loading_area: 'main' }); });
Dom.addListener(Dom.id('admin_refresh_bt'), Dom.Event.ON_CLICK, function(event) { hideAllView(); go({ action: 'admin', id: 'main', url: 'googlesheet/sheet.php?force=1', loading_area: 'main' }); });
Dom.addListener(Dom.id('admin_cron_bt'), Dom.Event.ON_CLICK, function(event) { hideAllView(); go({ action: 'admin', id: 'main', url: 'crontab.php', loading_area: 'main' }); });
Dom.addListener(Dom.id('log_eye_bt'), Dom.Event.ON_CLICK, function(event) { toogle('log_view'); });
Dom.addListener(Dom.id('users_eye_bt'), Dom.Event.ON_CLICK, function(event) { toogle('users_view'); });
Dom.addListener(Dom.id('alpha_eye_bt'), Dom.Event.ON_CLICK, function(event) { toogle('alpha_view'); });
</script>