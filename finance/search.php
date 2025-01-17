<?

require_once "sess_context.php";

session_start();

include "common.php";

$search = "";
$engine = "alpha";

foreach(['search', 'engine'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

?>

<div class="ui container inverted segment">
    <div class="ui search">
        <div class="ui icon input">
            <input class="search" id="search" name="search" type="text" placeholder="Alphavantage Quote"  value="<?= isset($search) ? $search : $search ?>" />
            <i class="search icon"></i>
        </div>
        <div class="ui primary small button" id="search_bt">Search</div>
    </div>
</div>

<? if (isset($search) && $search != "") {

    echo "<div class=\"ui container inverted segment\">";

    try {

        $data = aafinance::searchSymbol(rawurlencode($search));
        // $data = json_decode('{ "bestMatches": [ { "1. symbol": "FDX", "2. name": "Fedex Corp", "3. type": "Equity", "4. region": "United States", "5. marketOpen": "09:30", "6. marketClose": "16:30", "7. timezone": "UTC-04", "8. currency": "USD", "9. matchScore": "0.7500" } ] }', true);

        if (count($data["bestMatches"]) > 0) {
            echo "<table class=\"ui inverted very compact single line table\" id=\"lst_search_quote\">";
            $i = 0;
            foreach ($data["bestMatches"] as $key => $val) {
                echo "<tr>
                    <td>" . $val["1. symbol"] . "</td>
                    <td><div class=\"td_name\">" . tools::UTF8_encoding($val["2. name"]) . "</div></td>
                    <td>" . $val["3. type"] . "</td>
                    <td>" . $val["4. region"] . "</td>
                    <td>" . $val["8. currency"] . "</td>
                    <td><button id=\"search_add_".$i."\" onclick=\"go({ action: 'stock_add', id: 'main', url: 'stock_action.php?action=add&symbol=".$val["1. symbol"]."&name=".urlencode($val["2. name"])."&f_type=".$val["3. type"]."&region=".$val["4. region"]."&marketopen=".$val["5. marketOpen"]."&marketclose=".$val["6. marketClose"]."&timezone=".urlencode($val["7. timezone"])."&currency=".$val["8. currency"]."', loading_area: 'search_add_".$i."' });\" class=\"ui small button\">Add</button></td>
                </tr>";
/*                 echo "<tr>
                    <td>" . $val["1. symbol"] . "</td>
                    <td><div class=\"td_name\">" . tools::UTF8_encoding($val["2. name") . "</div></td>
                    <td>" . $val["3. type"] . "</td>
                    <td>" . $val["4. region"] . "</td>
                    <td>" . $val["5. marketOpen"] . "</td>
                    <td>" . $val["6. marketClose"] . "</td>
                    <td>" . $val["7. timezone"] . "</td>
                    <td>" . $val["8. currency"] . "</td>
                    <td><button id=\"search_add_".$i."\" onclick=\"go({ action: 'stock_add', id: 'main', url: 'stock_action.php?action=add&symbol=".$val["1. symbol"]."&name=".urlencode($val["2. name"])."&f_type=".$val["3. type"]."&region=".$val["4. region"]."&marketopen=".$val["5. marketOpen"]."&marketclose=".$val["6. marketClose"]."&timezone=".urlencode($val["7. timezone"])."&currency=".$val["8. currency"]."', loading_area: 'search_add_".$i."' });\" class=\"ui small button\">Add</button></td>
                </tr>";
 */                $i++;
            }
            echo "</table>";
        } else 
            echo "<i class=\"inverted exclamation triangle red icon\"></i>Pas d'actif trouv�";
    } catch (RuntimeException $e) {
        if ($e->getCode() == 1) logger::error("CRON", $row['symbole'], $e->getMessage());
        if ($e->getCode() == 2) logger::info("CRON", $row['symbole'], $e->getMessage());
    }
    echo "</div>";
}
?>

<div class="ui container inverted segment">OU</div>

<div class="ui container inverted segment">
    <div class="ui search">
        <div class="ui icon input">
            <input class="search" id="search2" name="search2" type="text" placeholder="Google Finance Quote"  value="" />
            <i class="search icon"></i>
        </div>
        <div class="ui icon input">
            <select class="ui fluid search dropdown" id="f_search_type">
                <? foreach (uimx::$type_actif as $key => $val) echo '<option value="'.$val.'">'.$val.'</option>'; ?>
            </select>
        </div>
        <div class="ui primary small button" id="search2_bt">Add</div>
    </div>
</div>

<div class="ui container inverted segment">OU</div>

<div class="ui container inverted segment">
    <div class="ui search">
        <div class="ui icon input">
            <select class="ui fluid search dropdown" id="f_emetteur">
                <? foreach(uimx::$invest_emetteur as $key => $val) echo "<option value=\"".$val."\">".$val."</option>"; ?>
            </select>
        </div>
        <div class="ui icon input">
            <input class="search" id="f_ticker" name="f_ticker" type="text" placeholder="Ticker"  value="" />
        </div>
        <div class="ui icon input">
            <select class="ui fluid search dropdown" id="f_callput">
                <? foreach(uimx::$type_turbo as $key => $val) echo "<option value=\"".$val."\">".$val."</option>"; ?>
            </select>
        </div>
        <div class="ui icon input">
            <select class="ui fluid search dropdown" id="f_levier">
                <? foreach(range(1, 10) as $key => $val) echo "<option value=\"".$val."\">X".$val."</option>"; ?>
            </select>
        </div>
        <div class="ui icon input">
            <select id="f_sousjacent" class="ui fluid dropdown">
                <? foreach ($quotes["lst_actifs"] as $key => $val) { $q = $quotes["stocks"][$key]; ?>
                    <option value="<?= $key ?>" data-price="<?= sprintf("%.2f", $q['price']) ?>" data-currency="<?= $q['currency'] ?>"><?= $val ?></option>
                <? } ?>
            </select>
        </div>
        <div class="ui icon input">
            <input class="search" id="f_val_init" name="f_val_init" type="text" placeholder="Initial value"  value="" />
        </div>
        <div class="ui primary small button" id="search3_bt">Add</div>
    </div>
</div>

<script>
	Dom.addListener(Dom.id('search_bt'),  Dom.Event.ON_CLICK, function(event) {
        if (valof('search')  != '')
            go({ action: 'search',    id: 'main', url: 'search.php?engine=alpha&search='+valof('search'), loading_area: 'search_bt' });
    });
	Dom.addListener(Dom.id('search2_bt'), Dom.Event.ON_CLICK, function(event) {
        if (valof('search2') != '')
            go({ action: 'stock_add', id: 'main', url: 'stock_action.php?action=add&engine=google&symbol='+valof('search2')+'&f_search_type='+valof('f_search_type'),  loading_area: 'search2_bt' });
        });
	Dom.addListener(Dom.id('search3_bt'), Dom.Event.ON_CLICK, function(event) {

        if (!check_alphanum(valof('f_ticker'), "Ticker actif", 3))
            return false;

        if (!format_and_check_num('f_val_init', 'Initial price', 0, 999999999999))
		    return false;

        go({ action: 'stock_add', id: 'main', url: 'stock_action.php?action=add&engine=manual&f_ticker='+valof('f_ticker')+'&f_emetteur='+valof('f_emetteur')+'&f_callput='+valof('f_callput')+'&f_levier='+valof('f_levier')+'&f_sousjacent='+valof('f_sousjacent')+'&f_val_init='+valof('f_val_init'), loading_area: 'search3_bt' });
    });
</script>
