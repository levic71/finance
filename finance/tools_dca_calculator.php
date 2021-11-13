<?

require_once "sess_context.php";

session_start();

include "common.php";

$db = dbc::connect();

$tab_strategies = array();

$req = "SELECT * FROM strategies WHERE methode=2 AND (user_id=".$sess_context->getUserId()." OR defaut=1)";
$res = dbc::execSql($req);
while ($row = mysqli_fetch_assoc($res)) {
    $tab_strategies[] = $row;
}

?>

<div class="ui container inverted segment form">

    <div class="ui icon red message">
        <i class="alarm red inverted icon"></i>
        <div class="content">
            <div class="header">
            L'OBJECTIF DE CES OUTILS EST PEDAGOGIQUE - ILS N'ONT PAS VOCATION A INCITER A ACHETER OU VENDRE DES ACTIFS
            </div>
        </div>
    </div>

    <h2 class="ui inverted dividing header">Calcul Rebalancing</h2>
    <div class="field">
        <div class="two fields">
            <div class="field">
                <label>Par sélection d'une stratégie</label>
                <div class="ui action input">
                    <select class="ui fluid search dropdown" id="f_strategie_id">
                        <?
                            foreach($tab_strategies as $key => $val)
                                echo '<option value="'.$val['id'].'">'.$val['title'].'</option>';
                        ?>
                    </select>
                    <button id="dca_go_bt1" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
                </div>
            </div>
            <div class="field">
                <label>Par sélection d'actifs</label>
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
</div>



<script>

    launcher = function(attr) {
		params = attrs([ attr ]);
        go({ action: 'tools', id: 'main', url: 'tools_dca_calculator_go.php?'+params, loading_area: 'main' });
    }
    
    Dom.addListener(Dom.id('dca_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher('f_strategie_id'); });
    Dom.addListener(Dom.id('dca_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher('f_nb_actifs'); });

</script>