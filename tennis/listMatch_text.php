<?php
/*
	This script builds the weekly RSVP email message for
	practice sessions.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



//$DEBUG = TRUE;
$DEBUG = FALSE;



//----Declare the global variables----------------------------------------------
global $CRLF;
$lstErrExist = FALSE;
$lstErrMsg = "";


//----Declare local variables---------------------------------------------------
$tblName = 'qryRsvp';
$out = "";
				
				
				//   Declare array to hold the detail display
				//record.
$row = array();
$recID = array();

				//   Used to manage error handling within the script.
$localERR = 0;
$localERRMsg = "";

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
				//   Determine which event records to list.
				//Either the IDs were passed in via query
				//sting in the URL, or else the query string
				//only specified how many of the next scheduled
				//events to list for.
if (array_key_exists('ID', $_GET))
	{
	if ($_GET['ID'] > 0)
		{
		$recID[1] = $_GET['ID'];
		$byRecID = True;
		if ($recID[1] < 1)
			{
			$localERR = 1;
			$localERRMsg = "ERROR {$localERR}: No valid event selected to list.";
			}
						//   Two other events we can build the list for.
		if ($_GET['ID2'] > 0) $recID[2] = $_GET['ID2'];
		if ($_GET['ID3'] > 0) $recID[3] = $_GET['ID3'];
		}
	}
elseif (array_key_exists('NUM', $_GET))
	{
	if ($_GET['NUM'] > 0)
		{
		$NumEvts = $_GET['NUM'];
		$seriesID = $_GET['SID'];
		if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'FUT'))
			{
			$localERR = 2;
			$localERRMsg = "ERROR {$localERR}: Invalid query sent to server.<BR />{$lstErrMsg}";
			}
		for ($i=1; $i<=$NumEvts; $i++)
			{
			$row = mysql_fetch_array($qryResult);
			$recID[$i] = $row['evtID'];
			}
					//   If using the NUM / SID method, but no FUTURE events exist for the
					//series, this check will detect that and display an appropriate
					//message.
		if ($recID[1] < 1)
			{
			$localERR = 3;
			$localERRMsg = "No eligible events selected, unable to build player list.";
			}
		}
	}


				//   Output page header stuff.
$tbar = "Match Playing Lineup";
$pgL1 = "Event Details";
$pgL2 = "";
$pgL3 = "Current Lineup for Match";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

$out .= "<P><b>Please be sure to be on-site 1/2 hour prior to the official start time shown below.</b>";
$out .= "</P>{$CRLF}";
echo $out;

				//   If an error occurred in finding a valid event to list display
				//the error message.
	if ($localERR > 0)
		{
		echo "<P>{$localERRMsg}</P>";
		include './INCL_footer.php';
		exit;
		}


foreach ($recID as $curEvtID)
	{
				//   Get Event Record(s).
	if(!Tennis_GetSingleRecord($row, 'qryEventDisp', $curEvtID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
				//   Get the Series ID so we can use it later.
	$seriesID = $row['seriesID'];
				//   Build the list - in plain-text form.
	$dispDate = Tennis_DisplayDate($row['evtStart']);
	$dispTime = Tennis_DisplayTime($row['evtStart'], True);
	$dispVenue = $row['venueShtName'];
	$dispEvtName = $row['evtName'];
	$dispTitle = "SERIES: {$row['seriesName']}<BR>";
	$dispTitle .= "MATCH: <A HREF='dispEvent.php?ID={$curEvtID}'>{$dispDate} @ {$dispTime} - {$dispEvtName}</A>";
	$dispTitle .= ", at {$dispVenue}:<BR>";
	local_ListNames($dispTitle, $curEvtID);
	}


$out = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listSeriesRoster.php?ID={$seriesID}'>Full Schedule</A><BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php?ID={$seriesID}'>League Notes</A><BR>{$CRLF}";
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] > 0)
	{
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
	}
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>Club Home Page</A><BR>{$CRLF}";
$out .= "<BR>&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF=\"listSeries_Emails.php?ID={$seriesID}\">Make Email Address List</A><BR>{$CRLF}";
$out .= "</P>{$CRLF}";
echo $out;






echo  Tennis_BuildFooter('NORM', "listMatch_text.php?ID={$seriesID}");



//---FUNCTIONS ----------------------------------------------------------------

function local_ListNames($title, $eventID)
	{
	
	global $CRLF;
	
	$numResponses = 0;
	$out = "<P>$title<BR>{$CRLF}";
	$qryResult = local_getRSVPSet($eventID, 'PLAYING');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			if ($_SESSION['member'])
				{
				$playerName = $row['prsnFullName'];
				}
				else
				{
				$playerName = $row['prsnPName'];
				}
			$out .= "&nbsp;&nbsp;&nbsp;{$row['rsvpPosition']}: {$playerName}<BR>{$CRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	

	if ($numResponses == 0)
		{
		$out .= "&nbsp;&nbsp;&nbsp;*** NO PLAYING ASSIGNMENTS MADE ***{$CRLF}";
		}
	
	$out .= "<BR></P>{$CRLF}{$CRLF}";
	echo $out;

}


function local_getRSVPSet($eventID, $subset)
	{
	switch ($subset)
		{
		case 'TENT':
			$selCrit = "rsvpClaimCode=14"; // ="Tentative"
			break;
		
		case 'LATE':
			$selCrit = "rsvpClaimCode=13"; // ="Late"
			break;
		
		default:
			$selCrit = "rsvpPositionCode<>30"; // ="Playing"
		}
	
	if(!$qryResult = Tennis_OpenViewGeneric('qrySeriesRsvps', "WHERE (evtID={$eventID} AND {$selCrit})", "ORDER BY rsvpPositionSort"))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
	return $qryResult;
}


?> 
