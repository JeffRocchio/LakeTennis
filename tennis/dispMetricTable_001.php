<?php
/*
	   This script displays a table of metrics for a
	given series.
	
	   12/28/2006: Created initial version.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


//STANDARD STARTUP STUFF ------------------------------------------>>
$DEBUG = FALSE;
//$DEBUG = TRUE;

$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   Get the series ID from the URL.
				//string.
$seriesID = $_GET['ID'];

				//   Set return page for edits.
$_SESSION['RtnPg'] = "dispMetricTable.php?ID={$seriesID}";


				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


			//   Get the series name.
array($row);
if (!Tennis_GetSingleRecord($row, 'series', $seriesID))
	{
	echo "<P>An Error Has Occurred:<BR>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

$seriesLongName = $row['LongName'];
$seriesShortName = $row['ShtName'];
//-----------------------------------------------------------------<<




//BUILD A STANDARD PAGE HEADER ------------------------------------>>
$tbar = "Tennis: Team Metrics";
$pgL1 = "Details";
$pgL2 = "Metrics For Series";
$pgL3 = $seriesLongName;
echo Tennis_BuildHeader('METRIC', $tbar, $pgL1, $pgL2, $pgL3);
//-----------------------------------------------------------------<<



/*DESCRIPTION OF THE CUSTOM LOGIC --------------------------------->>

		1. Get the necessary metric meta-data for all metrics associated
	with the the series.
	
		2. Using the meta-data, build a query that will pull the value
	rows into columns, group the data by person, and sort on the desired
	column of metric-values.
	
		3. Using the result-set from the above query, and the metric
	meta-data, build the display table.

-----------------------------------------------------------------<<*/



//DECLARE CUSTOM VARIABLES ---------------------------------------->>

				//   Arrays to hold the MySQL data.
array($MetricMeta);

				//   How many metrics are tied to the series?
$numMetrics = 0;

				//   Which metric-ID represents the current sort?
$SortMetric = 0;

				//   Holds the query of step#2.
$qry = "";
//-----------------------------------------------------------------<<




//CUSTOM LOGIC SECTION -------------------------------------------->>

				//   Step #1: Get metric meta-data. And set the
				//current sort-ID. IF there are no metrics defined for
				//the series, show a nice message to that effect and
				//don't attempt to build a table.
$numMetrics = local_getMetricMetaData($MetricMeta, $seriesID);
if ($MetricMeta['1']['ID']  > 0)
				//   YES - we do have some metrics for the series.
	{
	$SortMetric = '1';
	
				//   If there are Announcements to display, show them.
	foreach($MetricMeta as $key => $value)
		{
		if (strlen($MetricMeta[$key]['Announce']) > 3)
			{
			echo "<P><strong>{$MetricMeta[$key]['Name']}</strong> ({$MetricMeta[$key]['ShtName']}):<BR>";
			echo "{$MetricMeta[$key]['Announce']}</P>";
			}
		}
				//   Step #2: Build query to get and organize the
				//detailed metric values such that we can display
				//them into a table.
	$qry = local_BuildMetricValueQuery($MetricMeta, $SortMetric);

				//   Step #3: Build the display table.
	local_BuildMetricTable($MetricMeta, $numMetrics, $qry, $seriesLongName, 'NORM');
	}

else
				//   NO - We don't have any metrics for the series.
	{
		echo "<P>There are no metrics defined for this series.</P>";
	}

				//   Make links to navigate to other places.
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}\">Roster Grid for Series</A></P>{$CRLF}";




echo  Tennis_BuildFooter("NORM", "dispEvent.php?ID={$recID}");




//LOCAL FUNCTIONS =================================================>>

//----------------------------------------------------------------->>
function local_getMetricMetaData(&$MetricMeta, $seriesID)
{
$DEBUG = FALSE;
//$DEBUG = TRUE;

global $CRLF;

$WHERE = "WHERE (seriesID={$seriesID} AND metricDisplayCode <> 32)";
$SORT = "ORDER BY metricSort, ID";

if(!$qryResult = Tennis_OpenViewGeneric('qryMetricDisp', $WHERE, $SORT))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

$row = mysql_fetch_array($qryResult);

$numMetrics = 0;
do
	{
	$numMetrics ++;
	$MetricMeta[$numMetrics]['ID'] = $row['ID'];
	$MetricMeta[$numMetrics]['Name'] = $row['metricName'];
	$MetricMeta[$numMetrics]['ShtName'] = $row['metricShtName'];
	$MetricMeta[$numMetrics]['Announce'] = $row['metricAnnouncement'];
			//   Translate the metric's math-type code into a standard
			//set of mnemonics. These mnemonics tie to the style names
			//in metrics.css.
	switch ($row['metricValTypeCode'])
		{
		case 49:
			$MetricMeta[$numMetrics]['VType'] = 'INT';
			break;
			
		case 50:
			if ($row['metricValTypeCode']==50) $MetricMeta[$numMetrics]['VType'] = 'FLT';
			break;
			
		case 51:
			if ($row['metricValTypeCode']==51) $MetricMeta[$numMetrics]['VType'] = 'PCT';
			break;
			
		default:
			if ($row['metricValTypeCode']==52) $MetricMeta[$numMetrics]['VType'] = 'STR';
		}
	}
while ($row = mysql_fetch_array($qryResult));

if ($DEBUG)
	{
	echo "<P>Number of Metrics: {$numMetrics}</P>{$CRLF}";
	echo "<P>LISTING MetricMeta[][] ARRAY:</P>{$CRLF}<P>";
	foreach($MetricMeta as $key => $value)
		{
		foreach($value as $key2 => $value2)
			{
			echo "row[{$key2}] = {$value2}<BR>";
			}
		echo "<BR>{$CRLF}";
		}
	echo "<BR></P>{$CRLF}{$CRLF}";
	}

return $numMetrics;

} // END FUNCTION



//----------------------------------------------------------------->>
function local_BuildMetricValueQuery(&$MetricMeta, $SortMetric)
{
$DEBUG = FALSE;
//$DEBUG = TRUE;

global $CRLF;


$qry = "";

				//   The query built below pulls the rows of the value
				//table into seperate Metric columns, then groups them
				//by individual person. This allows me to sort
				//the result-set table by any of the metric-columns.
$qry = "
	SELECT 
		value.Person AS prsnID, 
		person.PName AS prsnPName, 
		person.FName AS prsnFName, 
		person.LName AS prsnLName, 
		CONCAT(person.FName,' ',person.LName) AS prsnFullName";
		foreach($MetricMeta as $keyD1 => $valueD1)
			{
			$qry .= ", ";
			$qry .= "MAX(CASE WHEN value.metric = {$MetricMeta[$keyD1]['ID']} THEN Value END) AS M{$keyD1}, ";
			$qry .= "CASE WHEN value.metric = {$MetricMeta[$keyD1]['ID']} THEN value.Note END AS M{$keyD1}Note";
			}
	$qry .= "
	FROM 
		value, 
		person, 
		metric 
	WHERE 
		(value.metric=metric.ID AND value.Person=person.ID) 
		AND (value.metric IN (0";
		foreach($MetricMeta as $keyD1 => $valueD1)
			{
			$qry .= ", ";
			$qry .= $MetricMeta[$keyD1]['ID'];
			}
		$qry .= ")) ";
	$qry .= "
	GROUP BY 
		prsnID
	ORDER BY 
		M{$SortMetric}, 
		prsnPName";
//END query build.




	if ($DEBUG)
		{
		echo "<P>Value Query Built:</P>{$CRLF}";
		echo "<P>{$qry}</P>{$CRLF}{$CRLF}";
		}

return $qry;

} // END FUNCTION




//----------------------------------------------------------------->>
function local_BuildMetricTable(&$MetricMeta, $numMetrics, $qry, $seriesName, $tblFormat)
{
$DEBUG = FALSE;
//$DEBUG = TRUE;

global $CRLF;


				//   "Open" a table.
				//Give the table a title and build it's header row.
$NumCols = $numMetrics + 1;
$TableTitle = "Metrics for<BR>{$seriesName}";
switch ($tblFormat)
	{
	case 'XSMALL':
		$html = "<TABLE CLASS='metric_tblXSmall'>{$CRLF}";
		break;
		
	case 'NORM':
	default:
		$html = "<TABLE CLASS='metric_tblnorm'>{$CRLF}";
	
	}
$html .= "<TR>{$CRLF}";
$html .= "<TD CLASS='metric_tblnorm_TblTitle' COLSPAN={$NumCols}>{$TableTitle}</TD>{$CRLF}</TR>{$CRLF}{$CRLF}";
$html .= "<TR CLASS='metric_tblnorm_RowHdr''>{$CRLF}";
echo $html;
foreach($MetricMeta as $keyD1 => $valueD1)
	{
	$mName = $MetricMeta[$keyD1]['ShtName'];
	$mType = $MetricMeta[$keyD1]['VType'];
	$mID = $MetricMeta[$keyD1]['ID'];
	$html = "<TD CLASS='metric_tblnorm_Hdr{$mType}'><A HREF=\"dispMetric.php?ID={$mID}\">{$mName}</A>{$CRLF}";
	if ($_SESSION['evtmgr'] == TRUE)
		{
		$html .= "<P CLASS=cellEditLink>";
		$html .= "<a href='editMetricValues.php?ID={$mID}'>EDIT</a>";
		$html .= "</P></TD>";
		}
	echo $html;
	}
$html = "<TD CLASS='metric_tblnorm_HdrPerson'>NAME</TD>{$CRLF}";
$html .= "</TR>{$CRLF}{$CRLF}";
echo $html;

				//   Build the rows with the actual metric values in them.
if(!$qryResult = Tennis_OpenViewCustom($qry))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
$row = mysql_fetch_array($qryResult);
do
	{
	$html = "<TR>{$CRLF}";
	foreach($MetricMeta as $keyD1 => $valueD1)
		{
		$mType = $MetricMeta[$keyD1]['VType'];
		if ($mType == 'PCT') { $mPostQlfr = " %"; } else { $mPostQlfr = ""; }
		$mColName = "M{$keyD1}";
		$html .= "<TD CLASS='metric_tblnorm_Cell{$mType}'>{$row[$mColName]}{$mPostQlfr}</TD>{$CRLF}";
		}
	$html .= "<TD CLASS='metric_tblnorm_CellPerson'>{$row['prsnPName']}</TD>{$CRLF}";
	$html .= "</TR>{$CRLF}{$CRLF}";
	echo $html;
	}
while ($row = mysql_fetch_array($qryResult));

				//   "Close" the table.
$html = "</TABLE>{$CRLF}{$CRLF}";
echo $html;



} // END FUNCTION


?> 
