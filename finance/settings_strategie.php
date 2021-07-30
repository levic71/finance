<?

include_once "include.php";

$strategie_id = 1;

foreach(['strategie_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

if ($row['total'] != 1) {
    echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>"';
    exit(0);
}

$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$lst_symbol = array();
$t = json_decode($row['data'], true);
foreach($t['quotes'] as $key => $val)  $lst_symbol[] = $key;

?>

<style type="text/css">
	.column { max-width: 450px; }
</style>

<div class="ui inverted middle aligned center aligned grid segment container">
    <div class="column">
        <h2 class="ui teal image header">
            <div class="content">Stratégie</div>
        </h2>
        <form class="ui inverted large form">
            <div class="ui inverted stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" name="email" placeholder="E-mail address">
                    </div>
                </div>
                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password" placeholder="Password">
                    </div>
                </div>
                <div class="ui fluid large teal submit button">Login</div>
            </div>
            <div class="ui error message"></div>
        </form>
    </div>
</div>

<script>
//	Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $strategie_id ?>&capital_init='+valof('capital_init')+'&invest='+valof('invest')+'&date_start='+valof('date_start')+'&date_end='+valof('date_end'), loading_area: 'sim_go_bt' }); });
</script>