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
include_once('../classdefs/error.class.php');
include_once('../classdefs/debug.class.php');
include_once('../clsdef_mdl/database.class.php');
include_once('../clsdef_mdl/event.class.php');
include_once('../clsdef_mdl/recordset.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "event Class";


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

	$caseName = "openRecordset(), using the 4Series infoSet";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;

	$objUnderTest = new event();
	$rst = new recordset();
	$objUnderTest->setQrySpec_id($seriesID);
	$objUnderTest->setQrySpec_infoSet('4Series');
	$objUnderTest->setQrySpec_subset('UPCOMING');
	$rst = $objUnderTest->openRecordset();

	if($objError->getErrCount($reportedClass="UNREPORTED")>0)
		{
		dm("Listing Out All Registered Errors:");
		$errCount = $objError->ReportAllErrs(0, FALSE);
		}

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Get Event Record";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$eventID = 268;
		if ($_SERVER['HTTP_HOST'] == "tennis") $eventID = 27;
		
	$dbRow = array();
		
	$objUnderTest = new event();
	$dbRow = $objUnderTest->getRecord4ID($eventID);
	
	$message = "Record for Series {$eventID}:<BR />";
	$message .= $objDebug->displayDBRecord($dbRow, FALSE);
	dm($message);

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase03($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Test Case Name Goes Here";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




?> 
