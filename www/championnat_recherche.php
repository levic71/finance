<?include "../include/sess_context.php";session_start();$jorkyball_redirect_exception = 1;include "common.php";include "../include/inc_db.php";include "ManagerFXList.php";$db = dbc::connect();if (isset($sess_context) && $sess_context->isChampionnatValide()){	$menu = new menu("full_access");	$menu->debut($sess_context->getChampionnatNom());}else{	$menu = new menu("forum_access");	$menu->debut("");}?><form action="../www/championnat_recherche.php" method="post"><div id="pageint"><h2>R�sultats de la recherche</h2><?$filtre = "";if (isset($search_type) && $search_type != 0) $filtre = "AND type_sport=".$search_type;$scs = new SQLChampionnatsServices();$lst = $scs->getAllChampionnats(false, " WHERE (nom LIKE '%".$search_champ."%' OR lieu LIKE '%".$search_champ."%') ".$filtre);if (count($lst) > 0){	echo "<table border=0>";	foreach($lst as $item)	{		echo "<tr><td style=\"width: 200px;\"><div class=\"nom icon_".$item['type']."\"><a href=\"championnat_acces.php?ref_champ=".$item['id']."\">".htmlspecialchars($item['nom'])."</a></td><td style=\"width: 350px;\">".$item['lieu']."</td></tr>";	}	echo "</table>";}else	echo "<div>Aucun championnat trouv�.</div>";?><br /><div> <label for="search_champ" id="labelCamp">Nouvelle recherche :</label><input type="text" name="search_champ" size="31" value="<?= $search_champ ?>" /><label for="search_type" id="labelType">Type sport : </label><select id="search_type" name="search_type">	<option value="0" <?= $search_type == 0 ? "selected=\"selected\"" : "" ?>> Tous </option>	<option value="1" <?= $search_type == 1 ? "selected=\"selected\"" : "" ?>> <?= $libelle_genre[_TS_JORKYBALL_] ?></option>	<option value="2" <?= $search_type == 2 ? "selected=\"selected\"" : "" ?>> <?= $libelle_genre[_TS_FUTSAL_] ?></option>	<option value="3" <?= $search_type == 3 ? "selected=\"selected\"" : "" ?>> <?= $libelle_genre[_TS_FOOTBALL_] ?></option></select><button onclick="javascript:document.forms[0].submit();"><img src="../images/templates/defaut/bt_ok.gif" alt="" /></button></div><br /></div></form><? $menu->end(); ?>