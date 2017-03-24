<?php
/*
	This script allows the club ADM to add an existing person to
	to their club.
	
	02-28-2009:
	   1) Initial Release.
---------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



$DEBUG = TRUE;
$DEBUG = FALSE;



//----DECLARE GLOBAL VARIABLES------------------------------------------------>

				//   Defined in the INCL_Tennis_Functions_Session.php file.
global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------------->
$viewName = 'qryPersonDisp';
$tblName = 'ClubMember';

$row = array();
$tmpString = "";

				//   To build the where clause of the delete SQL query.
$whereclause = "";

				//   To hold the club ID we are adding people to.
$clubID = 0;

				//   Determines if we are posting data or displaying data.
				//A or R=Posting (Add or Remove), D=Displaying.
$action = "D";

				//   The person ID we are adding to the club during Data-Post.
$personID = 0;

				//   Where we go to if we need to show the user an 'OK' (continue)
				//link. Typically we do this after posting the data.
$returnPage = "addClubMember.php";

				//   Array to hold the data to post. This must in the form
				//suitable for the Tennis_dbRecordInsert() function to take.
$postData = array();

				//   Used to get the number of database records affected by
				//the data posting operation.
$numRecordsUpdated = 0;

				//   Used to build a string to put into the ClubMember.Note field
				//when we are posting data.
$note = "";



//----GET URL QUERY STRING DATA----------------------------------------------->
if (array_key_exists('ACT', $_GET)) $action = $_GET['ACT'];
if (array_key_exists('PSN', $_GET)) $personID = $_GET['PSN'];
if (array_key_exists('REC', $_GET)) $recID = $_GET['REC'];
if (array_key_exists('RTNPG', $_GET)) $returnPage = $_GET['RTNPG'];


//----SET CLUB ID------------------------------------------------------------->
$clubID = $_SESSION['clubID'];



//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit(0);
	}


//----GET USER EDIT RIGHTS---------------------------------------------------->
				//   NOTE that in the nested IF statement, SESSION['clbmgr']=TRUE
				//really means Club ADM level rights. If this session value is
				//FALSE for the current user then the below code will look up
				//the user's authority on the club, which may be at the (lower)
				//'MGR' level of rights (or of course, no rights at all).
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='ADM'; } // Superuser.
else
	if ($_SESSION['clbmgr']==True) { $userPriv='ADM'; }
	else { $userPriv=Session_GetAuthority(55, $clubID); }


//====TAKE THE DESIGNATED ACTION =============================================>
switch ($action)
	{
	case "A":
		//Posting a new member record (adding member to club).

					//   Build the ClubMember.Note info string.
		$tmpString = date("M-d Y h:i a");
		$note = "Added to club On: {$tmpString} By: ";
		$tmpString = "{$_SESSION['userName']} (MemberID: {$_SESSION['recID']}).";
		$note .= $tmpString;

					//   Build the data-posting array.
		$postData['Club'] = $clubID;
		$postData['Person'] = $personID;
		$postData['Active'] = 1;
		$postData['Note'] = $note;

					//   Save the updates to the DB.
		$tmpString = Tennis_dbRecordInsert($postData, $tblName, $DEBUG);
		if (substr($tmpString,0,6)=="ERROR:")
			{
			echo "<P>{$GLOBALS['lstErrMsg']}</P>";
			}
					//   Output page header stuff.
		$tbar = "Add Person to Club";
		$pgL1 = "Edit Records";
		$pgL2 = "Post Update";
		$pgL3 = "Add Person to Club";
		echo ADMIN_Post_HeaderOK($tblName, $returnPage, $tmpString, "GO");
		break;


	case "R":
		//Removing an existing member record (removing member from club).

		$whereclause = "WHERE (ClubMember.ID={$recID})";
		$tmpString = Tennis_dbRecordDeleteByID($tblName, $recID, FALSE);
		if (substr($tmpString,0,6)=="ERROR:")
			{
			echo "<P>{$GLOBALS['lstErrMsg']}</P>";
			}
					//   Output page header stuff.
		$tbar = "Remove Person from Club";
		$pgL1 = "Edit Records";
		$pgL2 = "Post Update";
		$pgL3 = "Remove Person from Club";
		echo ADMIN_Post_HeaderOK($tblName, $returnPage, $tmpString, "GO");
		break;


	default:
		//DISPLAYING THE DATA, NOT POSTING IT.

				//   MAKE PAGE HEADER--------------------------------------------->
		$tbar = "Add and Remove People to Your Club";
		$pgL1 = "Edit Records";
		$pgL2 = "Add Person to Club";
		$pgL3 = "Add and Remove People to Your Club";
		echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);

				// ENSURE USER RIGHTS ARE OK TO PROCEED--------------------------->
		if($userPriv<>'ADM')
			{
			echo "<P>You are Not Authorized to View This Page.<BR><BR>";
			echo "<P>Your User Rights on this Page Are: {$userPriv}</P>";
			include './INCL_footer.php';
			exit(0);
			}

				//   BUILD THE LISTS---------------------------------------------->
						//   Side-by-side lists of current ClubMembers and persons
						//who are not currently members of this particular club.
		$rowHTML = "<P>&nbsp;</P>{$CRLF}";
		$rowHTML .= "<table border='1' CELLPADDING='3' rules='cols'>";
		echo $rowHTML;

		$fldLabel = "Current Club Membership -- Add or Remove People from Your Club";
		$fldHelp = "Click appropriate links to REMOVE/ADD a person from your club.";
		$rowHTML = "<TR CLASS='deTblRow'>{$CRLF}";
		$rowHTML .= "<TD CLASS='deTblCellSectiontitle' COLSPAN='2'>";
		$rowHTML .= "<P CLASS='deSectionTitle'>{$fldLabel}</P>{$CRLF}";
		$rowHTML .= "</TD></TR>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "<TR class=deTblRow>{$CRLF}";
		$rowHTML .= "<TD class=deTblCellInput COLSPAN='2'>";
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
		$rowHTML .= "</TD></TR>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "<TR class=deTblRow>{$CRLF}";
		echo $rowHTML;

		$fldLabel = "Current Club Members";
		$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
		$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
		$rowHTML .= "</TD>{$CRLF}";
		echo $rowHTML;

		$fldLabel = "NOT Currently A Club Member";
		$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
		$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
		$rowHTML .= "</TD>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "</TR>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "<TR class=deTblRow>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "<TD class=deTblCellInput>";
		$rowHTML .= "<P class=deFieldInput>";
		echo $rowHTML;

		local_listMembers($clubID, $userPriv);

		$rowHTML = "</TD>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "<TD class=deTblCellInput>";
		$rowHTML .= "<P class=deFieldInput>";
		echo $rowHTML;

		local_listNonMembers($clubID, $userPriv);

		$rowHTML = "</TD>{$CRLF}";
		echo $rowHTML;

		$rowHTML = "</TR>{$CRLF}";
		echo $rowHTML;

		echo "</table>{$CRLF}{$CRLF}{$CRLF}";

						//   NAVIGATION AND VIEW LINKS------------------------------>
		$rowHTML = "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
		$rowHTML .= "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
		$rowHTML .= "*&nbsp;<A HREF=\"listPerson_PhoneList.php\">Club Phone List</A><BR>{$CRLF}";
		echo $rowHTML;

						//   If current user has rights to manage this series,
						//give them some additional options.
		if ($userPriv=='MGR' or $userPriv=='ADM')
			{
			$rowHTML = "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
			$rowHTML .= "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
			$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='addPerson.php?RTNPG=addClubMember.php'>{$CRLF}";
			$rowHTML .= "ADD New Person</A>";
			$rowHTML .= "</P>{$CRLF}";
			}
		echo $rowHTML;
					//<----END ADMIN OPTIONS SECTION


	} //END SWITCH STATEMENT.

	echo  Tennis_BuildFooter('ADMIN', "addClubMember.php");



//=============================================================================
//=============================================================================

function local_listMembers($clubID, $userPriv)
	{

	global $CRLF;
	global $lstErrMsg;
	$rowHTML = "";

	if (!$qryResult = Tennis_ClubMembersOpen($clubID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	while ($row = mysql_fetch_array($qryResult))
		{
	if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
			{
			$rowHTML .= "<A HREF=\"addClubMember.php?REC={$row['ID']}&ACT=R&RTNPG=addClubMember.php\">";
			$rowHTML .= "REMOVE</A>&nbsp;&nbsp;";
			}
		if ($_SESSION['member'] == TRUE)
			{
			$rowHTML .= "{$row['prsnLName']}";
			$rowHTML .= ", ";
			$rowHTML .= "{$row['prsnFName']}<BR>{$CRLF}";
			}
		else
			{
			$rowHTML .= "{$row['prsnPName']}<BR>{$CRLF}";
			}
		}
	echo $rowHTML;
	
}


function local_listNonMembers($clubID, $userPriv)
	{

	global $CRLF;
	global $lstErrMsg;
	$rowHTML = "";
	$status = "";
	
	if (!$qryResult = Tennis_NonClubMembersOpen($clubID, TRUE))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	while ($row = mysql_fetch_array($qryResult))
		{
	if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
			{
			$rowHTML .= "<A HREF=\"addClubMember.php?PSN={$row['ID']}&ACT=A&RTNPG=addClubMember.php\">";
			$rowHTML .= "ADD</A>&nbsp;&nbsp;";
			}
		if ($_SESSION['member'] == TRUE)
			{
			if ($row['Currency']==40) $status="inactive"; else $status="active";
			$rowHTML .= $row['prsnLName'];
			$rowHTML .= ", ";
			$rowHTML .= $row['prsnFName'];
			$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// (";
			$rowHTML .= $status;
			$rowHTML .= ")<BR>{$CRLF}";
			}
		else
			{
			$rowHTML .= "{$row['prsnPName']}<BR>{$CRLF}";
			}
		}
	echo $rowHTML;

	
}



?> 
