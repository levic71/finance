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
    .input input  { text-align: right !important; background: rgba(75, 192, 192, 0.1) !important; color: rgb(75, 192, 192) !important; }
    .field label { margin-top: 10px !important; color: rgba(255, 255, 255, 0.75) !important; }
    .input .label { width: 50px; background: rgb(75, 192, 192) !important; }
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
                <canvas id="rentier_chart" height="150"></canvas>
            </div>
        </div>
    </div>

</div>


<div class="ui container inverted segment">
    <h2><i class="inverted black help circle icon"></i>&nbsp;&nbsp;Conseils d'utilisation et hypothèses<button id="faq_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chevron down icon"></i></button></h2>

    <div id="faq_view">
<p>
L'objectif de cet outil est de définir approximativement le nombre d'années nécessaire pour devenir financièrement indépendant en étant rentier en investissant la même somme tous les mois.
<br />
<br />
C'est une manière de calculer le montant à cumuler pour pouvoir partir en retraire et de pouvoir couvrir ses dépenses avec les intérets cumulés générés par cet investissement.
<br />
<br />
L'outil est intéractif, il suffit de renseigner les données à gauche et les calculs + graphe sont automatiquement mis à jour.
<br />
<br />
<b>Quelques hypothèses et remarques :</b>
<ul>
    <li>Les revenus à renseigner sont après impôts</li>
    <li>Ne prend pas en compte l'inflation</li>
    <li>Augmentation revenu couvre l'inflation dans le temps</li>
    <li>L'hypothèse de rente permet de couvrir les depenses mensuelles</li>
    <li>Le taux de rente ne doit pas etre inférieur au taux réel, sinon le capital cumulé sera imputé par les dépenses</li>
</ul>
<br />
<br />

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

el("rentier_chart").height = document.body.offsetWidth > 700 ? 150 : 150;

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

    Dom.attribute(Dom.id('f_epargne'), { value: f_epargne.toFixed(0) });
    Dom.attribute(Dom.id('f_rentier_montant'), { value: f_rentier_montant.toFixed(0) });

    var rentier_dans = 0;
    var sim_valo = 0;
    for(i = 12; i <=  12 * 60; i++) {
		sim_valo += f_epargne;
        if ((i % 12) == 0) {
            actifs_labels.push(Math.floor(i / 12));
            actifs_data.push(sim_valo);
        }
        if (sim_valo >= f_rentier_montant && rentier_dans == 0) rentier_dans = i;
		sim_valo += (sim_valo * (f_taux_reel / 12) ) / 100;
    }

    f_rentier_dans = Math.round(rentier_dans / 12);
    Dom.attribute(Dom.id('f_rentier_dans'), { value: f_rentier_dans.toFixed(0) });

    if (actifs_data.length == 0) {
		actifs_data.push(100);
		actifs_labels.push('N/A');
	} else {
        actifs_data   = actifs_data.slice(0,   f_rentier_dans + 10);
        actifs_labels = actifs_labels.slice(0, f_rentier_dans + 10);
        sim_valo = actifs_data[actifs_data.length - 1];
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
                },
                ticks: {
                    callback: function(value, index, values) {
                        var c = value+((value == 0 || value == 1) ? " an" : " ans");
                        return c;
                    }
                },
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
			borderWidth: 0.5
		}]
	};

	if (myChart) myChart.destroy();
	myChart = new Chart(ctx, { type: 'line', data: data, options: options, plugins: [horizontalLines] } );
	mychart.update();

}

// Declencheur sur changement dans un des champs de saisie
Dom.find('#rentier_calc_form input').forEach(function(item) {
    Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) { compute(); });
});

compute();

</script>