<?

include_once "include.php";
$pea = isset($_GET["pea"]) ? $_GET["pea"] : -1;
$admin = isset($_GET["admin"]) && $_GET["admin"] == 1 ? true : false;

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="z-ua-compatible" content="ie=edge">
        <title>Market Data</title>
		<link rel="stylesheet" href="css/style.css?ver=123" />
		<script type="text/javascript" src="js/scripts.js"></script>
		<script type="text/javascript" src="js/sweetalert2.all.min.js"></script>
        <style>
.swal2-html-container ul { list-style-type: none; }
        </style>
    </head>
    <body>

    <div class="container">
        
        <nav class="navbar navbar-default">
          <div class="container">
            <div class="navbar-header">
            	Simulator
                <button onclick="window.location='index.php?admin=1'">back</button>
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">
<?

$db = dbc::connect();

$a = "2019-02-01";
$b = date("Y-m-d");
$lst = ["ESE.PAR", "BRE.PAR", "PUST.PAR", "OBLI.PAR"];
$capital = 0;
$invest = 1000; // montant investit par mois
$nb_mois = 0;
$actifs_achetees_nb = 0;
$actifs_achetees_pu = 0;
$actifs_achetees_symbol = "";

echo "<h2>Simulation DM RP PEA<br />[".implode('-', $lst)."]<br />Du ".$a." au ".$b."</h2>";

?>

<pre><table id="lst_sim" class="table table-hover table-striped">
    <tr>
        <th>Date</th>
        <th>Cash</th>
        <th>Vente</th>
        <th>Nb</th>
        <th>PU</th>
        <th>Perf</th>
        <th>Achat</th>
        <th>Nb</th>
        <th>PU</th>
        <th>Valorisation</th>
        <th>Perf</th>
        <th>Info</th>
    </tr>
<?

$i = date("Ym", strtotime($a));
while($i <= date("Ym", strtotime($b))) {

    // On investit !!!
    $capital += $invest;

    // Recuperation du dernier jour du mois 
    $day = date("Y-m-t", strtotime(substr($i, 0, 4)."-".substr($i, 4, 2)."-01"));

    // Calcul du DM sur les valeurs selectionnees
    $data = calc::getDualMomentum("'".implode("', '", $lst)."'", $day);

    // Tri par performance decroissante en gardant l'index dui contient le symbol
    arsort($data["perfs"]);

    // Recuperation de l'actif le plus performant
    $best_quote = array_keys($data["perfs"])[0];

    echo "<tr><td>".$data["stocks"][$best_quote]["ref_day"]."</td><td>".round($capital, 2)."</td>";

    // Vente anciens actifs si different du nouveau plus performant
    if ($actifs_achetees_nb > 0 && $actifs_achetees_symbol != $best_quote) {

        $pu = floatval(calc::getDailyHistoryQuote($actifs_achetees_symbol, $data["stocks"][$best_quote]["ref_day"]));
        $capital += $actifs_achetees_nb * $pu;

        $perf = round(($pu - $actifs_achetees_pu)*100/$actifs_achetees_pu, 2);

        echo "<td>".$actifs_achetees_symbol."</td><td>".$actifs_achetees_nb."</td><td>".round($pu, 2)."</td><td>".$perf."</td>";

        $actifs_achetees_nb = 0;
    }
    else {
        echo "<td>-</td><td>-</td><td>-</td><td>-</td>";
    }

    // Achat nouveaux actifs
    if ($capital > 0) {

        $actifs_achetees_pu = $data["stocks"][$best_quote]["ref_close"];

        // achat nouveaux actifs
        $x = floor($capital / $actifs_achetees_pu);
        $actifs_achetees_nb = ($actifs_achetees_symbol == $best_quote) ? $actifs_achetees_nb + $x : $x;
        $capital -= $x * $actifs_achetees_pu;
        $actifs_achetees_symbol = $best_quote;

        echo "<td>".$actifs_achetees_symbol."</td><td>".$x."</td><td>".round($actifs_achetees_pu, 2)."</td>";
    }


    $info_title =  "[".$data["stocks"][$best_quote]["ref_day"]."] => ".$best_quote;

    $info_content = "<ul>";
    foreach($data["perfs"] as $key => $val) {
        $info_content .= "<li>".$key." : ".$val."</li>";
    }
    $info_content .= "</ul>";

    if(substr($i, 4, 2) == "12")
        $i = (date("Y", strtotime($i."01")) + 1)."01";
    else
        $i++;

    $nb_mois++;

    $valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
    $perf = round(($valo - ($invest * $nb_mois))*100/($invest * $nb_mois), 2);
    echo "<td>".$valo."</td><td>".$perf."%</td>";
    echo "<td><span class=\"icon info\" onclick=\"	Swal.fire({ title: '".$info_title."', icon: 'info', html: '".$info_content."' });\">An Icon</span></td>";

    echo "</tr>";
}

?></table></pre>

<?

    $valo = round($capital+($actifs_achetees_nb * $actifs_achetees_pu), 2);
    $perf = round(($valo - ($invest * $nb_mois))*100/($invest * $nb_mois), 2);
    echo "Valorisation portefeuille = ".$valo."&euro;<br />";
    echo "Capital investit = ".$nb_mois*$invest."&euro;<br />";
    echo "Performance = ".$perf."%<br />";

?>
            </div>
        </div>
    </div>
    
    </body>
</html>