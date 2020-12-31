<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";

$db = dbc::connect();

?>

<HTML>
<BODY>
<FORM ACTION="superuser_fcts.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">
<TR><TD ALIGN=CENTER><TABLE CELLPADDING=0 CELLSPACING=0 CLASS=masterfree>

<?

echo "<TR>";
HTMLTable::printCellWithColSpan("<FONT SIZE=5 COLOR=white>Résultat réorganisation</FONT>", "#4863A0", "100%", "CENTER", _CELLBORDER_ALL_, 3);

/*
// Remplacement d'une équipe par une autre (un peu bourrin et pas pas tout terrain !!!!!
function reaffect_team($id_champ, $id_team1, $id_team2)
{
	$req = "SELECT * FROM jb_equipes WHERE id=".$id_team1.";";
	$res = dbc::execSQL($req);
	$equipe = mysql_fetch_array($res);
//	echo $equipe['nom']."-".$equipe['id_champ'].".........";

	$req = "SELECT * FROM jb_equipes WHERE id=".$id_team2.";";
	$res = dbc::execSQL($req);
	$equipe = mysql_fetch_array($res);
//	echo $equipe['nom']."-".$equipe['id_champ']."<br>";

	$new_joueurs = "";
	$req = "SELECT * FROM jb_equipes WHERE id=".$id_team2.";";
	$res = dbc::execSQL($req);
	$equipe = mysql_fetch_array($res);
	if ($equipe['nb_joueurs'] > 0)
	{
		$j = explode('|', $equipe['joueurs']);
		$new_joueurs = ",".$j[0].",".$j[1];
	}

	$req = "SELECT * FROM jb_saisons WHERE id_champ=".$id_champ.";";
	$res = dbc::execSQL($req);
	while($saison = mysql_fetch_array($res))
	{
		$sql2 = "UPDATE jb_saisons SET joueurs='".$saison['joueurs'].$new_joueurs."', equipes='".str_replace($id_team1, $id_team2, $saison['equipes'])."' WHERE id=".$saison['id'];
		$res2 = dbc::execSQL($sql2);
	}

	$req = "SELECT * FROM jb_journees WHERE id_champ=".$id_champ.";";
	$res = dbc::execSQL($req);
	while($journee = mysql_fetch_array($res))
	{
		$sql2 = "UPDATE jb_journees SET equipes='".str_replace($id_team1, $id_team2, $journee['equipes'])."', classement_equipes='".str_replace($id_team1, $id_team2, $journee['classement_equipes'])."' WHERE id=".$journee['id'];
		$res2 = dbc::execSQL($sql2);
	}

	$req = "SELECT * FROM jb_classement_poules WHERE id_champ=".$id_champ.";";
	$res = dbc::execSQL($req);
	while($poule = mysql_fetch_array($res))
	{
		$sql2 = "UPDATE jb_classement_poules SET classement_equipes='".str_replace($id_team1, $id_team2, $poule['classement_equipes'])."' WHERE id=".$poule['id'];
		$res2 = dbc::execSQL($sql2);
	}

	$sql2 = "UPDATE jb_matchs SET id_equipe1=".$id_team2." WHERE id_equipe1=".$id_team1;
	$res2 = dbc::execSQL($sql2);

	$sql2 = "UPDATE jb_matchs SET id_equipe2=".$id_team2." WHERE id_equipe2=".$id_team1;
	$res2 = dbc::execSQL($sql2);
	
	$sql2 = "DELETE FROM jb_equipes WHERE id=".$id_team1;
	$res2 = dbc::execSQL($sql2);
}

reaffect_team(17, 437, 495);
reaffect_team(17, 365, 496);
reaffect_team(17, 368, 497);
reaffect_team(17, 366, 503);
reaffect_team(17, 370, 527);
reaffect_team(17, 369, 579);
reaffect_team(17, 372, 510);
reaffect_team(17, 383, 499);
reaffect_team(17, 385, 508);
reaffect_team(17, 373, 532);
reaffect_team(17, 381, 513);
reaffect_team(17, 374, 505);
reaffect_team(17, 380, 522);
reaffect_team(17, 378, 520);
reaffect_team(17, 379, 528);
reaffect_team(17, 388, 504);
reaffect_team(17, 387, 511);
reaffect_team(17, 390, 524);
reaffect_team(17, 384, 525);
reaffect_team(17, 386, 500);
reaffect_team(17, 363, 501);
reaffect_team(17, 389, 512);
reaffect_team(17, 392, 519);
reaffect_team(17, 395, 507);
reaffect_team(17, 438, 518);
reaffect_team(17, 396, 509);

// Mise à jour du champ joueurs des saisons
$req = "SELECT * FROM jb_championnat";
$res = dbc::execSQL($req);
while($championnat = mysql_fetch_array($res))
{
	if ($championnat['type'] != 0)
	{
		$req2 = "SELECT * FROM jb_saisons WHERE id_champ=".$championnat['id'];
		$res2 = dbc::execSQL($req2);
		while($saison = mysql_fetch_array($res2))
		{
			$joueurs = array();
			echo $championnat['nom']."::".$saison['nom']."<BR>";
			$req3 = "SELECT * FROM jb_equipes WHERE id IN (".$saison['equipes'].")";
			$res3 = dbc::execSQL($req3);
			while($equipe = mysql_fetch_array($res3))
			{
				if ($equipe['nb_joueurs'] > 0)
				{
					$j = explode('|', $equipe['joueurs']);
					foreach($j as $elt)
					{
						$joueurs[$elt] = $elt;
					}
				}
			}

			$lst = "";
			foreach($joueurs as $x)
				$lst .= ($lst == "" ? "" : ",").$x;

			$sql5 = "UPDATE jb_saisons SET joueurs='".$lst."' WHERE id=".$saison['id'];
			$res5 = dbc::execSQL($sql5);

			unset($joueurs);
		}
	}
}


	$req = "SELECT * FROM jb_matchs WHERE id_champ=21";
	$res = dbc::execSQL($req);
	$tab = array();
	$equipes = "";
	while($match = mysql_fetch_array($res))
	{
		$tab[$match['id_equipe1']] = $match['id_equipe1'];
		$tab[$match['id_equipe2']] = $match['id_equipe2'];
	}
	foreach($tab as $elt)
		$equipes .= ($equipes == "" ? "" : ",").$elt;
	$sql = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id=21";
	$res = dbc::execSQL($sql);



function updateEquipesSaison($championnat, $saison)
{
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);
	$equipes = "";
	while($eq = mysql_fetch_array($res))
	{
		$equipes .= ($equipes == "" ? "" : ",").$eq['id'];
	}
	$sql = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id=".$saison;
	$res = dbc::execSQL($sql);
}
$req = "SELECT * FROM jb_forum WHERE in_response=0";
$res = dbc::execSQL($req);
while($msg = mysql_fetch_array($res))
{
	$req2 = "SELECT * FROM jb_forum WHERE in_response=".$msg['id']." ORDER BY date DESC";
	$res2 = dbc::execSQL($req2);
	if ($row2 = mysql_fetch_array($res2))
		$update = "UPDATE jb_forum SET last_reponse='".$row2['date']."', last_user=\"".$row2['nom']."\" WHERE id=".$msg['id'];
	else
		$update = "UPDATE jb_forum SET last_reponse='".$msg['date']."', last_user=\"".$msg['nom']."\" WHERE id=".$msg['id'];
	$res3 = dbc::execSQL($update);
}


// On parcours tout le forum
$i = 0;
$req = "SELECT * FROM jb_forum WHERE in_response=0";
$res = dbc::execSQL($req);
while($msg = mysql_fetch_array($res))
{
	$req2 = "SELECT count(*) nb FROM jb_forum WHERE in_response=".$msg['id'];
	$res2 = dbc::execSQL($req2);
	$row2 = mysql_fetch_array($res2);
	
	$update = "UPDATE jb_forum SET nb_lectures=".$row2['nb'].",  nb_reponses=".$row2['nb']." WHERE id=".$msg['id'];
	$res3 = dbc::execSQL($update);
}

$update = "UPDATE jb_forum SET id_champ=17 WHERE id_champ=21";
$res = dbc::execSQL($update);

// Réorg saison DOI
//updateEquipesSaison(22, 22);

// Réorg saison CHamptAlfortville
//updateEquipesSaison(23, 23);

// Réorg saison alfortville Master
$req = "SELECT * FROM jb_equipes WHERE id_champ=21";
$res = dbc::execSQL($req);
$equipes = "";
while($eq = mysql_fetch_array($res))
{
	$equipes .= ($equipes == "" ? "" : ",").$eq['id'];
}
$sql = "UPDATE jb_joueurs SET id_champ=17 WHERE id_champ=21";
$res = dbc::execSQL($sql);
$sql = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id=21";
$res = dbc::execSQL($sql);
$sql = "UPDATE jb_equipes SET id_champ=17 WHERE id_champ=21";
$res = dbc::execSQL($sql);
$sql = "UPDATE jb_championnat SET nom='Alfortville Masters' WHERE id=17";
$res = dbc::execSQL($sql);
$sql = "DELETE FROM jb_championnat WHERE id=21";
$res = dbc::execSQL($sql);





// On parcours toutes les équipes
$i = 0;
$req = "SELECT * FROM jb_equipes";
$res = dbc::execSQL($req);
while($equipe = mysql_fetch_array($res))
{
	if (isset($equipe['id_joueur1']) && isset($equipe['id_joueur2']))
	{
	    $js = $equipe['id_joueur1']."|".$equipe['id_joueur2'];
	    $update = "UPDATE jb_equipes SET joueurs='".$js."', nb_joueurs=2 WHERE id=".$equipe['id'];
	    $res_update = dbc::execSQL($update);

		echo "<TR onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";
		HTMLTable::printCell($i, "",  "5%", "CENTER", _CELLBORDER_BOTTOM_);
		HTMLTable::printCell("update équipe id=".$equipe['id'], "#BCC5EA", "80%", "CENTER", _CELLBORDER_BOTTOM_);
		HTMLTable::printCell("Ok", "",  "", "CENTER", _CELLBORDER_BOTTOM_);

		$i++;
	}
}
*/

/*
// On parcours toutes les matchs
$i = 0;
$req = "SELECT * FROM jb_matchs";
$res = dbc::execSQL($req);
while($match = mysql_fetch_array($res))
{

	$resultat = "";
	if ($match['res1'] != 0 || $match['res2'] != 0) $resultat = $match['res1']."/".$match['res2'];
	if ($match['res3'] != 0 || $match['res4'] != 0) $resultat .= ",".$match['res3']."/".$match['res4'];
	if ($match['res5'] != 0 || $match['res6'] != 0) $resultat .= ",".$match['res5']."/".$match['res6'];
	if ($match['res7'] != 0 || $match['res8'] != 0) $resultat .= ",".$match['res7']."/".$match['res8'];
	if ($match['res9'] != 0 || $match['res10'] != 0) $resultat .= ",".$match['res9']."/".$match['res10'];

	if ($resultat == "" && $match['nbset'] == 1)
		$resultat = "0/0";
	else if ($resultat == "" && $match['nbset'] == 2)
		$resultat = "0/0,0/0";
	else if ($resultat == "" && $match['nbset'] == 3)
		$resultat = "0/0,0/0,0/0";
	else if ($resultat == "" && $match['nbset'] == 4)
		$resultat = "0/0,0/0,0/0,0/0";
	else if ($resultat == "" && $match['nbset'] == 5)
		$resultat = "0/0,0/0,0/0,0/0,0/0";
		
	$fanny = 0;
	if ($match['nbset'] == 1 && (($match['res1'] == 0 && $match['res2'] > 0) || ($match['res1'] > 0 && $match['res2'] == 0)))
		$fanny = 1;

    $update = "UPDATE jb_matchs SET resultat='".$resultat."', fanny=".$fanny." WHERE id=".$match['id'];
    $res_update = dbc::execSQL($update);

	echo "<TR onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";
	HTMLTable::printCell($i, "",  "5%", "CENTER", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell("update match id=".$match['id'], "#BCC5EA", "80%", "CENTER", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell("Ok", "",  "", "CENTER", _CELLBORDER_BOTTOM_);

	$i++;
}
*/

mysql_close($db);

?>

</TABLE></TD>

</TABLE>
</FORM>
</BODY>
</HTML>
