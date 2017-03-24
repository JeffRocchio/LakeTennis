<?php
/*
	This script lists all club members. It generates the list
	of all persons from ClubMember who are members of the given
	club.
	
	08/02/2009:
		1)	Modified to also show the person's email addresses.

	02-28-2009:
	   1) First release of revised script to replace the old non-multi-club
	   	version of the script with the same name.
---------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



$DEBUG = FALSE;
//$DEBUG = TRUE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'qryClubMembers';
$row = array();
				//   Where and Sort clauses for the person view on this
				//list.
$where="WHERE Active=1 AND clubID={$clubID}";
$orderby="ORDER BY prsnFName, prsnLName";



//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	


//----OPEN THE TABLE TO LIST-------------------------------------------------->
if(!$qryResult = Tennis_OpenViewGeneric($tblName, $where, $orderby))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	



//----GET USER RIGHTS--------------------------------------------------------->
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


//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Member Contact List";
$pgL1 = "List Records";
$pgL2 = "Member Contact List";
$pgL3 = "All Active Club Members";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

if ($_SESSION['member'] != TRUE)
		{
		$out = "<P>You Are Not Authorized to View this Page.</P>{$CRLF}";
		$out .= "<P>IF you are a club member, please login.</P>{$CRLF}";
		echo $out;
		echo  Tennis_BuildFooter('NORM', "listPerson_PhoneList.php");
		exit;
		}



//----BUILD THE LIST----------------------------------------------------->
				//   Display in standard record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
/* if ($_SESSION['evtmgr'] == TRUE) $out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>USTA#</P></TD>{$CRLF}"; */
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Home Phone</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Work Phone</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Cell Phone</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Emails</P></TD>{$CRLF}";
$out .= "</TR></THEAD>{$CRLF}";
echo $out;
				
				//   Build table body.
$out = "<TBODY>{$CRLF}";
echo $out;
while ($row = mysql_fetch_array($qryResult))
	{
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
				//   USTA#.
/*	if ($_SESSION['evtmgr'] == TRUE)
		{
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['USTANum']}</P></TD>{$CRLF}";
		} */
				//   Name.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='dispPerson.php?ID={$row['prsnID']}&FORMAT=FULL'>";
	$out .= "{$row['prsnFName']} {$row['prsnLName']}</A>";
	$out .= "</P></TD>{$CRLF}";
				//   Home Phone.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneH']}</P></TD>{$CRLF}";
				//   Work Phone.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneW']}</P></TD>{$CRLF}";
				//   Cell Phone.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneC']}</P></TD>{$CRLF}";
				//   Emails.
	$out .= "<TD CLASS='ddTblCellDisplay'>";
	if ($row['Email1Active'] == 1) $out .= "<P CLASS='ddFieldData'>{$row['Email1']}";
	if ($row['Email2Active'] == 1) $out .= ", {$row['Email2']}";
	if ($row['Email3Active'] == 1) $out .= ", {$row['Email3']}";
	$out .= "</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}{$CRLF}";
	echo $out;
	}
$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
echo $out;

					//   Make links to navigate to other places. 12/28/2006.
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "*&nbsp;<A HREF=\"listPerson_Emails.php\">Club Email List</A><BR>{$CRLF}";

//----LINKS FOR THE ADMIN/CLUB-MANAGER/SERIES-MANAER------------------------->
if ($userPriv=='MGR' or $userPriv=='ADM')
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"addClubMember.php\">Add Club Member</A>{$CRLF}";
	echo "</P>{$CRLF}";
	}


echo  Tennis_BuildFooter('NORM', "listPerson_PhoneList.php");
?> 
