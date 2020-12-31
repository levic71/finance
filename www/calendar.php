<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom(), "05");

function MakeCalendar($sDateArg, $journees)
{
	global $sess_context;

	//	Store the month names in an array
	$monthName = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');

	//	Separate the date passed to the function into m, d, y...
	list($iThisMonth, $iThisDay, $iThisYear) = split ('[/.-]', $sDateArg);

	//	...and calculate the date as a UNIX timestamp (useful for calculations later)
	$thismonthfulldate = mktime (0,0,0,$iThisMonth,$iThisDay,$iThisYear);

	//	Get the month name for the date passed to the function
	$sThisMonthName = $monthName[$iThisMonth-1];

	//	Retrieve the day of the week this month starts on
	$iThisMonthStartsThisDay = date("w", mktime(0, 0, 0, $iThisMonth, $iThisDay, $iThisYear));

	/*	Calculate how many days there are in this month	*/
	//	Get the UNIX timestamp for the following month...
	$nextmonthfulldate = mktime (0,0,0,$iThisMonth+1,$iThisDay,$iThisYear);

	//	Get the difference between the two timestamps
	$iDateDiffInMs = $thismonthfulldate - $nextmonthfulldate;

	//	Get the numbers of days from the remainder
	$iDaysThisMonth = abs ($iDateDiffInMs / 86400);
	if ($iDaysThisMonth > 31) $iDaysThisMonth = 31;
	if ($iThisMonth == 3) $iDaysThisMonth = 31; // Sinon bug sur mars 2009 !!!

	echo "<TR>";
	$lib = "<TABLE BORDER=0 SUMMARY=\"\"><TR><TD><BUTTON CLASS=calendar onClick=\"javascript:go_calendar('$iThisYear-$iThisMonth-01');\"> $sThisMonthName $iThisYear </BUTTON></TD></TABLE>";
	HTMLTable::printCellWithColSpan($lib, "#636563", "", "center", _CELLBORDER_NONE_, 7);

	//	Write the row of weekday initials...
	echo "<TR>";
	HTMLTable::printCell("D", "#BCC5EA", "", "center", _CELLBORDER_ALL_);
	HTMLTable::printCell("L", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	HTMLTable::printCell("M", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	HTMLTable::printCell("M", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	HTMLTable::printCell("J", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	HTMLTable::printCell("V", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	HTMLTable::printCell("S", "#BCC5EA", "", "center", _CELLBORDER_U270_);
	echo "<TR><TD HEIGHT=1 BGCOLOR=#BCC5EA COLSPAN=7></TD>";

	//	then calculate and display the first week.
	echo "<TR>";

	static $iDayToDisplay=1;
	$prev_is_empty = 0;
	for ($i=0; $i<7; $i++)
	{
		if ($i==$iThisMonthStartsThisDay)           // start with the numeral 1.
			$iDayToDisplay=1;
		else if ($i>$iThisMonthStartsThisDay)       // increment the date
			$iDayToDisplay+=1;
		else                                        // not first day yet? a non-breaking space
			$iDayToDisplay="&nbsp;";

		$next_day_is_not_empty = ($i+1) >= $iThisMonthStartsThisDay ? 1 : 0;

		if ($iDayToDisplay == "&nbsp;")
		{
			HTMLTable::printCell("&nbsp;", "", "", "", $next_day_is_not_empty ? _CELLBORDER_SE_ : _CELLBORDER_BOTTOM_);
		}
		else
		{
			$ldate = $iThisYear."-".sprintf("%02d-%02d", $iThisMonth, $iDayToDisplay);
			if (isset($journees[$ldate]))
			{
				$url = $journees[$ldate]['virtuelle'] == 0 ? ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php") : "journees_virtuelles_ajouter.php";
				$img = $journees[$ldate]['id_journee_mere'] != 0 ? "../images/ballon2.gif" : "../images/ballon.gif";
				$anchor = "<A HREF=".$url."?pkeys_where_jb_journees=+WHERE+id=".$journees[$ldate]['id']."><IMG SRC=".$img." BORDER=0 ALT=\"Visualisation de la journée du $ldate\" /></A>";
				$libd   = "<FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT>";
				$css    = "#D5D9EA";
			}
			else
			{
				$anchor = "";
				$libd   = $sess_context->isAdmin() ? "<BUTTON CLASS=light onClick=\"javascript:go_journees_ajouter('".sprintf("%02d/%02d/%d", $iDayToDisplay, $iThisMonth, $iThisYear)."');\"><FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT></BUTTON>" : "<FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT>";
				$css    = "";
			}
			$lib = "<table border=0 CELLPADDING=0 CELLSPACING=0 SUMMARY=\"\"><TR><TD>$libd</TD><TD></TD><TR><TD COLSPAN=2 ALIGN=right HEIGHT=10 WIDTH=20>".$anchor."</TD></TABLE>";
			HTMLTable::printCell($lib, $css, "", "center", $i == 0 ? _CELLBORDER_U_ : _CELLBORDER_SE_);
		}
	}

	//	Now, display the rest of the month.
	$weekstogo = round( ($iDaysThisMonth-$iDayToDisplay+$iThisMonthStartsThisDay) / 7 );

	//	Bugfix below! [There seemed to be a problem with my math.  I'm bad at math.
	//                 Got a problem with that? Well! Then let's settle this the way nudists
	//                 throughout history have always settled their differences.
	//                 Beach Volleyball!]
	//                 Special thanks to Howard van Rooijen for the fix below.]
	//	Here's the fix:
	if (($iDaysThisMonth==30) && ($iThisMonthStartsThisDay==0)) {$weekstogo=4;}
	if (($iDaysThisMonth==30) && ($iThisMonthStartsThisDay==5)) {$weekstogo=4;}
	if (($iDaysThisMonth==31) && ($iThisMonthStartsThisDay==0)) {$weekstogo=4;}
	if (($iDaysThisMonth==31) && ($iThisMonthStartsThisDay==6)) {$weekstogo=5;}
	if (($iDaysThisMonth==31) && ($iThisMonthStartsThisDay==4)) {$weekstogo=4;}

	for ($x=1; $x<=$weekstogo; $x++)
	{
		echo "<TR>";
		for ($i=0; $i < 7; $i++)
		{
			if ( $iDayToDisplay<$iDaysThisMonth && is_int($iDayToDisplay) )
				$iDayToDisplay+=1;          // if not end of month, display.
			else
				$iDayToDisplay="&nbsp;";    // month ended?  non-breaking spaces.

			if ($iDayToDisplay == "&nbsp;")
				HTMLTable::printCell("", "", "", "", ($i == 0) ? _CELLBORDER_U_ : _CELLBORDER_SE_);
			else
			{
				$ldate = $iThisYear."-".sprintf("%02d-%02d", $iThisMonth, $iDayToDisplay);
				if (isset($journees[$ldate]))
				{
					$url = $journees[$ldate]['virtuelle'] == 0 ? ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php") : "journees_virtuelles_ajouter.php";
					$img = $journees[$ldate]['id_journee_mere'] != 0 ? "../images/ballon2.gif" : "../images/ballon.gif";
					$anchor = "<A HREF=".$url."?pkeys_where_jb_journees=+WHERE+id=".$journees[$ldate]['id']."><IMG SRC=".$img." BORDER=0 ALT=\"Visualisation de la journée du $ldate\" /></A>";
					$libd   = "<FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT>";
					$css    = "#D5D9EA";
				}
				else
				{
					$anchor = "";
					$libd   = $sess_context->isAdmin() ? "<BUTTON CLASS=light onClick=\"javascript:go_journees_ajouter('".sprintf("%02d/%02d/%d", $iDayToDisplay, $iThisMonth, $iThisYear)."');\"><FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT></BUTTON>" : "<FONT COLOR=#99a7c4>".$iDayToDisplay."</FONT>";
					$css    = "";
				}
				$lib = "<table border=0 CELLPADDING=0 CELLSPACING=0 SUMMARY=\"\"><TR><TD>$libd</TD><TD ALIGN=right></TD><TR><TD COLSPAN=2 ALIGN=right HEIGHT=10 WIDTH=20>".$anchor."</TD></TABLE>";
				HTMLTable::printCell($lib, $css, "", "center", ($i == 0) ? _CELLBORDER_U_ : _CELLBORDER_SE_);
			}
		}
	}
}

?>
<SCRIPT type="text/javascript">
linkset='';
<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
linkset+='<div class="menuitems"><a href="journees_virtuelles_ajouter.php">Création d\'une journée virtuelle</a></div>';
<? } ?>
function go_calendar(date)
{
	document.forms[0].refdate.value=date;
	document.forms[0].submit();
}
function go_journees_ajouter(date)
{
    document.forms[0].action = '<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "journees_ajouter_tournoi.php" : "journees_ajouter.php" ?>';
	document.forms[0].refdate.value=date;
	document.forms[0].submit();
}
</SCRIPT>
<FORM ACTION=calendar.php METHOD=post>
<INPUT TYPE=HIDDEN NAME=refdate VALUE="" />

<TABLE CLASS=master CELLPADDING=0 CELLSPACING=0 SUMMARY="calendrier" STYLE="margin-bottom: 5px;">
<?

// Date du jour
if (isset($refdate))
{
	$item = split('-', $refdate);
	$aujourdhui['year'] = $item[0];
	$aujourdhui['mon']  = $item[1];
}
else
	$aujourdhui = getdate();

// Calcul de la date M-4
$ref1year  = $aujourdhui['year'];
$ref1month = $aujourdhui['mon'] - 4;
if ($ref1month <= 0)
{
	$ref1month += 12;
	$ref1year--;
}

// Calcul de la date M+1
$ref2year  = $aujourdhui['year'];
$ref2month = $aujourdhui['mon'] + 1;
if ($ref2month > 12)
{
	$ref2month -= 12;
	$ref2year++;
}

// Calcul de la date M-6
$ref3year  = $aujourdhui['year'];
$ref3month = $aujourdhui['mon'] - 6;
if ($ref3month <= 0)
{
	$ref3month += 12;
	$ref3year--;
}

// Calcul de la date M+6
$ref4year  = $aujourdhui['year'];
$ref4month = $aujourdhui['mon'] + 6;
if ($ref4month > 12)
{
	$ref4month -= 12;
	$ref4year++;
}

// Récupération des journees à afficher
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journees = $sjs->getListeJourneesIndexedByDate($ref1year."-".$ref1month."-01", $ref2year."-".$ref2month."-31");

echo "<TR><TD>";
$fxlist = new FXListPresentation("");
$lib_fg = "<A HREF=calendar.php?refdate=".$ref3year."-".$ref3month."-01><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"\" /></A>";
$lib_fd = "<A HREF=calendar.php?refdate=".$ref4year."-".$ref4month."-01><IMG SRC=../images/journee_nxt.gif BORDER=0 ALT=\"\" /></A>";
$lib_aide = $sess_context->isAdmin() ? "<FONT SIZE=2 COLOR=white> CALENDRIER </FONT><BR><FONT COLOR=#BECBD6>[Pour ajouter une journée, cliquez sur les N°]</FONT>" : "<FONT CLASS=big SIZE=3 COLOR=white> CALENDRIER </FONT>";
$lib = "<div class=\"tc_box\"><div class=\"box1\">".$lib_fg."</div><div class=\"box2\">".$lib_aide."</div><div class=\"box3\">".$lib_fd."</div></div>";
$fxlist->FXSetTitle($lib, "center");
$fxlist->FXSetColumnsAlign(array("center"));
$fxlist->FXDisplay(false);

// Affichage du calendrier
echo "<TR><TD style=\"border:1px solid #AAAAAA\"><TABLE BORDER=0 SUMMARY=\"\" STYLE=\"width: 100%;\">";
$i = 0;
while($i < 6)
{
	if (!($i%3))
	{
		if ($i != 0) echo "<TR VALIGN=top><TD HEIGHT=5> </TD>";
		echo "<TR VALIGN=top><TD WIDTH=3> </TD>";
	}

	$caldate = ($ref1month+$i) > 12 ? ($ref1month+$i-12)."/01/".($ref1year+1) : ($ref1month+$i)."/01/".$ref1year;

	echo "<TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 SUMMARY=\"\" STYLE=\"width: 100%;\">";
	MakeCalendar($caldate, $journees);
	echo "</TABLE></TD>";

	echo "<TD WIDTH=3> </TD>";

	$i++;
}

?>

</TABLE></TD>
</TABLE></TD>

</TABLE>

<div class="cmdbox">
<div><a class="cmd" href="journees.php">Mode liste</a></div>
<? if ($sess_context->isAdmin() && $sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
<div><a class="cmd" href="journees_alias_choisir.php">Création d'une journée alias</a></div>
<div><a class="cmd" href="journees_virtuelles_ajouter.php">Création d'une journée virtuelle</a></div>
<? } ?>
<? if ($sess_context->isAdmin() && $sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_) { ?>
<div><a class="cmd" href="journees_ajouter_championnat.php">Création automatique des matchs d'une saison</a></div>
<? } ?>
</div>

</FORM>

<? $menu->end(); ?>
