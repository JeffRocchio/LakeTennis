<?php
/*
	This script takes the post-data from an edit record php script page and 
	uses it to update the changed record into the mysql database.

	05/03/2009: This script was modified so that it can handle the update
	of an associative table record that goes with the 'master' record being
	edited here. This was done to accomodate the new multi-club feature via
	the ClubMember table. This feature is accomplished by adding a send 
	table-specifier to the meta_ fields in the form, and then by using a 
	field naming convention for that associative table and it's fields. 
	We also assume that the associative table has two ID fields that define
	the association.
	
	It assumes that a hidden field exists which holds the table name.
----------------------------------------------------------------------------- */
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');

$DEBUG = FALSE;
//$DEBUG = TRUE;


//----GLOBALS-------------------------------------------------------------------
global $CRLF;

$lstErrExist = FALSE;
$lstErrMsg = "";



//----LOCAL VARIABLES-----------------------------------------------------------
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
$T2TblRecID = "";
$T2TblID1Field = "";
$T2TblID1Value = "";
$T2TblID2Field = "";
$T2TblID2Value = "";
$T2Post = array();


//====CODE======================================================================

				//   Get the table name from the post data. If
				//empty, report error and do nothing.
if (array_key_exists('meta_TBL', $_POST)) $tblName = $_POST['meta_TBL'];
if ($tblName == "")
	{
	echo "<html><head>";
	echo "<title>Tennis - Record Update</title>";
	echo "</head>";
	echo "<body>";
	echo "<P>ERROR, No Table Specified.</P>";
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


$message = Tennis_dbRecordUpdate($_POST, $tblName);
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

				//   Determine if there is also an associative table entry to
				//to update (e.g., ClubMember when editing a person). If so,
				//extract the meta-data, field-names, and values for that entry
				//and do the record insert.
if (array_key_exists('meta_TBLT2_NAME', $_POST))
	{
	$T2TblName = $_POST['meta_TBLT2_NAME'];
	$T2TblRecID = $_POST['meta_TBLT2_ID'];
	$T2TblID1Field = $_POST['meta_TBLT2_ID1_FLD'];
	$T2TblID1Value = $_POST['meta_TBLT2_ID1_VAL'];
	$T2TblID2Field = $_POST['meta_TBLT2_ID2_FLD'];
	$T2TblID2Value = $_POST['meta_TBLT2_ID2_VAL'];
	$T2Post['ID'] = $T2TblRecID;
	$T2Post[$T2TblID1Field] = $T2TblID1Value;
	$T2Post[$T2TblID2Field] = $T2TblID2Value;
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
	$message = Tennis_dbRecordUpdate($T2Post, $T2TblName);
	echo $message;
	}



echo ADMIN_Post_HeaderOK($tblName, $_POST['meta_RTNPG'], $message);

include './INCL_footer.php';

?> 
