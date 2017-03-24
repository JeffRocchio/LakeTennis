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
include_once('../clsdef_mdl/simulatedRecordset.class.php');
include_once('../clsdef_mdl/series.class.php');
include_once('../clsdef_mdl/event.class.php');
include_once('../clsdef_mdl/rsvp.class.php');
include_once('../clsdef_mdl/autoAction.class.php');
include_once('../clsdef_view/eventViewChunks.class.php');
include_once('../clsdef_ctrl/eventViewRequests.class.php');
include_once('../clsdef_ctrl/viewFromTemplate.class.php');


include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "autoAction Class";


					//Data we may use across all the test cases.
$clubID = 2;
$seriesID = 5;


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

	$caseName = "Get and Display Simulated Record for an autoAction, with it's Transposed Params";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$actionDBArray = array();

	$autoAction = new autoAction();
	$rstActions = new simulatedRecordset();

	$autoAction->setQrySpec_id(0);
	$autoAction->setQrySpec_infoSet('NOTICES');
	$autoAction->setQrySpec_subset('');

	$statusMessage = "*************************************************<BR />";
	$statusMessage .= "Merged autoAction and Transposed autoActionParm Records.<BR />";
	$statusMessage .= "*************************************************<BR />";
	$rstActions = $autoAction->openRecordset();
	$rstActions->getNextRecord($actionDBArray);
	$statusMessage .= $objDebug->displayDBRecord($actionDBArray, FALSE);
	dm($statusMessage);
	

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
