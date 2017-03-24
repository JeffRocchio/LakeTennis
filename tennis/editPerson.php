<?php
/*
	This script allows the admin to edit an existing person
	record.
	
	12/20/2014: Added the Gender field.
	
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



$DEBUG = FALSE;
//$DEBUG = TRUE;

//----DECLARE GLOBAL VARIABLES--------------------------------------------------
global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES---------------------------------------------------
$clubID=$_SESSION['clubID'];
$tblName = 'person';
$recID = 0;
$AssocTblName = "ClubMember";
$AssocTblRecID = 0;
$row = array();
$TBL2row = array();


//----GET URL QUERY-STRING DATA-------------------------------------------------
$recID = 0;
if (array_key_exists('ID', $_GET)) $recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Person Selected.</P>";
	include './INCL_footer.php';
	exit;
	}


//----CONNECT TO MYSQL----------------------------------------------------------
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

//----GET USER EDIT RIGHTS------------------------------------------------------
				//   Levels of rights on this page:
				//     1) SADMIN. System admin. Can do anything.
				//     2) MANAGER. Can edit any field except the clubID.
				//     3) SELF. You are editing your own personal data.
$userPriv='GST';
			//   First, if user is super-admin (jeff rocchio) then rights
			//are SADM period.
			//   Second, make sure the person we want to edit is actually a
			//member of the club we are logged into. If not, then you do not
			//have edit rights on the person; unless of course you are the
			//super-admin (jeff rocchio).
			//   Third, assuming now that the person you want to edit is a member
			//of your club, see if you have sufficient club rights to edit
			//person records.
$AssocTblRecID = Tennis_IsUserInClub($recID, $clubID, $DEBUG=FALSE);
if ($_SESSION['admin']==True) { $userPriv='SADM'; }
elseif($AssocTblRecID > 0)
	{
	$tmp=Session_GetAuthority(55, $clubID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; }
	}
$slfStr = "&self={$recID}";



//----FETCH THE RECORDS TO EDIT-------------------------------------------------
				//   Master person record.
if(!Tennis_GetSingleRecord($row, $tblName, $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
				//   The ClubMember associative record.
if(!Tennis_GetSingleRecord($TBL2row, $AssocTblName, $AssocTblRecID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Edit Person";
$pgL1 = "Edit Record";
if ($DEBUG) $pgL1 .= " [Priv: {$userPriv}]";
$pgL2 = "PERSON";
$pgL3 = "{$row['FName']} {$row['LName']}";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);




//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------------->
if(!ADMIN_EditAuthorized("MGR{$slfStr}", $userPriv))
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	echo "<P>Your User Rights on this Page Are: {$userPriv}</P>";
	echo "<P><A HREF='$rtnpg'>RETURN</A></P>";
	include './INCL_footer.php';
	exit;
	}



//----BUILD DATA-ENTRY FORM---------------------------------------------->
echo "<form method='post' action='editGeneric_post.php'>{$CRLF}";

echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
echo "<input type=hidden name=meta_TBL value={$tblName}>{$CRLF}";
echo "<input type=hidden name=meta_EMAIL value=Y>{$CRLF}";
echo "<input type=hidden name=ID value={$row['ID']}>{$CRLF}";

				//   Define parameters for the associative table, in this case
				//the ClubMember table that binds the person to a club.
echo "<input type=hidden name=meta_TBLT2_NAME value={$AssocTblName}>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID value={$TBL2row['ID']}>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID1_FLD value=Club>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID1_VAL value={$TBL2row['Club']}>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID2_FLD value=Person>{$CRLF}";
echo "<input type=hidden name=meta_TBLT2_ID2_VAL value={$TBL2row['Person']}>{$CRLF}";

echo "<table CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Record ID.
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>ID</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= $row['ID'];
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;

				//   First Name.
$fldLabel = "First Name";
$fldHelp = "";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'FName', 100, 30, $row['FName'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Last Name.
$fldLabel = "Last Name";
$fldHelp = "";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'LName', 100, 30, $row['LName'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Public Name.
$fldLabel = "Public Name";
$fldHelp = "Publicly viewable name of person. Used to be able to identify ";
$fldHelp .= "people in public views while also protecting privacy.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'PName', 100, 30, $row['PName'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Gender.
$fldLabel = "Gender";
$fldHelp = "Male (M), Female (F) or Unknown (U)";
$rowHTML = ADMIN_GenFieldGender($fldLabel, $fldHelp, 'Gender', $row['Gender'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   User ID.
$fldLabel = "User Login ID";
$fldHelp = "User ID for login purposes.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'UserID', 200, 30, $row['UserID'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   User Password.
$fldLabel = "User Password";
$fldHelp = "User password for login purposes.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'Pass', 20, 20, $row['Pass'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;


				//   Phone - HOME.
$fldLabel = "Home Phone";
$fldHelp = "Home phone number.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'PhoneH', 20, 20, $row['PhoneH'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Phone - WORK.
$fldLabel = "Work Phone";
$fldHelp = "Work phone number.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'PhoneW', 20, 20, $row['PhoneW'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Phone - CELL.
$fldLabel = "Cell Phone";
$fldHelp = "Cell phone number.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'PhoneC', 20, 20, $row['PhoneC'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//EMAIL FIELDS --------->
				//   Have to build the email fields "manually" so that I can
				//put the Active/Inactive radio-buttons next to the text
				//field (vs them being a seperate field under the email text
				//field).
				
				//   Email #1.
if (ADMIN_EditAuthorized("MGR{$slfStr}", $userPriv))
	{
	$fldSpecStr = "<INPUT TYPE=text NAME=Email1 ";
	$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
	$fldSpecStr .= "VALUE='{$row['Email1']}'>&nbsp;&nbsp;";
	if ($row['Email1Active'])
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email1Active' value='1' CHECKED> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email1Active' value='0'> ";
		}
	else
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email1Active' value='1'> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email1Active' value='0' CHECKED> ";
		}
	}
else
	{
	$fldSpecStr = $row['Email1'];
	if ($row['Email1Active']) { $fldSpecStr .= "&nbsp;&nbsp;Active"; } else { $fldSpecStr .= "&nbsp;&nbsp;Inactive"; }
	}
$fldLabel = "Email #1";
$fldHelp = "Email address. Set address to Active or Inactive as needed.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Email #2.
if (ADMIN_EditAuthorized("MGR{$slfStr}", $userPriv))
	{
	$fldSpecStr = "<INPUT TYPE=text NAME=Email2 ";
	$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
	$fldSpecStr .= "VALUE='{$row['Email2']}'>&nbsp;&nbsp;";
	if ($row['Email2Active'])
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email2Active' value='1' CHECKED> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email2Active' value='0'> ";
		}
	else
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email2Active' value='1'> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email2Active' value='0' CHECKED> ";
		}
	}
else
	{
	$fldSpecStr = $row['Email2'];
	if ($row['Email2Active']) { $fldSpecStr .= "&nbsp;&nbsp;Active"; } else { $fldSpecStr .= "&nbsp;&nbsp;Inactive"; }
	}
$fldLabel = "Email #2";
$fldHelp = "Email address. Set address to Active or Inactive as needed.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;

				//   Email #3.
if (ADMIN_EditAuthorized("MGR{$slfStr}", $userPriv))
	{
	$fldSpecStr = "<INPUT TYPE=text NAME=Email3 ";
	$fldSpecStr .= "SIZE=60 MAXLENGTH=255 ";
	$fldSpecStr .= "VALUE='{$row['Email3']}'>&nbsp;&nbsp;";
	if ($row['Email3Active'])
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email3Active' value='1' CHECKED> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email3Active' value='0'> ";
		}
	else
		{
		$fldSpecStr .= "Active <INPUT type='radio' name='Email3Active' value='1'> ";
		$fldSpecStr .= "Inactive <INPUT type='radio' name='Email3Active' value='0' CHECKED> ";
		}
	}
else
	{
	$fldSpecStr = $row['Email3'];
	if ($row['Email3Active']) { $fldSpecStr .= "&nbsp;&nbsp;Active"; } else { $fldSpecStr .= "&nbsp;&nbsp;Inactive"; }
	}
$fldLabel = "Email #3";
$fldHelp = "Email address. Set address to Active or Inactive as needed.";
$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
echo $rowHTML;


				//   USTA Number.
$fldLabel = "USTA Member Number";
$fldHelp = "USTA Membership ID Number.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'USTANum', 50, 20, $row['USTANum'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Club-Specific Notes.
$fldLabel = "Club-Specific Notes";
$fldHelp = "Notes and comments that apply specifically to this person's membership in";
$fldHelp .= " your club (will not be seen from within other clubs).";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'meta_TBLT2FLD_Note', 5, 65, $TBL2row['Note'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Universal Notes.
$fldLabel = "Universal Notes";
$fldHelp = "General notes and comments. These notes will appear for this";
$fldHelp .= " person across all clubs.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Note', 5, 65, $row['Note'], "MGR{$slfStr}", $userPriv);
echo $rowHTML;

				//   Club Status Y/N field.
$fldLabel = "Active Member?";
$fldHelp = "The current status of this person with the club.";
$rowHTML = ADMIN_GenFieldYN($fldLabel, $fldHelp, 'meta_TBLT2FLD_Active', $TBL2row['Active'], 'MGR', $userPriv);
echo $rowHTML;

				//   Site-level Status drop-down.
$fldLabel = "Site-Level Status";
$fldHelp = "The current status of this person on the site as a whole for all clubs.";
$fldHelp .= " (Can only be set by the system administrator.)";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Currency', 8, $row['Currency'], FALSE, 'SADM', $userPriv);
echo $rowHTML;

				//   Privlidges drop-down.
$fldLabel = "Privlidges";
$fldHelp = "The rights this person has on the site.";
$fldHelp .= " (Can only be set by the system administrator.)";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'HighPriv', 1, $row['HighPriv'], FALSE, 'SADM', $userPriv);
echo $rowHTML;



echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";

echo  Tennis_BuildFooter('ADMIN', "editPerson.php?ID={$recID}");


?> 
