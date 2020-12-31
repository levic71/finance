<html>
<body>

<?

include "../include/inc_db.php";
$db = dbc::connect();

?>

<style>
* {
	margin: 5px;
	font-family: arial;
	font-size: 11px;
}

#main {
	width: 700px;
	padding: 5px;
}
#main div {
	width: 100%;
	padding: 5px;
}
#main #form {
	background: #DDDDDD;
	width: 100%;
}
#main #form tr {
	margin: 5px;
	font-family: arial;
	font-size: 11px;
}
#main #toolbar {
	height: 30px;
}
#main #toolbar input, #main #toolbar table {
	float: right;
}
#main #detail {
	background: #FFFFF;
	border: 1px solid #DDDDDD;
	height: 320px;
	overflow: scroll;
}
.waiting {
	background: url('waiting.gif') no-repeat center center;
	width: 100%;
	height: 250px;
}
</style>

<form action="#" method="post">
<div id="main">

<div id="form"><table border="0" cellpadding="0" cellspacing="0" style="">

<tr><td colspan="2" align="center"><b>IMPORT DE MATCHS POUR UNE JOURNEE</b><br/><br/></td></tr>

<tr>

<td>Championnat cible :</td>

<td><table border="0" cellpadding="0" cellspacing="0"><tr>

<td><select id="championnatForm" name="championnatForm" onchange="changeConf()">
<option value="0" selected="selected">Choisir un championnat</option>
<?
$select = "SELECT * FROM jb_championnat WHERE actif=1 ORDER BY nom";
$res = dbc::execSQL($select);
while ($row = mysql_fetch_array($res))
	echo "<option value=\"".$row['id']."\">".$row['nom']."</option>";
?>
</select></td>
<td><div id="journeebox"></div></td>

</tr></table></td>

</tr>

<tr><td>Donn�es : </td><td><textarea id="dataForm" name="dataForm" cols="100" rows="10"></textarea></td></tr>

<tr><td>Matchs jou�s ? : </td><td>
<table border="0" cellpadding="0" cellspacing="0"><tr>
<td><input type="radio" id="playedForm" name="playedForm" value="1" checked="checked" /></td><td>Oui</td>
<td><input type="radio" id="playedForm" name="playedForm" value="0" /></td><td>Non</td>
</tr></table>
</td></tr>

</table></div>

<div id="toolbar">
	<table border="0" cellpadding="0" cellspacing="0"><tr>
		<td>Mode debug : </td><td><input type="checkbox" id="debugForm" name="debugForm" /></td>
		<td><input type="submit" value="Preview" onclick="javascript:xmlhttpPost('preview'); return false;" /></td>
		<td><input type="submit" value="Importer" onclick="javascript:xmlhttpPost('import'); return false;" /></td>
	</tr></table>
</div>

<div id="detail"></div>

</div>
</form>

<script>
function changeConf()
{
	var myrefchamp = new Array();
	var myjournee = new Array();
	var myrefsaison = new Array();
<?

$select = "SELECT c.id id, s.id id2, c.ref_champ ref_champ FROM jb_championnat c, jb_saisons s WHERE c.actif=1 AND c.id = s.id_champ AND s.active = 1 ORDER BY c.nom ASC";
$res = dbc::execSQL($select);
while ($row = mysql_fetch_array($res))
{
	echo "myrefchamp[".$row['id']."] = '".$row['ref_champ']."';\n";
	echo "myrefsaison[".$row['id']."] = '".$row['id2']."';\n";
	$select2 = "SELECT * FROM jb_journees WHERE id_champ=".$row['id2']." AND TO_DAYS(now())-TO_DAYS(date) <= 0 ORDER BY date ASC LIMIT 0,1";
	$res2 = dbc::execSQL($select2);
	if ($row2 = mysql_fetch_array($res2))
	{
		echo "myjournee[".$row['id']."] = '".str_replace(':', '', str_replace(':', '', $row2['nom']))."';\n";
	}
	else
	{
		$select3 = "SELECT * FROM jb_journees WHERE id_champ=".$row['id2']." ORDER BY date DESC LIMIT 0,1";
		$res3 = dbc::execSQL($select3);
		if ($row3 = mysql_fetch_array($res3))
			echo "myjournee[".$row['id']."] = '".str_replace(':', '', $row3['nom'])."';\n";
		else
			echo "myjournee[".$row['id']."] = '1';\n";
	}
}
?>
    var championnatForm = document.getElementById("championnatForm").value;

    getjournee(myrefsaison[championnatForm]);
}

function getjournee(saison) {
	var xmlHttpReq = false;
    var self = this;
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open('POST', 'parseur_getjournee_csv_do.php', true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
		    document.getElementById("journeebox").innerHTML = self.xmlHttpReq.responseText;
        }
    }

    qstr = 'refSaisonForm=' + escape(saison);
    self.xmlHttpReq.send(qstr);
}

function xmlhttpPost(action) {
    var championnatForm = document.getElementById("championnatForm").value;

    if (championnatForm == "0")
    {
    	alert('Il faut s�lectionner un championnat cible');
    	return false;
    }

    var xmlHttpReq = false;
    var self = this;
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open('POST', 'parseur_csv_do.php', true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    updatepage("<div class=waiting>Importing ...</div>");
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
            updatepage(self.xmlHttpReq.responseText);
        }
    }
    self.xmlHttpReq.send(getquerystring(action));
}

function getquerystring(action) {
    var dataForm = document.getElementById("dataForm").value;
    var playedForm = document.forms[0].playedForm[0].checked ? 1 : 0;
    var debugForm = document.forms[0].debugForm.checked ? 1 : 0;
    var championnatForm = document.getElementById("championnatForm").value;
    var idJourneeForm = 0;
	if (document.getElementById("idJourneeForm"))
		idJourneeForm = document.getElementById("idJourneeForm").value;

    qstr = 'action=' + action + '&dataForm=' + escape(dataForm) + '&playedForm=' + escape(playedForm) + '&championnatForm=' + escape(championnatForm) + '&debugForm=' + escape(debugForm) + '&idJourneeForm=' + escape(idJourneeForm);

    return qstr;
}

function updatepage(str){
    document.getElementById("detail").innerHTML = str;
}
</script>

</body>
</html>
