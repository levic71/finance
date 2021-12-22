<?

require_once "sess_context.php";

session_start();

include "common.php";

$db = dbc::connect();

$tab_strategies = array();

// On récupère les portefeuilles de l'utilisateur
$lst_portfolios = array();
$req = "SELECT * FROM portfolios WHERE synthese=0 AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) $lst_portfolios[] = $row;

// On récupère les strategies de l'utilisateur + defaut
$req = "SELECT * FROM strategies WHERE methode=2 AND (user_id=".$sess_context->getUserId()." OR defaut=1)";
$res = dbc::execSql($req);
while ($row = mysqli_fetch_assoc($res)) {
    $tab_strategies[] = $row;
}

?>

<div class="ui container inverted segment form">

    <h2 class="ui inverted dividing header"><i class="inverted grey balance scale icon"></i> Rebalancing</h2>
    <div class="field">
        <div class="three fields">

            <div class="field">
                <label>Par sélection d'un portefeuille</label>
                <div class="ui action input">
                    <select class="ui fluid search dropdown" id="f_portfolio_id">
                        <?
                            foreach($lst_portfolios as $key => $val)
                                echo '<option value="'.$val['id'].'">'.utf8_decode($val['name']).'</option>';
                        ?>
                    </select>
                    <button id="dca_go_bt0" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
                </div>
            </div>

            <div class="field">
                <label>Par sélection d'une stratégie DCA</label>
                <div class="ui action input">
                    <select class="ui fluid search dropdown" id="f_strategie_id">
                        <?
                            foreach($tab_strategies as $key => $val)
                                echo '<option value="'.$val['id'].'">'.utf8_decode($val['title']).'</option>';
                        ?>
                    </select>
                    <button id="dca_go_bt1" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
                </div>
            </div>

            <div class="field">
                <label>Choix libre par sélection d'actifs</label>
                <div class="ui action input">
                    <select class="ui fluid search dropdown" id="f_nb_actifs">
                        <?
                            foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $key)
                                echo '<option value="'.$key.'">'.$key.'</option>';
                        ?>
                    </select>
                    <button id="dca_go_bt2" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="ui hidden divider"></div>

    <h2 class="ui inverted dividing header"><i class="inverted grey money sign icon"></i> Libre financièrement en étant rentier</h2>
    <div class="field">
        <div class="three fields">

            <div class="field">
                <label>Revenus mensuels</label>
                <div class="ui right labeled input">
                    <input id="f_revenus" type="text" value="2500">
                    <div class="ui basic label">&euro;</div>
                </div>
            </div>

            <div class="field">
                <label>Dépenses mensuelles</label>
                <div class="ui action input">
                    <div class="ui right labeled input">
                        <input id="f_depenses" type="text" value="2000">
                        <div class="ui basic label">&euro;</div>
                    </div>
                    <button id="dca_go_bt3" class="ui pink right icon button"><i class="play inverted icon"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="ui hidden divider"></div>

    <h3>REGLES D'OR D'UN BON INVESTISSEUR</h3>
    <ol>
        <li>On investit que l'argent qu'il reste après avoir couvert ses besoins essentiels et payé ces factures</li>
        <li>On ne devient pas riche en 1 jour, l'investissement est sur le moyen/long terme (10, 20, 30 ans)</li>
        <li>On n'emprunte pas pour investir et on n'investit pas l'argent des autres, économisez et patientez</li>
        <li>Les performances passées ne préjugent pas des performances futures</li>
        <li>Investir dans ce que l'on comprend, il faut se former et s'informer pour bien choisir</li>
        <li>Définir sa stratégie d'investissement et s'y tenir</li>
        <li>Diversifier et investir petit à petit</li>
        <li>Attention aux commisions et aux marges des conseillers</li>
        <li>Réinvestir les dividendes</li>
    </ol>

    <h3>ERREUR DEBUTANT BOURSE</h3>
    <ol>
        <li>Ne jamais moyenner à la baisse (Loosers average losers)</li>
        <li>Manque de diversification ou positions disproportionnées</li>
        <li>Ne jamais suivre les recommandations externes</li>
        <li>Ne pas acheter les actifs surévaluer</li>
        <li>Ne pas investir dans des entreprises/actifs en perte</li>
    </ol>

    <div class="ui hidden divider"></div>

    <?= uimx::staticInfoMsg("L'OBJECTIF DE CES OUTILS EST PEDAGOGIQUE - ILS N'ONT PAS VOCATION A INCITER A ACHETER OU VENDRE DES ACTIFS", "alarm", "red"); ?>

</div>



<script>

    launcher = function(attr) {
		params = attrs([ attr ]);
        go({ action: 'tools', id: 'main', url: 'tools_dca_calculator_go.php?'+params, loading_area: 'main' });
    }
    
    Dom.addListener(Dom.id('dca_go_bt0'), Dom.Event.ON_CLICK, function(event) { launcher('f_portfolio_id'); });
    Dom.addListener(Dom.id('dca_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher('f_strategie_id'); });
    Dom.addListener(Dom.id('dca_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher('f_nb_actifs'); });
    Dom.addListener(Dom.id('dca_go_bt3'), Dom.Event.ON_CLICK, function(event) { params = attrs([ 'f_revenus', 'f_depenses' ]); go({ action: 'tools', id: 'main', url: 'tools_rentier_go.php?'+params, loading_area: 'main' }); });

</script>