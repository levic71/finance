<?

include "../include/sess_context.php";

session_start();

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/Xclasses.php";
include "../include/HTMLTable.php";
include "../include/inc_db.php";

$db = dbc::connect();

if (!isset($send_mail)) $send_mail = 0;

if ($send_mail == 0)
{ ?>
<FORM ACTION=../admin/admin_send.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=send_mail VALUE=1>
<TABLE BORDER=0>
<TR><TD>Destinataire</TD><TD><INPUT TYPE=TEXT NAME=dest SIZE=64></TD>
<TR><TD>Sujet</TD><TD><INPUT TYPE=TEXT NAME=sujet SIZE=64></TD>
<TR><TD>Message</TD><TD><TEXTAREA NAME=message COLS=70 ROWS=15></TEXTAREA></TD>
<TR><TD COLSPAN=2 ALIGN=RIGHT><INPUT TYPE=SUBMIT></TD>
</TABLE>
</FORM>
<? }
else
{
	$res = mail($dest,  $sujet, $message, "From: contact@jorkers.com");
}

?>
