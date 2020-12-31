<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$page = Wrapper::getRequest('page', '');

if (!isset($sess_context)) exit(0);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body>

<div>
<script type="text/javascript">

<? if (!$sess_context->isSuperUser()) { ?>

var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));

_gaq.push(['_setAccount', 'UA-1509984-1']);
_gaq.push(['_trackPageview', '/wrapper/<?= $page ?>']);
<? } ?>

</script>
</div>
</body>
</html>