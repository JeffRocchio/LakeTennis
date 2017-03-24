<?php
/*
	This script displays a single txtBlock record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;

global $CRLF;



//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$qryResult;

$tblName = 'txtBlock';

			//   For building an output message to display as needed.
$message = "";

			//   True if the txtBlock record cannot be obtained.
$recordNotAvail = FALSE;

$row = array();
$tmp = "";



//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No item specified in query string.</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Set return page for edits.
$_SESSION['RtnPg'] = "dispTextBlock.php?ID={$recID}";



//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----GET USER EDIT RIGHTS----------------------------------------------------->
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='ADM'; }


//----SET INITIAL PAGE HEADER INFO--------------------------------------------->
$tbar = "Tennis - Display Text Block Details";
$pgL1 = "Display Record";
$pgL2 = "Text Block";

//----FETCH THE RECORD--------------------------------------------------------->
$result = Tennis_GetSingleRecord($row, $tblName, $recID);
if (!$result)
	{
	$recordNotAvail = TRUE;
	$message = "<P>This text block does not exist.</p>";
	$pgL3 = "Text Block Does Not Exist";	
	}
else
	{
	$recordNotAvail = FALSE;
	$pgL3 = $row['BlockTitle'];
	}


//----MAKE PAGE HEADER--------------------------------------------------------->
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------------->


//----DISPLAY RECORD----------------------------------------------------------->
if ($recordNotAvail == TRUE)
	{
	echo $message;
	}
else
	{
	$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";
	$out .= "<TBODY>{$CRLF}";
	echo $out;

					//   Section Title - Meta Data.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>META DATA</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>ID</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ID']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Block Title</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['BlockTitle']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Block Usage</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['BlockUsage']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$activeStatus = "INACTIVE";
	if ($row['Active'] == 1) $activeStatus = "ACTIVE";
	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Active Status</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$activeStatus}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Effective Start</P></TD>{$CRLF}";
	$dispdate = Tennis_DisplayDate($row['EffStart']);
	$disptime = Tennis_DisplayTime($row['EffStart'], TRUE);
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$dispdate} @ {$disptime}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Effective End</P></TD>{$CRLF}";
	$dispdate = Tennis_DisplayDate($row['EffEnd']);
	$disptime = Tennis_DisplayTime($row['EffEnd'], TRUE);
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$dispdate} @ {$disptime}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";


					//   Section Title - The Block's Content.
	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>CONTENT</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>The Block's Content</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['BlockText']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";
	
	$out .= "</TBODY></TABLE>{$CRLF}{$CRLF}";

	echo $out;
	}


//----MAKE NAVIGATION LINKS---------------------------------------------->
				//   Make an edit link for authorized users.
if(($userPriv=='ADM') and ($recordNotAvail == FALSE))
	{
	$out = "<P><A HREF='editTextBlock.php?ID={$recID}'>EDIT</A></P>{$CRLF}";
	echo $out;
	}


//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter("NORM", "dispTextBlock.php?ID={$recID}");

?> 
