<?php
/*
	This script displays an RSVP note.
	
	03/16/2008:	Added two rows to "NOTES" format view. (1) To display
					the person's phone numbers. (2) To display a link
					to the person's full detail record so that users
					can more easily get to other info about the person,
					esp their email addresses. These two additional
					rows will only appear if logged in as a group
					member.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
Session_Initalize();

//$DEBUG = TRUE;
$DEBUG = FALSE;

global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'rsvp';
$row = array();
				//   Get the query-string data.
$dispFormat = $_GET['FORMAT'];
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No RSVP Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
				//   Fetch the record.
if(!Tennis_GetSingleRecord($row, "qryRsvp", $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

				//   Make pretty date.
$date = Tennis_DisplayDate($row['evtStart']);
$time = Tennis_DisplayTime($row['evtStart'], TRUE);


				//   Get name-string to use for header.
$dispName = $row['prsnPName'];
if ($_SESSION['member'])
	{
	$dispName = $row['prsnFullName'];
	}
				//   Output page header stuff.
$tbar = "RSVP Detail for {$dispName}";
if ($dispFormat == 'NOTE') $tbar = "RSVP Notes for {$dispName}";
$tbar = "NOTE for {$dispName}";
$pgL1 = "RSVP Details";
if ($dispFormat == 'NOTE') $pgL1 = "RSVP Notes";
$pgL2 = "{$row['evtName']} on {$date}";
$pgL3 = "{$dispName}";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

				//   Display the event details in standard
				//record-detail-display format.
$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";
echo $out;

if ($dispFormat == 'FULL')
	{
				//   Display Record ID.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>RSVP ID</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ID']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

				//   Event.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Event</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['evtName']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

				//   Person.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Person</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$dispName}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

				//   Claim.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Availability</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['rsvpClaim']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

				//   Position.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Position</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['rsvpPosition']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

				//   Role.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Role</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['rsvpRole']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	echo $out;

	} // end if
				//   Notes.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Notes</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['rsvpNote']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

echo $out;

				//   Phone Numbers & Link to Person's Detail Record.
if ($_SESSION['member'])
	{
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "&nbsp;</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "</TR>{$CRLF}";
	
	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Phone</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'>";
	$out .= "<P CLASS='ddFieldData'>";
	$tmp = "H: {$row['prsnPhoneH']} <BR />";
	$tmp .= "C: {$row['prsnPhoneC']} <BR />";
	$tmp .= "W: {$row['prsnPhoneW']} <BR />";
	$out .= "{$tmp}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	
	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Detail</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'>";
	$out .= "<P CLASS='ddFieldData'>";
	$perID = $row['prsnID'];
	$tmp = "<A HREF=\"dispPerson.php?ID={$perID}&FORMAT=FULL\">{$row['prsnFullName']}</A>";
	$out .= "{$tmp}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	
	echo $out;
	}

$out = "</TABLE>";
echo $out;


echo  Tennis_BuildFooter("NORM", "dispRSVPnote.php?ID={$recID}");

?> 
