<?php
/*
	This script takes the post-data from an add-new
	record php script page and uses it to insert a
	new record into the mysql database.
	
	05/02/2009: This script was modified so that it can handle the creation
	of associative table records also. This was done to accomodate the new
	multi-club feature via the ClubMember table. This feature is accomplished
	by adding a send table-specifier to the meta_ fields in the form, and
	then by using a field naming convention for that associative table and
	it's fields. We also assume that the associative table has two ID fields
	that define the association.
	
	It assumes that several hidden fields exist that hold the
	table names to be inserted into.
---------------------------------------------------------------------------- */
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');


$DEBUG = FALSE;
//$DEBUG = TRUE;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

global $CRLF;

//----Declare local variables---------------------------------------------------

				//   The database pointer.
$link;

				//   The table name of the master table we are inserting into.
$tblName = "";

				//   To build a user-display status message.
$message = "";

				//   To hold the ID number of the master record after it has
				//been inserted into the table.
$newID = 0;

				//   To define the parameters for an associative table, if any.
$T2TblName = "";
$T2TblIDKnownField = "";
$T2TblIDKnownValue = "";
$T2TblIDUnknownField = "";
$T2Post = array();



//----CODE----------------------------------------------------------------------

				//   Get the table name from the POST data. If
				//empty, report error and do nothing.
if (array_key_exists('meta_TBL', $_POST)) $tblName = $_POST['meta_TBL'];
if ($tblName == "")
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

$message = "Insert Into {$tblName} -- ";
$message .= Tennis_dbRecordInsert($_POST, $tblName);
$newID=mysql_insert_id($link);
$message .= "New ID: {$newID}";


				//   Determine if there is also an associative table entry to
				//to insert (e.g., ClubMember when adding a new person). If so,
				//extract the meta-data and field-names and values for that entry
				//and do the record insert.
if (array_key_exists('meta_TBLT2_NAME', $_POST))
	{
	$T2TblName = $_POST['meta_TBLT2_NAME'];
	$T2TblIDKnownField = $_POST['meta_TBLT2_IDKNOWN_FLD'];
	$T2TblIDKnownValue = $_POST['meta_TBLT2_IDKNOWN_VAL'];
	$T2TblIDUnknownField = $_POST['meta_TBLT2_IDUNKNOWN_FLD'];
	$T2Post[$T2TblIDKnownField] = $T2TblIDKnownValue;
	$T2Post[$T2TblIDUnknownField] = $newID;
	foreach ($_POST as $key => $value)
		{
		if ($DEBUG)
			{
			echo "<P>key: {$key} // Value: {$value}</P>";
			}
		if (substr($key, 0, 14) == 'meta_TBLT2FLD_')
			{
			$fieldName = substr($key, 14);
			$T2Post[$fieldName] = $value;
			}
		}
	$message .= "<BR /><BR />Insert Into {$T2TblName} -- ";
	$message .= Tennis_dbRecordInsert($T2Post, $T2TblName);
	$newID=mysql_insert_id($link);
	$message .= "New ID: {$newID}";
	}

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
	$message .= "<BR /><BR />Creation of RSVPs: ";
	$message .= "RSVP Records Added: {$qryResult}";
	}

				//   Special case #2:
				//   We're adding a new metric,
				//so we also need to add 'value' table
				//records for the new metric.
if ($tblName == "metric")
	{
				//   Create initial privilige record.
	$qry = "INSERT INTO authority (Person, ObjType, ObjID, Privilege, Note) ";
	$qry .= "VALUES(";
	$qry .= $_POST['meta_UserRecID'] . ", ";
	$qry .= "44, ";
	$qry .= "{$newID}, ";
	$qry .= "48, ";
	$qry .= "'Entry added automatically when metric was created by user: " . $_POST['meta_UserID'] .".'";
	$qry .= ")";
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
	$message .= "<BR /><BR />Default Authority Record Added.";
	
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
	$message .= "<BR /><BR />Metric Value Records Added.";
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
