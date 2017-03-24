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
include_once('../clsdef_view/html2text.class.php');
include_once('../clsdef_ctrl/emailNotice.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "emailNotice Class";


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

	$caseName = "Generate TO Address List | Scope=AVAIL";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;
	$addrList = "";


	$emailNotice = new emailNotice();
	$emailNotice->genToList(OBJSERIES, $seriesID, "AVAIL", "TO");
	$addrList = $emailNotice->get_AddressList($addrLine="TO");
	dm("Generated Address List: {$addrList}");
	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Create Plain Text Body from HTML Body.";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$bodyTxt = "";
	$htmlBody = "";
	$textBody = "";
	$messageText = "";

	$bodyTxt = "<H1>--- Upcoming Match ---</H1>";
	$bodyTxt .= "<P><b>NOTICE</b>: If you are on the TO line of this email, ";
	$bodyTxt .= "you are scheduled to play this week.</P>";
	$bodyTxt .= "<P><i>Please be on site 30 minutes prior to the match start";
	$bodyTxt .= " time.</i></P>";
	$bodyTxt .= "<P>Also, please bring a can of balls to the match.</P>";

	$objUnderTest = new emailNotice();

	$objUnderTest->appendBody("", "HTML");
	$objUnderTest->appendBody("", "TEXT");
	$objUnderTest->appendBody($bodyTxt);

	$htmlBody = $objUnderTest->get_Body("HTML");
	$textBody = $objUnderTest->get_Body("TEXT");

	$messageText = "BODY TEXT in HTML Version:<BR />";
	$messageText .= "<DIV>{$htmlBody}</DIV>";
	$messageText .= "<BR />BODY TEXT in Plain Text Version:<BR />";
	$messageText .= "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";
	dm($messageText);

	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase03($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Different HTML and Plain Text Bodies";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$bodyTxtHtml = "";
	$bodyTxtTxt = "";
	$htmlBody = "";
	$textBody = "";
	$messageText = "";

	$bodyTxtHtml = "<H1>--- Upcoming Match ---</H1>";
	$bodyTxtHtml .= "<P><b>NOTICE</b>: If you are on the TO line of this email, ";
	$bodyTxtHtml .= "you are scheduled to play this week.</P>";
	$bodyTxtHtml .= "<P><i>Please be on site 30 minutes prior to the match start";
	$bodyTxtHtml .= " time.</i></P>";
	$bodyTxtHtml .= "<P>Also, please bring a can of balls to the match.</P>";

	$bodyTxtTxt = "<H1>|| Upcoming Match || </H1>";
	$bodyTxtTxt .= "<P><b>NOTICE</b>: If you are on the TO line of this email,";
	$bodyTxtTxt .= " you are scheduled to play this week. | </P>";
	$bodyTxtTxt .= "<P> *** Please be on site 30 minutes prior to the match ";
	$bodyTxtTxt .= "start time. *** | </P>";
	$bodyTxtTxt .= "<P>Because this is a home match, we need to supply the ";
	$bodyTxtTxt .= "balls. Please bring a can with you.</P>";

	$objUnderTest = new emailNotice();

	$objUnderTest->appendBody($bodyTxtHtml, "HTML");
	$objUnderTest->appendBody($bodyTxtTxt, "TEXT");

	$htmlBody = $objUnderTest->get_Body("HTML");
	$textBody = $objUnderTest->get_Body("TEXT");

	$messageText = "BODY TEXT in HTML Version:<BR />";
	$messageText .= "<DIV>{$htmlBody}</DIV>";
	$messageText .= "<BR />BODY TEXT in Plain Text Version:<BR />";
	$messageText .= "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";
	dm($messageText);

	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase04($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "TEST ERROR HANDLING IN dataValidityErrors()";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$messageText = "";
	$result = FALSE;

	$objUnderTest = new emailNotice();

	$result = $objUnderTest->sendEmail(TRUE, TRUE);
	if (!$result) $objError->ReportAllErrs(0);

	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase05($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Send Plain Text Email - Single Part";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$bodyTxtHtml = "";
	$bodyTxtTxt = "";
	$htmlBody = "";
	$textBody = "";
	$addrList = "";
	$messageText = "";

	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;

	$bodyTxtTxt = "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
	$bodyTxtTxt .= "<H1>|| Upcoming Match || </H1>";
	$bodyTxtTxt .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
	$bodyTxtTxt .= "<P> *** Please be on site 30 minutes prior to the match start time. ***</P>";
	$bodyTxtTxt .= "<P>Because this is a home match, we need to supply the balls. Please bring a can with you.</P>";

	$objUnderTest = new emailNotice();

	$objUnderTest->genToList(OBJSERIES, $seriesID, "AVAIL", "TO");
	$addrList = $objUnderTest->get_AddressList($addrLine="TO");
	dm("Generated Address List: {$addrList}");

	$objUnderTest->set_Subject("TEST ID 5: Send Plain Text Email");

	$objUnderTest->appendBody($bodyTxtTxt, "TEXT");

	$textBody = $objUnderTest->get_Body("TEXT");

	$messageText .= "BODY TEXT in Plain Text Version:<BR />";
	$messageText .= "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";
	dm($messageText);

	$result = $objUnderTest->sendEmail(FALSE, TRUE);
	if (!$result) $objError->ReportAllErrs(0);


	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase06($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Send HTML Formatted Email - Single Part";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$bodyTxtHtml = "";
	$bodyTxtTxt = "";
	$body = "";
	$htmlBody = "";
	$textBody = "";
	$addrList = "";
	$messageText = "";

	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;

	$body = "";
	$body .= "<HTML><BODY>";
	$body .= "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
	$body .= "<H1>--- Upcoming Match ---</H1>";
	$body .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
	$body .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
	$body .= "<P>Also, please bring a can of balls to the match.</P>";
	$body .= "</BODY></HTML>";

	$objUnderTest = new emailNotice();

	$objUnderTest->genToList(OBJSERIES, $seriesID, "AVAIL", "TO");
	$addrList = $objUnderTest->get_AddressList($addrLine="TO");
	dm("Generated Address List: {$addrList}");

	$objUnderTest->set_Subject("TEST ID 6: Send HTML Formatted Email");

	$objUnderTest->appendBody($body, "HTML");

	$htmlBody = $objUnderTest->get_Body("HTML");

	$messageText = "BODY TEXT in HTML Version:<BR />";
	$messageText .= "<DIV>{$htmlBody}</DIV>";
	dm($messageText);

	$result = $objUnderTest->sendEmail(TRUE, FALSE);
	if (!$result) $objError->ReportAllErrs(0);

	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase07($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Send Multi Part Email";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	
	$bodyTxtHtml = "";
	$bodyTxtTxt = "";
	$body = "";
	$htmlBody = "";
	$textBody = "";
	$addrList = "";
	$messageText = "";

	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;

	$bodyTxtHtml = "";
	$bodyTxtHtml .= "<HTML><BODY>";
	$bodyTxtHtml .= "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
	$bodyTxtHtml .= "<H1>--- Upcoming Match ---</H1>";
	$bodyTxtHtml .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
	$bodyTxtHtml .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
	$bodyTxtHtml .= "<P>Also, please bring a can of balls to the match.</P>";
	$bodyTxtHtml .= "</BODY></HTML>";
	
	$bodyTxtTxt = "";
	$bodyTxtTxt .= "<H2>===Upcoming Match===</H2>";
	$bodyTxtTxt .= "<P>NOTICE: If you are on the TO line of this email, you are scheduled to play this week.<P>";

	$objUnderTest = new emailNotice();

	$objUnderTest->genToList(OBJSERIES, $seriesID, "AVAIL", "TO");
	$addrList = $objUnderTest->get_AddressList($addrLine="TO");
	dm("Generated Address List: {$addrList}");

	$objUnderTest->set_Subject("TEST ID 7: Send Multi-Part Email Using Email Object");

	$objUnderTest->appendBody($bodyTxtHtml, "HTML");
	$objUnderTest->appendBody($bodyTxtTxt, "TEXT");

	$htmlBody = $objUnderTest->get_Body("HTML");
	$textBody = $objUnderTest->get_Body("TEXT");

	$messageText = "BODY TEXT in HTML Version:<BR />";
	$messageText .= "<DIV>{$htmlBody}</DIV>";
	$messageText .= "<BR />BODY TEXT in Plain Text Version:<BR />";
	$messageText .= "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";
	dm($messageText);

	$result = $objUnderTest->sendEmail(TRUE, FALSE);
	if (!$result) $objError->ReportAllErrs(0);

	$objUnderTest->resetObject();

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




function TstCase08($caseNumber)
	{
	global $objError;
	global $objDebug;
	global $CRLF;

	$caseName = "Generate Email Distro List as Array";
	dmtcenex($caseNumber, $caseName, TRUE);
	
	//---Test Script Code Begins Here -------------------------------------------	

	$distroList = array();
	$bResult = TRUE;

	$seriesID = 50;
		if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;


	$emailNotice = new emailNotice();
	$bResult = $emailNotice->genToArray(OBJSERIES, $seriesID, "AVAIL", $distroList);
	dm("Return Result: {$bResult}");
	dm("Generated Distro List: {$distroList}");
	
	$i = 1;
	foreach($distroList as $key => $value)
		{
		$message = $objDebug->displayDBRecord($distroList[$i], FALSE);
		dm($message);
		$i++;
		}

	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}




?> 
