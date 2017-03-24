<?php
/*
	This script allows editing of an existing
	textBlock record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>

global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'txtBlock';
$recID = 0;
$row = '';



//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Text Block Selected.</P>";
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



//----GET USER EDIT RIGHTS----------------------------------------------------->
				//   As of 2/26/2012 only Jeff Rocchio is permitted to edit
				//a txtBlock record.
				//   Determining edit rights on a text block could be a challenge
				//as it is really determined by the context in which the block
				//is used. E.g., if the block is used for a series, then the
				//series managers should be able to edit. So this could be a bit
				//dicy. I may have to add a field to the txtBlock table that
				//specifies a "context" value. This field would have to contain
				//a value from codeset #9 (object types). This could then be
				//used to join with authority records to determine rights.
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='ADM'; } // Superuser.


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Edit Text Block {$row['BlockTitle']}";
$pgL1 = "Edit Record";
$pgL2 = "Text Bock";
$pgL3 = $row['BlockTitle'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);



//----EDIT SECTION OF THE PAGE------------------------------------------------->
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

				//   Block Title.
$fldLabel = "Block Title";
$fldHelp = "Enter a brief title for the text block.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'BlockTitle', 200, 65, $row['BlockTitle'], 'MGR', $userPriv);
echo $rowHTML;

				//   Block Usage.
$fldLabel = "Block Usage";
$fldHelp = "Briefly state intended use for the text block (250 characters or less).";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'BlockUsage', 5, 65, $row['BlockUsage'], 'MGR', $userPriv);
echo $rowHTML;

				//   Block Active?
$fldLabel = "Block Active?";
$fldHelp = "If you select 'No' then the text block will not be displayed,";
$fldHelp .= " even if the effective end date has not yet expired.";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'Active', $row['Active'], 'MGR', $userPriv);
echo $rowHTML;

				//   Block Effective Start Date/Time.
$fldLabel = "Effective Start Date & Time";
$fldHelp = "Date/Time when the text block may be displayed.<BR />";
$fldHelp .= "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR />";
$fldHelp .= "For example, March 15, 2012 at 2:30pm must be entered as:";
$fldHelp .= " 2012-03-15 14:30.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'EffStart', 20, 20, $row['EffStart'], 'MGR', $userPriv);
echo $rowHTML;

				//   Block Effective End Date/Time.
$fldLabel = "Effective End Date & Time";
$fldHelp = "Date/Time when the text block should stop being displayed.<BR />";
$fldHelp .= "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= "For example, March 15, 2012 at 2:30pm must be entered as: ";
$fldHelp .= "2012-03-15 14:30.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'EffEnd', 20, 20, $row['EffEnd'], 'MGR', $userPriv);
echo $rowHTML;

				//   Block Text.
$fldLabel = "Block Text";
$fldHelp = "Enter the actual text for the block.";
$fldHelp .= " All text blocks are presumed to be in HTML format.";
$fldHelp .= " (when displayed, the text block will be enclosed within";
$fldHelp .= "DIV or P tags; but will need to use BR or P tags to do line breaks";
$fldHelp .= "within your text.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'BlockText', 15, 65, $row['BlockText'], 'MGR', $userPriv);
echo $rowHTML;




echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";

echo "</P>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";


//----BOTTOM OF PAGE ACTION-LINKS---------------------------------------->
$rowHTML = "<P><A HREF='{$rtnpg}'>RETURN</A>";

				//   If current user has rights to manage this club, give them
				//some additional options.
/*
if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
	{
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp";
	$rowHTML .= "<A HREF='editEvent_Delete.php?EID={$recID}'>";
	$rowHTML .= "DELETE Event</A>";
	}

$rowHTML .= "</P>{$CRLF}";
echo $rowHTML;
*/

//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "editTextBlock.php?ID={$recID}");


?> 
