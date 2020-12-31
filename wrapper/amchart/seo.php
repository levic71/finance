<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>amCharts</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="style.css" rel="stylesheet" type="text/css">
<link href="jk.css" rel="stylesheet" type="text/css">
<link href="components.css" rel="stylesheet" type="text/css">
<script src="jxs_compressed.js" type="text/javascript"></script>
<script src="jk.js" type="text/javascript"></script>
</head>
<body>

<?

include("stats_globales.php");

$db = mysqli_connect("localhost", "root", "") or die("Impossible de se connecter : " . mysqli_error());
mysqli_select_db('jk');

//for($i = 0; $i < 366; $i++) echo rand(0, 1000).',';
$val = "207,909,596,950,645,145,709,240,111,575,897,834,576,792,880,72,508,891,877,803,875,463,607,366,146,604,287,608,290,907,638,872,777,466,877,1000,574,875,723,337,149,226,396,727,474,115,614,513,14,635,595,540,741,380,58,150,121,443,223,189,125,418,809,1000,221,398,31,424,377,979,613,809,62,252,770,996,246,814,222,329,396,756,187,652,482,672,386,310,973,657,35,145,836,303,855,502,540,706,62,224,55,458,377,655,851,654,18,639,894,887,705,519,653,250,656,139,98,339,588,844,698,245,722,476,420,564,775,380,735,389,968,399,610,313,17,877,514,430,143,658,416,335,63,84,785,120,999,1,590,381,665,754,298,209,283,182,881,200,571,633,804,446,748,949,38,542,532,473,51,582,142,51,814,158,297,25,792,366,216,239,956,798,617,37,774,548,720,263,215,210,242,261,749,879,403,874,342,147,922,445,465,108,15,820,86,356,405,340,674,986,308,852,197,590,444,697,124,262,31,720,425,596,788,264,568,687,934,173,521,126,186,792,753,9,765,964,233,930,389,30,431,981,276,839,313,757,146,731,350,351,217,551,510,357,208,642,262,870,338,848,575,481,325,514,917,286,389,482,256,878,247,96,62,351,119,201,304,299,724,140,451,815,279,757,470,493,505,395,832,427,619,898,503,210,340,610,955,938,888,375,153,201,981,537,569,96,837,939,170,215,175,925,426,654,215,337,305,3,676,523,276,359,774,323,307,315,230,81,868,964,262,644,926,900,589,352,952,216,430,50,912,508,503,83,277,868,27,293,15,890,722,25,595,667,805,122,986,795,995,929,656,365,511,287,573,973";

$sg = new Stats_globales();
$sg->update_year("2012", $val, $val, $val);

mysqli_close($db);

?>

<script type="text/javascript">
$ = function(id){return document.getElementById(id);}
_jx_ = function(name, url) {
    jx.load(url, false, 'text', 'post', { "handler":function(http) {
        if(http.readyState == 4) {
			cc(name, http.responseText);
        }
    }});
}
window.onload = function () {
	_jx_("main", "getprofile.php?iframe=1");
}
</script>
<style>
ul.sidebar { right: 50px; }

.ToolTextHover span {
	width: 180px;
	z-index: -100;
	top: -8px;
	left:-195px;
	height: 45px;
	line-height: 45px;
	-moz-border-radius: 30px;
	-webkit-border-radius: 30px;
	border-radius: 30px;
	padding-right: 50px !important;
}
#main {
display: block; background: #fff; width:860px; height:500px; border: 1px solid #ddd;
}
</style>



<!-- ul class="sidebar">
	<li><a href="#" onclick="" id="sb_best" class="ToolText ToolTextHover" onmouseover="showtip('sb_best');"><span>Classement général</span></a></li>
	<li><a href="#" onclick="" id="sb_besta" class=" ToolText" onmouseover="showtip('sb_besta');"><span>Meilleurs attaquants</span></a></li>
	<li><a href="#" onclick="" id="sb_bestd" class="ToolText" onmouseover="showtip('sb_bestd');"><span>Meilleurs défenseurs</span></a></li>
	<li><a href="#" onclick="" id="sb_bestea" class="ToolText" onmouseover="showtip('sb_bestea');"><span>Meilleures attaques</span></a></li>
	<li><a href="#" onclick="" id="sb_bested" class="ToolText" onmouseover="showtip('sb_bested');"><span>Meilleures défenses</span></a></li>
</ul -->


<div id="main"></div>
</body>
</html>