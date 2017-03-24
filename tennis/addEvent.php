<?php
/*
	This script allows the admin to add a new event record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();

$DEBUG = TRUE;
//$DEBUG = FALSE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID = $_SESSION['clubID'];
$tblName = 'Event';



//----GET URL QUERY-STRING DATA----------------------------------------->
$seriesID = $_GET['ID'];
if (!$seriesID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}



//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----FETCH THE SERIES RECORD------------------------------------------->
if(!Tennis_GetSingleRecord($seriesRow, "series", $seriesID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Event";
$pgL1 = "ADD New Record";
if ($DEBUG) $pgL1 .= " [Club: {$clubID}]";
$pgL2 = "For Series: {$seriesRow['LongName']}";
$pgL3 = "ADD EVENT";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


//----GET USER EDIT RIGHTS---------------------------------------------->
				//   Levels of rights on this page:
				//     1) SADMIN. System admin. Can do anything.
				//     2) OADMIN. Object admin. A person with ADM rights to
				//        the series.
				//     3) MANAGER. A person with MGR rights to the series.
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='SADM'; }
else
	{
	$tmp=Session_GetAuthority(55, $clubID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='ADM'; }
	else { $userPriv=Session_GetAuthority(42, $seriesID); }
	}


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'MGR' and $userPriv<>'ADM' and $userPriv<>'SADM')
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	if ($DEBUG) echo "<P>Your User Rights are: {$userPriv}</P>";
	include './INCL_footer.php';
	exit;
	}




//----BUILD ENTRY FORM--------------------------------------------------->

				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
echo "<form method='post' action='addGeneric_post.php'>";

echo "<input type=hidden name=meta_TBL value={$tblName}>";

echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
echo "<input type=hidden name=meta_ADDPG value=addEvent.php>";

echo "<input type=hidden name=ID value=0>";

echo "<input type=hidden name=Series value={$seriesID}>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";



				//   Series drop-down.
$fldLabel = "Series";
$fldHelp = "What series-of-events does this specific event belong to? For ";
$fldHelp .= "example, a USTA league season would be a series of events. ";
$fldHelp .= "Select the appropriate series from the drop-down menu. ";
$fldHelp .= "(NOTE: Requires club Administrator rights to edit.)";
$rowHTML = ADMIN_GenFieldDropTbl($fldLabel, $fldHelp, 'Series', 'Series', 0, $seriesID, FALSE, 'XXX', $userPriv);
echo $rowHTML;

				//   Event Name.
$fldLabel = "Event Name";
$fldHelp = "Enter a brief name for the event. IF this event represents a match ";
$fldHelp .= "in a league season, enter just the opponent's name ";
$fldHelp .= "for the Event Name.";
$fldSpecStr = "<INPUT TYPE=text NAME=Name ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Purpose drop-down.
$fldLabel = "Purpose";
$fldHelp = "Select the purpose of this event. If you are entering events ";
$fldHelp .= "for a USTA league season, you can still create non-match ";
$fldHelp .= "events for things ";
$fldHelp .= "like practice sessions, post-match get-togethers, etc.";
$fldSpecStr = Tennis_GenLBoxCodeSet('Purpose', 2, 17);
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Venue drop-down.
$fldLabel = "Venue";
$fldHelp = "Select where this event will occur. ";
$fldHelp .= "For example, for matches choose where the match is to ";
$fldHelp .= "be played.";
$fldSpecStr = Tennis_GenLBoxVenue('Venue', 1);
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Start Date/Time.
$fldLabel = "Start Date & Time";
$fldHelp = "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= " For example, March 15, 2006 at 2:30pm must be entered as: ";
$fldHelp .= "2006-03-15 14:30.";
$fldSpecStr = "<INPUT TYPE=text NAME=Start ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='2012-MM-DD 09:00'";
$fldSpecStr .= ">";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   End Date/Time.
$fldLabel = "End Date & Time";
$fldHelp = "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= " For example, March 15, 2006 at 2:30pm must be entered as: ";
$fldHelp .= "2006-03-15 14:30.";
$fldSpecStr = "<INPUT TYPE=text NAME=End ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='2012-MM-DD 09:00'";
$fldSpecStr .= ">";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;


				//   Make-up Event?
$fldLabel = "Make Up Event?";
$fldHelp = "Check the box to indicate this is a Make-Up event. ";
$fldHelp .= "If you are changing an original event to now be a Make-Up ";
$fldHelp .= "event it is good practice to note the original event's ";
$fldHelp .= "information (original dates, etc) in the Note field.";
$fldSpecStr = "<INPUT TYPE=checkbox NAME=MakeUp VALUE='0' UNCHECKED>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Display Code drop-down.
$fldLabel = "Display Rule";
$fldHelp = "Select the appropriate display rule for this event. 'Normal' means ";
$fldHelp .= "the event will be displayed using the rules and formulas defined ";
$fldHelp .= "for the view it appears in ";
$fldSpecStr = Tennis_GenLBoxCodeSet('Display', 6, 31);
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Result Code drop-down.
$fldLabel = "Event Result";
$fldHelp = "Select the appropriate result of this event. ";
$fldSpecStr = Tennis_GenLBoxCodeSet('ResultCode', 7, 34);
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Result Notes.
$fldLabel = "Result Notes";
$fldHelp = "If you wish you can record any relevenat results ";
$fldHelp .= "from this event in this note field.";
$fldSpecStr = "<TEXTAREA NAME=Results ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Notes.
$fldLabel = "General Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this event in this field.";
$fldSpecStr = "<TEXTAREA NAME=Notes ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;


echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";



//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addEvent.php");

?> 
