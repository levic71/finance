<?php

include("../include/rss10.inc");
include "../include/inc_db.php";
include "../include/cache_manager.php";
include "../include/toolbox.php";

$rss = new RSSWriter(
		"http://www.jorkers.com/",
		"Jorkers.com",
		"Gestion de Championnats/tournois de Foot 2x2, Futsall, Football - Tout est gratuit - Saisissez vos joueurs/équipes/matchs et automatiquement les classements et les statistiques sont calculés. Affichage et personnalisation de ces informations sur votre site grâce à la syndication des classements.",
		array("dc:publisher" => "Jorkers.com Publisher", "dc:creator" => "contact@jorkers.com")
		);

$rss->setImage(
		"http://www.jorkers.com/images/jorkers.images/pub_jorkers.gif",
		"Example Site: All the Examples Fit to Print",
		100,
		50
		);

$most_active = JKCache::getCache("../cache/most_active_home.txt", -1, "_FLUX_MOST_ACTIVE_");

$k = 0;
foreach($most_active as $c)
{
	if ($k++ > 15) break;
	$lib = $k.") Accès à ".$c['nom']." [".$c['points']." points]";
	$rss->addItem(
		"http://www.jorkers.com/www/championnat_acces.php?ref_champ=".$c['id'],
		$lib,
		array(	"description" => $lib,
				"dc:subject"  => $lib,
				"dc:creator"  => "contact@jorkers.com")
		);
}

$lstmsgs = JKCache::getCache("../cache/forum_home.txt", -1, "_FLUX_FORUM_HOME_");
$i = 0;
foreach($lstmsgs as $row)
{
	$rss->addItem(
		"http://www.jorkers.com/www/forum_message.php?id_msg=".$row['id'] ,
		Toolbox::mysqldate2smalldatetime($row['date'])." ".$row['title'],
		array(	"description" => $row['message'],
				"dc:subject" => Toolbox::mysqldate2smalldatetime($row['date'])." ".$row['title'],
				"dc:creator" => $row['nom'])
		);
}


$rss->serialize();

?>
