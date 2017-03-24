<?php
/*
	This script adds a new metric record.
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
$tblName = 'metric';



//----GET URL QUERY-STRING DATA----------------------------------------->
$seriesID = $_GET['ID'];
if (!$seriesID) $seriesID = 3;



//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET USER EDIT RIGHTS---------------------------------------------->
$userPrivEvt='GST';
if ($_SESSION['evtmgr']==True) { $userPrivEvt='ADM'; }
else
	{
	$tmp=Session_GetAuthority(42, $seriesID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPrivEvt='ADM'; }
	}


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Metric";
$pgL1 = "ADD New Record";
$pgL2 = "";
$pgL3 = "ADD METRIC";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);







//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPrivEvt<>'MGR' and $userPrivEvt<>'ADM')
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	if ($DEBUG) echo "<P>Your User Rights are: {$userPrivEvt}</P>";
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
echo "<input type=hidden name=meta_ADDPG value=addMetric.php>";

echo "<input type=hidden name=meta_UserRecID value={$_SESSION['recID']}>";
echo "<input type=hidden name=meta_UserID value={$_SESSION['userID']}>";

echo "<input type=hidden name=ID value=0>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";


				//   Series drop-down.
$fldLabel = "Series";
$fldHelp = "What series-of-events does this specific metric belong to? For ";
$fldHelp .= "example, a USTA league season would be a series of events. ";
$fldHelp .= "Select the appropriate series from the drop-down menue.";
$fldSpecStr = Tennis_GenLBoxSeries('Series', $seriesID);
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Full Name.
$fldLabel = "Metric Name";
$fldHelp = "Enter a brief name for the metric.";
$fldSpecStr = "<INPUT TYPE=text NAME=Name ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Short Name.
$fldLabel = "Metric Short Name";
$fldHelp = "Enter 3-10 character name for the metric that can be ";
$fldHelp .= "used as a column header in when displaying the metric ";
$fldHelp .= "values in a table.";
$fldSpecStr = "<INPUT TYPE=text NAME=ShtName ";
$fldSpecStr .= "SIZE=10 MAXLENGTH=10 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Sort.
$fldLabel = "Metric Sort Order";
$fldHelp = "Enter a 5 character string which will be used to determine ";
$fldHelp .= "the order the metric will be displayed in when more than ";
$fldHelp .= "one metric is displayed.";
$fldSpecStr = "<INPUT TYPE=text NAME=Sort ";
$fldSpecStr .= "SIZE=5 MAXLENGTH=5 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Value-Type drop-down.
$fldLabel = "Value Type";
$fldHelp = "What math value-type is this metric? ";
$fldHelp .= "Select the appropriate type from the drop-down menue.";
$fldSpecStr = Tennis_GenLBoxCodeSet('ValType', 11, 51);
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Sort values in descending order instead of ascending?
$fldLabel = "Sort Descending?";
$fldHelp = "Check YES if the values for this metric should be sorted ";
$fldHelp .= "in descending order instead of the normal ascending.";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'member', 'SortDesc', 0);
echo $rowHTML;

				//   Display Code drop-down.
$fldLabel = "Display Rule";
$fldHelp = "Select the appropriate display rule for this event. 'Normal' means ";
$fldHelp .= "the event will be displayed using the rules and formulas defined ";
$fldHelp .= "for the view it appears in ";
$fldSpecStr = Tennis_GenLBoxCodeSet('Display', 6, 31);
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Describe the metric here.";
$fldSpecStr = "<TEXTAREA NAME=Description ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Announcement.
$fldLabel = "Announcement";
$fldHelp = "Use this text field to post a timely announcement concerning ";
$fldHelp .= "the metric.";
$fldSpecStr = "<TEXTAREA NAME=Announcement ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;


echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";



//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addEvent.php");

?> 
