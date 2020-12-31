<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();
$is_journee_alias = $sjs->isJourneeAlias($journee);

$id_j = $sess_context->getJourneeId();
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ && $is_journee_alias)
	$id_j = $journee['id_journee_mere'];

// Suppression du match
$delete = "DELETE FROM jb_matchs ".urldecode($pkeys_where)." AND id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$id_j;
$res = dbc::execSQL($delete);

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

?>
