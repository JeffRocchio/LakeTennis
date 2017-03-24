<?php
/*
	This script displays a single Person record.
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

$clubID=$_SESSION['clubID'];

$tblName = 'qryPersonDisp';
			//   To define the critera for fetching the person-ClubMember
			//joined record we need.
$where = "";

			//   For building an output message to display as needed.
$message = "";

			//   True if the person record cannot be obtained.
$prsnNotAvail = FALSE;

$row = array();
$tmp = "";



//----GET URL QUERY-STRING DATA----------------------------------------->
$dispFormat = $_GET['FORMAT'];
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No item specified in query string.</P>";
	include './INCL_footer.php';
	exit;
	}
				//   Set return page for edits.
$_SESSION['RtnPg'] = "dispPerson.php?ID={$recID}&FORMAT=FULL";



//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----GET USER EDIT RIGHTS---------------------------------------------->
				//   Levels of rights on this page:
				//     1) MEMBER. Sees the displayed record. No Edit link.
				//     2) MANAGER. Sees a link to Edit the record.
				//     3) SELF. Sees a link to Edit the record.
$userPriv='GST';
if ($_SESSION['member']==TRUE) $userPriv='USR';
if ($_SESSION['recID']==$recID) $userPriv='MGR';
if ($_SESSION['admin']==TRUE) { $userPriv='MGR'; }
else
	{
	$tmp=Session_GetAuthority(55, $clubID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; }
	}




//----SET INITIAL PAGE HEADER INFO--------------------------------------------->
$tbar = "Tennis - Display Person Details";
$pgL1 = "Display Record";
$pgL2 = "Person";

//----FETCH THE RECORD-------------------------------------------------->
//$testResult = Tennis_GetSingleRecord($row, $tblName, $recID);
$where = "WHERE clubID={$clubID} AND prsnID={$recID}";
$qryResult = Tennis_OpenViewGeneric("qryClubMembers", $where, "");
if (!$qryResult)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
				//   If the query returns an empty set it is because the person
				//is no longer a member of the club. (the query is empty because
				//there is no associated ClubMember record for the clubID used
				//for the join.
				//   So in this case just display a notice and don't attempt to
				//to show the record.
if (mysql_num_rows($qryResult) <= 0)
	{
	$prsnNotAvail = TRUE;
	$message = "<P>This person is no longer a member of this club.</p>";
	$pgL3 = "Person No Longer In Club";	
	}
else
	{
	$prsnNotAvail = FALSE;
	$row = mysql_fetch_array($qryResult);
	$pgL3 = $row['FullName'];
	}


//----MAKE PAGE HEADER--------------------------------------------------->
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv!='MGR' and $userPriv!='USR')
	{
	$out = "<P>You Are Not Authorized to View this Page.</P>{$CRLF}";
	$out .= "<P>IF you are a club member, please login.</P>{$CRLF}";
	echo $out;
	echo  Tennis_BuildFooter('NORM', "http://laketennis.com/index.php");
	exit;
	}


//----DISPLAY RECORD----------------------------------------------------->
if ($prsnNotAvail == TRUE)
	{
	echo $message;
	}
else
	{
	$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";
	$out .= "<TBODY>{$CRLF}";
	echo $out;

	if ($dispFormat == 'FULL')
		{

					//   Section Title - PERSONAL INFO.
		$out = "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>PERSONAL INFORMATION</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";

		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Name</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['FullName']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Public Name</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnPName']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Gender</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['prsnGender']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";

		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>UserID</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['UserID']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";

					//   Section Title - CONTACT INFO.
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>CONTACT INFORMATION</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Home Phone</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneH']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Work Phone</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneW']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Cell Phone</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['PhoneC']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		if ($row['Email1Active'] == 1)
			{
			$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Email 1</P></TD>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['Email1']}</P></TD>{$CRLF}";
			$out .= "</TR>{$CRLF}";
			} 
		if ($row['Email2Active'] == 1)
			{
			$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Email 2</P></TD>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['Email2']}</P></TD>{$CRLF}";
			$out .= "</TR>{$CRLF}";
			} 
		if ($row['Email3Active'] == 1)
			{
			$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Email 3</P></TD>{$CRLF}";
			$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['Email3']}</P></TD>{$CRLF}";
			$out .= "</TR>{$CRLF}";
			} 

					//   Section Title - OTHER MISC INFO.
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>OTHER INFORMATION</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>USTA Number</P></TD>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['USTANum']}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Active in Club?</P></TD>{$CRLF}";
		$tmp = "Yes";
		if($row['Active'] <> 1) $tmp = "No";
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
	
		$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
		$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Current Site Status</P></TD>{$CRLF}";
		$tmp = Tennis_dbGetNameCode($row['PrsnCurrency'], FALSE);
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}";
		echo $out;
	
		} // end if

					//   Section Title - NOTES.
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>NOTES</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Club-Specific Notes</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['ClubNote']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Universal Notes</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['PrsnNote']}</P></TD>{$CRLF}";
	$out .= "</TR>{$CRLF}";

	$out .= "</TBODY></TABLE>{$CRLF}{$CRLF}";

	echo $out;
	}


//----MAKE NAVIGATION LINKS---------------------------------------------->
				//   Make an edit link for authorized users.
if(($userPriv=='MGR') and ($prsnNotAvail == FALSE))
	{
	$out = "<P><A HREF='editPerson.php?ID={$recID}'>EDIT</A></P>{$CRLF}";
	echo $out;
	}
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "*&nbsp;<A HREF=\"listPerson_PhoneList.php\">Club Phone List</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listPerson_Emails.php\">Club Email List</A><BR>{$CRLF}";



//----DEBUG:SPIT OUT THE RAW RECORD-------------------------------------->
if ($DEBUG)
	{
				//   Display the raw data.
	$outHTML = "<P>IN DEBUG MODE -- RAW DATA FROM ROW:</P>{$CRLF}";
	echo $outHTML;	echo "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=2>";
	foreach ($row as $key => $value)
		{
		$tblRow = "<TR class=ddTblRow>";
		$tblRow .= "<TD class=ddTblCellLabel>";
		$tblRow .= "<P class=ddFieldName>{$key}</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "<TD class=ddTblCellLabel>";
		$tblRow .= "<P class=ddFieldName>&nbsp;&nbsp;&nbsp;</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "<TD class=ddTblCellInput>";
		$tblRow .= "<P class=ddFieldData>{$value}</P>";
		$tblRow .= "</TD>{$CRLF}";
		$tblRow .= "</TR>{$CRLF}";
		echo $tblRow;
		}
	echo "</TABLE>";
	}




//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter("NORM", "dispPerson.php?ID={$recID}");

?> 
