<?php
/*
	This script adds a new club record.
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
$tblName = 'club';



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
if ($_SESSION['admin']==True) { $userPrivEvt='ADM'; }


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Club";
$pgL1 = "ADD New Record";
$pgL2 = "";
$pgL3 = "ADD CLUB";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);





//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPrivEvt<>'ADM')
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
echo "<input type=hidden name=meta_ADDPG value=addClub.php>";

echo "<input type=hidden name=meta_UserRecID value={$_SESSION['recID']}>";
echo "<input type=hidden name=meta_UserID value={$_SESSION['userID']}>";

echo "<input type=hidden name=ID value=0>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";


				//   Club Name.
$fldLabel = "Club Name";
$fldHelp = "Enter a name for the club.";
$fldSpecStr = "<INPUT TYPE=text NAME=ClubName ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Lobby Show.
$fldLabel = "Display In Lobby?";
$fldSpecStr = "<INPUT TYPE=checkbox NAME=LobbyShow VALUE='1' CHECKED>";
$fldHelp = "Be sure this box is checked to have your club listed on the ";
$fldHelp .= "site's main entry, or lobby page.";
$fldHelp .= " (This is where all clubs are shown so users can select which ";
$fldHelp .= "club to go into.)";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Lobby Blurb.
$fldLabel = "Lobby Blurb";
$fldHelp = "Enter a brief description for the club";
$fldHelp .= " to appear on the site's main home page where all";
$fldHelp .= " the clubs are listed.";
$fldHelp .= " This should help users determine which club is yours.";
$fldSpecStr = "<TEXTAREA NAME=LobbyBlurb ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Enter a detailed description or notes for the club.";
$fldHelp = " This will appear at the top of your club's home page.";
$fldSpecStr = "<TEXTAREA NAME=Descript ROWS=15 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Home Page Footer.
$fldLabel = "Home Page Footer";
$fldHelp = "Enter information you'd like to appear at the bottom";
$fldHelp .= " of your club's home page.";
$fldSpecStr = "<TEXTAREA NAME=HomePgFoot ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Club Active.
$fldLabel = "Club Status";
$fldSpecStr = "<INPUT TYPE=checkbox NAME=Active VALUE='1' CHECKED>";
$fldHelp = "Be sure this box is checked so that your club is active on the ";
$fldHelp .= "site.";
$fldHelp .= " Setting this to Inactive will eventually cause the club to ";
$fldHelp .= "be removed.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;



echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";



//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addClub.php");

?> 
