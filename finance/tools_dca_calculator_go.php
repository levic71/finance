<?

require_once "sess_context.php";

session_start();

include "common.php";

$f_nb_actifs = 0;

foreach(['f_strategie_id', 'f_nb_actifs'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$strategie_defined = $f_nb_actifs > 0 ? false : true;

$tab_stocks = array();
$lst_symbol_strategie = array();
$lst_symbol_strategie_pct = array();

// On réxupère tous les actifs avec leurs dernieres cotations
$req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol ORDER BY s.symbol";
$res = dbc::execSql($req);

while($row = mysqli_fetch_assoc($res)) {
    $tab_stocks[$row['symbol']] = $row;
}

// Initialistaion des data du formulaire
if ($strategie_defined && $f_strategie_id != "")
{
    $req = "SELECT * FROM strategies WHERE id=".$f_strategie_id;
    $res = dbc::execSql($req);
    if ($row = mysqli_fetch_assoc($res)) {
        $t = json_decode($row['data'], true);
		$i = 1;
		foreach($t['quotes'] as $key => $val) {
			$lst_symbol_strategie[$i] = $key;
			$lst_symbol_strategie_pct[$i++] = $val;
		}

		$f_nb_actifs = count($lst_symbol_strategie);
    }
    else {
        echo "Stratégie inexistante !!!";
        exit(0);
    }
} else {
    $first_symbol = array_keys($tab_stocks)[0];
    $sum = 0;
    for($i=1 ; $i <= $f_nb_actifs; $i++) {
        $lst_symbol_strategie[$i] = $first_symbol;
        $pct = floor(100 / $f_nb_actifs);
        $sum += $pct;
        $lst_symbol_strategie_pct[$i] = $i == $f_nb_actifs && $i !=1 ? 100-$sum+$pct : $pct;
    }
}

?>

<div id="dca_calc_form" class="ui container inverted segment form">
    
    <div class="ui centered grid">
        <div class="sixteen wide column">
    
            <div class="ui inverted left labeled fluid input">
                <label for="f_pft_actif" class="ui black label">Portefeuille actif</label>
                <div class="ui fitted inverted toggle checkbox" style="padding: 8px 10px;">
                    <input id="f_pft_actif" type="checkbox" />
                    <label></label>
                </div>
            </div>

            <table class="ui compact inverted selectable table">
                <thead>
                <tr>
                        <th class="center aligned" rowspan="2">Actif</th>
                        <th class="center aligned" rowspan="2">Cotation<br />observée</th>
                        <th class="center aligned" rowspan="2">Répartition<br />cible</th>
                        <th class="center aligned" colspan="3" data-toogle="1">En portefeuille</th>
                        <th class="center aligned" colspan="3">Achat/Vente</th>
                        <th class="center aligned" colspan="3" data-toogle="1">Final</th>
                    </tr>
                    <tr>
                        <th class="center aligned" data-toogle="1">Nb</th>
                        <th class="right aligned" data-toogle="1">&euro;</th>
                        <th class="right aligned" data-toogle="1">%</th>
                        <th class="center aligned">Nb</th>
                        <th class="right aligned">&euro;</th>
                        <th class="right aligned">%</th>
                        <th class="center aligned" data-toogle="1">Nb</th>
                        <th class="right aligned" data-toogle="1">&euro;</th>
                        <th class="right aligned" data-toogle="1">%</th>
                    </tr>
                </thead>
                <tbody>
                    <? for($i = 1; $i <= $f_nb_actifs; $i++) { ?>
                    <tr>
                        <td>
                            <select class="ui fluid search dropdown" id="f_lst_actifs_<?= $i ?>">
                                <?
                                    foreach($tab_stocks as $key => $val)
                                        echo '<option value="'.$key.'" '.($key == $lst_symbol_strategie[$i] ? 'SELECTED="SELECTED"' : "").' data-price="'.sprintf("%.2f", $val['price']).'">'.$key.'</option>';
                                ?>
                            </select>
                        </td>
                        <td class="center aligned"><div class="ui right labeled input">
                            <input id="f_price_<?= $i ?>" type="text" size="2" value="<?= sprintf("%.2f", $tab_stocks[$lst_symbol_strategie[$i]]['price']) ?>" />
                            <div class="ui basic label">&euro;</div>
                        </div></td>
                        <td class="center aligned"><div class="ui right labeled input">
                            <input id="f_pct_<?= $i ?>" type="text" size="1" value="<?= $lst_symbol_strategie_pct[$i] ?>" />
                            <div class="ui basic label">%</div>
                        </div></td>
                        <td class="center aligned" data-toogle="1"><div class="ui input">
                            <input id="f_get_<?= $i ?>" type="text" size="3" value="0" />
                        </div></td>
                        <td class="right aligned" data-toogle="1" id="f_valo1_<?= $i ?>">0 &euro;</td>
                        <td class="right aligned" data-toogle="1" id="f_pct1_<?= $i ?>">0 %</td>
                        <td class="center aligned"><div class="ui input">
                            <input id="f_buy_<?= $i ?>" type="text" size="3" value="0" readonly="readonly" />
                        </div></td>
                        <td class="right aligned" id="f_valo2_<?= $i ?>">0 &euro;</td>
                        <td class="right aligned" id="f_pct2_<?= $i ?>">0 %</td>
                        <td class="right aligned" data-toogle="1"><div class="ui input">
                            <input id="f_final_<?= $i ?>" type="text" size="3" value="0" readonly="readonly" />
                        </div></td>
                        <td class="right aligned" data-toogle="1" id="f_valo3_<?= $i ?>">0 &euro;</td>
                        <td class="right aligned" data-toogle="1" id="f_pct3_<?= $i ?>">0 %</td>
                    </tr>
                    <? } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td class="center aligned"></td>
                        <td class="center aligned"><div class="ui right labeled input">
                            <input id="sum_pct" type="text" size="1" value="0" />
                            <div class="ui basic label">%</div>
                        </div></td>
                        <td class="right aligned" id="sum_before" data-toogle="1" colspan="2">0 &euro;</td>
                        <td class="right aligned" id="sum_pct1" data-toogle="1"></td>
                        <td class="right aligned" id="sum_buy" colspan="2">0 &euro;</td>
                        <td class="right aligned" id="sum_pct2"></td>
                        <td class="right aligned" id="sum_final" data-toogle="1" colspan="2">0 &euro;</td>
                        <td class="right aligned" id="sum_pct3" data-toogle="1"></td>
                    </tr>
                </thead>

                </tfoot>
            </table>
        </div>
        <div class="ten wide column" style="padding-left: 25px;">
            <div class="ui right labeled input">
                <label for="f_montant" class="ui inverted label">Montant à investir</label>
                <input type="text" id="f_montant" size="8" value="1000" />
                <div class="ui basic label">&euro;</div>
            </div>
        </div>
        <div class="six wide column">
            <button id="dca_go_bt" class="ui pink left right floated button">Rééquilibrage</button>
        </div>
    </div>
</div>

<div class="ui container inverted segment">
    <h2> Comment utiliser cet outil <button id="faq_eye_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white eye icon"></i></button></h2>

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
    
    hide('faq_view');
    Dom.addListener(Dom.id('faq_eye_bt'), Dom.Event.ON_CLICK, function(event) { toogle('faq_view'); });

    rebalance = function(attr) {

        nb_actifs = <?= $f_nb_actifs ?>;

        Dom.find('#dca_calc_form td:nth-child(3) div input').forEach(function(item) {
            Dom.css(item, {'background': 'rgba(255, 255, 255, 1)', 'color': 'black'});
        });
        Dom.find('#dca_calc_form td:nth-child(3) div .label').forEach(function(item) {
            Dom.css(item, {'background': 'rgba(255, 255, 255, 1)', 'color': 'black'});
        });

        // Valeurs des actifs en possession + Total
        sum_before = 0;
        for(i=1; i <= nb_actifs; i++) {
            v = parseInt(valof('f_get_'+i)) * parseInt(valof('f_price_'+i));
            el('f_valo1_'+i).innerHTML = v + ' &euro;';
            sum_before += v;
        }
        el('sum_before').innerHTML = sum_before + ' &euro;';

        // Calcul repartition actuelle
        for(i=1; i <= nb_actifs; i++) {
            v = sum_before == 0 ? 0 : Math.floor((parseInt(el('f_valo1_'+i).innerHTML.replace(' &euro; ', '')) * 100) / sum_before);
            el('f_pct1_'+i).innerHTML = v + ' %';
        }
        v = sum_before == 0 ? 0 : Math.floor((sum_before * 100)/ sum_before);
        // el('sum_pct1').innerHTML = v + ' %';

        // Totaux des repartitions souhaitees
        sum_pct = 0;
        Dom.find('#dca_calc_form tbody td:nth-child(3) div input').forEach(function(item) {
            sum_pct += parseInt(item.value);
        });
        el('sum_pct').value = sum_pct;

        // Controle somme des repartitions
        sum = 0;
        for(i=1; i <= nb_actifs; i++) {
            ret = check_num(valof('f_pct_'+i), 'de la colonne répartition', 0, 1000);
            if (!ret) return false;
            sum += parseInt(valof('f_pct_'+i));
        }

        // Controle sur la colonne repartition
        if (sum != 100) {
            Dom.find('#dca_calc_form td:nth-child(3) div input').forEach(function(item) {
                Dom.css(item, {'background': 'rgba(255, 255, 255, 0.8)', 'color': 'red'});
            });
            Dom.find('#dca_calc_form td:nth-child(3) div .label').forEach(function(item) {
                Dom.css(item, {'background': 'rgba(255, 255, 255, 0.8)', 'color': 'red'});
            });
            return false;
        }

        // Controle si champ numerique
        for(i=1; i <= nb_actifs; i++) {
            ret = check_num(valof('f_price_'+i), 'de la colonne valeur', 0, 10000);
            if (!ret) return false;
        }

        // Controle si champ numerique
        for(i=1; i <= nb_actifs; i++) {
            ret = check_num(valof('f_get_'+i), 'de la colonne valeur', 0, 10000);
            if (!ret) return false;
        }

        // Controle si champ numerique
        if (!check_num(valof('f_montant'), 'montant à investir', 0, 10000000))
            return false;

        invest_init = parseInt(valof('f_montant'));
        invest = invest_init;

        for(i=1; i <= nb_actifs; i++) {
            invest += parseInt(valof('f_price_'+i)) * parseInt(valof('f_get_'+i));
        }

        // On recherche si tous les actifs sont elligibles - On retire les actifs en surpoids
        actifs_elligibles = new Array();
        actifs_inelligibles = new Array();
    
        for(i=1; i <= nb_actifs; i++) {

            budget = (invest * parseInt(valof('f_pct_'+i))) / 100;
            nb_actif = budget/parseInt(valof('f_price_'+i));
            value = Math.floor(nb_actif) - parseInt(valof('f_get_'+i));
            el('f_buy_'+i).value = 0;

            if (true || value > 0)
                actifs_elligibles.push(i);
            else
                actifs_inelligibles.push(i);
        }
        
        retrait = 0;
        actifs_inelligibles.forEach(function(item) {
            retrait += (invest * parseInt(valof('f_pct_'+item))) / 100;
        });
        invest -= retrait;

        // Calcul du nb d'actifs a acheter
        actifs_elligibles.forEach(function(item) {
            budget = (invest * parseInt(valof('f_pct_'+item))) / 100;
            nb_actif = parseInt(valof('f_price_'+item)) == 0 ? 0 : Math.floor(budget / parseInt(valof('f_price_'+item)));
            el('f_buy_'+item).value = nb_actif - parseInt(valof('f_get_'+item));
        });

        // Valeur des actifs a acheter + Total
        sum_buy = 0;
        for(i=1; i <= nb_actifs; i++) {
            v = parseInt(valof('f_buy_'+i)) * parseInt(valof('f_price_'+i));
            el('f_valo2_'+i).innerHTML = v + ' &euro;';
            sum_buy += v;
        }
        el('sum_buy').innerHTML = sum_buy + ' &euro;';

        // Calcul des % de repartition des actifs a acheter/vendre
        for(i=1; i <= nb_actifs; i++) {
            v = invest_init == 0 ? 0 : Math.floor((parseInt(el('f_valo2_'+i).innerHTML.replace(' &euro; ', '')) * 100) / sum_buy);
            el('f_pct2_'+i).innerHTML = v + ' %';
        }
        v = invest_init == 0 ? 0 : Math.floor((sum_buy * 100)/ sum_buy);
        // el('sum_pct2').innerHTML = v + ' %';

        // Final
        sum_final = 0;
        for(i=1; i <= nb_actifs; i++) {
            x = parseInt(valof('f_buy_'+i)) + parseInt(valof('f_get_'+i));
            v = x * parseInt(valof('f_price_'+i));
            el('f_final_'+i).value = x;
            el('f_valo3_'+i).innerHTML = v + ' &euro;';
            sum_final += v;
        }
        el('sum_final').innerHTML = sum_final + ' &euro;';

        // Calcul des nouveaux % de repartition
        for(i=1; i <= nb_actifs; i++) {
            v = sum_final == 0 ? 0 : Math.floor((parseInt(el('f_valo3_'+i).innerHTML.replace(' &euro; ', '')) * 100) / sum_final);
            el('f_pct3_'+i).innerHTML = v + ' %';
        }
        v = sum_final == 0 ? 0 : Math.floor((sum_final * 100)/ sum_final);
        // el('sum_pct3').innerHTML = v + ' %';

    }
    
    // Declencheur sur bouton reequilibrage
    Dom.addListener(Dom.id('dca_go_bt'), Dom.Event.ON_CLICK, function(event) { rebalance(); });

    switchStateToogle = function(item) {
        t = Dom.attribute(item, 'data-toogle');
        if (t !== null) {
            item.style.display = t == 1 ? 'table-cell' : 'none';
            Dom.attribute(item, { 'data-toogle': t == 1 ? '0' : '1' });
            t = Dom.attribute(item, 'data-toogle');
        }
    }
    // Declencheur affichage portefeuille actif
    Dom.addListener(Dom.id('f_pft_actif'), Dom.Event.ON_CHANGE, function(event) {
        Dom.find('#dca_calc_form table th').forEach(function(item) { switchStateToogle(item); });
        Dom.find('#dca_calc_form table td').forEach(function(item) { switchStateToogle(item); });
    });

    // Declenceheur sur changement de valeur dans une des listes d'actifs
    Dom.find('#dca_calc_form select').forEach(function(item) {
        Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) {
            input_price_id = 'f_price_'+Dom.attribute(item, 'id').split('_')[3];
            new_price = Dom.attribute(item.options[item.selectedIndex], 'data-price');
            el(input_price_id).value = new_price;
            rebalance();
        });
    });
    
    // Declencheur sur changement dans un des champs de saisie
    Dom.find('#dca_calc_form input').forEach(function(item) {
        Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) { rebalance(); });
    });

    rebalance();

</script>