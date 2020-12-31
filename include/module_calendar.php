<?

function MakeCalendar2($sDateArg, $today, $journees)
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
?>
<div class="pave">
	<div class="titre"> <?= $sThisMonthName ?> </div>
	<div class="corps">

	<table cellpadding="0" cellspacing="0" class="menu_droit" summary="Calendar module">
	<tr>
		<td align="center" class="calendar_j"> D </td>
		<td align="center" class="calendar_j"> L </td>
		<td align="center" class="calendar_j"> M </td>
		<td align="center" class="calendar_j"> M </td>
		<td align="center" class="calendar_j"> J </td>
		<td align="center" class="calendar_j"> V </td>
		<td align="center" class="calendar_j"> S </td>
	</tr>
	<tr>
<?
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
			echo "<td class=\"calendar\"> </td>";
		}
		else
		{
			$ldate = $iThisYear."-".sprintf("%02d-%02d", $iThisMonth, $iDayToDisplay);
			if (isset($journees[$ldate]))
			{
				$url = $journees[$ldate]['virtuelle'] == 0 ? ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php") : "journees_virtuelles_ajouter.php";
				$anchor = $url."?pkeys_where_jb_journees=+WHERE+id=".$journees[$ldate]['id'];
				echo "<td class=\"calendar_red\"><a class=\"calendar_red\" href=\"".$anchor."\" title=\"Accès à la journée\">".$iDayToDisplay."</a></td>";
			}
			else if ($iDayToDisplay == $today)
				echo "<td class=\"calendar_blue\"><a class=\"calendar_blue\" href=\"#\" title=\"Aujourd'hui\">".$iDayToDisplay."</a></td>";
			else
				echo "<td class=\"calendar_fill\">".$iDayToDisplay."</td>";
		}
	}
	echo "</tr>";

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
		echo "<tr>";
		for ($i=0; $i < 7; $i++)
		{
			if ( $iDayToDisplay<$iDaysThisMonth && is_int($iDayToDisplay) )
				$iDayToDisplay+=1;          // if not end of month, display.
			else
				$iDayToDisplay="&nbsp;";    // month ended?  non-breaking spaces.

			if ($iDayToDisplay == "&nbsp;")
				echo "<td class=\"calendar\"></td>";
			else
			{
				$ldate = $iThisYear."-".sprintf("%02d-%02d", $iThisMonth, $iDayToDisplay);
				if (isset($journees[$ldate]))
				{
					$url = $journees[$ldate]['virtuelle'] == 0 ? ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php") : "journees_virtuelles_ajouter.php";
					$anchor = $url."?pkeys_where_jb_journees=+WHERE+id=".$journees[$ldate]['id'];
					echo "<td class=\"calendar_red\"><a class=\"calendar_red\" href=\"".$anchor."\" title=\"Accès à la journée\">".$iDayToDisplay."</a></td>";
				}
				else if ($iDayToDisplay == $today)
					echo "<td class=\"calendar_blue\"><a class=\"calendar_blue\" href=\"#\" title=\"Aujourd'hui\">".$iDayToDisplay."</a></td>";
				else
					echo "<td class=\"calendar_fill\">".$iDayToDisplay."</td>";
			}
		}
		echo "</tr>";
	}
?>
	</table>
	
	</div>
</div>

<? }

if (isset($inscription)) return;

$aujourdhui = getdate();

// Calcul de la date M-4
$ref1year  = $aujourdhui['year'];
$ref1month = $aujourdhui['mon'];
$ref1day = $aujourdhui['mday'];
if ($ref1month <= 0)
{
	$ref1month += 12;
	$ref1year--;
}

// Récupération des journees à afficher
if (!$this->display_calendar_global && $sess_context->isChampionnatValide())
{
	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
	$journees = $sjs->getListeJourneesIndexedByDate($ref1year."-".$ref1month."-01", $ref1year."-".$ref1month."-31");
}
else
	$journees = array();

$i=0;
$caldate = ($ref1month+$i) > 12 ? ($ref1month+$i-12)."/01/".($ref1year+1) : ($ref1month+$i)."/01/".$ref1year;
MakeCalendar2($caldate, $ref1day, $journees);
