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
	.column { max-width: 90%; }
</style>

<div class="ui inverted middle aligned center aligned grid segment container">
    <div class="column">
        <h2 class="ui teal image header">
            <div class="content">Stratégie</div>
        </h2>
        <form class="ui inverted large form">
            <div class="ui inverted stacked segment">

                <div class="inverted field">
                    <div class="ui inverted labeled input">
                        <div class="ui label">Nom</div><input type="text" id="s_name" value="<?= $row['title'] ?>" placeholder="0">
                    </div>
                </div>

                <div class="inverted field">
                    <div class="ui inverted labeled input">
                        <div class="ui label">Methode</div><input type="text" id="s_name" value="<?= $row['title'] ?>" placeholder="0">
                    </div>
                </div>

                <div class="inverted field">
                    <div class="ui inverted right labeled input">
                        <input placeholder="Methode" type="text">
                        <div class="ui inverted dropdown label">
                            <div class="text">Dropdown</div>
                            <i class="inverted dropdown icon"></i>
                            <div class="menu">
                                <div class="item">Choice 1</div>
                                <div class="item">Choice 2</div>
                                <div class="item">Choice 3</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inverted field">
                    <div class="ui inverted labeled input">
                        <div class="ui label">Data</div><input type="text" id="s_data" value="<?= $row['data'] ?>" placeholder="0">
                    </div>
                </div>

<?

$req3 = "SELECT * FROM stock";
$res3 = dbc::execSql($req3);
while($row3 = mysqli_fetch_array($res3)) {
    echo $row3['name']."<br />";
}

?>

				<div class="ui fluid buttons">
                    <div id="strategie_cancel_bt" class="ui grey submit button">Cancel</div>
                    <div class="ui teal submit button">Insert</div>
                </div>

            </div>
            <div class="ui error message"></div>
        </form>
    </div>
</div>

<script>
	Dom.addListener(Dom.id('strategie_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'home_content.php', loading_area: 'strategie_cancel_bt' }); });
</script>