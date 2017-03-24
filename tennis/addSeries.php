<?php
/*
	This script allows the admin to add a new series record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();

				//   Instead of going back to the admin page upon posting,
				//let's take the user to the list of series for the club so
				//they can immediately go edit it.
//$rtnpg = Session_SetReturnPage();
$rtnpg = "listSeries.php";

$DEBUG = FALSE;
$DEBUG = TRUE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID = $_SESSION['clubID'];
$tblName = 'series';
array($row);
				//   Used as a dummy "current field value" to the function
				//calls that build the data-entry fields. These calls require
				//a variable-by-reference which contains the current value
				//in the record. As we are creating a new record,
				//we don't have existing data to pass, we're passing in
				//empty strings - or sometimes we use this to set a default
				//value for the field.
$defaultVal = '';




//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----FETCH THE CLUB RECORD----------------------------------------->
if(!Tennis_GetSingleRecord($row, "club", $clubID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "TENNIS - ADD New Series";
$pgL1 = "ADD New Record";
$pgL2 = "For Club: {$row['ClubName']}";
$pgL3 = "ADD SERIES";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


//----GET USER EDIT RIGHTS---------------------------------------------->
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='SADM'; } // Superuser.
elseif ($_SESSION['clbmgr']==True) { $userPriv='ADM'; } // Club Admin.


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'SADM' and $userPriv<>'ADM')
	{
	echo "<P>You are Not Authorized to add a new Series.</P>";
	if ($DEBUG) echo "<P>Your User Rights are: {$userPriv}</P>";
	echo Tennis_BuildFooter('ADMIN', "addSeries.php");
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
echo "<input type=hidden name=meta_ADDPG value=addSeries.php>";

echo "<input type=hidden name=ID value=0>";
echo "<input type=hidden name=ClubID value=$clubID>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";



				//   Club ID.
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>For Club</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= "{$row['ClubName']} (ID: {$row['ID']})";
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;


				//   Series Type drop-down.
$fldLabel = "Series Type";
$fldHelp = "REQUIRED. Select a type for this series - how it will be used. E.g., ";
$fldHelp .= "Recreational Play or League Play.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Type', 12, 54, FALSE, 'ADM', $userPriv);
echo $rowHTML;

				//   Series View Level Permission drop-down (code-set 13).
$fldLabel = "Series View Level";
$fldHelp = "REQUIRED. Select which level of user you must be in order";
$fldHelp .= " to view the series details";
$fldHelp .= ", including the roster-grid.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'ViewLevel', 13, 57, FALSE, 'ADM', $userPriv);
echo $rowHTML;

				//   Series Sort.
$fldLabel = "Sort";
$fldHelp = "Defines an alternative sort order for listing series (5 characters).";
$defaultVal = "0000";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'Sort', 5, 5, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   Series Short Name.
$fldLabel = "Series Short Name";
$fldHelp = "REQUIRED. Short reference name for the series. (30 characters).";
$defaultVal = "";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'ShtName', 30, 30, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   Series Long Name.
$fldLabel = "Series Long Name";
$fldHelp = "REQUIRED. Descriptive name for the series (150 characters).";
$defaultVal = "";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'LongName', 150, 65, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Detailed description of the series.";
$fldHelp = " (NOTE: You may use HTML formatting tags if you wish.)";
$defaultVal = "";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Description', 5, 65, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   URL.
$fldLabel = "Related URL";
$fldHelp = "Reference URL to a web-site associated with the series.";
$defaultVal = "";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'URL', 255, 65, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   Notes.
$fldLabel = "Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this series in this field.";
$defaultVal = "";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Notes', 5, 65, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;

				//   Events to Include In Reminder Email. This field contains the
				//number of events to include when generating reminder emails for
				//upcoming events in the series. Default is 1. A recreational
				//series where the group plays twice/week may wish to set this to 2.
$fldLabel = "Events in Reminder Emails";
$fldHelp = "The number of Events to include when user selects the";
$fldHelp .= " Make Confirm Email link at the bottom of the roster grid page";
$fldHelp .= " (1 digit number).";
$defaultVal = "1";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'EvtsIREmail', 1, 1, $defaultVal, 'ADM', $userPriv);
echo $rowHTML;



echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";



//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addSeries.php");

?> 
