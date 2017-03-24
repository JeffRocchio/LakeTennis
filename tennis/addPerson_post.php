<?php
/*
	This script takes the post-data from addPerson.php
	and uses it to insert a new record into the
	mysql database.
	
	It assumes two hidden fields exist that hold the
	name of the mysql database and the table name.
*/


/*DECLARE VARIABLES -------------------------------------------------

$DEBUG
	:: AS Boolean.
	:: TRUE if running in debug mode. Used to control execution
	   debug related code.

$message
	:: AS String.
	:: Reusable string to build a status or error message for
	   eventual display to the user.

$dbName
	:: AS String.
	:: The name of the database that we're listing tables or
	   columns for. We get this from the URL Query-String as
	   passed in from a previous page.
	   
$tblName
	:: AS String.
	:: Name of the table to list the columns for. We get this
	   from the URL Query-String as passed in from a previous
	   page.

------------------------------------------------------------------ */
include './INCL_Tennis_Functions_ADMIN.php';

$DEBUG = FALSE;
//$DEBUG = TRUE;

				//   Get the database and table name from the query string. If
				//empty, report error and do nothing.
$dbName = $_POST['meta_DB'];
if (!$dbName)
	{
	echo "<P>ERROR, no Database Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
$tblName = $_POST['meta_TBL'];
if (!$tblName)
	{
	echo "<P>ERROR, no Table Selected.</P>";
	include './INCL_footer.php';
	exit;
	}


				//   Output page header stuff.
echo "<html><head>
<title>Adding Record to {$tblName}</title>
</head>
<body>
<h2>ADDING Record to Table <u>{$tblName}</u> in <u>{$dbName}</u></h2>";


$message = Tennis_dbRecordInsert($_POST, $tblName);

echo $message;

echo "<P>
	<a href='listSeriesRoster.php'>OK</a>
	&nbsp;&nbsp;
	<a href='addPerson.php'>ADD Another</a>
	&nbsp;&nbsp;
	<a href='../mysqlmaint/listRecords.php?DB={$dbName}&TBL={$tblName}'>List All Records</a>
</P>";


include './INCL_footer.php';

?> 
