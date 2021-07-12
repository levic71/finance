<?

include_once "include.php";
$search = isset($_GET["search"]) ? $_GET["search"] : "";

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="z-ua-compatible" content="ie=edge">
    <title>Market Data</title>
    <script type="text/javascript" src="js/scripts.js"></script>
</head>

<body>

    <div class="container">

        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    Search Quote
                </div>
            </div>
        </nav>

        <div class="row">
            <div class="col-lg-12 table-responsive">
                <form action="search.php" method="get">
                    <input type="text" name="search" value="<?= isset($search) ? $search : $search ?>" />
                    <input type="submit" />
                </form>

                <pre><table>
<?
if (isset($search) && $search != "") {

    try {

        $data = aafinance::searchSymbol($search);

        if (isset($data["bestMatches"])) {
            foreach ($data["bestMatches"] as $key => $val) {
                echo "<tr><td>" . $val["1. symbol"] . "</td><td>" . $val["2. name"] . "</td><td>" . $val["3. type"] . "</td><td>" . $val["4. region"] . "</td><td>" . $val["5. marketOpen"] . "</td><td>" . $val["6. marketClose"] . "</td><td>" . $val["7. timezone"] . "</td><td>" . $val["8. currency"] . "</td><td><button onclick=\"addStock('" . $val["1. symbol"] . "', '" . addslashes($val["2. name"]) . "', '" . $val["3. type"] . "', '" . $val["4. region"] . "', '" . $val["5. marketOpen"] . "', '" . $val["6. marketClose"] . "', '" . urlencode($val["7. timezone"]). "', '" . $val["8. currency"] . "');\">add</button></td></tr>";
            }
        }
    } catch (RuntimeException $e) {
        if ($e->getCode() == 1) logger::error("CRON", $row['symbole'], $e->getMessage());
        if ($e->getCode() == 2) logger::info("CRON", $row['symbole'], $e->getMessage());
    }
}
?>
            </table></pre>
            
            <button onclick="window.location='index.php?admin=1'">back</button>

            </div>
        </div>
    </div>

</body>

</html>