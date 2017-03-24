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

$testSetName = "eventParticipationStatus Class";


					//Data we may use across all the test cases.
$seriesID = 28;
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

	$caseName = "Generate Participation Status for Series ID#28";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	global $seriesID;
	$returnString = "";

	$viewEvtParticStatus = new eventViewRequests();

	$result = $viewEvtParticStatus->getPlayingStatus4Series($seriesID, $returnString, 'UPCOMING');
	dm("Result of Call to getPlayingStatus4Series({$seriesID}, ...):<BR />----------");
	dm($returnString);
	dm("----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
