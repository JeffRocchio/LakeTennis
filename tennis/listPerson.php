<?php
/*
	This script list all the members of the group.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



//$DEBUG = TRUE;
$DEBUG = FALSE;


$CRLF = "\n";

//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$tblName = 'qryPersonDisp';
array($row);







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
$tbar = "List All Persons";
$pgL1 = "List Records";
$pgL2 = "";
$pgL3 = "All Persons in Database";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'ADM')
	{
	echo "<P>You are Not Authorized to View This Page.<BR><BR>";
	echo "(Use your club's Phone List to access and edit members ";
	echo "of your club.)</P>";
	echo "<P>Your User Rights on this Page Are: {$userPriv}</P>";
	include './INCL_footer.php';
	exit;
	}


//----OPEN PERSON TABLE-------------------------------------------------->
if(!$qryResult = Tennis_OpenViewGeneric($tblName, "", "ORDER BY ClubID, CurrencyLName, FName, LName"))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----BUILD THE LIST----------------------------------------------------->
				//   Display the in standard
				//record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel' STYLE='WIDTH:5em'><P CLASS='ddSectionTitle'>ID</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel' STYLE='WIDTH:5em'><P CLASS='ddSectionTitle'>Club&nbsp;ID</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel' STYLE='WIDTH:5em'><P CLASS='ddSectionTitle'>Status</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>High Priv</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>First Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Last Name</P></TD>{$CRLF}";
if ($_SESSION['evtmgr'] == TRUE) $out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>USTA#</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>&nbsp;</P></TD>{$CRLF}";
$out .= "</TR></THEAD>{$CRLF}";
echo $out;
				
				//   Build table body.
$out = "<TBODY>{$CRLF}";
echo $out;
while ($row = mysql_fetch_array($qryResult))
	{
	$out = "<TR CLASS='ddTblRow'>{$CRLF}";
				//   Record ID.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ID']}</P></TD>{$CRLF}";
				//   Club ID.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ClubID']}</P></TD>{$CRLF}";
				//   Current Status.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['CurrencyLName']}</P></TD>{$CRLF}";
				//   High Priv.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['HighPriv']}</P></TD>{$CRLF}";
				//   First Name.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['FName']}</P></TD>{$CRLF}";
				//   Last Name.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['LName']}</P></TD>{$CRLF}";
				//   USTA Member Number.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['USTANum']}</P></TD>{$CRLF}";
				//   Edit Link.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='editPerson.php?ID={$row['ID']}&RTNPG=listPerson.php'>EDIT</A></P></TD>{$CRLF}";
	
	$out .= "</TR>{$CRLF}{$CRLF}";
	echo $out;
	}
$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
echo $out;

echo  Tennis_BuildFooter('NORM', "listPerson.php");
?> 
