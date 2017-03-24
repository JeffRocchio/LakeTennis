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
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "Series Class";


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

	$caseName = "openRecordset(), using the SeriesList infoSet";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;

	$clubID = 2;
		if ($_SERVER['HTTP_HOST'] == "tennis") $clubID = 2;
		
	$objUnderTest = new series();

	$rstEvents = new recordset();
	$objUnderTest->setQrySpec_id($clubID);
	$objUnderTest->setQrySpec_infoSet('SeriesList');
	$objUnderTest->setQrySpec_subset('4CLUB');
	$rstEvents = $objUnderTest->openRecordset();

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

	$caseName = "Get Series Record";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
		
	$dbRow = array();
		
	$objUnderTest = new series();
	$dbRow = $objUnderTest->getRecord4ID($seriesID);
	
	$message = "Record for Series {$seriesID}:<BR />";
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

	$caseName = "Test function IsUserParticipant(series, memRecID)";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	

	$objUnderTest = new series();

	$message = "";
	$message .= "1-| Person NOT in Series:";
	dm($message);
	$message = "";
	$seriesID = 50;
	$memRecID = 1;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$seriesID = 5;
			$memRecID = 13;
			}
	$result = $objUnderTest->IsUserParticipant($seriesID, $memRecID);
	if ($result) $bResult="TRUE"; else $bResult="FALSE";
	$message .= "Result for Series={$seriesID} and Member={$memRecID}:<BR />";
	$message .= "<BR />...{$bResult}";
	dm($message);
	$message = "";


	$message .= "2-| Person IS in Series:";
	dm($message);
	$message = "";
	$seriesID = 50;
	$memRecID = 1;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$seriesID = 5;
			$memRecID = 14;
			}
	$result = $objUnderTest->IsUserParticipant($seriesID, $memRecID);
	if ($result) $bResult="TRUE"; else $bResult="FALSE";
	$message .= "Result for Series={$seriesID} and Member={$memRecID}:<BR />";
	$message .= "<BR />...{$bResult}";
	dm($message);
	$message = "";

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




?> 
