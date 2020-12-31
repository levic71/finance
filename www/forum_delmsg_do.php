<?php

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$sfs = new SQLForumServices($sess_context->getRealChampionnatId());

function delmsg($id)
{
	global $sfs, $sess_context;
	
//	$msg = $sfs->getMessage($id);
//	if ($msg['image'] != "" && file_exists($msg['image'])) unlink($msg['image']);

//	$delete = "DELETE FROM jb_forum WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id;
//	$res = dbc::execSQL($delete);

	$update = "UPDATE jb_forum SET del=1 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id;
	$res = dbc::execSQL($update);
}

// Suppression des réponses au message initial
$lst_reponses = $sfs->getReponses($id_msg2del);
foreach($lst_reponses as $rep)
	delmsg($rep['id']);

// Suppression du messqge initial
delmsg($id_msg2del);

mysql_close ($db);

ToolBox::do_redirect("forum.php");

?>
