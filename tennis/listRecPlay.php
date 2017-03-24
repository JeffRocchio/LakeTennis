<?php
/*
	   NOTE: 12/29/2006 - This script was obsoleted by the changes I made
	to the "listSeriesRoster.php" script. I am leaving this
	script on the server until I am sure that I have updated
	all my links to it.
	
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
include_once('./INCL_Roster.php');
Session_Initalize();


//$DEBUG = FALSE;
$DEBUG = TRUE;


					//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

$CRLF = "\n";

$hdrBuilt = FALSE;

					//   Query strings.
$seriesView = $_GET['VIEW'];
if (strlen($seriesView) == 0) $seriesView = 'ALL';
$seriesID = $_GET['ID'];
if (!$seriesID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
//added 7/30:
$seriesPhoneOFF = $_GET['PHOFF'];
if ($seriesPhoneOFF == 'Y')
	{
	$_SESSION['RSTR_PhListOff'] = TRUE;
	}
elseif ($seriesPhoneOFF == 'N')
	{
	$_SESSION['RSTR_PhListOff'] = FALSE;
	}
// 7/30 end

					//   Set the return page for edits.
$_SESSION['RtnPg'] = "listRecPlay.php?ID={$seriesID}&VIEW={$seriesView}";

					//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

					//   Open the Event-query so we can
					//build the heading row.
if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, $seriesView))
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
		switch ($seriesView)
			{
			case 'FUT':
				$pgL2 = "Future Events Only";
				break;
			
			case 'DON':
				$pgL2 = "Completed Events Only";
				break;
			
			default:
				$pgL2 = "All Events";
			}
		$pgL3 = "<a href='dispSeries.php?ID={$seriesID}'>{$row['seriesShtName']}</a>";
		if ($_SESSION['evtmgr'])
			{
			$pgL3 .= "&nbsp;&nbsp;<span style='font-size: x-small'>(";
			$pgL3 .= "<a href='editSeries.php?ID={$seriesID}'>";
			$pgL3 .= "EDIT</a>";
			$pgL3 .= ")</span>";
			}
		echo Tennis_BuildHeader('REC', $tbar, $pgL1, $pgL2, $pgL3);
		Roster_BuildEvtLableCells($row, $tblHdrArray, 'REC');
		$hdrBuilt = TRUE;
		} // End If
	Roster_BuildEvtCells($row, $tblHdrArray, 'REC', True);
	$iCols++;
	
	} // end while

					//   Open a table and output the table heading rows
					//that were built into an array using the above
					//while-loop.
echo Roster_TblOpen('REC');
echo Roster_TblHeadOutput($tblHdrArray, 'REC', True);
Roster_TblBodyOutput($seriesID, $seriesView);
echo Roster_TblClose();




					//   Make the table color-coding key.
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Color Scheme:{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
$tblRowStr = "<TABLE>";
$tblRowStr .= "<TR>";
$tblRowStr .= "<TD CLASS=rosterCellClear STYLE='border-top: 0; border-right: 0' COLSPAN='4'>";
$tblRowStr .= "<P CLASS=rosterPhone>Person Color-Coding:</P></TD>";
$tblRowStr .= "</TR>";
$tblRowStr .= "<TR>";
$tblRowStr .= "<TD CLASS=rosterUnknown>";
$tblRowStr .= "<P CLASS=rosterPhone>Not Heard From</P></TD>";
$tblRowStr .= "<TD CLASS=rosterCellPlay><P CLASS=rosterPhone>Committed to Play</P></TD>";
$tblRowStr .= "<TD CLASS=rosterCellTent><P CLASS=rosterPhone>Tentative or Late</P></TD>";
$tblRowStr .= "<TD CLASS=rosterCellNota><P CLASS=rosterPhone>Not Available</P></TD>";
$tblRowStr .= "</TR>";
$tblRowStr .= "</TABLE>";
echo $tblRowStr;
echo "</P>";


					//   Provide some alternative views
					//for the user.
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Alternative Views:<BR>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "*&nbsp;<A HREF=\"listRecPlay.php?ID={$seriesID}&VIEW=ALL\">All Events</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listRecPlay.php?ID={$seriesID}&VIEW=FUT\">Future Events Only</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listRecPlay.php?ID={$seriesID}&VIEW=DON\">Completed Events Only</A><BR><BR>{$CRLF}";
//added 12/17/2006:
echo "*&nbsp;<A HREF=\"dispMetricTable.php?ID={$seriesID}\">Show Standings and Metrics for This Series</A><BR><BR>{$CRLF}";
//added 7/30/2006:
if ($_SESSION['RSTR_PhListOff'] == FALSE)
	{
	echo "*&nbsp;<A HREF=\"listRecPlay.php?ID={$seriesID}&VIEW={$seriesView}&PHOFF=Y\">Phone Numbers Off</A><BR>{$CRLF}";
	}
else
	{
	echo "*&nbsp;<A HREF=\"listRecPlay.php?ID={$seriesID}&VIEW={$seriesView}&PHOFF=N\">Phone Numbers On</A><BR>{$CRLF}";
	}
echo "<BR>*&nbsp;<A HREF=\"listPractice_text.php?NUM=2&SID={$seriesID}\">Make Confirm Email</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeries_Emails.php?ID={$seriesID}\">Make Email Address List</A><BR>{$CRLF}";
// 7/30 end

					
					//   Make links for the Admin/EventManager.
if ($_SESSION['evtmgr'] == True)
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"editSeries.php?ID={$seriesID}\">Edit Series</A></P>{$CRLF}";
	}


echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);

?> 
