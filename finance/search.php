<?

require_once "sess_context.php";

session_start();

include "common.php";

$search = "";

foreach(['search'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

?>

<div class="ui container inverted segment">
    <div class="ui search">
        <div class="ui icon input">
            <input class="search" id="search" name="search" type="text" placeholder="Quote search ..."  value="<?= isset($search) ? $search : $search ?>" />
            <i class="search icon"></i>
        </div>
        <div class="ui primary small button" id="search_bt">Search</div>
    </div>
</div>

<div class="ui container inverted segment">
<?

if (isset($search) && $search != "") {

    try {
        $data = aafinance::searchSymbol(rawurlencode($search));

        if (isset($data["bestMatches"])) {
            echo "<table class=\"ui inverted very compact single line table\" id=\"lst_search_quote\">";
            $i = 0;
            foreach ($data["bestMatches"] as $key => $val) {
                echo "<tr>
                    <td>" . $val["1. symbol"] . "</td>
                    <td><div class=\"td_name\">" . utf8_decode($val["2. name"]) . "</div></td>
                    <td>" . $val["3. type"] . "</td>
                    <td>" . $val["4. region"] . "</td>
                    <td>" . $val["5. marketOpen"] . "</td>
                    <td>" . $val["6. marketClose"] . "</td>
                    <td>" . $val["7. timezone"] . "</td>
                    <td>" . $val["8. currency"] . "</td>
                    <td><button id=\"search_add_".$i."\" onclick=\"go({ action: 'stock_add', id: 'main', url: 'stock_action.php?action=add&symbol=".$val["1. symbol"]."&name=".urlencode($val["2. name"])."&type=".$val["3. type"]."&region=".$val["4. region"]."&marketopen=".$val["5. marketOpen"]."&marketclose=".$val["6. marketClose"]."&timezone=".urlencode($val["7. timezone"])."&currency=".$val["8. currency"]."', loading_area: 'search_add_".$i."' });\" class=\"ui small button\">Add</button></td>
                </tr>";
                $i++;
            }
            echo "</table>";
        }
    } catch (RuntimeException $e) {
        if ($e->getCode() == 1) logger::error("CRON", $row['symbole'], $e->getMessage());
        if ($e->getCode() == 2) logger::info("CRON", $row['symbole'], $e->getMessage());
    }
}

?>
</div>

<script>
	Dom.addListener(Dom.id('search_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('search') != '') go({ action: 'search', id: 'main', url: 'search.php?search='+valof('search'), loading_area: 'search_bt' }); });
</script>
