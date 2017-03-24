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
include_once('../clsdef_mdl/series.class.php');
include_once('../clsdef_ctrl/rsvpUpdateViaEmailLink.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "rsvpUpdateViaEmailLink Class";


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

	$caseName = "Validate the loginKey_Create() Function.";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new rsvpUpdateViaEmailLink();

			//loginKey_Create($memRecID, $objType, $objID)
	$hashKey1 = $ObjUnderTest->loginKey_Create(5, OBJSERIES, 1);
	$hashKey2 = $ObjUnderTest->loginKey_Create(51, OBJSERIES, 11);
	$hashKey3 = $ObjUnderTest->loginKey_Create(511, OBJSERIES, 111);

	$statusMessage = "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= "...Single Digit memID and Series ID: {$hashKey1} <br />";
	$statusMessage .= "...Two Digit memID and Series ID: {$hashKey2} <br />";
	$statusMessage .= "...Three Digit memID and Series ID: {$hashKey3} <br />";
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

	$caseName = "Validate the loginKey_Parse() Function.";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$ObjUnderTest = new rsvpUpdateViaEmailLink();
	
	$parsedValues = array();

			//   First, create some hash keys using
			//loginKey_Create($memRecID, $objType, $objID)
	$hashKey1 = $ObjUnderTest->loginKey_Create(24, OBJSERIES, 5);
	$hashKey2 = $ObjUnderTest->loginKey_Create(51, OBJSERIES, 11);
	$hashKey3 = $ObjUnderTest->loginKey_Create(511, OBJSERIES, 111);

	$statusMessage = "";
	$statusMessage .= "|---<i>TEST CASE RESULT</i>---|<br />";
	$statusMessage .= "...STEP-1: Create Some Hash Keys.<br />";
	$statusMessage .= "... ...Single Digit memID and Series ID: {$hashKey1} <br />";
	$statusMessage .= "... ...Two Digit memID and Series ID: {$hashKey2} <br />";
	$statusMessage .= "... ...Three Digit memID and Series ID: {$hashKey3} <br />";
	
			//   Second, parse the hash keys back out using
			//loginKey_Parse($key).
	$parsedValues = $ObjUnderTest->loginKey_Parse($hashKey1);
	$statusMessage .= "<br />...STEP-2: Parse the Hash Keys Back Out.<br />";
	$statusMessage .= "... ...Single Digit memID and Series ID:<br />";
	$statusMessage .= "... ... ... memRecID: {$parsedValues[1]} <br />";
	$statusMessage .= "... ... ... objType: {$parsedValues[2]} <br />";
	$statusMessage .= "... ... ... objID: {$parsedValues[3]} <br />";

	$parsedValues = $ObjUnderTest->loginKey_Parse($hashKey2);
	$statusMessage .= "... ...Two Digit memID and Series ID: {$hashKey2} <br />";
	$statusMessage .= "... ... ... memRecID: {$parsedValues[1]} <br />";
	$statusMessage .= "... ... ... objType: {$parsedValues[2]} <br />";
	$statusMessage .= "... ... ... objID: {$parsedValues[3]} <br />";

	$parsedValues = $ObjUnderTest->loginKey_Parse($hashKey3);
	$statusMessage .= "... ...Three Digit memID and Series ID: {$hashKey3} <br />";
	$statusMessage .= "... ... ... memRecID: {$parsedValues[1]} <br />";
	$statusMessage .= "... ... ... objType: {$parsedValues[2]} <br />";
	$statusMessage .= "... ... ... objID: {$parsedValues[3]} <br />";

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

	$caseName = "Validate the assessUserState(memRecID, clubID, objType, objID) Function.";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$userState = "";
	$ObjUnderTest = new rsvpUpdateViaEmailLink();
	
	$message = "";
	$message = "|---<i>TEST CASE RESULT</i>---|<br />";
	dm($message);
	$message = "";

	$message .= "...1-| Person is in Club, but not in Series:";
	$clubID = 2;
	$seriesID = 50;
	$memRecID = 999;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$clubID = 2;
			$seriesID = 5;
			$memRecID = 25;
			}
	$userState = $ObjUnderTest->assessUserState($memRecID, $clubID, OBJSERIES, $seriesID);
	$message .= "<BR />... ...memRecID: {$memRecID}";
	$message .= "<BR />... ...clubID: {$clubID}";
	$message .= "<BR />... ...seriesID: {$seriesID}";
	$message .= "<BR />... ...RESULTING STATE: {$userState}";
	dm($message);
	$message = "";


	$message .= "...2-| Person is not in Club, but is in series:";
	$clubID = 2;
	$seriesID = 50;
	$memRecID = 999;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$clubID = 2;
			$seriesID = 9;
			$memRecID = 21;
			}
	$userState = $ObjUnderTest->assessUserState($memRecID, $clubID, OBJSERIES, $seriesID);
	$message .= "<BR />... ...memRecID: {$memRecID}";
	$message .= "<BR />... ...clubID: {$clubID}";
	$message .= "<BR />... ...seriesID: {$seriesID}";
	$message .= "<BR />... ...RESULTING STATE: {$userState}";
	dm($message);
	$message = "";


	$message .= "...3-| Person is in Club and is in series:";
	$clubID = 2;
	$seriesID = 50;
	$memRecID = 999;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$clubID = 2;
			$seriesID = 5;
			$memRecID = 14;
			}
	$userState = $ObjUnderTest->assessUserState($memRecID, $clubID, OBJSERIES, $seriesID);
	$message .= "<BR />... ...memRecID: {$memRecID}";
	$message .= "<BR />... ...clubID: {$clubID}";
	$message .= "<BR />... ...seriesID: {$seriesID}";
	$message .= "<BR />... ...RESULTING STATE: {$userState}";
	dm($message);
	$message = "";


	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase04($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Simulate Login Via URL query string key.";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$userState = "";
	$hashKey = "";
	$parsedValues = array();
	$clubID = 0;
	$seriesID = 0;
	$memRecID = 0;
	$errInfo = array();
	$errDisplay = "";
	$ObjUnderTest = new rsvpUpdateViaEmailLink();
	
	$message = "";
	$message = "|---<i>TEST CASE RESULT</i>---|<br />";
	dm($message);
	$message = "";

	$message .= "...1-| Person is in Club, but not in Series:";
	$clubID = 2;
	$seriesID = 50;
	$memRecID = 999;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$clubID = 2;
			$seriesID = 5;
			$memRecID = 25;
			}
	$message .= "<BR />... ...memRecID: {$memRecID}";
	$message .= "<BR />... ...seriesID: {$seriesID}";
	dm($message);
	$message = "";
	$hashKey = $ObjUnderTest->loginKey_Create($memRecID, OBJSERIES, $seriesID);
	$loginResult = $ObjUnderTest->userLogin($hashKey, "RSVPUPDATE");
	if ($loginResult) $bResult="TRUE"; else $bResult="FALSE";
	$message .= "... ...userLogin() RESULT: {$bResult}";
	dm($message); $message = "";
	if (!$loginResult)
		{
		$errInfo = $objError->GetLastErr();
		if ($errInfo[1] == ERRCLASS_NOTAUTH)
			{
			$errDisplay = "*** UNABLE TO PROCESS REQUEST ***";			
			$errDisplay .= "<BR /><BR />";			
			$errDisplay .= "You are not a member of the club or series specified";			
			$errDisplay .= " in the email link.";			
			$errDisplay .= " This may have occurred if you clicked the link from";			
			$errDisplay .= " an old email message.";			
			$errDisplay .= "<BR /><BR />";			
			$errDisplay .= " Please contact your club manager for help with this.";			
			$message .= $errDisplay;
			dm($message); $message = "";
			}
		$objError->ReportAllErrs(0);
		}

				//NOTE: the selection of $memRecID needs to align to who you are
				//actually currently logged in as in order to understand the
				//test results. E.g., if you are logged as as the specified user,
				//then you should expect a TRUE return and nothing had happened.
				//If, on the other hand, you are logged in as somone else, then
				//you should get a TRUE return and you should see that you are
				//now logged as the new specified user.
	$message .= "...2-| Person is in both Club and Series:";
	$clubID = 2;
	$seriesID = 50;
	$memRecID = 999;
		if ($_SERVER['HTTP_HOST'] == "tennis")
			{
			$clubID = 2;
			$seriesID = 5;
			$memRecID = 24;
			}
	$message .= "<BR />... ...memRecID: {$memRecID}";
	$message .= "<BR />... ...seriesID: {$seriesID}";
	dm($message);
	$message = "";
	$hashKey = $ObjUnderTest->loginKey_Create($memRecID, OBJSERIES, $seriesID);
	$loginResult = $ObjUnderTest->userLogin($hashKey, "RSVPUPDATE");
	if ($loginResult) $bResult="TRUE"; else $bResult="FALSE";
	$message .= "... ...userLogin() RESULT: {$bResult}";
	dm($message); $message = "";
	if (!$loginResult)
		{
		$errInfo = $objError->GetLastErr();
		//print_r($errInfo);
		if ($errInfo[1] == ERRCLASS_NOTAUTH)
			{
			$errDisplay = "*** UNABLE TO PROCESS REQUEST ***";			
			$errDisplay .= "<BR /><BR />";			
			$errDisplay .= "You are not a member of the club or series specified";			
			$errDisplay .= " in the email link.";			
			$errDisplay .= " This may have occurred if you clicked the link from";			
			$errDisplay .= " an old email message.";			
			$errDisplay .= "<BR /><BR />";			
			$errDisplay .= " Please contact your club manager for help with this.";			
			$message .= $errDisplay;
			dm($message); $message = "";
			}
		$objError->ReportAllErrs(0);
		}


	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



?> 
