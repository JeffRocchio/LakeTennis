<?php
/*
	This script allows the admin to add a new person record.
	
	05/02/2009: Modified for multi-clubs. In concert with modifications
	made to addGeneric_post.php this script was modified so that it also
	gets input for the ClubMember associative table and creates the
	"TBLT2" hidden meta-fields so the ClubMember record can be created by
	addGeneric_post.php.
----------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();

$DEBUG = TRUE;
//$DEBUG = FALSE;



//----DECLARE GLOBAL VARIABLES--------------------------------------------------
global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES---------------------------------------------------
$tblName = 'person';
$AssocTblName = "ClubMember";
$clubID = $_SESSION['clubID'];




//----CONNECT TO MYSQL----------------------------------------------------------
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET USER EDIT RIGHTS------------------------------------------------------
				//   There are 2-levels of user rights on this page.
				//      1) ADMIN. Can do anything. This is for system admin only.
				//      2) MANAGER. Can add a new person to their club only.
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='SADM'; } // Super User (Jeff-R)
else
	{
	$tmp=Session_GetAuthority(55, $clubID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; }
	}




//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "ADD New Person";
$pgL1 = "ADD New Record [Club ID: {$clubID}]";
$pgL2 = "";
$pgL3 = "ADD PERSON";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);



//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if(($userPriv<>'MGR') AND ($userPriv<>'SADM'))
	{
	echo "<P>You are Not Authorized to Add a New Person.</P>";
	echo "<P>Your User Rights on this Page Are: {$userPriv}</P>";
	include './INCL_footer.php';
	exit;
	}


//----BUILD ENTRY FORM--------------------------------------------------->
				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
echo "<form method='post' action='addGeneric_post.php'>{$CRLF}";

echo "<input type=hidden name=meta_TBL value={$tblName}>{$CRLF}";
echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";

echo "<input type=hidden name=meta_ADDPG value=addPerson.php>{$CRLF}";

echo "<input type=hidden name=ID value=0>{$CRLF}";

				//   Define parameters for the associative table, in this case
				//the ClubMember table that binds the person to a club.
echo "<input type=hidden name=meta_TBLT2_NAME value={$AssocTblName}>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID value=0>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_IDKNOWN_FLD value=Club>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_IDKNOWN_VAL value={$clubID}>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_IDUNKNOWN_FLD value=Person>{$CRLF}";

echo "<table border='1' CELLPADDING='3' rules='rows'>{$CRLF}";


				//   First Name.
$fldSpecStr = "<INPUT TYPE=text NAME=FName ";
$fldSpecStr .= "SIZE=30 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "First Name";
$fldHelp = "";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Last Name.
$fldSpecStr = "<INPUT TYPE=text NAME=LName ";
$fldSpecStr .= "SIZE=30 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "Last Name";
$fldHelp = "";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Public Name.
$fldSpecStr = "<INPUT TYPE=text NAME=PName ";
$fldSpecStr .= "SIZE=30 MAXLENGTH=100 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "Public Name";
$fldHelp = "Publicly viewable name of person. Used to be able to identify ";
$fldHelp .= "people in public views while also protecting privacy.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;


				//   User ID.
$fldSpecStr = "<INPUT TYPE=text NAME=UserID ";
$fldSpecStr .= "SIZE=30 MAXLENGTH=200 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "User Login ID";
$fldHelp = "User ID for login purposes.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   User Password.
$fldSpecStr = "<INPUT TYPE=text NAME=Pass ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "User Password";
$fldHelp = "User password for login purposes.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;


				//   Phone - HOME.
$fldSpecStr = "<INPUT TYPE=text NAME=PhoneH ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='704-###-####'>";
$fldLabel = "Home Phone";
$fldHelp = "Home phone number.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Phone - WORK.
$fldSpecStr = "<INPUT TYPE=text NAME=PhoneW ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='704-###-####'>";
$fldLabel = "Work Phone";
$fldHelp = "Work phone number.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Phone - CELL.
$fldSpecStr = "<INPUT TYPE=text NAME=PhoneC ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=20 ";
$fldSpecStr .= "VALUE='704-###-####'>";
$fldLabel = "Cell Phone";
$fldHelp = "Cell phone number.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Email #1.
$fldSpecStr = "<INPUT TYPE=text NAME=Email1 ";
$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
$fldSpecStr .= "VALUE=''>";
$fldSpecStr .= "&nbsp;&nbsp; <INPUT TYPE=checkbox NAME=Email1Active VALUE='1' CHECKED>";
$fldSpecStr .= "&nbsp;Active Email?";
$fldLabel = "Email #1";
$fldHelp = "You can register up to 3 email addresses to use for ";
$fldHelp .= "announcements and notifications. ";
$fldHelp .= "Each email address can be Activated and De-Activated ";
$fldHelp .= "independently as needed.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Email #2.
$fldSpecStr = "<INPUT TYPE=text NAME=Email2 ";
$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
$fldSpecStr .= "VALUE=''>";
$fldSpecStr .= "&nbsp;&nbsp; <INPUT TYPE=checkbox NAME=Email2Active VALUE='0' UNCHECKED>";
$fldSpecStr .= "&nbsp;Active Email?";
$fldLabel = "Email #2";
$fldHelp = "";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Email #3.
$fldSpecStr = "<INPUT TYPE=text NAME=Email3 ";
$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
$fldSpecStr .= "VALUE=''>";
$fldSpecStr .= "&nbsp;&nbsp; <INPUT TYPE=checkbox NAME=Email3Active VALUE='0' UNCHECKED>";
$fldSpecStr .= "&nbsp;Active Email?";
$fldLabel = "Email #3";
$fldHelp = "";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   USTA Number.
$fldSpecStr = "<INPUT TYPE=text NAME=USTANum ";
$fldSpecStr .= "SIZE=20 MAXLENGTH=50 ";
$fldSpecStr .= "VALUE=''>";
$fldLabel = "USTA Member Number";
$fldHelp = "USTA Membership ID Number.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Club-specific Notes.
$fldSpecStr = "<TEXTAREA NAME=meta_TBLT2FLD_Note ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$fldLabel = "Club-Specific Notes";
$fldHelp = "Notes and comments that apply specifically to this person's membership in";
$fldHelp .= " your club (will not be seen from within other clubs).";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Site-Wide Notes.
$fldSpecStr = "<TEXTAREA NAME=Note ROWS=5 COLS=65>";
$fldSpecStr .= '';
$fldSpecStr .= "</TEXTAREA>";
$fldLabel = "Universal Notes";
$fldHelp = "General notes and comments. These notes will appear for this";
$fldHelp .= " person across all clubs.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Club Status Y/N field.
$fldLabel = "Active Member?";
$fldHelp = "The current status of this person with the club.";
$rowHTML = ADMIN_GenFieldYN($fldLabel, $fldHelp, 'meta_TBLT2FLD_Active', 1, 'MGR', $userPriv);
echo $rowHTML;

				//   Site-level Status drop-down.
$fldLabel = "Site-Level Status";
$fldHelp = "The current status of this person on the site as a whole for all clubs.";
$fldHelp .= "(NOTE: Requires system Administrator rights to edit.)";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Currency', 8, 39, FALSE, 'SADM', $userPriv);
echo $rowHTML;

				//   Privlidges drop-down.
$fldLabel = "Privlidges";
$fldHelp = "The rights this person has on the site.";
$fldHelp .= "(NOTE: Requires system Administrator rights to edit.)";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'HighPriv', 1, 3, FALSE, 'SADM', $userPriv);
echo $rowHTML;



echo "<tr>{$CRLF}<td colspan='2'><p align='center'><input type='submit' value='Enter record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";


//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "addPerson.php");

?> 
