<?php
/*
	This script allows the admin to edit an existing
	event record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


//$DEBUG = TRUE;
$DEBUG = FALSE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'Event';
$row = '';



//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Event Selected.</P>";
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
	


//----FETCH THE RECORD TO EDIT------------------------------------------>
if(!Tennis_GetSingleRecord($row, $tblName, $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET USER EDIT RIGHTS---------------------------------------------->
$userPrivEvt='GST';
if ($_SESSION['clbmgr']==True) { $userPrivEvt='ADM'; }
else
	{
	$tmp=Session_GetAuthority(42, $row['Series']);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPrivEvt='ADM'; }
	else { $userPrivEvt=Session_GetAuthority(43, $recID); }
	}



//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Edit Event {$row['Name']}";
$pgL1 = "Edit Record";
$pgL2 = "EVENT";
$pgL3 = $row['Name'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);



//----EVENT-TABLE EDIT SECTION OF THE PAGE------------------------------->
				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
echo "<form method='post' action='editGeneric_post.php'>";

echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";

echo "<input type=hidden name=meta_TBL value={$tblName}>";

echo "<input type=hidden name=ID value={$row['ID']}>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";

				//   Record ID.
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>ID</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= $row['ID'];
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;

				//   Event Name.
$fldLabel = "Event Name";
$fldHelp = "Enter a brief name for the event. IF this event represents a match ";
$fldHelp .= "in a league season, enter just the opponent's name ";
$fldHelp .= "for the Event Name.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'Name', 100, 65, $row['Name'], 'ADM', $userPrivEvt);
echo $rowHTML;

				//   Series drop-down.
$fldLabel = "Series";
$fldHelp = "What series-of-events does this specific event belong to? For ";
$fldHelp .= "example, a USTA league season would be a series of events. ";
$fldHelp .= "Select the appropriate series from the drop-down menu. ";
$fldHelp .= "(NOTE: Requires club Administrator rights to edit.)";
$rowHTML = ADMIN_GenFieldDropTbl($fldLabel, $fldHelp, 'Series', 'series', 0, $row['Series'], '', 'XXX', $userPrivEvt);
echo $rowHTML;

				//   Purpose drop-down.
$fldLabel = "Purpose";
$fldHelp = "Select the purpose of this event. If you are entering events ";
$fldHelp .= "for a USTA league season, you can still create non-match ";
$fldHelp .= "events for things ";
$fldHelp .= "like practice sessions, post-match get-togethers, etc.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Purpose', 2, $row['Purpose'], '', 'ADM', $userPrivEvt);
echo $rowHTML;

				//   Venue drop-down.
$fldLabel = "Venue";
$fldHelp = "Select where this event will occur. ";
$fldHelp .= "For example, for matches choose where the match is to ";
$fldHelp .= "be played.";
$rowHTML = ADMIN_GenFieldDropTbl($fldLabel, $fldHelp, 'Venue', 'venue', 0, $row['Venue'], '', 'MGR', $userPrivEvt);
echo $rowHTML;

				//   Start Date/Time.
$fldLabel = "Start Date & Time";
$fldHelp = "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= " For example, March 15, 2008 at 2:30pm must be entered as: ";
$fldHelp .= "2008-03-15 14:30.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'Start', 20, 20, $row['Start'], 'MGR', $userPrivEvt);
echo $rowHTML;

				//   End Date/Time.
$fldLabel = "End Date & Time";
$fldHelp = "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= " For example, March 15, 2008 at 2:30pm must be entered as: ";
$fldHelp .= "2008-03-15 14:30.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'End', 20, 20, $row['End'], 'MGR', $userPrivEvt);
echo $rowHTML;


				//   Make-up Event?
$fldLabel = "Make Up Event?";
$fldHelp = "Check the box to indicate this is a Make-Up event. ";
$fldHelp .= "If you are changing an original event to now be a Make-Up ";
$fldHelp .= "event it is good practice to note the original event's ";
$fldHelp .= "information (original dates, etc) in the Note field. MAKEUP= {$row['MakeUp']}";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'MakeUp', $row['MakeUp'], 'MGR', $userPrivEvt);
echo $rowHTML;



				//   Display Code drop-down.
$fldLabel = "Display Rule";
$fldHelp = "Select the appropriate display rule for this event. 'Normal' means ";
$fldHelp .= "the event will be displayed using the rules and formulas defined ";
$fldHelp .= "for the view it appears in ";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Display', 6, $row['Display'], '', 'MGR', $userPrivEvt);
echo $rowHTML;

				//   Result Code drop-down.
$fldLabel = "Event Result";
$fldHelp = "Select the appropriate result of this event. ";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'ResultCode', 7, $row['ResultCode'], '', 'MGR', $userPrivEvt);
echo $rowHTML;

				//   Results Notes.
$fldLabel = "Result Notes";
$fldHelp = "If you wish you can record any relevant results ";
$fldHelp .= "from this event in this note field.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Results', 5, 65, $row['Results'], 'MGR', $userPrivEvt);
echo $rowHTML;

				//   Notes.
$fldLabel = "General Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this event in this field.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Notes', 5, 65, $row['Notes'], 'MGR', $userPrivEvt);
echo $rowHTML;


echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";

echo "</P>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";


//----BOTTOM OF PAGE ACTION-LINKS---------------------------------------->
$rowHTML = "<P><A HREF='{$rtnpg}'>RETURN</A>";

				//   If current user has rights to manage this event, give them
				//some additional options.
if ($userPrivEvt=='MGR' or $userPrivEvt=='ADM' or $userPrivEvt=='SADM')
	{
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp";
	$rowHTML .= "<A HREF='editEvent_Delete.php?EID={$recID}'>";
	$rowHTML .= "DELETE Event</A>";
	}

$rowHTML .= "</P>{$CRLF}";
echo $rowHTML;


//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "editEvent.php?ID={$recID}");


?> 
