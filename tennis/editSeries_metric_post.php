<?php
/*
	This script HIDES or UNHIDES a metric.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
//$rtnpg = Session_SetReturnPage();

$DEBUG = FALSE;
//$DEBUG = TRUE;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$CRLF = "\n";

$tblName = 'metric';
$row = '';

				//   Get the query-string data.
$metricID = $_GET['ID'];
if (!metricID)
	{
	echo "<P>ERROR, No Metric Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
$seriesID = $_GET['SID'];
if (!$seriesID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
$action = $_GET['ACT'];
if (!$action)
	{
	echo "<P>ERROR, No Action Specified.</P>";
	include './INCL_footer.php';
	exit;
	}



				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<html><head>";
	echo "<title>Tennis - Record Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR Attempting to Post Update:</P>";
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Define the Update queries.
switch ($action)
	{
	case "V":
		$qry = "UPDATE metric SET Display=31 WHERE ID={$metricID}";
		break;

	case "H":
		$qry = "UPDATE metric SET Display=32 WHERE ID={$metricID}";
		break;
	
	default:
		$qry = "UPDATE metric SET Display=32 WHERE ID={$metricID}";
	}


if ($DEBUG)
	{
	echo "<p>Qry:<BR>{$qry}</p>";
	}

			//   Perform the query and check the results.
			//   IF error, show the actual query sent to MySQL, and the
			//error, if any.
$qryResult = mysql_query($qry);
if (!$qryResult)
	{
	$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
	$message .= '<P>Whole query: ' . $query1 . "</P>";
	echo "<html><head>";
	echo "<title>Tennis - Metric Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR Attempting to Post Update:</P>";
	echo "<P>{$message}</P>";
	include './INCL_footer.php';
	exit;
	}
$message = "<p>Records Affected: {$qryResult}</p>";


				//   Output page header stuff.
echo "<html><head>";
echo "<title>Updated Record in {$tblName}</title>";
echo "<meta http-equiv='REFRESH' content='0;url=editSeries.php?ID={$seriesID}'>";
echo "</head>";
echo "<body>";
echo "<h2>UPDATED Records in Table <u>{$tblName}</u></h2>";

echo $message;

echo "<P>
	<a href='editSeries.php?ID={$seriesID}'>OK</a>
	&nbsp;&nbsp;
</P>";


include './INCL_footer.php';

?> 
