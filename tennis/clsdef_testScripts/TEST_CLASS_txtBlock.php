<?php
/*
	Test cases for associated class def code.
	
	NOTES:
		1. Each test case *must* be defined using a seperate function whose 
			name is of the form: "TstCase##", where "##' represents the
			sequence number of the test case.
================================================================================
==============================================================================*/
session_start();
include_once('../INCL_Tennis_CONSTANTS.php');
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../INCL_Tennis_Functions_ADMIN_v2.php');
include_once('../classdefs/error.class.php');
include_once('../classdefs/debug.class.php');
include_once('../clsdef_mdl/database.class.php');
include_once('../clsdef_mdl/recordset.class.php');
include_once('../clsdef_mdl/txtBlock.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "txtBlock Class";


					//Data we may use across all the test cases.
$clubID = 1;
$seriesID = 1;
$seriesLongName = "";
$seriesShtName = "";

$result = false;
$dbmsRow = array();


//==============================================================================
//   TEST CASE EXECUTION ENGINE
include_once('../clsdef_testScripts/TEST_CLASS_INCLUDE_BaseexeEngine.php');
//==============================================================================


//====TEST CASE FUNCTIONS=======================================================
//==============================================================================
function TstCase01($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Fetch Single Record by ID";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new txtBlock();
	$row = $ObjUnderTest->fetch_Record_byID(1);

	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= $objDebug->displayDBRecord($row, FALSE);
	dm($statusMessage);
	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "See if Text Block Record is Active";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new txtBlock();
	$row = $ObjUnderTest->fetch_Record_byID(1);
	$active = $ObjUnderTest->determineIfActive($row);

	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= "Record Is: ";
	if($active) $statusMessage .= "ACTIVE"; else $statusMessage .= "INACTIVE";
	$statusMessage .= "<br /><br />";
	$statusMessage .= $objDebug->displayDBRecord($row, FALSE);
	dm($statusMessage);
	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase03($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Fetch Single Record by Where Clause";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new txtBlock();
	$whereClause = "WHERE ID=1";
	$row = $ObjUnderTest->fetch_Record_byWhere($whereClause);

	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= $objDebug->displayDBRecord($row, FALSE);
	dm($statusMessage);

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase04($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Open Recordset";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= "This Test Case is yet to Be Implemented.";
//	$statusMessage .= $objDebug->displayDBRecord($row, FALSE);
	dm($statusMessage);

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase05($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Toggle Active Status for One Record";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new txtBlock();

		//   Text block to work with.
	$blockID = 1;
		//   Declare scratch variables we need for text case.
	$originalStatus = FALSE;
	$newStatus = FALSE;
	$currStatus = FALSE;
	$currStatusTxt = "";

		//   Begin test logic.
	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	dm($statusMessage);

	$row = $ObjUnderTest->fetch_Record_byID($blockID);
	$originalStatus = $row['Active'];
	$currStatus = $originalStatus;
	if($currStatus) $currStatusTxt = "TRUE"; else $currStatusTxt = "FALSE";
	$statusMessage = "Step 1 - Block's Original Active Field Value Is: {$currStatusTxt}<br /><br />";
	dm($statusMessage);

	$newStatus = !$originalStatus;
	$ObjUnderTest->update_BlockStatus($blockID, $newStatus);
	$row = $ObjUnderTest->fetch_Record_byID($blockID);
	$currStatus = $row['Active'];
	if($currStatus) $currStatusTxt = "TRUE"; else $currStatusTxt = "FALSE";
	$statusMessage = "Step 2 - Active Field Value Should Now be Opposite of Step 1. ";
	$statusMessage .= "Actual Field Value Is: ";
	$statusMessage .= $currStatusTxt;
	$statusMessage .= "<br /><br />";
	dm($statusMessage);

	$ObjUnderTest->update_BlockStatus($blockID, $originalStatus);
	$row = $ObjUnderTest->fetch_Record_byID($blockID);
	$currStatus = $row['Active'];
	if($currStatus) $currStatusTxt = "TRUE"; else $currStatusTxt = "FALSE";
	$statusMessage = "Step 3 - Active Field Value Should Now be Same as Step 1. ";
	$statusMessage .= "Actual Field Value Is: ";
	$statusMessage .= $currStatusTxt;
	$statusMessage .= "<br /><br />";
	dm($statusMessage);

//	$statusMessage .= $objDebug->displayDBRecord($row, FALSE);
//	dm($statusMessage);


	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
