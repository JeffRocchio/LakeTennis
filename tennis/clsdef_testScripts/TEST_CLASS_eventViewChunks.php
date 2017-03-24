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
include_once('../clsdef_mdl/event.class.php');
include_once('../clsdef_mdl/rsvp.class.php');
include_once('../clsdef_view/eventViewChunks.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "eventViewChunks Class";


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

	$caseName = "Generate RSVP List for Event ID#28";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$eventID = 28;
	$returnString = "";

	$EvtViewChunks = new eventViewChunks();

	$result = $EvtViewChunks->getRSVPstatString($eventID, $returnString);
	dm("Result of Call to getRSVPstatString(28, ...):<BR />----------");
	dm($returnString);
	dm("----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}





function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Generate Current Event Titles for Series ID#5";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	

	$objDebug->DEBUG = FALSE;

	$seriesID = 5;
	$dbmsRow = array();
	$eventTitle = "";

	$rstEvents = new recordset();
	$objEvent = new event();

	$objEvent->setQrySpec_id($seriesID);
	$objEvent->setQrySpec_infoSet('4Series');
	$objEvent->setQrySpec_subset('UPCOMING');
	$rstEvents = $objEvent->openRecordset();
	dm("Event Rows Fetched: {$rstEvents->get_rowsAffected()}");
	if($objError->getErrCount($reportedClass="UNREPORTED")>0)
		{
		dm("Listing Out All Registered Errors:");
		$errCount = $objError->ReportAllErrs(0, FALSE);
		}
	elseif($rstEvents->get_rowsAffected()==0)
		{
		dm("No Current Events Available");
		}
	else
		{
		$EvtViewChunks = new eventViewChunks();
		while ($rstEvents->getNextRecord($dbmsRow)<>RTN_EOF)
			{
			$EvtViewChunks->MakeEventHeaderString($dbmsRow, $eventTitle);
			dm("Event Name: {$eventTitle}");
			}
		}
	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
