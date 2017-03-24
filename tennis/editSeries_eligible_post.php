<?php
/*
	This script ADDS or REMOVES a person from
	the series eligible list.
	
	   MODIFIED: 12/10/2006 JRR. Added the queries to add/remove
	the metric-value associative records.
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

$tblName = 'eligible';
$row = '';

				//   Get the query-string data.
$prsnID = $_GET['ID'];
if (!$prsnID)
	{
	echo "<P>ERROR, No Person Selected.</P>";
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

				//   Define the Insert/Update queries.
switch ($action)
	{
	case "A":
				//   Insert eligible record.
		$query1 = "INSERT INTO {$tblName} SET ";
		$query1 .= "ID='0', ";
		$query1 .= "Series='{$seriesID}', ";
		$query1 .= "Person='{$prsnID}'";
				//   Insert new rsvp records for all events for this new person.
		$query2 = "INSERT rsvp (Event, Person, ClaimCode, Position, Role, Note) ";
		$query2 .= "SELECT Event.ID Event, ";
		$query2 .= "{$prsnID} AS Person, ";
		$query2 .= "15 AS ClaimCode, ";
		$query2 .= "30 AS Position, ";
		$query2 .= "20 AS Role, ";
		$query2 .= "'' AS Note ";
		$query2 .= "FROM Event ";
		$query2 .= "WHERE (Event.Series={$seriesID})";
				//   If there are metrics defined for this series, insert
				//new metric-value records for all metrics for this new person.
		$query3 = "INSERT value (metric, Person, Value, Note) ";
		$query3 .= "SELECT metric.ID AS metricID, ";
		$query3 .= "{$prsnID} AS Person, ";
		$query3 .= "0 AS Value, ";
		$query3 .= "'' AS Note ";
		$query3 .= "FROM metric ";
		$query3 .= "WHERE (metric.Series={$seriesID})";
		break;

	case "R":
				//   If there are metrics defined for this series, delete
				//the metric-value records for all metrics for this person.
		$query1 = "DELETE value ";
		$query1 .= "FROM value, metric ";
		$query1 .= "WHERE (value.metric=metric.ID AND value.Person={$prsnID} AND metric.Series={$seriesID})";
				//   Delete the rsvp records for all events for this removed person.
		$query2 = "DELETE rsvp ";
		$query2 .= "FROM rsvp, Event ";
		$query2 .= "WHERE (rsvp.Event=Event.ID AND rsvp.Person={$prsnID} AND Event.Series={$seriesID})";
				//   Delete eligible record.
		$query3 = "DELETE eligible ";
		$query3 .= "FROM eligible ";
		$query3 .= "WHERE (eligible.Person={$prsnID} AND eligible.Series={$seriesID})";
				//   If there are metrics defined for this series, remove
				//the metric-value records for for this removed person.
		break;
	
	default:
		$query1 = "";
		$query2 = "";
		$query3 = "";

	}


if ($DEBUG)
	{
	echo "<p>Query1:<BR>{$query1}</p>";
	echo "<p>Query2:<BR>{$query2}</p>";
	echo "<p>Query3:<BR>{$query3}</p>";
	}

			//   Perform the queries and check the results.
			//   IF error, show the actual query sent to MySQL, and the
			//error, if any.
			
			//   1st Query.
$qryResult = mysql_query($query1);
if (!$qryResult)
	{
	$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
	$message .= '<P>Whole query: ' . $query1 . "</P>";
	echo "<html><head>";
	echo "<title>Tennis - Eligibility Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR Attempting to Post Update:</P>";
	echo "<P>{$message}</P>";
	include './INCL_footer.php';
	exit;
	}
$message = "<p>Records Affected: {$qryResult}</p>";

			//   2nd Query.
$qryResult = mysql_query($query2);
if (!$qryResult)
	{
	$message .= 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
	$message .= '<P>Whole query: ' . $query2 . "</P>";
	echo "<html><head>";
	echo "<title>Tennis - Eligibility Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR Attempting to Post Update:</P>";
	echo "<P>{$message}</P>";
	include './INCL_footer.php';
	exit;
	}
$message .= "<p>Records Affected: {$qryResult}</p>";

			//   3nd Query.
$qryResult = mysql_query($query3);
if (!$qryResult)
	{
	$message .= 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
	$message .= '<P>Whole query: ' . $query3 . "</P>";
	echo "<html><head>";
	echo "<title>Tennis - Eligibility Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR Attempting to Post Update:</P>";
	echo "<P>{$message}</P>";
	include './INCL_footer.php';
	exit;
	}
$message .= "<p>Records Affected: {$qryResult}</p>";


				//   Output page header stuff.
echo "<html><head>";
echo "<title>Updated Record in {$tblName}</title>";
if (!$DEBUG) echo "<meta http-equiv='REFRESH' content='0;url=editSeries.php?ID={$seriesID}'>";
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
