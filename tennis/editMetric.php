<?php
/*
	This script allows the admin to edit an existing
	metric record.
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

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'metric';
$row = '';

				//   Get the query-string data.
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
	

				//   Fetch the record to edit.
if(!Tennis_GetSingleRecord($row, $tblName, $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
				//   Fetch the series name.
if(!Tennis_GetSingleRecord($Seriesrow, 'series', $row['Series']))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

				//   Output page header stuff.
$tbar = "Edit Metric {$row['Name']}";
$pgL1 = "Edit Metric";
$pgL2 = "For Series {$Seriesrow['ShtName']}";
$pgL3 = $row['Name'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);

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

				//   Series.(Can't change the series from here due to wierd
				//things that would happen to the value associative records.)
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>Series</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= $Seriesrow['ShtName'];
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;

				//   Name.
$fldLabel = "Metric Name";
$fldHelp = "Enter a brief name for the metric.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'evtmgr', 'Name', 100, 65, $row['Name']);
echo $rowHTML;

				//   Announcement.
$fldLabel = "Announcement";
$fldHelp = "Use this field to publish timely announcements concerning ";
$fldHelp .= "this metric.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'member', 'Announcement', 5, 65, $row['Announcement']);
echo $rowHTML;


				//   Description.
$fldLabel = "Metric Description and Notes";
$fldHelp = "Provide a detailed description of the metric here.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'member', 'Description', 5, 65, $row['Description']);
echo $rowHTML;

				//   Short Name.
$fldLabel = "Metric Short Name";
$fldHelp = "Enter 3-10 character name for the metric that can be ";
$fldHelp .= "used as a column header in when displaying the metric ";
$fldHelp .= "values in a table.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'member', 'ShtName', 100, 65, $row['ShtName']);
echo $rowHTML;

				//   Sort.
$fldLabel = "Metric Sort Order";
$fldHelp = "Enter a 5 character string which will be used to determine ";
$fldHelp .= "the order the metric will be displayed in when more than ";
$fldHelp .= "one metric is displayed.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'evtmgr', 'Sort', 10, 10, $row['Sort']);
echo $rowHTML;

				//   Display Code drop-down.
$fldLabel = "Display Rule";
$fldHelp = "Select the appropriate display rule for this event. 'Normal' means ";
$fldHelp .= "the event will be displayed using the rules and formulas defined ";
$fldHelp .= "for the view it appears in ";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'member', 'Display', 6, $row['Display'], '');
echo $rowHTML;

				//   Value-Type drop-down.
$fldLabel = "Value Type";
$fldHelp = "What math value-type is this metric?";
$fldHelp .= "Select the appropriate series from the drop-down box.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'member', 'ValType', 11, $row['ValType'], '');
echo $rowHTML;

				//   Sort values in descending order instead of ascending?
$fldLabel = "Sort Descending?";
$fldHelp = "Check YES if the values for this metric should be sorted ";
$fldHelp .= "in descending order instead of the normal ascending.";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'member', 'SortDesc', $row['SortDesc']);
echo $rowHTML;

echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$rtnpg}'>RETURN</A>";
echo "</P>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";

echo  Tennis_BuildFooter('ADMIN', "editMetric.php?ID={$recID}");


?> 
