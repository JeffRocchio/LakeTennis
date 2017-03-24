<?php
/*
	This script allows the admin to edit an existing venue
	record.
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
$tblName = 'venue';
$row = '';

//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Venue Selected.</P>";
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
if ($_SESSION['admin']==True) { $userPriv='ADM'; } // Superuser.


//----OUTPUT PAGE HEADER ----------------------------------------------->
$tbar = "Edit Venue {$row['Name']}";
$pgL1 = "Edit Venue";
$pgL2 = "";
$pgL3 = $row['LongName'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'ADM')
	{
	echo "<P>You are Not Authorized to Edit Venues.<BR><BR>";
	echo "(Because Venues are shared across Clubs, only the site ";
	echo "administrator can add or edit Venues. Contact the site ";
	echo "administrator if you need to add or modify any Venues.</P>";
	echo "<P>Your User Rights on this Page Are: {$userPrivEvt}</P>";
	include './INCL_footer.php';
	exit;
	}

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

				//   Sort.
$fldLabel = "Sort";
$fldHelp = "Defines an alternative sort order for listing venues (5 characters).";
$fldSpecStr = "<INPUT TYPE=text NAME=Sort ";
$fldSpecStr .= "SIZE=5 MAXLENGTH=5 ";
$fldSpecStr .= "VALUE='{$row['Sort']}'>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Short Name.
$fldLabel = "Series Short Name";
$fldHelp = "REQUIRED. Short reference name for the series. (15 characters).";
$fldSpecStr = "<INPUT TYPE=text NAME=ShtName ";
$fldSpecStr .= "SIZE=15 MAXLENGTH=15 ";
$fldSpecStr .= "VALUE='{$row['ShtName']}'>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Long Name.
$fldLabel = "Series Long Name";
$fldHelp = "REQUIRED. Descriptive name for the venue (150 characters).";
$fldSpecStr = "<INPUT TYPE=text NAME=LongName ";
$fldSpecStr .= "SIZE=65 MAXLENGTH=150 ";
$fldSpecStr .= "VALUE='{$row['LongName']}'>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Location.
$fldLabel = "Location";
$fldHelp = "Detailed description of the venue location. Including driving directions.";
$fldSpecStr = "<TEXTAREA NAME=Location ROWS=5 COLS=65>";
$fldSpecStr .= "{$row['Location']}";
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Detailed description of the venue.";
$fldSpecStr = "<TEXTAREA NAME=Description ROWS=5 COLS=65>";
$fldSpecStr .= "{$row['Description']}";
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   URL.
$fldLabel = "Related URL";
$fldHelp = "Reference URL to a web-site associated with the venue.";
$fldSpecStr = "<INPUT TYPE=text NAME=URL ";
$fldSpecStr .= "SIZE=65 MAXLENGTH=255 ";
$fldSpecStr .= "VALUE='{$row['URL']}'>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Notes.
$fldLabel = "Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this venue in this field.";
$fldSpecStr = "<TEXTAREA NAME=Notes ROWS=5 COLS=65>";
$fldSpecStr .= "{$row['Notes']}";
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;


echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";

echo  Tennis_BuildFooter('ADMIN', "editVenue.php?ID={$recID}");


?> 
