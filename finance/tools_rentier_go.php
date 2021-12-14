<?

require_once "sess_context.php";

session_start();

include "common.php";

$f_revenus    = 2500;
$f_depenses   = 2000;
$f_taux_rente = 4;
$f_taux_reel  = 5;

foreach(['f_revenus', 'f_depenses'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

?>

<style>
    .input input  { text-align: right !important; }
    .input .label { width: 50px; }
    .input .label, .input input { padding: 5px 15px !important; }
</style>

<div id="rentier_calc_form" class="ui container inverted segment form">
    
    <h2 class="ui inverted dividing header"><i class="inverted black dollar sign icon"></i> Libre financièrement en étant rentier</h2>
    <div class="fields">
        <div class="three wide field">
            <div class="field">
                <label>Revenus mensuels</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_revenus" value="" placeholder="0">
                    <div class="ui basic label">&euro;</div>
                </div>
            </div>
            <div class="field">
                <label>Depenses mensuelles</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_depenses" value="" placeholder="0">
                    <div class="ui basic label">&euro;</div>
                </div>
            </div>
            <div class="field">
                <label>Epargne mensuelle</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_epargne" value="" placeholder="0">
                    <div class="ui basic label">&euro;</div>
                </div>
            </div>
            <div class="field">
                <label>Hypothèse de rente annuelle</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_taux_rente" value="" placeholder="0">
                    <div class="ui basic label">%</div>
                </div>
            </div>
            <div class="field">
                <label>Montant pour devenir rentier</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_rentier_montant" value="" placeholder="0">
                    <div class="ui basic label">&euro;</div>
                </div>
            </div>
            <div class="field">
                <label>Rentier dans</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_rentier_dans" value="" placeholder="0">
                    <div class="ui basic label">ans</div>
                </div>
            </div>
            <div class="field">
                <label>Taux de rendement réel</label>
                <div class="ui right labeled input">
                    <input type="text" id="f_taux_reel" value="" placeholder="0">
                    <div class="ui basic label">%</div>
                </div>
            </div>
        </div>
        <div class="thirteen wide field">
            <div class="field">
                <canvas id="rentier_chart" height="120"></canvas>
            </div>
            <div class="field">
                <button id="rentier_go_bt" class="ui pink right floated button">Compute</button>
            </div>
        </div>
    </div>

</div>


<div class="ui container inverted segment">
    <h2><i class="inverted black help circle icon"></i>&nbsp;&nbsp;Comment utiliser cet outil <button id="faq_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chevron down icon"></i></button></h2>

    <div id="faq_view">
<p>
Après avoir choisit soit la stratégie, soit le nombre d'actifs souhaités dans le portefeuille, le tableau se remplit automatiquement avec les valeurs par défaut.
<br />
<br />
La modification d'une des données par l'utilisateur déclenche le recalcul automatique du tableau. Le bouton "Rééquilibrage" fait de même.
<br />
<br />
<b>Liste des données modifiables :</b>
<ul>
    <li>Choix des actifs sélectionnés : La modification de la valeur d'un des actifs, modifie sa cotation et relance le réquilibrage</li>
    <li>La cotation et la répartition de chaque actif peuvent être ajustées manuellement et leurs prises en compte est immédiament intégré au nouveau calcul</li>
    <li>La colonne Nb dans la section portefeuille permet de saisir le nombre d'actifs déjà en possession</li>
    <li>Le montant à investir permet de déterminer le nombre d'actifs à acheter pour retourner à l'équilibre</li>
</ul>
<b>Quelques remarques :</b>
<ul>
    <li>L'option "Portefeuille actif" permet de saisir le nombre d'actifs déjà en possession pour l'intégrer dans le calcul du rééquilibrage</li>
    <li>Un déséquilibre trop important dans son portefeuille actuel peut amener à un calcul qui conduit à vendre des actifs (nombre négatif dans la colonne Nb de Achat/Vente)</li>
</ul>

<?= uimx::staticInfoMsg("L'OBJECTIF DE CES OUTILS EST PEDAGOGIQUE - ILS N'ONT PAS VOCATION A INCITER A ACHETER OU VENDRE DES ACTIFS", "alarm", "red"); ?>

</p>
    </div>

</div>


<script>

var f_revenus    = <?= $f_revenus ?>;
var f_depenses   = <?= $f_depenses ?>;
var f_epargne    = f_revenus - f_depenses;
var f_taux_rente = <?= $f_taux_rente ?>;
var f_taux_reel  = <?= $f_taux_reel ?>;
var f_rentier_montant = 0;
var f_rentier_dans    = 0;
var f_taux_reel       = 5;

// Initialisation champs
Dom.attribute(Dom.id('f_revenus'),    { value: f_revenus } );
Dom.attribute(Dom.id('f_depenses'),   { value: f_depenses } );
Dom.attribute(Dom.id('f_epargne'),    { value: f_epargne } );
Dom.attribute(Dom.id('f_taux_rente'), { value: f_taux_rente } );
Dom.attribute(Dom.id('f_taux_reel'),  { value: f_taux_reel } );

var actifs_labels = [];
var actifs_data = [];

var myChart;
var ctx = document.getElementById('rentier_chart').getContext('2d');

el("rentier_chart").height = document.body.offsetWidth > 700 ? 120 : 120;

var options = {
    responsive: false,
    maintainAspectRatio: true,
	plugins: {
            legend: {
                display: true,
				position: 'right'
            }
        },
	};
    
hide('faq_view');
Dom.addListener(Dom.id('faq_eye_bt'), Dom.Event.ON_CLICK, function(event) { toogle('faq_view'); });

compute = function() {

    actifs_data   = [];
    actifs_labels = [];
    f_revenus     = valof('f_revenus');
    f_depenses    = valof('f_depenses');
    f_taux_rente  = valof('f_taux_rente');
    f_taux_reel   = valof('f_taux_reel');

    f_epargne         = f_revenus - f_depenses;
    f_rentier_montant = (f_depenses * 12 * 100) / f_taux_rente;
    f_rentier_dans    = f_rentier_montant / (f_epargne * 12);

    Dom.attribute(Dom.id('f_epargne'), { value: f_epargne.toFixed(0) })
    Dom.attribute(Dom.id('f_rentier_montant'), { value: f_rentier_montant.toFixed(0) })
    Dom.attribute(Dom.id('f_rentier_dans'), { value: f_rentier_dans.toFixed(1) })

    var sim_valo = f_epargne;
    for(i = 1; i <= 50; i++) {
		actifs_labels.push(i);
		actifs_data.push(sim_valo);
		sim_valo += sim_valo * ((1 + f_taux_reel) / 100);
    }

    if (actifs_data.length == 0) {
		actifs_data.push(100);
		actifs_labels.push('N/A');
	}

    var stepSize = (sim_valo / 6).toFixed(0);
    stepSize = stepSize > 100000 ? 100000 : (stepSize > 50000 ? 50000 : (stepSize > 25000 ? 25000 : (stepSize > 10000 ? 10000 : 5000)));

    var ratio = sim_valo > f_rentier_montant ? getRatio(sim_valo, f_rentier_montant) : 100;

    const horizontalLines = {
        id: 'horizontalLines',
        beforeDraw(chart, args, options) {
            const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
            ctx.save();
            ctx.strokeStyle = 'rgba(255, 215, 0, 0.5)';
            // Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
            ctx.beginPath();
            ctx.setLineDash([3, 3]);
            h = (height * (1 - (ratio / 100))) + top;
            ctx.moveTo(left, h);
            ctx.lineTo(right, h);
            ctx.stroke();
            ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.restore();
        }
    };

    var options = {
        interaction: {
            intersect: false
        },
        radius: 1,
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            x: {
                grid: {
                    display: true
                }
            },
            y1: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)'
                },
                beginAtZero: true,
                ticks: {
                    stepSize: stepSize,
                    display: true,
                    align: 'end',
                    callback: function(value, index, values) {
                        var c = value+" \u20ac";
                        return c;
                    }
                },
                type: 'linear',
                position: 'left'
            }
        }
    };
    
    const data = {
		labels: actifs_labels,
		datasets: [{
			label: 'Rendement taux réel',
			data: actifs_data,
            borderColor: 'rgb(75, 192, 192)',
			borderWidth: 8
		}]
	};

	if (myChart) myChart.destroy();
	myChart = new Chart(ctx, { type: 'line', data: data, options: options, plugins: [horizontalLines] } );
	mychart.update();

}

// Declencheur sur bouton reequilibrage
Dom.addListener(Dom.id('rentier_go_bt'), Dom.Event.ON_CLICK, function(event) { compute(); });

// Declencheur sur changement dans un des champs de saisie
Dom.find('#rentier_calc_form input').forEach(function(item) {
    Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) { compute(); });
});

compute();

</script>