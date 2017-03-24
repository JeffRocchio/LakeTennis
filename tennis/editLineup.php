<?php
/*
	This script allows the event manager to edit RSVP records to
	build the lineup for a match.
------------------------------------------------------------------ */
session_start();
set_include_path("/tennis");
include_once('INCL_Tennis_CONSTANTS.php');
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


//$DEBUG = TRUE;
$DEBUG = FALSE;


				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'rsvp';
$row = array();
$eventInfo = array();
$lBoxList = array();

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



if ((array_key_exists('meta_POST', $_POST)) && ($_POST['meta_POST'] == 'TRUE'))
				//   We're saving the records, not displaying the form.
	{
				//   Output page header stuff.
				//Feb-10-2008: Obsolete, using the ADMIN_Post_HeaderOK function.
/*
	$tbar = "Post Lineup";
	$pgL1 = "Edit Records";
	$pgL2 = "Lineup";
	$pgL3 = "Save Lineup Changes";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
*/
				//   Save the updates to the DB.
	$recordsUpdated = Tennis_dbLineupUpdate();
	if ($recordsUpdated==0)
		{
		$message = "{$GLOBALS['lstErrMsg']}";
		}

	$message = "{$recordsUpdated} Records Updated.";
//	$rtnpg = $_SESSION['RtnPg'];
	$rtnpg = "editLineup.php?ID={$_POST['ID']}";;
	echo ADMIN_Post_HeaderOK($tblName, $rtnpg, $message);
	}


else
					//   We're not saving records, we're displaying
					//the data-entry form.
	{
	$recID = $_GET['ID'];
	if (!$recID)
		{
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
		}
	
		
					//   Fetch the event's info.
	if(!Tennis_GetSingleRecord($eventInfo, 'Event', $recID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
		
	
	
	
	
					//   Output page header stuff.
	$tbar = "Edit Lineup";
	$pgL1 = "Edit Records";
	$pgL2 = "Lineup";
	$pgL3 = "For Event: {$eventInfo['Name']}";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	
					//   Create a form.
					//Also need to create two hidden fields to hold
					//the database and table name to pass to the
					//page we're going to post the data to.
	echo "<form method='post' action='editLineup.php'>";
	
	echo "<input type=hidden name=meta_POST value=TRUE>";
	
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	
	echo "<input type=hidden name=meta_TBL value={$tblName}>";
	
	echo "<input type=hidden name=ID value={$recID}>";
	
	echo "<table border='1' CELLPADDING='3' rules='rows'>";
	
					//   Record ID.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>Event ID</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= $eventInfo['ID'];
	$rowHTML .= "</P></TD></TR>";
	echo $rowHTML;
	
					//   Event Name.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>Event Name</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= $eventInfo['Name'];
	$rowHTML .= "</P></TD></TR>{$CRLF}";
	echo $rowHTML;
	
	
					//   List of currently playing and currently not-playing
					//people, along with a drop-down field to allow the event-manager
					//to change the playing status and position.
	
					//   Create a new section within the table to list
					//the players.
	$fldLabel = "Current Lineup For This Event";
	$rowHTML = "<TR CLASS='deTblRow'>{$CRLF}";
	$rowHTML .= "<TD CLASS='deTblCellSectiontitle' COLSPAN='2'>";
	$rowHTML .= "<P CLASS='deSectionTitle'>{$fldLabel}</P>{$CRLF}";
	$rowHTML .= "</TD></TR>{$CRLF}";
	echo $rowHTML;
	
					//   Create a cell to hold the player list, and
					//put a new table within that cell to control
					//columns.
	$fldLabel = "Lineup";
	$fldHelp = "Select position from the drop-down boxes as desired.";
	$fldHelp .= "<BR>Click SAVE when done making your changes.";
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput>";
	$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
	
	$rowHTML .= "<table border='0' CELLPADDING='4' rules='cols' width='100%'>";
	echo $rowHTML;
	
				//   List the currently playing folks in the
				//left cell of the inner table.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput>";
	$rowHTML .= "<P class=deFieldInput>{$CRLF}";
	local_ConfigLBox($lBoxList);
	$rowHTML .= local_ListNames($lBoxList, $recID, 'PLAYING');
	$rowHTML .= "</P></TD>{$CRLF}";
				//   List the not-currently playing folks in the
				//right cell of the inner table.
	$rowHTML .= "<TD class=deTblCellInput>";
	$rowHTML .= "<P class=deFieldInput>";
	$rowHTML .= local_ListNames($lBoxList, $recID, 'NP');
	$rowHTML .= "</P></TD>{$CRLF}";
				//   Close out the inner table's row.
	$rowHTML .= "</TR>{$CRLF}";
	echo $rowHTML;
	
				//   Close out the innter table.
	echo "</table>{$CRLF}";
				//   Make the form's Save button.
	echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='SAVE'>";
				//   Close out the outer table and the form.
	echo "</table>{$CRLF}";
	echo "</form>{$CRLF}";
	
	
	
	$rowHTML = "<P><A HREF='{$rtnpg}'>RETURN</A>";
	$rowHTML .= "</P>{$CRLF}";
	echo $rowHTML;
	} // Bottom of else clause for form-display section.

echo  Tennis_BuildFooter('ADMIN', "editLineup.php?ID={$recID}");




//=============================================================================

function local_ConfigLBox(&$lBoxList)
	{

	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

	$query = "SELECT Code.ID AS codeID, ";
	$query .= "Code.ShtName AS codeName ";
	$query .= "FROM Code ";
	$query .= "WHERE Code.fkCodeSet=5 ";
	$query .= "ORDER BY Code.Sort;";
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		echo "ERROR: ";
		echo '<P>Invalid query: ' . mysql_error() . "<\p>";
		echo '<P>Query Sent: ' . $query . "</P>";
		return False;
		}
	
	while ($row = mysql_fetch_array($qryResult))
		{
		$lBoxList[$row['codeID']][] = $row['codeID'];
		$lBoxList[$row['codeID']][] = $row['codeName'];
		}
	
	return True;


} //END FUNCTION



function local_GenLBox(&$lBoxList, $name, $defaultKey)
	{

	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

	$listBox = "<SELECT name={$name}>";
	foreach ($lBoxList as $v)
		{
		if ($v[0] == $defaultKey)
			{
			$listBox .= '<OPTION SELECTED value ="';
			}
		else
			{
			$listBox .= '<OPTION value ="';
			}
		$listBox .=$v[0];
		$listBox .= '">';
		$listBox .= $v[1];
		$listBox .= "</OPTION>{$CRLF}";
		}
	$listBox .= "</SELECT>{$CRLF}";
	
	return $listBox;


} //END FUNCTION





function local_ListNames(&$lBoxList, $eventID, $group)
	{
	$numResponses = 0;
	$qryResult = local_getRSVPSet($eventID, $group);
	$row = mysql_fetch_array($qryResult);
	$plyrAssigns = '';
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$plyrAssigns .= local_GenLBox($lBoxList, "xPOS{$row['rsvpID']}", $row['rsvpPositionCode']);
			if ($_SESSION['member'])
				{
				$playerName = $row['prsnFullName'];
				}
			else
				{
				$playerName = $row['prsnPName'];
				}
			$plyrAssigns .= "&nbsp;{$playerName} ({$row['rsvpClaim']})<BR>";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}

	if ($numResponses == 0)
		{
		if ($group == 'PLAYING')
			{
			$plyrAssigns .= "*** NO PLAYING ASSIGNMENTS MADE ***" . LF;
			}
		else
			{
			$plyrAssigns .= "*** NO PLAYERS AVAILABLE ***" . LF;
			}
		}
	
	return $plyrAssigns;

}


function local_getRSVPSet($eventID, $subset)
	{
	switch ($subset)
		{
		case 'NP':
			$selCrit = "(rsvpPositionCode=28 OR rsvpPositionCode=30 OR rsvpPositionCode=27)"; // ="Available"
			break;
		
		case 'LATE':
			$selCrit = "rsvpClaimCode=13"; // ="Late"
			break;
		
		default:
			$selCrit = "(rsvpPositionCode<>28 AND rsvpPositionCode<>30 AND rsvpPositionCode<>27)"; // ="Playing"
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
