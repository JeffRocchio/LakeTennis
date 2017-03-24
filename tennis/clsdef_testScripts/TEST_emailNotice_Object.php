<?php
/*
	   This script is used to test/debug the emailNotice object.
	   
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./classdefs/error.class.php');
include_once('./classdefs/emailNotice.class.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----GLOBAL VARIABLES--------------------------------------------------->
$objError = new ErrorList();

$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;

$objUnderTest = new emailNotice();
$objUnderTest->DEBUG = $DEBUG;

$errLastErrorKey = 0;
$errCount = 0;

$emObject = OBJSERIES;
$emObjectID = 50;
	if ($_SERVER['HTTP_HOST'] == "tennis") $emObjectID = 1;

$emScope = "AVAIL";

$addrList = "";


//----DECLARE LOCAL VARIABLES-------------------------------------------->
				//   Holds the current function name and program line#.
				//For use in error reporting.
$thisFunction = __FUNCTION__;
$currCodeLine = __LINE__;


$clubID = 1;
$seriesID = 1;
$seriesLongName = "";
$seriesShtName = "";
$personID = $_SESSION['recID'];

$dispMessage = "";
$debugMessage = "";


//=== BEGIN CODE ==============================================================>
//=============================================================================>


//----GET URL-QUERY-STRING-DATA------------------------------------------------>
if (array_key_exists('OBJ', $_GET)) $emObject = $_GET['OBJ'];
if (array_key_exists('ID', $_GET)) $emObjectID = $_GET['ID'];
if (array_key_exists('SCOPE', $_GET)) $emScope = $_GET['SCOPE'];


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Testing emailNotice Object";
$pgL1 = "";
$pgL2 = "";
$pgL3 = "Testing emailNotice Object";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


echo "{$OpenPara}-----<BR>Server: " . $_SERVER['HTTP_HOST'] . "<BR>-----" . $ClosePara;

//----LIST ALL CLASS METHODS/PARAMS-------------------------------------------->
documentClass();

//==============================================================================
//    BEGIN TESTS
//==============================================================================

//====TEST 1 : PULL 'TO' ADDRESSES==============================================
echo "{$OpenPara}<b>----| BEGIN TEST 1 : Generating TO Addresses ...</b>:{$ClosePara}";

$objUnderTest->genToList($emObject, $emObjectID, $emScope, "TO");
$addrList = $objUnderTest->getAddressList($addrLine="TO");
echo "{$OpenPara}<b>TO Address List:</b> :{$addrList}{$ClosePara}";

echo "{$OpenPara}END TEST 1 |----{$ClosePara}";



//====TEST 2 : CREATE PLAIN TEXT BODY FROM THE HTML BODY========================
echo "{$OpenPara}<b>----| BEGIN TEST 2 : Create Plain Text Body from HTML Body.</b>:{$ClosePara}";

$bodyTxt = "";
$bodyTxt .= "<H1>--- Upcoming Match ---</H1>";
$bodyTxt .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$bodyTxt .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
$bodyTxt .= "<P>Also, please bring a can of balls to the match.</P>";
$htmlBody = "";
$textBody = "";

$objUnderTest->appendBody("", "HTML");
$objUnderTest->appendBody("", "TEXT");
$objUnderTest->appendBody($bodyTxt);
$htmlBody = $objUnderTest->getBody("HTML");
$textBody = $objUnderTest->getBody("TEXT");
echo "{$OpenPara}BODY TEXT in HTML Version:{$ClosePara}";
echo "<DIV>{$htmlBody}</DIV>";
echo "{$OpenPara}BODY TEXT in Plain Text Version:{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";

$objUnderTest->resetObject();

echo "{$OpenPara}END TEST 2 |----{$ClosePara}";



//====TEST 3 : DIFFERENT HTML AND PLAIN-TEXT BODIES=============================
echo "{$OpenPara}<b>----| BEGIN TEST 3 : Different HTML and Plain Text Bodies.</b>:{$ClosePara}";

$bodyTxtHtml = "";
$bodyTxtHtml .= "<H1>--- Upcoming Match ---</H1>";
$bodyTxtHtml .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$bodyTxtHtml .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
$bodyTxtHtml .= "<P>Also, please bring a can of balls to the match.</P>";

$bodyTxtTxt = "";
$bodyTxtTxt .= "<H1>|| Upcoming Match || </H1>";
$bodyTxtTxt .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week. | </P>";
$bodyTxtTxt .= "<P> *** Please be on site 30 minutes prior to the match start time. *** | </P>";
$bodyTxtTxt .= "<P>Because this is a home match, we need to supply the balls. Please bring a can with you.</P>";

$htmlBody = "";
$textBody = "";

$objUnderTest->appendBody($bodyTxtHtml, "HTML");
$objUnderTest->appendBody($bodyTxtTxt, "TEXT");
$htmlBody = $objUnderTest->getBody("HTML");
$textBody = $objUnderTest->getBody("TEXT");
echo "{$OpenPara}BODY TEXT in HTML Version:{$ClosePara}";
echo "<DIV>{$htmlBody}</DIV>";
echo "{$OpenPara}BODY TEXT in Plain Text Version:{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";

$objUnderTest->resetObject();

echo "{$OpenPara}END TEST 3 |----{$ClosePara}";





//====TEST 4 : TEST ERROR HANDLING IN dataValidityErrors()======================
echo "{$OpenPara}<b>----| BEGIN TEST 4 : TEST ERROR HANDLING IN dataValidityErrors()</b>:{$ClosePara}";

$eol="\r\n";
$result = FALSE;
$msgTxt = "";

$result = $objUnderTest->sendEmail(TRUE, TRUE);
if (!$result) $objError->ReportAllErrs(0);

$objUnderTest->resetObject();

echo "{$OpenPara}END TEST 4 |----{$ClosePara}";



//====TEST 5 : Send Plain Text Email - Single Part==============================
echo "{$OpenPara}<b>----| BEGIN TEST 5 : Send Plain Text Email - Single Part</b>:{$ClosePara}";

$eol="\r\n";
$result = FALSE;
$msgTxt = "";

$body = "";
$body .= "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
$body .= "<H1>|| Upcoming Match || </H1>";
$body .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$body .= "<P> *** Please be on site 30 minutes prior to the match start time. ***</P>";
$body .= "<P>Because this is a home match, we need to supply the balls. Please bring a can with you.</P>";

$objUnderTest->genToList($emObject, $emObjectID, $emScope, "TO");
$objUnderTest->setSubject("TEST ID 5: Send Plain Text Email");
$objUnderTest->appendBody($body, "TEXT");

$textBody = $objUnderTest->getBody("TEXT");
echo "{$OpenPara}BODY TEXT in Plain Text Version:{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";

$result = $objUnderTest->sendEmail(FALSE, TRUE);
if (!$result) $objError->ReportAllErrs(0);

$objUnderTest->resetObject();

echo "{$OpenPara}END TEST 5 |----{$ClosePara}";



//====TEST 6 : Send HTML Formatted Email - Single Part==============================
echo "{$OpenPara}<b>----| BEGIN TEST 6 : Send HTML Formatted Email - Single Part</b>:{$ClosePara}";

$eol="\r\n";
$result = FALSE;

$body = "";
$body .= "<HTML><BODY>";
$body .= "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
$body .= "<H1>--- Upcoming Match ---</H1>";
$body .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$body .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
$body .= "<P>Also, please bring a can of balls to the match.</P>";
$body .= "</BODY></HTML>";

$objUnderTest->genToList($emObject, $emObjectID, $emScope, "TO");
$objUnderTest->setSubject("TEST ID 6: Send HTML Email");
$objUnderTest->appendBody($body, "HTML");

$textBody = $objUnderTest->getBody("HTML");
echo "{$OpenPara}BODY TEXT in HTML Version:{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$textBody}</TEXTAREA>";

$result = $objUnderTest->sendEmail(TRUE, FALSE);
if (!$result) $objError->ReportAllErrs(0);

$objUnderTest->resetObject();

echo "{$OpenPara}END TEST 6 |----{$ClosePara}";



//====TEST 7 : SEND MULTI-PART EMAIL - USING EMAIL OBJECT======================
echo "{$OpenPara}<b>----| BEGIN TEST 7 : Send Multi Part Email - Using Email Object.</b>:{$ClosePara}";

$eol="\r\n";
$result = FALSE;
$msgTxt = "";

$bodyHtml = "";
$bodyHtml .= "<HTML><BODY>";
$bodyHtml .= "<P><i>NOTE: This is a test message, Please Ignore it. Jeffrey Rocchio is testing enhancements to the tennis web site. These enhancements are for adding email notification capabilities. My intent is to be using a test set of email addresses, so you should not have received this email at all. You ended up receiving it because I mistakenly pointed my test code at our live club ID# instead of the test club. I shall fix that. I apologize for any inconvience.</i></P>";
$bodyHtml .= "<H1>--- Upcoming Match ---</H1>";
$bodyHtml .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$bodyHtml .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
$bodyHtml .= "<P>Also, please bring a can of balls to the match.</P>";
$bodyHtml .= "</BODY></HTML>";

$bodyText = "";
$bodyText .= "<H2>===Upcoming Match===</H2>";
$bodyText .= "<P>NOTICE: If you are on the TO line of this email, you are scheduled to play this week.<P>";

$objUnderTest->genToList($emObject, $emObjectID, $emScope, "TO");
$objUnderTest->setSubject("TEST ID 7: Send Multi-Part Email Using Email Object");

$objUnderTest->appendBody($bodyHtml, "HTML");
$objUnderTest->appendBody($bodyText, "TEXT");

$inspectBody = $objUnderTest->getBody("HTML") . $objUnderTest->getBody("TEXT");
echo "{$OpenPara}BODY STRING:{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$inspectBody}</TEXTAREA>";

$result = $objUnderTest->sendEmail(TRUE, TRUE);
if (!$result) $objError->ReportAllErrs(0);

$objUnderTest->resetObject();



echo "{$OpenPara}END TEST 7 |----{$ClosePara}";





echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
//==============================================================================
//    END TESTS
//==============================================================================




//==============================================================================
//    FUNCTIONS
//==============================================================================

function documentClass()
{
	/*	PURPOSE: Document the class's Methods.
	
		RETURNS: A list outputted to the screen.
	*/
	
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$clsMethodsList = array();
	$clsMethodDoc = "";
	$dispText = "";
	
	
	$clsMethodsList = get_class_methods('emailNotice');
	$clsReflector = new ReflectionClass('emailNotice');

	echo "{$OpenPara}<b>List of All Class Methods ---:</b>: " . $LineFeed;
	foreach ($clsMethodsList as $methodName)
		{
		$method = $clsReflector->getMethod($methodName);
		$parameters = $method->getParameters();
		$dispText = $methodName . "(";
		$dispText .= formatParameters($parameters);
		$dispText .= ")";
		echo $dispText . $LineFeed;
		}
	echo $LineFeed . $LineFeed;

}

function formatParameters($params) 
	{
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;


	$args = array();
	$arg = "";

    foreach($params as $param)
    	{
		$arg = '';
		if($param->isPassedByReference())
			{
			$arg .= '&';
			}
		if($param->isOptional())
			{
			$arg .= '[' . $param->getname();
			if($param->isDefaultValueAvailable())
				{
				$arg .= ' = ';
				$default = $param->getDefaultValue();
				if (is_bool($default))
					{
					if ((boolean)$default) { $arg.='TRUE'; } else { $arg.='FALSE'; }
					}
				elseif(empty($default)) $arg .= '""';
				else $arg .= $default;
				}
			$arg .= ']';
			}
			else
			{
			$arg .= $param->getName();
			}
		$args[] = $arg;
		}
	return implode(', ', $args);
	}

?> 
