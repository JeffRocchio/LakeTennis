<?php
/*
	   Functions specific the "Metrics" object.
	
	   12/28/2006: Created initial version.

------------------------------------------------------------------ */
include_once('INCL_Tennis_Functions_QUERIES.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');



function metrics_getMetricMetaData(&$MetricMeta, $seriesID)
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
	$MetricMeta[$numMetrics]['Privilege'] = Session_GetAuthority(44, $row['ID']);
	$MetricMeta[$numMetrics]['SortDesc'] = $row['metricSortDesc'];
	$MetricMeta[$numMetrics]['SeriesID'] = $seriesID;

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



function metrics_BuildValueQuery(&$MetricMeta, $SortMetric)
{
$DEBUG = FALSE;
//$DEBUG = TRUE;

global $CRLF;

$SortOrder = "ASC";
if ($MetricMeta[$SortMetric]['SortDesc']) $SortOrder = "DESC";

$qry = "";

				//   The query built below pulls the rows of the value
				//table into seperate Metric columns, then groups them
				//by individual person. This allows me to sort
				//the result-set table by any of the metric-columns.
				//   It also does a type-conversion on the value fields
				//so they will sort properly.
$qry = "
	SELECT *";
	foreach($MetricMeta as $keyD1 => $valueD1)
		{
		$qry .= ", ";
		switch ($MetricMeta[$keyD1]['VType'])
			{
			case 'INT':
				$qry .= "CAST(M{$keyD1} AS SIGNED) AS M{$keyD1}Value";
				break;
				
			case 'FLT':
			case 'PCT':
										//The 'DECIMAL' cast type is not available until MySQL 5.0.
				//$qry .= "CAST(M{$keyD1} AS DECIMAL) AS M{$keyD1}Value";
										//So my work-around is simply that you have to
										//treat float and percent as strings. Meaning the
										//user has to be certain to zero-fill as needed
										//to ensure proper sorting. 12/30/2006.
				$qry .= "M{$keyD1} AS M{$keyD1}Value";
				break;
				
			default:
				$qry .= "M{$keyD1} AS M{$keyD1}Value";
			}
		}
	$qry .= "
	FROM 
		(SELECT 
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
			prsnID)
		AS qryMetricTable 
	ORDER BY 
		M{$SortMetric}Value {$SortOrder}, 
		prsnPName";
//END query build.




	if ($DEBUG)
		{
		echo "<P>Value Query Built:</P>{$CRLF}";
		echo "<P>{$qry}</P>{$CRLF}{$CRLF}";
		}

return $qry;

} // END FUNCTION




function metrics_BuildMetricTable(&$MetricMeta, $numMetrics, $qry, $seriesName, $tblFormat)
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
	$html = "<TD CLASS='metric_tblnorm_Hdr{$mType}'><A HREF=\"/tennis/dispMetric.php?ID={$mID}\">{$mName}</A></TD>{$CRLF}";
	echo $html;
	}
$html = "<TD CLASS='metric_tblnorm_HdrPerson'>NAME</TD>{$CRLF}";
$html .= "</TR>{$CRLF}{$CRLF}";
echo $html;

				//   Build the rows with the actual metric values in them.
if(!$qryResult = Tennis_OpenViewCustom($qry))
	{
	echo "<P>{$lstErrMsg}</P>";
	include 'INCL_footer.php';
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

				//   Build a row at the bottom with a link that allows
				//the user to select a different column for sorting by.
$html = "<TR>{$CRLF}";
foreach($MetricMeta as $keyD1 => $valueD1)
	{
	$mType = $MetricMeta[$keyD1]['VType'];
	$mSeriesID = $MetricMeta[$keyD1]['SeriesID'];
	$sortReturn = $_SERVER['SCRIPT_NAME'];
	$html .= "<TD CLASS='metric_tblnorm_Cell{$mType}'>";
	$html .= "<A HREF=\"$sortReturn?ID={$mSeriesID}&SORTCOL={$keyD1}\">sort</A></TD>{$CRLF}";
	}
	$html .= "<TD CLASS='metric_tblnorm_CellPerson'><P CLASS=cellEditLink>&nbsp;</P></TD>{$CRLF}";
	$html .= "</TR>{$CRLF}{$CRLF}";
	echo $html;


				//   Build a row at the bottom with an edit link
				//for site, series or metric admins.
				//Note that the $i counter is being used to
				//supress the edit-link row if it ends up being
				//empty.
$i = 0;
$html = "<TR>{$CRLF}";
foreach($MetricMeta as $keyD1 => $valueD1)
	{
	$Privilege = $MetricMeta[$keyD1]['Privilege'];
	$mType = $MetricMeta[$keyD1]['VType'];
	$mID = $MetricMeta[$keyD1]['ID'];
	$html .= "<TD CLASS='metric_tblnorm_Cell{$mType}'>";
	$html .= "<P CLASS=cellEditLink>";
	if (($_SESSION['evtmgr'] == TRUE) OR ($Privilege == 'MGR') OR ($Privilege == 'ADM'))
		{
		$i++;
		$html .= "<a href='/tennis/editMetricValues.php?ID={$mID}'>EDIT</a>";
		}
	$html .= "</P></TD>";
	}
$html .= "<TD CLASS='metric_tblnorm_CellPerson'><P CLASS=cellEditLink>&nbsp;</P></TD>{$CRLF}";
$html .= "</TR>{$CRLF}{$CRLF}";
if ($i > 0) echo $html;

				//   "Close" the table.
$html = "</TABLE>{$CRLF}{$CRLF}";
echo $html;

} // END FUNCTION


?> 
