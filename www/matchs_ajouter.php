<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";
include "ManagerFXList.php";

$modifier = isset($pkeys_where) && $pkeys_where != "" ? true : false;

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Récupération des informations de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = ($row['id_journee_mere'] == "" || $row['id_journee_mere'] == "0" ? false : true);

// Attention, si journée alias, prendre les infos de la journée mère (équipes)
$real_id_journee = $is_journee_alias ? $row['id_journee_mere'] : $row['id'] ;

// Si pref_saisie = 0 alors la création de la journée a été faites avec choix des joueurs, si = 1 avec choix des équipes
$pref_saisie = $row['pref_saisie'];

if ($is_journee_alias)
{
	$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), $real_id_journee);
	$row2 = $sjs2->getJournee();
	$row['equipes'] = $row2['equipes'];
	$pref_saisie = $row2['pref_saisie'];
}

// Récupération des équipes
$selected_eq1 = "";
$selected_eq2 = "";
$type_matchs = "";
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	// Pour résoudre pb avec la page matchs_tournoi.php
	if (isset($niveau)) $options_type_matchs = $niveau;

	$items = explode('|', $options_type_matchs);
	$type_matchs = $items[0];
	$niveau_type = $items[1];
	$ordre       = isset($items[2]) ? $items[2] : 0;

	// Formatage du champs équipes pour ne prendre que les equipes de poules pour les poules et toutes équipes pour la phase finale
	$equipes = "";
	if ($type_matchs == "P" || $type_matchs == "SP")
	{
		$tmp = explode('|', $row['equipes']);
		$equipes = isset($tmp[$niveau_type-1]) ? $tmp[$niveau_type-1] : "";
	}
	else
	{
		$tmp = str_replace('|', ',', $row['equipes']);
		$items = explode(',', $tmp);
		foreach($items as $item)
			if ($item != "") $equipes .= $equipes == "" ? $item : ",".$item;

		// Sur la phase finale, on cherche à connaitre les équipes par défaut (ex: pour la finale, on prend les 2 vainqueurs des demis)
		if (!$modifier)
		{
			$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $real_id_journee, -1);
			// Choix equipe1
			$row_match = $sms->getMatchByNiveau("F|".($niveau_type*2)."|".(($ordre*2) - 1));
			if ($row_match) $selected_eq1 = StatsJourneeBuilder::kikiGagne($row_match) == 1 ? $row_match['id_equipe1'] : $row_match['id_equipe2'];
			// Choix equipe2
			$row_match = $sms->getMatchByNiveau("F|".($niveau_type*2)."|".($ordre*2));
			if ($row_match) $selected_eq2 = StatsJourneeBuilder::kikiGagne($row_match) == 1 ? $row_match['id_equipe1'] : $row_match['id_equipe2'];
		}
	}
}
else
	$equipes = $row['equipes'];

// Liste des joueurs
$tab_jj = explode(',', $row['joueurs']);

// Liste des équipes possibles
$eq = array();

// Récupération des informations des équipes qui participent à cette journée
if ($pref_saisie == 0) // Journée saisie avec sélection de joueurs
{
	$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($select);
	while($row = mysql_fetch_array($res))
	{
		$items = explode('|', $row['joueurs']);

		// Il faut qu'au moins 2 des joueurs de l'équipe soit dans la liste des joueurs sélectionnés de cette journée
		$nb = 0;
		foreach($items as $j)
			if (ToolBox::findInArray($j, $tab_jj)) $nb++;

		if ($nb > 1) $eq[$row['nom']] = $row;
	}
}
else // Journée saisie avec sélection d'équipes
{
	if ($equipes != "")
	{
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$equipes.")";
		$res = dbc::execSQL($select);
		while($row = mysql_fetch_array($res)) $eq[$row['nom']] = $row;
	}
}

// Tri des equipes
if (count($eq) > 0) ksort($eq);

// Valeur des d'une victoire/défaite pour un match de type tournoi (classement+finale)
$points_victoire = 0;
$points_defaite  = 0;
$match_joue      = 0;
$prolongation    = 0;
$tirs_au_but     = 0;
$tirs1           = "";
$tirs2           = "";

// Récupération des infos si match à modifier
if ($modifier)
{
	$items = explode('=', urldecode($pkeys_where));
	$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $real_id_journee, $items[1]);
	$row_match = $sms->getMatch();
	$selected_eq1 = $row_match['id_equipe1'];
	$selected_eq2 = $row_match['id_equipe2'];
	$match_joue   = $row_match['match_joue'];
	$prolongation = $row_match['prolongation'];
	$tirs_au_but  = $row_match['penaltys'] != ""  && $row_match['penaltys'] != "|" ? 1 : 0;
	if ($row_match['penaltys'] != "" && $row_match['penaltys'] != "|")
	{
		$tmp = explode('|', $row_match['penaltys']);
		$tirs1 = $tmp[0];
		$tirs2 = $tmp[1];
	}

	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		$items = explode('|', $row_match['niveau']);
		$type_matchs = $items[0];
		$niveau_type = $items[1];

		if ($type_matchs == "C" || ($type_matchs == "F" && $niveau_type == "1") || ($type_matchs == "Y" && $niveau_type == "1"))
		{
			$items = explode('|', $row_match['score_points']);
			$points_victoire = $items[0];
			$points_defaite  = $items[1];
		}
	}
}

?>
<SCRIPT>
function validate_and_submit()
{
    if (document.forms[0].equipe1.value == document.forms[0].equipe2.value)
    {
        alert('Vous devez sélectionner 2 équipes différentes ...');
        return false;
    }

	var eq1 = document.forms[0].equipe1.value.split('|');
	var eq2 = document.forms[0].equipe2.value.split('|');

	if (document.forms[0].forfait1.checked &&  document.forms[0].forfait2.checked)
	{
        alert('Les 2 équipes ne peuvent pas être fortaites ensemble ...');
        return false;
	}

<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
	if (eq1[1] == eq2[1] || eq1[1] == eq2[2] || eq1[2] == eq2[1] || eq1[2] == eq2[2])
    {
        alert('Vous devez sélectionner 2 équipes dont les joueurs sont tous différents ...');
        return false;
    }
<? } ?>

<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
	if (document.getElementById('penalty').style.display == "block")
	{
		s_tirs1 = -1;
		nb_sel=document.forms[0].tirs1.length;
	    for(i=1; i < nb_sel; i++)
	    {
	         if (document.forms[0].tirs1.options[i].selected)
		         s_tirs1=document.forms[0].tirs1.options[i].value;
	    }
		if (s_tirs1 == -1)
		{
	        alert('Vous devez au sélectionner au moins une valeur dans les tirs au buts ...');
	        return false;
		}
		s_tirs2 = -1;
		nb_sel=document.forms[0].tirs2.length;
	    for(i=1; i < nb_sel; i++)
	    {
	         if (document.forms[0].tirs2.options[i].selected)
		         s_tirs2=document.forms[0].tirs2.options[i].value;
	    }
		if (s_tirs2 == -1)
		{
	        alert('Vous devez au sélectionner au moins une valeur dans les tirs au buts ...');
	        return false;
		}
		if (s_tirs1 == s_tirs2)
		{
	        alert('Vous devez au sélectionner 2 scores différents ...');
	        return false;
		}
	}
<? } ?>

	document.forms[0].eq1.value = eq1[0];
	document.forms[0].eq2.value = eq2[0];

	return true;
}
function annuler()
{
	document.forms[0].action='<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php?options_type_matchs=".$options_type_matchs : "matchs.php" ?>';

	return true;
}
</SCRIPT>

<FORM ACTION=<?= $modifier ? "matchs_modifier_do.php" : "matchs_ajouter_do.php"?> METHOD=POST>
<? if ($modifier) { ?>
<INPUT TYPE=HIDDEN NAME=id_match VALUE="<?= $row_match['id'] ?>">
<? } ?>
<INPUT TYPE=HIDDEN NAME=eq1 VALUE="">
<INPUT TYPE=HIDDEN NAME=eq2 VALUE="">
<INPUT TYPE=HIDDEN NAME=options_type_matchs VALUE="<?= $options_type_matchs ?>">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$score[0][0] = 0;
$score[0][1] = 0;
$nb_set = $modifier ? $row_match['nbset'] : 1;
if ($modifier)
{
	$sm = new StatMatch($row_match['resultat'], $row_match['nbset']);
	$score = $sm->getScore();

	// Si forfait equipe1 ou equipe2
	if ($score == -1 || $score == -2)
	{
		$nb_set = 1;
		$score[0][0] = 0;
		$score[0][1] = 0;
	}
	if (!isset($score[0][0]) || $score[0][0] == "") $score[0][0] = 0;
	if (!isset($score[0][1]) || $score[0][1] == "") $score[0][1] = 0;
}

function buildSelect($name, $def)
{
	$input  = "<SELECT NAME=".$name.">";
	for($i=0; $i < 100; $i++) $input .= "<OPTION VALUE=$i ".($i == $def ? "SELECTED" : "")."> $i";
	$input .= "</SELECT>";

	return $input;
}

$input1  = buildSelect("score1",  $score[0][0]);
$input2  = buildSelect("score2",  $score[0][1]);
$input3  = buildSelect("score3",  $nb_set >= 2 ? $score[1][0] : 0);
$input4  = buildSelect("score4",  $nb_set >= 2 ? $score[1][1] : 0);
$input5  = buildSelect("score5",  $nb_set >= 3 ? $score[2][0] : 0);
$input6  = buildSelect("score6",  $nb_set >= 3 ? $score[2][1] : 0);
$input7  = buildSelect("score7",  $nb_set >= 4 ? $score[3][0] : 0);
$input8  = buildSelect("score8",  $nb_set >= 4 ? $score[3][1] : 0);
$input9  = buildSelect("score9",  $nb_set == 5 ? $score[4][0] : 0);
$input10 = buildSelect("score10", $nb_set == 5 ? $score[4][1] : 0);


$select1 = "";
reset($eq);
while(list($cle, $valeur) = each($eq))
	$select1 .= "<OPTION VALUE=".$valeur['id']."|".$valeur['joueurs']." ".($valeur['id'] == $selected_eq1 ? "SELECTED" : "")."> ".$cle;

$select2 = "";
reset($eq);
while(list($cle, $valeur) = each($eq))
	$select2 .= "<OPTION VALUE=".$valeur['id']."|".$valeur['joueurs']." ".($valeur['id'] == $selected_eq2 ? "SELECTED" : "")."> ".$cle;

$select1 = "<SELECT NAME=equipe1>".$select1."</SELECT>";
$select2 = "<SELECT NAME=equipe2>".$select2."</SELECT>";


$tab = array();

$tab[] = array("<INPUT TYPE=CHECKBOX NAME=forfait1 VALUE=-1 ".($score == -1 ? "CHECKED" : "").">", $select1, $input1."/".$input2, $select2, "<INPUT TYPE=CHECKBOX NAME=forfait2 VALUE=-2 ".($score == -2 ? "CHECKED" : "").">");
$tab[] = array("", "", "<DIV ID=div3>".$input3."/".$input4."</DIV>", "", "");
$tab[] = array("", "", "<DIV ID=div5>".$input5."/".$input6."</DIV>", "", "");
$tab[] = array("", "", "<DIV ID=div7>".$input7."/".$input8."</DIV>", "", "");
$tab[] = array("", "", "<DIV ID=div9>".$input9."/".$input10."</DIV>", "", "");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
if ($type_matchs == "C")
{
	$post = $niveau_type == -1 ? " d'un match de barrage" : " du match pour la ".$niveau_type."ième place";
	$lib  = ($modifier ? "Modification" : "Ajout").$post." <SELECT NAME=nbset onChange=\"javascript:change_state();\"><OPTION value=1 ".($nb_set == 1 ? "SELECTED" : "")."> 1 Set<OPTION VALUE=2 ".($nb_set == 2 ? "SELECTED" : "").">2 Sets<OPTION VALUE=3 ".($nb_set == 3 ? "SELECTED" : "").">3 Sets<OPTION VALUE=4 ".($nb_set == 4 ? "SELECTED" : "").">4 Sets<OPTION VALUE=5 ".($nb_set == 5 ? "SELECTED" : "").">5 Sets</SELECT>";
}
else
	$lib = ($modifier ? "Modification" : "Ajout")." d'un match <SELECT NAME=nbset onChange=\"javascript:change_state();\"><OPTION value=1 ".($nb_set == 1 ? "SELECTED" : "")."> 1 Set<OPTION VALUE=2 ".($nb_set == 2 ? "SELECTED" : "").">2 Sets<OPTION VALUE=3 ".($nb_set == 3 ? "SELECTED" : "").">3 Sets<OPTION VALUE=4 ".($nb_set == 4 ? "SELECTED" : "").">4 Sets<OPTION VALUE=5 ".($nb_set == 5 ? "SELECTED" : "").">5 Sets</SELECT>";
$fxlist->FXSetTitle($lib, "CENTER");
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	$fxlist->FXSetColumnsName(array("Forfait", "Equipe 1", "Score", "Equipe 2", "Forfait"));
else
	$fxlist->FXSetColumnsName(array("Forfait", "Equipe 1<BR>(Défenseur/Attaquant)", "Score", "Equipe 2<BR>(Défenseur/Attaquant)", "Forfait"));
$fxlist->FXSetColumnsAlign(array("CENTER", "CENTER", "CENTER"));
$fxlist->FXSetColumnsColor(array("", "", "#BCC5EA", "", ""));
$fxlist->FXSetColumnsWidth(array("5%", "", "15%", "", "5%"));
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ )
{
	if ($type_matchs == "C" || ($type_matchs == "F" && $niveau_type == "1") || ($type_matchs == "Y" && $niveau_type == "1"))
	{
		$select_victoire = "<SELECT NAME=points_victoire>";
		for($i=-50; $i < 51; $i++) $select_victoire .= "<OPTION VALUE=".$i." ".($points_victoire == $i ? "SELECTED" : "")."> ".$i;
		$select_victoire .= "</SELECT>";
		$select_defaite = "<SELECT NAME=points_defaite>";
		for($i=-50; $i < 51; $i++) $select_defaite .= "<OPTION VALUE=".$i." ".($points_defaite == $i ? "SELECTED" : "")."> ".$i;
		$select_defaite .= "</SELECT>";
		$fxlist->FXSetFooter("<TABLE BORDER=0><TR><TD>Nb points Victoire => </TD><TD>".$select_victoire."</TD><TD>".$select_defaite."</TD><TD> <= Nb points Défaite </TD></TABLE>");
	}
	else
	{
		echo "<INPUT TYPE=HIDDEN NAME=points_victoire VALUE=\"0\">";
		echo "<INPUT TYPE=HIDDEN NAME=points_defaite  VALUE=\"0\">";
	}
}
$fxlist->FXDisplay();
echo "</TD>";

?>

<SCRIPT>
<? if ($nb_set == 1) { ?>
document.getElementById('div3').style.visibility='hidden';
document.getElementById('div5').style.visibility='hidden';
document.getElementById('div7').style.visibility='hidden';
document.getElementById('div9').style.visibility='hidden';
<? } ?>
<? if ($nb_set == 2) { ?>
document.getElementById('div5').style.visibility='hidden';
document.getElementById('div7').style.visibility='hidden';
document.getElementById('div9').style.visibility='hidden';
<? } ?>
<? if ($nb_set == 3) { ?>
document.getElementById('div7').style.visibility='hidden';
document.getElementById('div9').style.visibility='hidden';
<? } ?>
<? if ($nb_set == 4) { ?>
document.getElementById('div9').style.visibility='hidden';
<? } ?>
function change_state()
{
	if (document.forms[0].nbset.value == 1)
	{
		document.getElementById('div3').style.visibility='hidden';
		document.getElementById('div5').style.visibility='hidden';
		document.getElementById('div7').style.visibility='hidden';
		document.getElementById('div9').style.visibility='hidden';
	}
	else if (document.forms[0].nbset.value == 2)
	{
		document.getElementById('div3').style.visibility='visible';
		document.getElementById('div5').style.visibility='hidden';
		document.getElementById('div7').style.visibility='hidden';
		document.getElementById('div9').style.visibility='hidden';
	}
	else if (document.forms[0].nbset.value == 3)
	{
		document.getElementById('div3').style.visibility='visible';
		document.getElementById('div5').style.visibility='visible';
		document.getElementById('div7').style.visibility='hidden';
		document.getElementById('div9').style.visibility='hidden';
	}
	else if (document.forms[0].nbset.value == 4)
	{
		document.getElementById('div3').style.visibility='visible';
		document.getElementById('div5').style.visibility='visible';
		document.getElementById('div7').style.visibility='visible';
		document.getElementById('div9').style.visibility='hidden';
	}
	else
	{
		document.getElementById('div3').style.visibility='visible';
		document.getElementById('div5').style.visibility='visible';
		document.getElementById('div7').style.visibility='visible';
		document.getElementById('div9').style.visibility='visible';
	}
}
function tirsaubut(value)
{
/*
	if (value)
		document.getElementById('penalty').style.display = "block";
	else
		document.getElementById('penalty').style.display = "none";
*/
}
</SCRIPT>

<? if (1 == 1 || $sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
<tr><td align=left><input type="checkbox" name="match_joue" <?= $match_joue == 1 ? "checked=\"checked\"" : "" ?> /> Match joué ? (A cocher uniquement sur les matchs joués qui se sont terminés par 0-0)</td>
<tr><td align=left><input type="checkbox" name="prolongation" <?= $prolongation == 1 ? "checked=\"checked\"" : "" ?> /> Prolongation ? (A cocher uniquement s'il y a eu une prolongation dans le match)</td>
<tr><td align=left><input type="checkbox" name="tirs_au_but" onchange="javascript:tirsaubut(this.checked);" <?= $tirs_au_but == 1 ? "checked=\"checked\"" : "" ?> /> Tirs au but ? (A cocher uniquement s'il y a eu une scéance de tirs au but dans le match)</td>
<tr><td align=left><div id=penalty style="margin: 0px 0px 0px 25px;<?= $tirs_au_but == 1 ? "" : "display:nooooooone;" ?>">
	<select name="tirs1">
		<option value=""></option>
		<? for($i = 0; $i < 24; $i++) echo "<option value=\"".$i."\" ".($tirs1 != "" && $tirs1 == $i ? "selected=\"selected\"" : "").">".$i."</option>"; ?>
	</select>
	/
	<select name="tirs2">
		<option value=""></option>
		<? for($i = 0; $i < 24; $i++) echo "<option value=\"".$i."\" ".($tirs2 != "" && $tirs2 == $i ? "selected=\"selected\"" : "").">".$i."</option>"; ?>
	</select>
</div></td>
<? } else { ?>
<input type="hidden" name="match_joue"   value="<?= $match_joue ?>" />
<input type="hidden" name="prolongation" value="<?= $prolongation ?>" />
<input type="hidden" name="tirs_au_but"  value="<?= $tirs_au_but ?>" />
<input type="hidden" name="tirs1"        value="<?= $tirs1 ?>" />
<input type="hidden" name="tirs2"        value="<?= $tirs2 ?>" />
<? } ?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier ? "Modifier" : "Ajouter" ?>" onclick="return validate_and_submit();"></INPUT></TD>
</TABLE></TD>


</TD>
</TABLE>
</FORM>

<? $menu->end(); ?>
