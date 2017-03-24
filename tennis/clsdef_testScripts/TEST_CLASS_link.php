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
include_once('../clsdef_mdl/recordset.class.php');
include_once('../clsdef_mdl/series.class.php');
include_once('../clsdef_mdl/link.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "link Class";


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

	$caseName = "Create List of Links for Series";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$urlArray = array();
	$testObject = new link();

	$seriesID = 28;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
	
	$urlArray = $testObject->getURLs4SeriesAsArray($seriesID);

	$message = "Dump of Created Links Array ---:<BR />";
	foreach ($urlArray as $rowKey => $rowArray)
		{
		$message .= "<BR />ROW: {$rowKey}<BR />";
		$message .= $objDebug->displayDBRecord($rowArray, FALSE);
		}
	dm($message);

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Test Case Name Goes Here";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;


	
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
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;


	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




?> 
