<?
session_start();
set_include_path("./tennis");
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_Metrics.php');
include_once('INCL_Tennis_Functions_QUERIES.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();
?>

<html>
<head>
	<title>Holbrook Tennis Group</title>
	<LINK REL=StyleSheet HREF="main.css" TYPE="text/css">
	<LINK REL=StyleSheet HREF="metric.css" TYPE="text/css">
	<LINK REL=StyleSheet HREF="handheld.css" media="handheld" TYPE="text/css">
	
<STYLE TYPE="text/css">
  #thispg_Bar
  	{
		font-size: small;
		background: #AAAA44;
		width: 20em;
		float: right;
		height: auto;
		padding-left: .1em;
		margin-left: 1em;
		border: solid
	}

  #thispg_announce
  	{
		padding-left: .5em;
		height: auto
	}

  #thispg_maincontent
  	{
		padding-left: .5em;
		clear: both
	}
</STYLE>


</head>


<BODY CLASS=bdyNormal>

<H1 CLASS=fntNormal>Holbrook Tennis Group</H1>



<?
//BUILD A METRICS TABLE IN THIS DIVISION ==========================>>

$SHOWMETRICS = FALSE;
//$SHOWMETRICS = TRUE;

//STANDARD STARTUP STUFF ------------------------------------------>>
if ($SHOWMETRICS) {

echo "<div id=\"home_MetricTbl\">";

$DEBUG = FALSE;
//$DEBUG = TRUE;

$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   Get Query string values.
$seriesID = $_GET['ID'];
if ($seriesID <=0) $seriesID = 1;
$metricCOLtoSORTby = $_GET['SORTCOL'];
if ($metricCOLtoSORTby <=0) $metricCOLtoSORTby = 1;

				//   Set return page for edits.
$_SESSION['RtnPg'] = "../index.php?ID={$seriesID}&SORTCOL={$metricCOLtoSORTby}";

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

			//   Get the series name.
array($row);
if (!Tennis_GetSingleRecord($row, 'series', $seriesID))
	{
	echo "<P>An Error Has Occurred:<BR>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
$seriesLongName = $row['LongName'];
$seriesShortName = $row['ShtName'];
//------------------------------------------------END STARTUP STUFF<<

//DECLARE CUSTOM VARIABLES ---------------------------------------->>

				//   Arrays to hold the MySQL data.
array($MetricMeta);

				//   How many metrics are tied to the series?
$numMetrics = 0;

				//   Holds the query of step#2.
$qry = "";
//---------------------------------------------END CUSTOM VARIABLES<<

//CUSTOM LOGIC SECTION -------------------------------------------->>
				//   Step #1: Get metric meta-data. And set the
				//current sort-ID. IF there are no metrics defined for
				//the series, show a nice message to that effect and
				//don't attempt to build a table.
$numMetrics = metrics_getMetricMetaData($MetricMeta, $seriesID);
if ($MetricMeta['1']['ID']  > 0)
				//   YES - we do have some metrics for the series.
	{
	if($metricCOLtoSORTby <= 0) $metricCOLtoSORTby = 1;

				//   Step #2: Build query to get and organize the
				//detailed metric values such that we can display
				//them into a table.
	$qry = metrics_BuildValueQuery($MetricMeta, $metricCOLtoSORTby);

				//   Step #3: Build the display table.
	metrics_BuildMetricTable($MetricMeta, $numMetrics, $qry, $seriesLongName, 'XSMALL');
				
				//   If there are Announcements to display, show them.
	foreach($MetricMeta as $key => $value)
		{
		if (strlen($MetricMeta[$key]['Announce']) > 3)
			{
			echo "<P style='font-size:x-small; margin-top:3px; margin-bottom:3px'><strong>{$MetricMeta[$key]['Name']}</strong> ({$MetricMeta[$key]['ShtName']}): ";
			echo "{$MetricMeta[$key]['Announce']}</P>";
			}
		}
	}

else
				//   NO - We don't have any metrics for the series.
	{
		echo "<P>There are no metrics defined for the series sepecified.</P>";
	}

echo "</DIV>";

} // End the IF statement used to control display of metrics table, or not.
?>
	




<DIV id="thispg_announce">

	<p><font color="#306030">
		A group of pretty die-hard tennis players in (mostly) Huntersville, NC.
		Arnold Fox and Jeff Rocchio are the leaders for this group.
	</font>
	</p>

	<p><b>RECREATIONAL PLAYING (Summer Hours):</b>
	<br>&nbsp;&nbsp;&nbsp;* Thursday evenings, 6:00pm at Holbrook Park.
	<br>&nbsp;&nbsp;&nbsp;* Saturday mornings, 8:00am at Bailey Road Park.
	<br>&nbsp;&nbsp;&nbsp;* Click link below for playing schedule and logistical details:
	<br>&nbsp;&nbsp;&nbsp;* <b><a href="./tennis/listSeriesRoster.php?ID=1">
			RECREATIONAL PLAYING SCHEDULE</a></b>
	</p>

	<p><b>SPRING 4.0 LEAGUE</b>
	<br>&nbsp;&nbsp;&nbsp;* Men 4.0 USTA League on Saturday mornings.
	<br>&nbsp;&nbsp;&nbsp;* Mike-N and Ken-S are captains.
	<br>&nbsp;&nbsp;&nbsp;* Click link below for roster and matches.
	<br>&nbsp;&nbsp;&nbsp;* <b><a href="./tennis/listSeriesRoster.php?ID=13">
			4.0 SPRING LEAGUE</a></b>
	</p>
	
	<p><b>SPRING 3.5 SENIORS LEAGUE</b>
	<br>&nbsp;&nbsp;&nbsp;* Men 3.5 Seniors USTA League on Wednesday Evenings.
	<br>&nbsp;&nbsp;&nbsp;* Rich-F is captain.
	<br>&nbsp;&nbsp;&nbsp;* Click link below for roster and matches.
	<br>&nbsp;&nbsp;&nbsp;* <b><a href="./tennis/listSeriesRoster.php?ID=14">
			3.5 SENIORS SPRING LEAGUE</a></b>
	</p>
	
	<p><b>SPRING 3.5 ADULT LEAGUE</b>
	<br>&nbsp;&nbsp;&nbsp;* Men 3.5 Adult on Saturday mornings.
	<br>&nbsp;&nbsp;&nbsp;* Arnold-F and Ken-S are coordinating.
	<br>&nbsp;&nbsp;&nbsp;* Click link below for roster and matches.
	<br>&nbsp;&nbsp;&nbsp;* <b><a href="./tennis/listSeriesRoster.php?ID=12">
			3.5 ADULT LEAGUE</a></b>
	</p>
	<p>
			<font size="-1">
			<a href="http://weather.weatherbug.com/NC/Huntersville-weather.html?zcode=z5602">
			Official Temp</a> // 
			<a href="http://weather.weatherbug.com/NC/Huntersville-weather/local-radar/doppler-radar.html?zcode=z5602">
			Huntersville Weather Radar</a> // 
			<a href="http://weather.weatherbug.com/NC/Huntersville-weather/local-forecast/7-day-forecast.html?zcode=z5602&zip=28078">Forecast</a> //
			<a href="http://www.corneliuspd.org/more_.html?sid=5647">Cornelius Web Cam</a> //
			<a href="http://www.huntersville.org/parksrec_2.asp#5">Holbrook Park</a>
			</font>
	</p>

<hr noshade="noshade" size="5">
		<h2 CLASS=fntNormal>Notes and Comments:</h2>

		<p>
			<b>PLAYING TIMES AND COURT LOCATIONS:</b> Our "home court" is Holbrook park.
			However, under some circumstances we may divert to an alternate location -
			usually Bailey Road Park. This may happen if the courts at Holbrook are full
			or if there are matches scheduled at Holbrook.
			We send out reminder emails for the recreational/practice playing. The 
			reminder emails will show the playing location planned as of
			Wednesday night. You can also display
			the <a href="./tennis/listRecPlay.php?ID=1">
			RECREATIONAL PLAYING SCHEDULE</a> to see dates, times and
			locations.
		</p>

		<p>
			<b>RATING:</b> We are are a group of 3.5 level players, with some
			close to or at 4.0 (see Self Rating Guidelines link below).
		</p>
		
								 
		<p>
			<b>COMPETITION:</b> Each spring and fall we form a team to play in the
			<a href="http://national.usta.com/">USTA Leagues</a>.
			But not everyone plays on those teams.
			Some of us just play for the fun of it without ever playing
			competitively on a team.
		</p>
		
		<p>
			<b>JOINING US:</b> If you want to come play with us, just come out to
			Holbrook Park during one of our playing times.
			Ask for the "Arnold Fox" or "Jeff Rocchio" group.
			We're a friendly bunch of guys, we'll do intros and you'll play with us.
			OR you can email Jeff Rocchio (see contact link at bottom of this page)
			to make arrangements and confirm playing days and times.
			We'll put you on our email list.
			We send out a weekly email to remind everyone of the playing schedule for
			that week, and we ask for an RSVP confirmation of your intent to play that
			week. That's about it. Plain and simple.
		</p>
		


</DIV>


<DIV id="thispg_maincontent">

		<hr noshade="noshade" size="5">

		<h2 CLASS=fntNormal>Useful Links</h2>


		<table id="Table3" border="0" cellpadding="3" cellspacing="1" width="100%">

				<tbody><tr>
					<td align="right" valign="bottom" width="65">
						</td>

					<td align="left" bgcolor="#dcdcdc" valign="bottom" width="40%"><font size="+1"> <strong>Link</strong>
						</font>
						</td>

					<td align="left" bgcolor="#dcdcdc" valign="bottom"><font size="+1"> <strong>Description</strong>
						</font>
						</td>
				</tr>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="./tennis/listPerson_PhoneList.php">
						Club Phone List</a>
						</td>

					<td align="left" valign="top"><strong>Club Phone List.</strong><br>
						This link will show a page that lists all our club members
						and their phone numbers.
						You have to be a club member to view this list.<br>
						</td>
				</tr>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://laketennis.com/tennis/files/guide_pros.html">
						Tennis Instructors</a>
						</td>

					<td align="left" valign="top"><strong>Tennis Instructors.</strong><BR />
						Information on local tennis pros. Use this reference list if
						you are looking to get formal lessions to improve your game or if
						you are interested in forming a multi-player clinic.<BR />
						</td>
				</tr>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://laketennis.com/tennis/listSeries.php">
						League History</a>
						</td>

					<td align="left" valign="top"><strong>League History.</strong><br>
						This link will display a list of all the series and leagues
						we have participated in, with links to their roster-grids.<br>
						</td>
				</tr>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top">
						<a href="http://national.usta.com/">Tennis Link</a>
						</td>

					<td align="left" valign="top"><strong>Tennis Link.</strong><br>
						The "My Teams" feature allows you to quickly track the activity of your 
						favorite teams without having to enter the team number every time you visit the 
						site. Add as many teams as you wish. Click on Help for instructions.<br>
						</td>
				</tr>

				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://nctennis.com/">North Carolina USTA Page</a>
						</td>

					<td align="left" valign="top"><strong>NC Tennis Web site.</strong><br>
						Home page for North Carolina chapter of the USTA.<br>
						</td>
				</tr>

				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.lakenormantennis.org">Lake Norman Tennis Home</a>
						</td>

					<td align="left" valign="top"><strong>Lake Norman Tennis Association.</strong><br>
						Home page for Lake Norman tennis. Ladders, tournaments, etc.<br>
						</td>
				</tr>

			<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.usta.com/leagues/custom.sps?iType=931&amp;icustompageid=1655">
						Self-Rating Guidelines</a>
						</td>

					<td align="left" valign="top"><strong>General Characteristics of
						Various NTRP Playing Levels.</strong><br>
						The page on USTA site that contain the guidelines for how to rate yourself.<br>
						</td>

			</tr>

			<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.usta.com/leagues/custom.sps?iType=931&amp;icustompageid=6250">
						Self-Rating Process</a>
						</td>

					<td align="left" valign="top"><strong>When and how do I get a
						SELF-RATING?</strong><br>
						The page on USTA site that contains a description of how to self-rate.<br>
						</td>
			</tr>

			<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.usta.com/leagues/custom.sps?iType=931&amp;icustompageid=6250">
						Become USTA Member</a>
						</td>

					<td align="left" valign="top"><strong>How do I get a USTA Number?</strong><br>
						The page on USTA site that allows you to sign up to become a USTA member.
						You must be a USTA member to register to play on a team.
						</td>
			</tr>

		</tbody></table>

		<hr noshade="noshade" size="5">

		<h2 CLASS=fntNormal>Useful Files</h2>

		<table id="Table1" border="0" cellpadding="3" cellspacing="3" width="100%">

			<tbody><tr>
				<td align="right" valign="bottom" width="65">
					</td>

				<td align="left" bgcolor="#dcdcdc" valign="bottom"><font size="+1"> <strong>File Name</strong>
					</font>
					</td>

				<td align="left" bgcolor="#dcdcdc" valign="bottom"><font size="+1"> <strong>Description</strong>
					</font>
					</td>
			</tr>

			<tr>
				<td align="right" valign="top" width="65">
					&nbsp;
					</td>

				<td align="left" valign="top">
					<a href="http://laketennis.com/tennis/files/DirectionsToMatchSites.doc">DirectionsToMatchSites.doc</a>
					</td>

				<td align="left" valign="top">Directions to Match Sites
					</td>
			</tr>

			<tr>
				<td align="right" valign="top" width="65">
					&nbsp;
					</td>

				<td align="left" valign="top">
					<a href="http://laketennis.com/tennis/files/TennisRules.PDF">TennisRules.pdf</a>
					</td>

				<td align="left" valign="top">Tennis Rules
					</td>
			</tr>

		</tbody></table>

		<hr noshade="noshade" size="5">

		<p><font size="-1">
			<strong>Contact: </strong>
			<a href="mailto:tennis@activeage.com">Jeffrey Rocchio</a>
			(tennis@activeage.com)
		</font></p>

</DIV>

</body></html>