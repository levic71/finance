<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

$menu = new menu("full_access");

// Récupération des informations de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

// Récupération des équipes
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

	$tmp = str_replace('|', ',', $row['equipes']);
	$items = explode(',', $tmp);
	foreach($items as $item) 
		if ($item != "") $equipes .= $equipes == "" ? $item : ",".$item;
}
else
	$equipes = $row['equipes'];
	
$lst_equipes = array();
if ($equipes != "") $lst_equipes = explode(',', $equipes);

$bonus = "";
foreach($lst_equipes as $item)
{
	if (ToolBox::get_global("bonus_".$item))
	{
		$val = ToolBox::get_global("bonus_".$item);
		$bonus .= ($bonus == "" ? "" : ",").$item."=".$val;
	}
}
	
$update = "UPDATE jb_journees SET bonus='".$bonus."' WHERE id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Mise des statistiques globales de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
$stats->SQLUpdateClassementJournee();

// Mise des statistiques de poules pour les journées de tournoi
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType(), "AND niveau='".$options_type_matchs."'");
	$stats->SQLUpdateClassementJourneeTournoi($options_type_matchs);
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php?options_type_matchs=".$options_type_matchs : "matchs.php");

?><?php

?>
