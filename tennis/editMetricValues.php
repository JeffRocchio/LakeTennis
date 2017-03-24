<?php
/*
	This script allows the metric manager to update the metric
	values.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
//$DEBUG = TRUE;

$CRLF = "\n";


				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'value';
array($row);
array($metricInfo);
array($lBoxList);

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



if ($_POST['meta_POST'] == 'TRUE')
				//   We're saving the records, not displaying the form.
	{
				//   Output page header stuff.
	$tbar = "Post Metric Values";
	$pgL1 = "Edit Records";
	$pgL2 = "Metric Values";
	$pgL3 = "Save Value Changes";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	
				//   Save the updates to the DB.
	$recordsUpdated = local_dbMetricValuesUpdate();
	if ($recordsUpdated==0)
		{
		echo "<P>{$GLOBALS['lstErrMsg']}</P>";
		}

	echo "<P>{$recordsUpdated} Records Updated.</P>";
	echo "<P>Click OK to continue.</P>";
	echo "<P STYLE='font-size: large'>";
	echo "<A HREF='editMetricValues.php?ID={$_POST['ID']}'>OK</A></P>";
	}


else
					//   We're not saving records, we're displaying
					//the data-entry form.
	{
	$recID = $_GET['ID'];
	if (!$recID)
		{
		echo "<P>ERROR, No Metric Selected.</P>";
		include './INCL_footer.php';
		exit;
		}
	
					//   Fetch the metric's info.
	if(!Tennis_GetSingleRecord($metricInfo, 'metric', $recID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
					//   Output page header stuff.
	$tbar = "Edit Metric Values";
	$pgL1 = "Edit Records";
	$pgL2 = "Metric Values";
	$pgL3 = "For Metric: {$metricInfo['Name']}";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	
					//   Create a form.
					//Also need to create two hidden fields to hold
					//the database and table name to pass to the
					//page we're going to post the data to.
	echo "<form method='post' action='editMetricValues.php'>";
	
	echo "<input type=hidden name=meta_POST value=TRUE>";
	
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	
	echo "<input type=hidden name=meta_TBL value={$tblName}>";
	
	echo "<input type=hidden name=ID value={$recID}>";
	
	echo "<table border='1' CELLPADDING='3' rules='rows'>";
	
					//   Record ID.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>Metric ID</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= $metricInfo['ID'];
	$rowHTML .= "</P></TD></TR>";
	echo $rowHTML;
	
					//   Metric Name.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>Metric Name</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= $metricInfo['Name'];
	$rowHTML .= "</P></TD></TR>{$CRLF}";
	echo $rowHTML;
	
	
					//   Metric Value-Type.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>Value Type</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= ADMIN_dbGetNameCode($metricInfo['ValType'],FALSE);
	$rowHTML .= "</P></TD></TR>{$CRLF}";
	echo $rowHTML;
	
	
					//   List of currently playing and currently not-playing
					//people, along with a drop-down field to allow the event-manager
					//to change the playing status and position.
	
					//   Create a new section within the table to list
					//the values.
	$fldLabel = "Metric Values";
	$rowHTML = "<TR CLASS='deTblRow'>{$CRLF}";
	$rowHTML .= "<TD CLASS='deTblCellSectiontitle' COLSPAN='2'>";
	$rowHTML .= "<P CLASS='deSectionTitle'>{$fldLabel}</P>{$CRLF}";
	$rowHTML .= "</TD></TR>{$CRLF}";
	echo $rowHTML;
	
					//   Create a cell to hold the player list, and
					//put a new table within that cell to control
					//columns.
	$fldLabel = "Values";
	$fldHelp = "Update values for each person as desired.";
	$fldHelp .= "<BR>Click SAVE when done making your changes.";
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput>";
	$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
	
	$rowHTML .= "<table border='0' CELLPADDING='4' rules='cols' width='100%'>";
	echo $rowHTML;
	
				//   List the value-person pairs.
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput>";
	$rowHTML .= "<P class=deFieldInput>{$CRLF}";
	$rowHTML .= local_ListNameValPairs($recID, $metricInfo['ValType']);
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

echo  Tennis_BuildFooter('ADMIN', "editMetricValues.php?ID={$recID}");




//=============================================================================

function local_ListNameValPairs($metricID, $ValueType)
	{
	$numResponses = 0;
	$HTMLString = "";
	$qryResult = local_getValueSet($metricID);
	$row = mysql_fetch_array($qryResult);
	do
		{
		//$HTMLString .= "[   ]";
		//$HTMLString .= ADMIN_GenFieldText($fldLabel, $fldHelp, 'evtmgr', 'Name', 100, 65, $row['Name']);
		$fldSpec = "<INPUT TYPE=text NAME=xPOS{$row['ID']} ";
		$fldSpec .= "SIZE=5 MAXLENGTH=20 ";
		$fldSpec .= "VALUE='{$row['Value']}'>{$CRLF}";
		$HTMLString .= $fldSpec;
		if ($ValueType==51)
			{
			$HTMLString .= " %";
			}
		if ($_SESSION['member'])
			{
			$playerName = $row['prsnFullName'];
			}
		else
			{
			$playerName = $row['prsnPName'];
			}
		$HTMLString .= "&nbsp;&nbsp;{$playerName}<BR>";
		$numResponses ++;
		}
	while ($row = mysql_fetch_array($qryResult));

	if ($numResponses == 0)
		{
		$HTMLString .= "*** NO PLAYERS ASSIGNED TO THIS METRIC ***{$CRLF}";
		}
	
	return $HTMLString;

}


function local_getValueSet($metricID)
	{
	if(!$qryResult = Tennis_OpenViewGeneric('qryValueDisp', "WHERE (metric={$metricID})", "ORDER BY Value"))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
	return $qryResult;
}





function local_dbMetricValuesUpdate()
	{
	/*
		This function updates multiple metric value records. This
		set of value records represents a metric for a given
		series.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) The updated data is contained in the global $_POST array,
		   as a result of an edit form having been posted to a
		   page which called this funtion.
		3) The ['key'] strings of $_POST array are of the form
			'xPOS#' where '#' is the value table record ID that is to
			be updated.
		5) ['key'] strings that begin with any string other
			than 'xPOS' are NOT value records to be updated
		   and should be ignored by this function.
	
	TAKES:
		1) nothing.
		
	RETURNS:
		1) The number of records inserted.

	*/
	
$DEBUG = FALSE;
//$DEBUG = TRUE;

	$ValtblName = 'value';
	$i=0;
	foreach ($_POST as $key => $field)
		{
		if (strpos($key, 'POS') == 1)
			{
			$ID = substr ($key, 4, strlen($key));
			$query = "UPDATE {$ValtblName} SET Value='{$field}' WHERE ID={$ID};";
			if ($DEBUG)
				{
				echo "<P>{$key} - ID: {$tmp} VALUE: {$field}</P>";
				echo "<P><b>query:</b><BR>{$query}";
				}
			$qryResult = mysql_query($query);
//			$qryResult = TRUE;
			if (!$qryResult)
				{
				$GLOBALS['lstErrExist'] = TRUE;
				$GLOBALS['lstErrMsg'] = "ERROR";
				$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
				$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent: ' . $query;
				$message = $GLOBALS['lstErrMsg'];
				return $i;
				}
			$i = $i + 1;
			}
		}
	return $i;

	
} // END FUNCTION




?> 
