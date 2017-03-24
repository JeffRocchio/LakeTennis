<?php
/*
	This script takes the post-data from an add-new
	record php script page and uses it to insert a
	new record into the mysql database.
	
	It assumes two hidden fields exist that hold the
	name of the mysql database and the table name.
------------------------------------------------------------------ */
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions_ADMIN.php');


$DEBUG = FALSE;
//$DEBUG = TRUE;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$CRLF = "\n";

				//   Get the table name from the query string. If
				//empty, report error and do nothing.
$tblName = $_POST['meta_TBL'];
if (!$tblName)
	{
	echo "<P>ERROR, no Table Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



$message = Tennis_dbRecordInsert($_POST, $tblName);
$newID=mysql_insert_id($link);
echo "<p>New ID: {$newID}</P>";
echo $message;

				//   Special case #1:
				//   We're adding a new Event,
				//so we also need to add 'RSVP' table
				//records for the new Event.
if ($tblName == "Event")
	{
	$qry = "INSERT rsvp (Event, Person, ClaimCode, Position, Role, Note) ";
	$qry .= "SELECT {$newID} AS Event, ";
	$qry .= "eligible.Person AS Person, ";
	$qry .= "15 AS ClaimCode, ";
	$qry .= "30 AS Position, ";
	$qry .= "20 AS Role, ";
	$qry .= "'' AS Note ";
	$qry .= "FROM eligible "; 
	$qry .= "WHERE (eligible.Series={$_POST['Series']})";
	$qryResult = mysql_query($qry);
	if (!$qryResult)
		{
		$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
		$message .= '<P>Whole query: ' . $qry . "</P>";
		echo "<html><head>";
		echo "<title>Tennis - Posting Record</title>";
		echo "</head>";
		echo "<body>";
		echo "<P>ERROR Attempting to Post RSVPs:</P>";
		echo "<P>{$message}</P>";
		include './INCL_footer.php';
		exit;
		}
	$message = "<p>RSVP Records Added: {$qryResult}</p>";
	echo $message;
	}

				//   Special case #2:
				//   We're adding a new metric,
				//so we also need to add 'value' table
				//records for the new metric.
if ($tblName == "metric")
	{
				//   Create initial privilige record.
	$qry = "INSERT authority (Person, ObjType, ObjID, Privilege, Note) ";
	$qry .= $_POST['UserRecID'] . "AS Person, ";
	$qry .= "44 AS ObjType, ";
	$qry .= "{$newID} AS ObjID, ";
	$qry .= "48 AS Privilege, ";
	$qry .= "Entry added automatically when metric was created by user: " . $_POST['UserID'] ." AS Note ";
	$qryResult = mysql_query($qry);
	if (!$qryResult)
		{
		$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . '<\p>';
		$message .= '<P>Whole query: ' . $qry . '</P>';
		echo "<html><head>";
		echo "<title>Tennis - Posting Record</title>";
		echo "</head>";
		echo "<body>";
		echo "<P>ERROR Attempting to Post authority record:</P>";
		echo "<P>{$message}</P>";
		include './INCL_footer.php';
		exit;
		}
	
				//   Create value records.
	$qry = "INSERT value (metric, Person, Value, Note) ";
	$qry .= "SELECT {$newID} AS metric, ";
	$qry .= "eligible.Person AS Person, ";
	$qry .= "'' AS Value, ";
	$qry .= "'' AS Note ";
	$qry .= "FROM eligible "; 
	$qry .= "WHERE (eligible.Series={$_POST['Series']})";
	$qryResult = mysql_query($qry);
	if (!$qryResult)
		{
		$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . '<\p>';
		$message .= '<P>Whole query: ' . $qry . '</P>';
		echo "<html><head>";
		echo "<title>Tennis - Posting Record</title>";
		echo "</head>";
		echo "<body>";
		echo "<P>ERROR Attempting to Post value records:</P>";
		echo "<P>{$message}</P>";
		include './INCL_footer.php';
		exit;
		}
	$message = "<p>value Records Added: {$qryResult}</p>";
	echo $message;
	}



				//   Output page header stuff.
echo "<html><head>
<title>Added Record in {$tblName}</title>";
//<meta http-equiv='REFRESH' content='0;url={$_POST['meta_RTNPG']}'>
echo "</head>
<body>
<h2>ADDED Record in Table <u>{$tblName}</u></h2>";

echo $message;

echo "<P>
	<a href='{$_POST['meta_RTNPG']}'>OK</a>
	&nbsp;&nbsp;
</P>";


include './INCL_footer.php';

?> 
