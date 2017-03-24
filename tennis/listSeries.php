<?php
/*
	This script list all Series.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$_SESSION['RtnPg'] = "listSeries.php";



//$DEBUG = TRUE;
$DEBUG = FALSE;


global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'series';
$row = array();

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
				//   Open series table.
if(!$qryResult = Tennis_OpenViewGeneric($tblName, "WHERE series.ClubID={$clubID}", "ORDER BY series.Sort, series.ShtName"))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

				//   Output page header stuff.
$tbar = "List All Series";
$pgL1 = "List Series";
$pgL2 = "";
$pgL3 = "All Series";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);



				//   Build the list.
				//   Display the event details in standard
				//record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>ID</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Sort</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Short Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Long Name</P></TD>{$CRLF}";
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
				//   Sort.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['Sort']}</P></TD>{$CRLF}";
				//   Short Name.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ShtName']}</P></TD>{$CRLF}";
				//   Long Name.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='listSeriesRoster.php?ID={$row['ID']}'>{$row['LongName']}</A>";
	$out .= "</P></TD>{$CRLF}";
				//   Edit Link.
	$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
	$out .= "<A HREF='editSeries.php?ID={$row['ID']}'>EDIT</A></P></TD>{$CRLF}";
	
	$out .= "</TR>{$CRLF}{$CRLF}";
	echo $out;
	}
$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
echo $out;

echo  Tennis_BuildFooter('NORM', "listSeries.php");
?> 
