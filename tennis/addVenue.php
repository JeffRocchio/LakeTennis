<?php
/*
	This script adds a new venue record.

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
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$tblName = 'venue';


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
if ($_SESSION['admin']==True) { $userPriv='ADM'; } // Superuser.



//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Venue";
$pgL1 = "ADD New Record";
$pgL2 = "";
$pgL3 = "ADD VENUE";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'ADM')
	{
	echo "<P>You are Not Authorized to add new Venues.<BR><BR>";
	echo "(Because Venues are shared across Clubs, only the site ";
	echo "administrator can add or edit Venues. Contact the site ";
	echo "administrator if you need to add or modify any Venues.</P>";
	echo "<P>Your User Rights on this Page Are: {$userPriv}</P>";
	echo  Tennis_BuildFooter('ADMIN', "addVenue");
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
echo "<input type=hidden name=meta_ADDPG value=addVenue.php>";

echo "<input type=hidden name=ID value=0>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";

				//   Venue Short Name.
$fldLabel = "Short Name";
$fldHelp = "Enter a brief name for the venue (15 characters or less).";
$fldSpecStr = "<INPUT TYPE=text NAME=ShtName ";
$fldSpecStr .= "SIZE=15 MAXLENGTH=15 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Venue Long Name.
$fldLabel = "Long Name";
$fldHelp = "Enter a longer, descriptive, name for the venue (150 characters or less).";
$fldSpecStr = "<INPUT TYPE=text NAME=LongName ";
$fldSpecStr .= "SIZE=65 MAXLENGTH=150 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Venue Sort.
$fldLabel = "Sort";
$fldHelp = "Defines the sort order when listing venues.";
$fldHelp .= " Enter 5-character or less text to define the sort order.";
$fldSpecStr = "<INPUT TYPE=text NAME=Sort ";
$fldSpecStr .= "SIZE=5 MAXLENGTH=5 ";
$fldSpecStr .= "VALUE='000'>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Venue URL.
$fldLabel = "Web Address";
$fldHelp = "If there is an appropriate web-site for this venue, enter that here.";
$fldSpecStr = "<INPUT TYPE=text NAME=URL ";
$fldSpecStr .= "SIZE=65 MAXLENGTH=255 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Location.
$fldLabel = "Location";
$fldHelp = "Physical location of the venue. Also include driving directions here. ";
$fldHelp .= "State the address first, then enter DIRECTIONS in all caps, then ";
$fldHelp .= "describe, step-by-step, how to get to the place.";
$fldSpecStr = "<TEXTAREA NAME=Location ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Description of the venue. E.g., facilities, # of courts, etc. Put whatever seems appropriate here. ";
$fldSpecStr = "<TEXTAREA NAME=Description ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;


				//   Notes.
$fldLabel = "General Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this event in this field.";
$fldSpecStr = "<TEXTAREA NAME=Notes ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
echo $rowHTML;


echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";

//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addVenue.php");

?> 
