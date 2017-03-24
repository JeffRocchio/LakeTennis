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
include_once('../clsdef_ctrl/eventViewRequests.class.php');
include_once('../clsdef_ctrl/c_eventRecFPCstatus.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "c_eventRecFPCstatus Class";



					//Data we may use across all the test cases
					//in this file.
$eventID = 268;

$result = false;


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

	$caseName = "Generate Participation Status Data for event ID#268";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	// $objDebug->DEBUG = TRUE;

	global $eventID;
	$resultsReport = "";

	$EvtParticStatusInfo = new c_eventRecFPCstatus($eventID);

	$rsvpList = $EvtParticStatusInfo->getRSVPlist();
	$rsvpStatistics = $EvtParticStatusInfo->getRSVPsummaryStats();
	$resultsReport .= "rsvpStatistics ===<BR />";
	foreach($rsvpStatistics as $key => $value) {
		$resultsReport .= $key . ": " . $value . "<BR />";
		}
	$resultsReport .= "<BR />rsvpList ===<BR />";
	foreach($rsvpList as $key => $value) {
		$resultsReport .= "Row # " . $key . "-----><BR /> ";
		foreach($value as $innerKey => $innerValue) {
			$resultsReport .= "--- " . $innerKey . ": " . $innerValue . "<BR />";
			}
		$resultsReport . "<BR />";
		}
	
	dm("Results from creating new instance of c_eventRecFPCstatus:<BR />----------");
	dm($resultsReport);
	dm("----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}

?> 
