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
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "RSVP Class";


					//Data we may use across all the test cases.
$clubID = 2;
$seriesID = 5;

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

	$caseName = "Open an RSVP RecordSet For Event ID#28";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	

	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$eventID = 28;
	$dbmsRow = array();
	$varType = "";

	$rstRsvps = new recordset();
	$objRSVP = new rsvp();

	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_subset('claimAVAIL');

	$rstRsvps = $objRSVP->openRecordset();
	if($rstRsvps->get_lastOpError() == 0)
		{
		dm("Rows Fetched: {$rstRsvps->get_rowsAffected()}");
		if($rstRsvps->get_rowsAffected()==0)
			{
			dm("No RSVPs of the Requested Type Available for Event ID {$eventID}");
			}
		else
			{
			dm("RSVP RecordSet Appears to Have Been Sucessfully Opened.");
			}
		}

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

	$caseName = "Test Info Subset 'PosPLAYING' Using Event ID#28";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	

	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$eventID = 28;
	$dbmsRow = array();
	$varType = "";

	$rstRsvps = new recordset();
	$objRSVP = new rsvp();

	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_subset('PosPLAYING');

	$rstRsvps = $objRSVP->openRecordset();
	if($rstRsvps->get_lastOpError() == 0)
		{
		dm("Rows Fetched: {$rstRsvps->get_rowsAffected()}");
		if($rstRsvps->get_rowsAffected()==0)
			{
			dm("No RSVPs of the Requested Type Available for Event ID {$eventID}");
			}
		else
			{
			dm("RSVP RecordSet Appears to Have Been Sucessfully Opened.");
			}
		}

	if($objError->getErrCount($reportedClass="UNREPORTED")>0)
		{
		dm("Listing Out All Registered Errors:");
		$errCount = $objError->ReportAllErrs(0, FALSE);
		}
	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
