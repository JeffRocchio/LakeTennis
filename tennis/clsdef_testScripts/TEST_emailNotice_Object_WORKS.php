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
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;

$objUnderTest = new emailNotice();
$objUnderTest->DEBUG = $DEBUG;

$errLastErrorKey = 0;
$errCount = 0;

$emObject = OBJSERIES;
$emObjectID = 1;
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




//====TEST 4 : SEND MULTI-PART EMAIL============================================

/*
	NOTE-A: If a header’s value needs more than one line, additional lines 
	should begin with a space.

	NOTE-B: As soon as your mail program gets to a blank line, it knows the 
	headers are over and the rest of the email is the message body, 
	which it should display.
	
	NOTE-C: The below code works. But only after LOTS of trial and error which
	involved constant playing around with the {$eol}'s in both the $header
	and in the $bodyTxtHtml and $bodyTxtTxt. BUT -- **I don't know what specific
	change suddenly caused it to work.**
*/

echo "{$OpenPara}<b>----| BEGIN TEST 4 : Send out Multi Part Email Message.</b>:{$ClosePara}";

$eol="\r\n";
$result = FALSE;
$msgTxt = "";


$to = "rocchio@rocketmail.com";
$from ="d529518@laketennis.com";
$subject = "Jeff Testing From PHP";
$emailEnvlopSendr="-f{$from}";

$boundary = "==Multipart_Boundary_x8745376";

$headers = "From: {$from}{$eol}Reply-To: {$from}{$eol}";
$headers .= "X-Mailer: PHP v" . phpversion() . $eol;
$headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"{$eol}{$eol}";

$bodyTxtHtml = "--{$boundary}{$eol}Content-Type: text/html; charset=\"iso-8859-1\"{$eol}Content-Transfer-Encoding: 7bit{$eol}{$eol}";
$bodyTxtHtml .= "<HTML><BODY>";
$bodyTxtHtml .= "<HTML><BODY>";
$bodyTxtHtml .= "<H1>--- Upcoming Match ---</H1>";
$bodyTxtHtml .= "<P><b>NOTICE</b>: If you are on the TO line of this email, you are scheduled to play this week.</P>";
$bodyTxtHtml .= "<P><i>Please be on site 30 minutes prior to the match start time.</i></P>";
$bodyTxtHtml .= "<P>Also, please bring a can of balls to the match.</P>";
$bodyTxtHtml .= "</BODY></HTML>";
$bodyTxtHtml .= "{$eol}{$eol}";

$bodyTxtTxt = "";
$bodyTxtTxt .= "--{$boundary}{$eol}Content-Type: text/plain; charset=\"iso-8859-1\"{$eol}Content-Transfer-Encoding: 7bit{$eol}{$eol}";
$bodyTxtTxt .= "Upcoming Match: ";
$bodyTxtTxt .= "NOTICE If you are on the TO line of this email, you are scheduled to play this week.";
$bodyTxtTxt .= "{$eol}";

$msgTxt = $bodyTxtHtml . $bodyTxtTxt . $eol . "--" . $boundary . $eol;

echo "{$OpenPara}Email Message Text{$ClosePara}";
echo "<TEXTAREA ROWS='10' COLS='100'>{$msgTxt}</TEXTAREA>";
echo "{$OpenPara}...Sending Email{$ClosePara}";

$result = mail($to, $subject, $msgTxt, $headers, $emailEnvlopSendr);

if ($result)
	{
	echo "{$OpenPara}...Mail Sent{$ClosePara}";
	}
else
	{
	echo "{$OpenPara}...mail() failed.{$ClosePara}";
	}


echo "{$OpenPara}END TEST 4 |----{$ClosePara}";




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
