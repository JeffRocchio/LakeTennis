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
set_include_path("../");
include_once('INCL_Tennis_CONSTANTS.php');
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_ADMIN_v2.php');
include_once('classdefs/error.class.php');
include_once('classdefs/debug.class.php');
include_once('clsdef_mdl/database.class.php');
include_once('clsdef_mdl/recordset.class.php');
include_once('clsdef_mdl/simulatedRecordset.class.php');
include_once('clsdef_mdl/series.class.php');
include_once('clsdef_mdl/event.class.php');
include_once('clsdef_mdl/rsvp.class.php');
include_once('clsdef_mdl/autoAction.class.php');
include_once('clsdef_mdl/link.class.php');
include_once('clsdef_mdl/txtBlock.class.php');
include_once('clsdef_view/eventViewChunks.class.php');
include_once('clsdef_view/html2text.class.php');
include_once('clsdef_view/linkViews.class.php');
include_once('clsdef_ctrl/emailNotice.class.php');
include_once('clsdef_ctrl/eventViewRequests.class.php');
include_once('clsdef_ctrl/viewFromTemplate.class.php');
include_once('clsdef_ctrl/txtBlockViewRequests.class.php');
include_once('clsdef_ctrl/rsvpUpdateViaEmailLink.class.php');
include_once('clsdef_ctrl/autoActionHandler.class.php');
include_once('INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;


$testSetName = "autoActionHandler Class";


					//Data we may use across all the test cases.


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

	$caseName = "Create a New Instance.";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	dm("Creating new autoActionHandler object.");
	$autoActionHandler = new autoActionHandler();
	dm("new autoActionHandler object has been created.");

	//---Test Script Code Ends Here ---------------------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Fetch 1st Action and Route It.";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$actionResult = FALSE;
	$actionArray = array();

	dm("Creating new autoActionHandler object.");
	$autoActionHandler = new autoActionHandler();

	dm("Calling the Action Fetch and Route Function.");
	$actionResult = $autoActionHandler->handleNextRequest();

	dm("Action Requested ------------------------------");
	$actionArray = $autoActionHandler->get_actionRecArray();
	$message = $objDebug->displayDBRecord($actionArray, FALSE);
	dm($message);

	switch ($actionResult)
		{
		case RTN_SUCCESS:
			dm("Action Returned Success (RTN_SUCCESS).");
			break;

		case RTN_FAILURE:
			dm("Action Returned Failure (RTN_FAILURE).");
			break;

		case RTN_NOACTION:
			dm("Action Returned No Action (RTN_NOACTION).");
			break;

		case RTN_EOF:
			dm("Action Returned End of File (RTN_EOF).");
			break;

		case RTN_WARNING:
			dm("Action Returned Warning (RTN_WARNING).");
			break;

		default:
			 dm("Action Returned Undefined Value: {$actionResult}");
			}

	$objError->ReportAllErrs(0);

	//---Test Script Code Ends Here ---------------------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase03($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Fetch All Actions in a Loop and Route Them (up to 10 actions).";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$actionResult = FALSE;
	$actionArray = array();
	
	$iMaxActions = 10;
	$iCurrActions = 0;

	dm("Creating new autoActionHandler object.");
	$autoActionHandler = new autoActionHandler();

	do	{
		$iCurrActions++;
		$message = "<b>Loop Pass #<i>{$iCurrActions}</i></b>";
		$message .= " ----------------------------------------------------<BR />";
		$message .= "---TASK: Calling the Action Fetch and Route Function.";
		dm($message);
		$actionResult = $autoActionHandler->handleNextRequest();
		$message = "---RESULT: handleNextRequest Returned: ";
		
		switch ($actionResult)
			{
			case RTN_SUCCESS:
				$message .= "Success (RTN_SUCCESS).";
				break;

			case RTN_FAILURE:
				$message .= "Failure (RTN_FAILURE).";
				break;

			case RTN_NOACTION:
				$message .= "No Action (RTN_NOACTION).";
				break;

			case RTN_EOF:
				$message .= "End of File (RTN_EOF).";
				break;

			case RTN_WARNING:
				$message .= "Warning (RTN_WARNING).";
				break;

			default:
				 $message .= "Undefined Value: <i>{$actionResult}</i>";
				}
		dm($message);

		if(($actionResult == RTN_SUCCESS) or ($actionResult == RTN_NOACTION))
			{
			$message = "---RESULT: Returned Action Record<BR />";
			$message .= "--------------------------------------------------<BR />";
			$actionArray = $autoActionHandler->get_actionRecArray();
			$message .= $objDebug->displayDBRecord($actionArray, FALSE);
			$message .= "--------------------------------------------------<BR />";
			dm($message);
			}
		$objError->ReportAllErrs(0);

		if($iCurrActions == $iMaxActions)
			{
			$message = "------------------------------<BR />";
			$message .= "<b>Potential Infinite Loop.<BR />";
			$message .= " Breaking out due to max limit <BR />";
			$message .= "({$iMaxActions})</b><BR />";
			$message .= "------------------------------";
			dm($message);
			break;
			}
		} while ($actionResult != RTN_EOF);

	//---Test Script Code Ends Here ---------------------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase04($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Test the rsvp update request email.";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$actionResult = FALSE;
	$actionArray = array();
	
	$autoActionID2Test = 3;

	dm("Creating new autoActionHandler object.");
	$autoActionHandler = new autoActionHandler();

	$message = "---TASK: Calling the Action Fetch and Handle Function.";
	dm($message);

	$actionResult = $autoActionHandler->handleRequestByID($autoActionID2Test);
	$message = "---RESULT: handleRequestByID Returned: ";
	switch ($actionResult)
		{
		case RTN_SUCCESS:
			$message .= "Success (RTN_SUCCESS).";
			break;

		case RTN_FAILURE:
			$message .= "Failure (RTN_FAILURE).";
			break;

		case RTN_NOACTION:
			$message .= "No Action (RTN_NOACTION).";
			break;

		case RTN_EOF:
			$message .= "End of File (RTN_EOF).";
			break;

		case RTN_WARNING:
			$message .= "Warning (RTN_WARNING).";
			break;

		default:
			 $message .= "Undefined Value: <i>{$actionResult}</i>";
			}
	dm($message);

	if(($actionResult == RTN_SUCCESS) or ($actionResult == RTN_NOACTION))
		{
		$message = "---RESULT: Returned Action Record<BR />";
		$message .= "--------------------------------------------------<BR />";
		$actionArray = $autoActionHandler->get_actionRecArray();
		$message .= $objDebug->displayDBRecord($actionArray, FALSE);
		$message .= "--------------------------------------------------<BR />";
		dm($message);
		}
	$objError->ReportAllErrs(0);


	//---Test Script Code Ends Here ---------------------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




?> 
