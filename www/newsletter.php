<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$no_ads468x60 = 1;

if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

?>

<form action="../www/newsletter_do.php" method="post">

<div id="pageint">

<h2>Abonnement newsletter</h2>

<table border="0" cellspacing="0" cellpadding="1" summary="Administration panel">
<tr>
	<td>Saisissez votre email:</td>
	<td><input name="email" type="text" size="24" maxlength="32" value="" /></td>
    <td><button onclick="submit();" style="padding-top: 2px;">
			<img src="../images/templates/defaut/bt_ok.gif" alt="bouton valider" />
		</button></td>
</tr>
</table>

</div>

</form>

<? $menu->end(); ?>
