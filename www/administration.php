<?

include "../include/sess_context.php";

session_start();

include "../include/templatebox.php";

$adminstration_page = 1;

TemplateBox::htmlBegin(true);

?>

<form action="<?= $sess_context->isAdmin() ? "../admin/superuser_exit.php" : "../admin/admin_valid.php" ?>" method=post TARGET="_parent">

<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=1 SUMMARY="Administration panel">

<? if ($sess_context->isAdmin()) { ?>
		<TR><TD><table border=0 cellspacing=0 cellpadding=0 SUMMARY="Déconnexion">
			<TR><TD CLASS=menu_input>Déconnexion</TD>
				<TD><button onClick="submit();"><img src="../images/jorkers/images/logout.gif" alt="bouton déconnecter" BORDER=0 /></button></TD>
			</TABLE></TD>
		<tr><td height=5></td></tr>
		<tr><td STYLE="background: #CCCCCC;" colspan=2><A CLASS=cmd HREF="../admin/superuser_fcts.php" TARGET="_parent">Admin console</a></td></tr>
<? } else { ?>
		<TR><TD CLASS=menu_input><table border=0 cellspacing=0 cellpadding=0 SUMMARY="Connexion">
				<tr>
					<td colspan=2 align=left><INPUT NAME=login TYPE=TEXT SIZE=9 MAXLENGTH=32 VALUE="login" /></TD>
				<tr>
					<TD><INPUT NAME=pwd TYPE=PASSWORD SIZE=9 MAXLENGTH=32 VALUE="login" /></td>
					<td><button STYLE="padding-top: 2px;" onClick="submit();"><img src="../images/templates/defaut/bt_ok.gif" alt="bouton valider" BORDER=0 /></button></TD>
			</table></td>
<? } ?>

</TABLE>

</form>

<? TemplateBox::htmlEnd(false); ?>
