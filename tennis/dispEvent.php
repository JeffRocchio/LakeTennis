<?php
/*
	This script displays a single Event record.
	
	02/06/2009:	Added security levels per Ken Sussewell request to allow
					club admin to control who can see the grid. This is controlled
					by setting the newly added ViewLevel series field value
					(new code-set #13).

	03/16/2008:	Removed the table-within-a-table in the Lineup
					section. So changed from a 2-column table to a
					3-column table. Thus changed the colspan values.
					Also made player names links into their RSVP
					detail records so their contact info can be more
					easily accessed.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_Roster.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------------->
				//   Declare array to hold the detail display
				//record.
$row = array();
				//   Name of the query to use to fetch the
				//detail record.
$tblName = 'qryEventDisp';

					//   Holds the series ID.
$seriesID = '';


					//   The current user's edit rights on the current series.
					//Initial default value is "Guest."
					//Note that declare of the rights[] array is for compatability
					//with the Roster_GetUserRights() function and is not
					//currently used in this script; this array approach is for
					//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');


					//   Holds Message to display when user does not have view
					//rights to this page.
$noViewMessageTxt = "<P>You do not have permission to view this page.";
$noViewMessageTxt .= " If you believe you are supposed to have the ability";
$noViewMessageTxt .= " to view this page, please contact your Series or";
$noViewMessageTxt .= " Club administrator.";
$noViewMessageTxt .= "</P>";



//=== BEGIN CODE =============================================================>
//============================================================================>


//----GET URL-QUERY-STRING-DATA----------------------------------------------->
				//   Get the Event ID from the query string. If
				//empty, report error and do nothing.
if (!array_key_exists('ID', $_GET))
	{
	echo "<P>ERROR, No Event Specified in Query String.</P>";
	include './INCL_footer.php';
	exit;
	}
$recID = $_GET['ID'];



//----SET RETURN PAGE FOR EDITS----------------------------------------------->
$_SESSION['RtnPg'] = "dispEvent.php?ID={$recID}";



//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----FETCH THE EVENT RECORD-------------------------------------------------->
$testResult = Tennis_GetSingleRecord($row, $tblName, $recID);
if (!$testResult)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
$seriesID = $row['seriesID'];
$ViewLevel = $row['seriesViewLevel'];


//----GET USER RIGHTS--------------------------------------------------------->
Roster_GetUserRights($seriesID, $ViewLevel, "dispEvent", $rights);
$userPrivSeries = $rights['view'];


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Tennis: {$row['evtName']} Details";
$pgL1 = "Event Details";
$pgL2 = $row['seriesName'];
$pgL3 = $row['evtName'];
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----ASSESS VIEW RIGHTS TO DETERMINE WHAT TO SHOW---------------------------->
					//   Determine if the user has view rights to this page.
					//If not, inform them of this fact and end the script.
if ($userPrivSeries=='NON')
	{
	echo $noViewMessageTxt;
	$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
	echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
	echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
	exit;
	}



//----DISPLAY EVENT DETAILS IN STD RECORD-DETAIL DISPLAY FORMAT--------------->
$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";

				//   Section Title - LOGISTICS.
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='3'><P CLASS='ddSectionTitle'>LOGISTICS</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Event Start</P></TD>{$CRLF}";
$dispdate = Tennis_DisplayDate($row['evtStart']);
$disptime = Tennis_DisplayTime($row['evtStart'], TRUE);
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$dispdate} @ {$disptime}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Estimated End</P></TD>{$CRLF}";
$dispdate = Tennis_DisplayDate($row['evtEnd']);
$disptime = Tennis_DisplayTime($row['evtEnd'], TRUE);
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$dispdate} @ {$disptime}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Venue</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$row['venueName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

				//   Section Title - PURPOSE.
//$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
//$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>PURPOSE</P></TD>{$CRLF}";
//$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Purpose</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$row['purposeName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Makeup Event?</P></TD>{$CRLF}";
if ($row['evtMakeUp'] == 0)
	{
	$tmp = 'NO';
	}
	else
	{
	$tmp = 'YES';
	} 
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Event Notes</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldDataLong'>{$row['evtNotes']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

echo $out;

				//   Section Title - Lineup.
/*
03/16/2008:	Redesigned this section to remove the table-within-a-table
				design. Trying to make it more friendly to handhelds.

$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>LINEUP";
if ($_SESSION['evtmgr'] == TRUE)
	{
	$out .= " // <a href='editLineup.php?ID={$recID}'>EDIT LINEUP</a>";
	}
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Player Assignments</P></TD>{$CRLF}";
//$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'>{$CRLF}";
//$out .= "<TABLE CLASS='ddTable' CELLSPACING='0' border='0'><TR CLASS='ddTblRow'>";
$out .= "<TABLE CELLSPACING='0' border='0' width=100%'><TR CLASS='ddTblRow'>";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName' ALIGN='left'>Playing</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'  ALIGN='left'>Available to Play or Sub</P></TD>{$CRLF}";
$out .= "</TR><TR CLASS='ddTblRow'>{$CRLF}";
$tmp = local_ListNames($recID, 'PLAYING');
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
$tmp = local_ListNames($recID, 'AVAIL');
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
$out .= "</TR></TABLE>{$CRLF}";
*/
				//   Section Title - Lineup.
				//03/16/2008: This is the redesigned section, removing the
				//table-within-a-table design.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='3'><P CLASS='ddSectionTitle'>LINEUP";
if ($_SESSION['evtmgr'] == TRUE)
	{
	$out .= " // <a href='editLineup.php?ID={$recID}'>EDIT LINEUP</a>";
	}
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Player Assignments</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName' ALIGN='left'>Playing</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'  ALIGN='left'>Available to Play or Sub</P></TD>{$CRLF}";
$out .= "</TR>";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>&nbsp;</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'>";
$tmp = local_ListNames($recID, 'PLAYING');
$out .= "<P CLASS='ddFieldData'>{$tmp}{$CRLF}";
$out .= "</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'>";
$tmp = local_ListNames($recID, 'AVAIL');
$out .= "<P CLASS='ddFieldData'>{$tmp}{$CRLF}";
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
echo $out;
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Make Email Lists</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
$out .= "<A HREF=\"listEmails.php?OBJ=EVENT&ID={$recID}&SCOPE=PLAY\">Email All Playing</A>{$CRLF}";
$out .= "</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
$out .= "<A HREF=\"listEmails.php?OBJ=EVENT&ID={$recID}&SCOPE=AVAIL\">Email Available to Play</A>{$CRLF}";
$out .= "</P></TD>{$CRLF}";
echo $out;


				//   Section Title - RESULTS.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='3'><P CLASS='ddSectionTitle'>RESULTS</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Event Result</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>{$row['resultLgName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Result Notes</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldDataLong'>{$row['evtResults']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

echo $out;

				//   Venue details.

				//   Section Title - VENUE DETAILS.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='3'><P CLASS='ddSectionTitle'>VENUE DETAILS</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Venue Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldData'>";
$out .= "<a href='{$row['venueURL']}'>{$row['venueName']}</a>";
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Location</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldDataLong'>{$row['venueLoc']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Venue Description</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldDataLong'>{$row['venueDesc']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Venue Notes</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay' COLSPAN='3'><P CLASS='ddFieldDataLong'>{$row['venueNotes']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "</TABLE>";
echo $out;

					//   Make links for the Admin/EventManager.
if ($_SESSION['evtmgr'] == True)
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listMatch_text.php?ID={$recID}\">Make Confirm Email</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listEmails.php?OBJ=SERIES&ID={$row['seriesID']}\">Make Email Address List</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"editEvent.php?ID={$recID}\">Edit Event</A></P>{$CRLF}";
	}



if ($DEBUG)
	{
				//   Display the raw data.
	$outHTML = "<P>IN DEBUG MODE -- RAW DATA FROM ROW:</P>{$CRLF}";
	echo $outHTML;	echo "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=2>";
	foreach ($row as $key => $value)
		{
		$tblRow = "<TR class=ddTblRow>";
		$tblRow .= "<TD class=ddTblCellLabel>";
		$tblRow .= "<P class=ddFieldName>{$key}</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "<TD class=ddTblCellLabel>";
		$tblRow .= "<P class=ddFieldName>&nbsp;&nbsp;&nbsp;</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "<TD class=ddTblCellInput>";
		$tblRow .= "<P class=ddFieldData>{$value}</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "</TR>{$CRLF}";
		echo $tblRow;
		}
	echo "</TABLE>";
	}


echo  Tennis_BuildFooter("NORM", "dispEvent.php?ID={$recID}");





function local_ListNames($eventID, $group)
	{

	global $CRLF;

	$numResponses = 0;
	$qryResult = local_getRSVPSet($eventID, $group);
	$row = mysql_fetch_array($qryResult);
	$plyrAssigns = '';
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			if ($_SESSION['member'])
				{
				$rsvpID = $row['rsvpID'];
				$playerName = "<A HREF=\"dispRSVP.php?ID={$rsvpID}&FORMAT=NOTE\">{$row['prsnFullName']}</A>";
				}
			else
				{
				$playerName = $row['prsnPName'];
				}
			if ($group == 'PLAYING')
				{
				$plyrAssigns .= "{$row['rsvpPosition']}: {$playerName}";
				}
			else
				{
				$plyrAssigns .= "{$playerName}";
				}
			if ($row['rsvpClaimCode'] <> 15)
				{
				$plyrAssigns .= "&nbsp;({$row['rsvpClaim']})";
				}
			$plyrAssigns .= "<BR>{$CRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	

	if ($numResponses == 0)
		{
		if ($group == 'PLAYING')
			{
			$plyrAssigns .= "*** NO PLAYING ASSIGNMENTS MADE ***{$CRLF}";
			}
		else
			{
			$plyrAssigns .= "*** NO SUBS AVAILABLE ***{$CRLF}";
			}
		}
	
	$plyrAssigns .= "</P>{$CRLF}{$CRLF}";
	return $plyrAssigns;

}


function local_getRSVPSet($eventID, $subset)
	{

	global $CRLF;

	switch ($subset)
		{
		case 'AVAIL':
			$selCrit = "(rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16) AND (rsvpPositionCode=28 OR rsvpPositionCode=30 OR rsvpPositionCode=27)"; // ="Available"
			break;
		
		default:
			//$selCrit = "(rsvpPositionCode<>28 AND rsvpPositionCode<>30 AND rsvpPositionCode<>27)"; // ="Playing"
			$selCrit = "(rsvpPositionCode<>28 AND rsvpPositionCode<>30)"; // ="Playing"
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
