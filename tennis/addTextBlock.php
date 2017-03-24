<?php
/*
	This script adds a new txtBlock record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();

$DEBUG = TRUE;
$DEBUG = FALSE;



//----DECLARE GLOBAL VARIABLES------------------------------------------>
global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$tblName = 'txtBlock';
$todayDateTime = date('Y-m-d H:i');



//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET USER EDIT RIGHTS---------------------------------------------->
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='ADM'; }


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Text Block";
$pgL1 = "ADD New Record";
$pgL2 = "";
$pgL3 = "ADD Text Block";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);





//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'ADM')
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	echo "<P>Your User Rights on this Page Are: {$userPrivEvt}</P>";
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
echo "<input type=hidden name=meta_ADDPG value=addTextBlock.php>";

echo "<input type=hidden name=ID value=0>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";


				//   Block Title.
$fldLabel = "Block Title";
$fldHelp = "Enter a brief title for the text block.";
$fldSpecStr = "<INPUT TYPE=text NAME=BlockTitle ";
$fldSpecStr .= "SIZE=65 MAXLENGTH=200 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Block Usage.
$fldLabel = "Block Usage";
$fldHelp = "Briefly state intended use for the text block (250 characters or less).";
$fldSpecStr = "<TEXTAREA NAME=BlockUsage ROWS=3 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Block Active?
$fldLabel = "Block Active?";
$fldSpecStr = "<INPUT TYPE=checkbox NAME=Active VALUE='1' CHECKED>";
$fldHelp = "If you select 'No' then the text block will not be displayed,";
$fldHelp .= " even if the effective end date has not yet expired.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Effective Start Date/Time.
$fldLabel = "Effective End Date & Time";
$fldHelp = "Date/Time when the text block should stop being displayed.<BR />";
$fldHelp .= "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= "For example, March 15, 2012 at 2:30pm must be entered as: ";
$fldHelp .= "2012-03-15 14:30.";
$fldSpecStr = "<INPUT TYPE=text NAME=EffStart ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='{$todayDateTime}'";
$fldSpecStr .= ">";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Effective End Date/Time.
$fldLabel = "Effective End Date & Time";
$fldHelp = "Date/Time when the text block should stop being displayed.<BR />";
$fldHelp .= "MUST BE ENTERED IN FORMAT: YYYY-MM-DD HH:MM<BR>";
$fldHelp .= "For example, March 15, 2012 at 2:30pm must be entered as: ";
$fldHelp .= "2012-03-15 14:30.";
$fldSpecStr = "<INPUT TYPE=text NAME=EffEnd ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='2099-12-31 11:59'";
$fldSpecStr .= ">";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Block Text.
$fldLabel = "Block Text";
$fldHelp = "Enter the actual text for the block.";
$fldHelp .= " All text blocks are presumed to be in HTML format.";
$fldHelp .= " (when displayed, the text block will be enclosed within";
$fldHelp .= "DIV or P tags; but will need to use BR or P tags to do line breaks";
$fldHelp .= "within your text.";
$fldSpecStr = "<TEXTAREA NAME=BlockText ROWS=15 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;




echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";



//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addTextBlock.php");

?> 
