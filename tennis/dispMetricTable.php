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
include_once('./INCL_Tennis_Functions_Metrics.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


//STANDARD STARTUP STUFF ------------------------------------------>>
$DEBUG = FALSE;
//$DEBUG = TRUE;

$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   Get Query string values.
$seriesID = $_GET['ID'];
$metricCOLtoSORTby = $_GET['SORTCOL'];

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

				//   Holds the query of step#2.
$qry = "";
//-----------------------------------------------------------------<<




//CUSTOM LOGIC SECTION -------------------------------------------->>

				//   Step #1: Get metric meta-data. And set the
				//current sort-ID. IF there are no metrics defined for
				//the series, show a nice message to that effect and
				//don't attempt to build a table.
$numMetrics = metrics_getMetricMetaData($MetricMeta, $seriesID);
if ($MetricMeta['1']['ID']  > 0)
				//   YES - we do have some metrics for the series.
	{
	if($metricCOLtoSORTby <=0) $metricCOLtoSORTby = 1;

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
	$qry = metrics_BuildValueQuery($MetricMeta, $metricCOLtoSORTby);

				//   Step #3: Build the display table.
	metrics_BuildMetricTable($MetricMeta, $numMetrics, $qry, $seriesLongName, 'NORM');
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

?> 
