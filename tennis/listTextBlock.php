<?php
/*
	This script list all clubs.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$_SESSION['RtnPg'] = "listTextBlock.php";



//$DEBUG = TRUE;
$DEBUG = FALSE;


global $CRLF;

//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$tblName = 'txtBlock';
				//   Declare array to hold the detail display
				//record.
$row = array();

$tmpString = "";


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
$tbar = "List All Text Blocks";
$pgL1 = "List Text Blocks";
$pgL2 = "";
$pgL3 = "All Text Blocks";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);



//----OPEN TABLE, PULLING UERS RIGHTS WITH IT----------------------->
if(!$qryResult = Tennis_OpenViewGenericAuth($tblName, "", "ORDER BY BlockTitle", 55))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	



//----BUILD THE LIST----------------------------------------------------->
				//   Display the list in standard
				//record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TH CLASS='ddTblCellHeading'><P CLASS='ddSectionTitle'>ID</P></TD>{$CRLF}";
$out .= "<TH CLASS='ddTblCellHeading'><P CLASS='ddSectionTitle'>Status</P></TD>{$CRLF}";
$out .= "<TH CLASS='ddTblCellHeading'><P CLASS='ddSectionTitle'>Block Title</P></TD>{$CRLF}";
$out .= "<TH CLASS='ddTblCellHeading'><P CLASS='ddSectionTitle'>Block Usage</P></TD>{$CRLF}";
$out .= "<TH CLASS='ddTblCellHeading'><P CLASS='ddSectionTitle'>&nbsp;</P></TD>{$CRLF}";
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
				//   Status (Active or Inactive?).
	if ($row['Active'] == 1) $tmpString = "ACTIVE"; else $tmpString = "INACTIVE";
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmpString}</P></TD>{$CRLF}";
				//   Title.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='dispTextBlock.php?ID={$row['ID']}'>{$row['BlockTitle']}</A></P></TD>{$CRLF}";
				//   Usage.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "{$row['BlockUsage']}</P></TD>{$CRLF}";
				//   Edit Link.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	if ($userPriv=='ADM')
		{
		$out .= "<A HREF='editTextBlock.php?ID={$row['ID']}'>EDIT</A></P></TD>{$CRLF}";
		}
	else
		{
		$out .= "&nbsp</P></TD>{$CRLF}";
		}

	
	$out .= "</TR>{$CRLF}{$CRLF}";
	echo $out;
	}
$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
echo $out;

echo  Tennis_BuildFooter('NORM', "listTextBlock.php");
?> 
