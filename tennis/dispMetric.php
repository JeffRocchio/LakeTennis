<?php
/*
	This script displays a single metric record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   Declare array to hold the detail display
				//record.
array($row);
				//   Name of the query to use to fetch the
				//detail record.
$tblName = 'qryMetricDisp';

				//   Get the metric ID from the query string. If
				//empty, report error and do nothing.
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No item specified in query string.</P>";
	include './INCL_footer.php';
	exit;
	}
				//   Set return page for edits.
$_SESSION['RtnPg'] = "dispMetric.php?ID={$recID}";


				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



				//   Get the requested record.
$testResult = Tennis_GetSingleRecord($row, $tblName, $recID);
if (!$testResult)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

				//   Build the page header.
$tbar = "Tennis: {$row['Name']} Details";
$pgL1 = "Metric Details";
$pgL2 = $row['seriesName'];
$pgL3 = $row['metricName'];
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

				//   Display the event details in standard
				//record-detail-display format.
$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";

				//   Section Title - Metric Master.
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>METRIC</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>ID</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ID']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>For Series</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['seriesName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Metric Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['metricName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Metric Short Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['metricShtName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Sort Order</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['metricSort']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Value Type</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['metricValType']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Value Sort Order</P></TD>{$CRLF}";
$tmp = 'Ascending';
if ($row['metricSortDesc']) $tmp = 'Descending';
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$tmp}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Display Setting</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['metricDisplay']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Metric Description</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['metricDiscription']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Current Announcement</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['metricAnnouncement']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

echo $out;

				//   Section Title - Current Metric Values.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellSectiontitle' COLSPAN='2'><P CLASS='ddSectionTitle'>CURRENT VALUES";
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";

$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Current Values</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'>{$CRLF}";
echo $out;
$out = local_ListValues($recID, $row['metricValTypeCode'], $row['metricSortDesc']);
echo $out;
$out = "</TR></TABLE>{$CRLF}";
$out .= "</TABLE>";
echo $out;

					//   Make links to navigate to other places.
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"dispMetricTable.php?ID={$row['seriesID']}\">Metric Table for Series</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$row['seriesID']}\">Roster Grid for Series</A></P>{$CRLF}";

					//   Make links for the Admin/EventManager.
$Privilege = 'GST';
$Privilege = Session_GetAuthority(44, $recID);
if (($_SESSION['evtmgr'] == True) AND ($Privilege <> 'ADM')) $Privilege = 'ADM';
if (($Privilege == 'ADM') OR ($Privilege == 'MGR'))
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"editMetric.php?ID={$recID}\">Edit Metric</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"editMetricValues.php?ID={$recID}\">Edit Metric Values</A></P>{$CRLF}";
	}



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


echo  Tennis_BuildFooter("NORM", "dispEvent.php?ID={$recID}");



function local_ListValues($metricID, $metricValType, $SortDesc)
	{
	$CRLF = "\n";

	$numResponses = 0;
	$qryResult = local_getValueSet($metricID, $metricValType, $SortDesc);
	$row = mysql_fetch_array($qryResult);
	$tmp = '';
	$tmp .= "<TABLE CELLSPACING='0' border='0' width=100%'><TR CLASS='ddTblRow'>";
	$tmp .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName' ALIGN='left'>Value</P></TD>{$CRLF}";
	$tmp .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName' ALIGN='left'>Person</P></TD>{$CRLF}";
	$tmp .= "</TR>";


	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$numResponses ++;
			if ($_SESSION['member'])
				{
				$playerName = $row['prsnFullName'];
				}
			else
				{
				$playerName = $row['prsnPName'];
				}
			$tmp .= "<TR CLASS='ddTblRow'>{$CRLF}";
			$tmp .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['Value']}";
			if ($MetricValueTypeCode==51)
				{
				$tmp .= " %";
				}
			$tmp .= "</P></TD>{$CRLF}";
			$tmp .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$playerName}</P></TD>{$CRLF}";
			$tmp .= "</TR>{$CRLF}{$CRLF}";
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	

	if ($numResponses == 0)
		{
		$tmp .= "<TR CLASS='ddTblRow'><P CLASS='ddFieldData'>*** NO METRIC ASSIGNMENTS MADE ***</P></TD><TD></TD>{$CRLF}";
		}
	
//	$tmp .= "</P>{$CRLF}{$CRLF}";
	return $tmp;

}


function local_getValueSet($metricID, $metricValType, $SortDesc)
	{
	$CRLF = "\n";

	$OrderBy = "ORDER BY ";
	switch ($metricValType)
		{
		case 49: //'INT':
			$OrderBy .= "CAST(qryValueDisp.value AS SIGNED)";
			break;
			
		case 50: //'FLT':
		case 51: //'PCT':
										//The 'DECIMAL' cast type is not available until MySQL 5.0.
			//$OrderBy .= "CAST(qryValueDisp.value AS DECIMAL)";
										//So my work-around is simply that you have to
										//treat float and percent as strings. Meaning the
										//user has to be certain to zero-fill as needed
										//to ensure proper sorting. 12/30/2006.
			$OrderBy .= "qryValueDisp.value";
			break;
			
		default:
			$OrderBy .= "qryValueDisp.value";
		}
	if ($SortDesc) $OrderBy .= " DESC";
	$OrderBy .= ", prsnPName";

	if(!$qryResult = Tennis_OpenViewGeneric('qryValueDisp', "WHERE (metricID={$metricID})", $OrderBy))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	return $qryResult;
}

?> 
