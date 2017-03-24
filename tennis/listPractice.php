<?php
/*
	This script lists ....
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



//$DEBUG = TRUE;
$DEBUG = FALSE;


$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'qryRsvp';
				//   Declare array to hold the detail display
				//record.
array($row);



$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Event Selected.</P>";
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
	
				//   Open table.
if(!$qryResult = Tennis_OpenViewGeneric($tblName, "WHERE (evtID={$recID} AND rsvpPosition='P')", "ORDER BY prsnPName"))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

				//   Output page header stuff.
$tbar = "Practice RSVPs";
$pgL1 = "List Practice RSVPs";
$pgL2 = "";
$pgL3 = "For Event: ({$recID})";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);



				//   Build the list.
				//   Display the event details in standard
				//record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Playing</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Tentative</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Not Available</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>No Response</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>&nbsp;</P></TD>{$CRLF}";
$out .= "</TR></THEAD>{$CRLF}";
echo $out;
				
				//   Build table body.
$out = "<TBODY>{$CRLF}";
echo $out;
while ($row = mysql_fetch_array($qryResult))
	{
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnPName']}</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnPName']}</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnPName']}</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnPName']}</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='editPerson.php?ID={$row['ID']}&RTNPG=listPerson.php'>EDIT</A></P></TD>{$CRLF}";
	
	$out .= "</TR>{$CRLF}{$CRLF}";
	echo $out;
	}
$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
echo $out;

echo  Tennis_BuildFooter('NORM', "listPractice.php");
?> 
