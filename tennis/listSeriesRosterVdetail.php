<?php
/*
	   This script displays a series roster. Meaning, each
	event in the series across columns with each eligible
	person in the series down the rows, and their RSVP
	records in the cells.
	   The series ID is assumed to be passed in via the
	query string.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');

Session_Initalize();


				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//$DEBUG = FALSE;
$DEBUG = TRUE;


$CRLF = "\n";


$hdrBuilt = FALSE;



				//   Get the series ID from the query string. If
				//empty, report error and do nothing.
$seriesID = $_GET['ID'];
if (!$seriesID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

				//   First build the table's header section by getting all the
				//events for the series.
				
				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Open the Event for this series query-result-set.
if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'ALL'))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
				
				//   Build the page header and the display table
				//heading row.
$iCols = 0;
while ($row = mysql_fetch_array($qryResult))
	{
	if ($hdrBuilt == FALSE)
		{
		$tbar = "Roster for {$row['seriesShtName']}";
		$pgL1 = "Roster for Series";
		$pgL2 = "";
		$pgL3 = "<a href='dispSeries.php?ID={$seriesID}'>{$row['seriesShtName']}</a>";
		echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
		echo "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=2>";
		$tblRowStr = "<THEAD>{$CRLF}";
		$tblRowStr .= "<TR>{$CRLF}";
		$tblRowStr .= "<TD valign=top align=right><P CLASS=evtDate>DATE:</P>";
		$tblRowStr .= "<P CLASS=evtTime>TIME:</P>";
		$tblRowStr .= "<P CLASS=evtOpponent>Opponent:</P>";
		$tblRowStr .= "<P CLASS=evtVenue>Venue:</P>";
		$tblRowStr .= "</TD>{$CRLF}";
		$hdrBuilt = TRUE;
		}
	$startDate = substr ($row['evtStart'], 5, 5);
	$startDate = substr_replace($startDate, "/", 2, 1);
	$startTime = substr ($row['evtStart'], 11, 5);
	$recID = $row['evtID'];
	$tblRowStr .= "<TD align='center' valign=top>";
	$tblRowStr .= "<P CLASS=evtDate>";
	$tblRowStr .= "<a href='dispEvent.php?ID={$recID}'>{$startDate}</a>";
	$tblRowStr .= "</P>";
	$tblRowStr .= "<P CLASS=evtTime>{$startTime}</P>";
	$tblRowStr .= "<P CLASS=evtOpponent>" . $row['evtName'] . "</P>";
	$tblRowStr .= "<P CLASS=evtVenue>" . $row['venueShtName'] . "</P>";
	if ($_SESSION['evtmgr'] == TRUE)
		{
		$tblRowStr .= "<P CLASS=cellEditLink><a href='editEvent.php?ID={$recID}'>EDIT</a></P>";
		}
	$tblRowStr .= "</TD>{$CRLF}";
	$iCols++;
	}	
$tblRowStr .= "</TR>{$CRLF}";
$tblRowStr .= "</THEAD>{$CRLF}";
echo $tblRowStr;
	

$i=1;

				//   Now get all the individual RSVP records and build that
				//table body.
if (!$qryResult = Tennis_SeriesRosterOpen($seriesID, 'ALL'))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
				
				//   Build the table body.
echo "<TBODY>{$CRLF}";
$row = mysql_fetch_array($qryResult);
do
	{
	$playerID = $row['prsnID'];
				//   Open the table-row.
	$tblRowStr = "<TR>{$CRLF}";
				//   Build the left-most column-cell, the person name cell.
	if ($_SESSION['member'] == TRUE)
		{
		$pName = $row['prsnFullName'];
		$pPhH = "h:{$row['prsnPhoneH']}";
		$pPhC = "c:{$row['prsnPhoneC']}";
		$pPhW = "w:{$row['prsnPhoneW']}";
		$tblRowStr .= "<TD class='rosterLable'><P CLASS='rosterFullName'>{$pName}</P>{$CRLF}";
		$tblRowStr .= "<P CLASS='rosterPhone'>$pPhH</P>";
		$tblRowStr .= "<P CLASS='rosterPhone'>$pPhC</P>";
		$tblRowStr .= "<P CLASS='rosterPhone'>$pPhW</P>";
		$tblRowStr .= "</TD>{$CRLF}";
		}
	else
		{
		$tblRowStr .= "<TD class='rosterLable'><P CLASS='rosterPublicName'>{$row['prsnPName']}</P></TD>{$CRLF}";
		}
	
				//   Now build the event cells stretching to the right.
	while ($playerID == $row['prsnID'])
		{
				//   Establish color highlighting based on availability.
		switch($row['rsvpClaim'])
			{
			case 'AVAIL':
			case 'CNFRM':
				$availColor = "green";
				break;
			
			case 'NORES':
			case 'TENT':
			case 'LATE':
				$availColor = "olive";
				break;
			
			case 'NOTAV':
				$availColor = "red";
				break;
			
			default:
				$availColor = "black";
			}
		$tblRowStr .= "<TD align=center>";
		switch($row['rsvpRole'])
			{
			case 'CAPTN':
				$tblRowStr .= "<P CLASS=rsvpRoleCaptain>CAPTAIN</P>";
				break;
			
			case 'COCAP':
				$tblRowStr .= "<P CLASS=rsvpRoleCoCaptain>CO-CAPTAIN</P>";
				break;
			
			default:
				$tblRowStr .= "<P CLASS=rsvpRoleOther>";
				$tblRowStr .= $row['rsvpRole'];
				$tblRowStr .= "</P>";
			}
		if ($row['rsvpPosition'] == 'NP')
			{
			$tblRowStr .= "<P CLASS=rsvpPosition><FONT color='silver'>";
			$tblRowStr .= $row['rsvpPosition'];
			$tblRowStr .= "</FONT></P>";
			}
		else
			{
			$tblRowStr .= "<P CLASS=rsvpPosition><FONT color='{$availColor}'>";
			$tblRowStr .= $row['rsvpPosition'];
			$tblRowStr .= "</FONT></P>";
			}
		$tblRowStr .= "<P CLASS=rsvpAvail><font size=-2 color='{$availColor}'>[";
		$tblRowStr .= $row['rsvpClaim'];
		$tblRowStr .= "]</font></P>";
		if ($_SESSION['evtmgr'] == TRUE)
			{
			$tblRowStr .= "<P CLASS=cellEditLink><a href='editRSVP.php?ID={$row['rsvpID']}'>EDIT</a></P>";
			}
		$tblRowStr .= "</TD>{$CRLF}";
		$row = mysql_fetch_array($qryResult);
		}
	$tblRowStr .= "</TR>{$CRLF}";
	echo $tblRowStr;
	} while ($row);


echo "</TBODY>{$CRLF}</TABLE>{$CRLF}";

echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Alternative Views:<BR>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=ALL\">All Events</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=FUT\">Future Events Only</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=DON\">Completed Events Only</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeriesRosterVdetail.php?ID={$seriesID}\">All RSVP Details</A></P>{$CRLF}";

echo  Tennis_BuildFooter('NORM', 'listSeriesRoster.php?ID=3');

?> 
