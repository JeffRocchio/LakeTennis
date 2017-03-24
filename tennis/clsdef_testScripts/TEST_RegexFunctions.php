<?php
/*
	   This script is used to prototype, play with and understand the
	   PHP regex functions.
	   
------------------------------------------------------------------ */
session_start();
include_once('../INCL_Tennis_CONSTANTS.php');
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../classdefs/error.class.php');
include_once('../classdefs/debug.class.php');
include_once('../INCL_Tennis_GLOBALS.php');


Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


//----GLOBAL VARIABLES--------------------------------------------------->
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;
$indent = $nbSpace . $nbSpace . $nbSpace;
$objError = new ErrorList();
$objError->DEBUG = $DEBUG;
$errLastErrorKey = 0;
$errCount = 0;


//----DECLARE LOCAL VARIABLES-------------------------------------------->
				//   Holds the current function name and program line#.
				//For use in error reporting.
$thisFunction = __FUNCTION__;
$currCodeLine = __LINE__;


				//   Holds counter and index values for what errors to simulate.
				//Default is 1 error, error ID #1.
				//These values can also be passed in via query string.
$errorCount = 1;
$errorNum = array($errorCount=>1);


$clubID = 1;
$seriesID = 1;
$seriesLongName = "";
$seriesShtName = "";
$personID = $_SESSION['recID'];

					//   The current user's edit rights on the current series.
					//Initial default value is "Guest."
					//Note that declare of the rights[] array is for compatability
					//with the Roster_GetUserRights() function and is not
					//currently used in this script; this array approach is for
					//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');

$dispMessage = "";
$debugMessage = "";


//=== BEGIN CODE ==============================================================>
//=============================================================================>


//----GET URL-QUERY-STRING-DATA------------------------------------------------>
if (array_key_exists('ERRS', $_GET))
	{
	$errorCount = 1;
	$arrkey = "NUM" . $errorCount;
	while (array_key_exists($arrkey, $_GET));
		{
		$errorNum[$errorCount] = $_GET[$arrkey];
		$errorCount++;
		$arrkey = "NUM" . $errorCount;
		}
	if ($errorNum[1] == 0) $errorNum[1]=1;
	}


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Prototyping REGEX Functions";
$pgL1 = "Prototyping REGEX Functions";
$pgL2 = "";
$pgL3 = "";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----LIST ALL CLASS METHODS/PARAMS-------------------------------------------->
//documentClass();


//==============================================================================
//----BEGIN PLAY--------------------------------------------------------------->
$result = false;
$matches = array();



/*
					//   CASE #1: ---------------------------------------------------
$template01 = "This is template #1.<br> |%0321 rsvp 1230%|";
$regex01 = '#\|%0321 (rsvp|links|sigseriesadmin) 1230%\|#';

echo "{$OpenPara}<b>#01 Template:</b>: {$template01}<BR>";
echo "<b>#01 Regex:</b>: {$regex01}{$ClosePara}";

$result = preg_match_all($regex01, $template01, $matches);

echo "{$OpenPara}";
echo "<b>Matches Found</b>: {$result} --:<BR>";
foreach ($matches as $matchValue)
	{
	foreach ($matchValue as $key => $value)
		{
		echo "{$indent}KEY:{$key} -> VALUE: {$value}<BR>";
		}
	}
echo "{$ClosePara}";



					//   CASE #2: ---------------------------------------------------
$template = "This is template #2. This one has 3 replacements in it.";
$template .= "<BR>1:|%0321 rsvp 1230%| <BR>2:|%0321 links 1230%| <BR>";
$template .= "3:|%0321 sigseriesadmin 1230%|";
$regex = '#\|%0321 (rsvp|links|sigseriesadmin) 1230%\|#';

echo "{$OpenPara}<b>#02 Template:</b>: {$template}<BR><BR>";
echo "<b>#02 Regex:</b>: {$regex}{$ClosePara}";

$result = preg_match_all($regex, $template, $matches, PREG_SET_ORDER);

echo "{$OpenPara}";
echo "<b>Matches Found</b>: {$result} --:<BR>";
foreach ($matches as $matchValue)
	{
	foreach ($matchValue as $key => $value)
		{
		echo "{$indent}KEY:{$key} -> VALUE: {$value}<BR>";
		}
	}
echo "{$ClosePara}";

*/

					//   CASE #3 - USE CALLBACK FUNCTION-----------------------------
$numReplacements = 0;
$template = "This is template #3. Using the Callback Approach.";
$template .= " This one has 3 replacements in it.";
$template .= "<BR>1:|%0321 rsvp 1230%| <BR>2:|%0321 links 1230%| <BR>";
$template .= "3:|%0321 sigseriesadmin 1230%|";

$regexArr[0] = '#\|%0321 (rsvp) 1230%\|#';
$regexArr[1] = '#\|%0321 (links) 1230%\|#';
$regexArr[2] = '#\|%0321 (sigseriesadmin) 1230%\|#';

echo "{$OpenPara}<b>#03 Template:</b>: {$template}<BR><BR>";
echo "<b>#03 Regex:</b>: {$regexArr}{$ClosePara}";


$replacedString = preg_replace_callback  ($regexArr, 'subStrings', $template, -1, $numReplacements);
echo "{$OpenPara}<b>#03 Replaced String:</b><BR>: {$replacedString}{$ClosePara}";



					//   CASE #4 - USE CALLBACK & WITH ID #s-------------------------
$numReplacements = 0;
$template = "This is template #4. Using the Callback Approach.";
$template .= " This one has 3 replacements in it.";
$template .= "<BR>1:|%0321 rsvp 1 1230%| <BR>2:|%0321 links 1 1230%| <BR>";
$template .= "3:|%0321 sigseriesadmin 1 1230%|";

//$regex = "|%0321 (rsvp|links|sigseriesadmin) \b[0-9]{1,4}\b 1230%|";
$regexArr[0] = '#\|%0321 (rsvp) (\b[0-9]{1,4}\b) 1230%\|#';
$regexArr[1] = '#\|%0321 (links) (\b[0-9]{1,4}\b) 1230%\|#';
$regexArr[2] = '#\|%0321 (sigseriesadmin) (\b[0-9]{1,4}\b) 1230%\|#';

echo "{$OpenPara}<b>#04 Template:</b>: {$template}<BR><BR>";
echo "<b>#04 Regex:</b>: {$regexArr}{$ClosePara}";


$replacedString = preg_replace_callback  ($regexArr, 'subStringsCase4', $template, -1, $numReplacements);
echo "{$OpenPara}<b>#04 Replaced String:</b><BR>: {$replacedString}{$ClosePara}";



function subStringsCase4($matches)
	{
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;
	global $indent;

	$replaceWith = "";

	echo "{$OpenPara}";
	echo "<b>In subString(). matches array</b> --:<BR>";
	foreach ($matches as $key => $value)
		{
		echo "{$indent}KEY:{$key} -> VALUE: {$value}<BR>";
		}
	echo "{$ClosePara}";

	
	switch ($matches[1])
		{
		case 'rsvp':
			$replaceWith = "";
			local_ListNames("Event Name", 1, $replaceWith, FALSE);
			break;

		case 'links':
			$replaceWith = "";
			$replaceWith= local_MakeLinks('EMAIL');
			break;

		case 'sigseriesadmin':
			$replaceWith = "Signature of Series Admin";
			break;

		default:
			$replaceWith = "";
		}

	return $replaceWith;
	}











function subStrings($matches)
	{
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;
	global $indent;

	$replaceWith = "REPLACED";

	echo "{$OpenPara}";
	echo "<b>In subString(). matches array</b> --:<BR>";
	foreach ($matches as $key => $value)
		{
		echo "{$indent}KEY:{$key} -> VALUE: {$value}<BR>";
		}
	echo "{$ClosePara}";

	
	switch ($matches[1])
		{
		case 'rsvp':
			$replaceWith = "RSVP";
			break;

		case 'links':
			$replaceWith = "LINKS";
			break;

		case 'sigseriesadmin':
			$replaceWith = "Signature of Series Admin";
			break;

		default:
			$replaceWith = "";
		}

	return $replaceWith;
	}





//----END PLAY----------------------------------------------------------------->
//==============================================================================

echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);




//---FUNCTIONS ----------------------------------------------------------------

function local_ListNames($title, $eventID, &$emBody, $pgDisp)
	{
	
	//   $pgDisp: IF TRUE then write the text of what the email will contain
	//to the screen as well as building it into the email body. IF FALSE then
	//do not write it to the screen.
	
	GLOBAL $DEBUG;
	GLOBAL $CRLF;
	GLOBAL $emCRLF;
	
	$numResponses = 0;
	$out = "<P>$title<BR>{$CRLF}";
	$keyPrsnName = 'prsnFullName';
	$qryResult = local_getRSVPSet($eventID, 'PLAYING');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;{$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	
	$qryResult = local_getRSVPSet($eventID, 'LATE');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;will be late> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * will be late> {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	
	$qryResult = local_getRSVPSet($eventID, 'TENT');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;tentative> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * tentative> {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}


	if ($numResponses == 0)
		{
		$out .= "&nbsp;&nbsp;&nbsp;*** NO RESPONSES. Where IS everybody? ***{$CRLF}";
		$emBody .= "*** NO RESPONSES. Where IS everybody? ***{$emCRLF}";
		}
	
	$out .= "</P>{$CRLF}{$CRLF}";
	$emBody .= "{$emCRLF}";
	if ($pgDisp) echo $out;
	if($DEBUG)
		{
		echo "<P>In Function local_ListNames, Contents of emBody --:<BR />";
		echo "{$emBody}</P>";
		}

}


function local_getRSVPSet($eventID, $subset)
	{
	switch ($subset)
		{
		case 'TENT':
			$selCrit = "rsvpClaimCode=14"; // ="Tentative"
			break;
		
		case 'LATE':
			$selCrit = "rsvpClaimCode=13"; // ="Late"
			break;
		
		default:
//			$selCrit = "rsvpPositionCode=29 AND rsvpClaimCode<>13 AND rsvpClaimCode<>14"; // ="Playing"
			$selCrit = "rsvpClaimCode=15 OR rsvpClaimCode=16"; // ="Available" or "Confirmed"
		}
	
	if(!$qryResult = Tennis_OpenViewGeneric('qrySeriesRsvps', "WHERE (evtID={$eventID} AND ({$selCrit}))", "ORDER BY prsnPName"))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
	return $qryResult;
}


function local_MakeLinks($format)
	{
	global $CRLF;
	global $emCRLF;
	global $seriesID;
	global $clubID;
	
	$serverTxt = "http://" . $_SERVER['HTTP_HOST'];
	$homePg = $serverTxt . "/ClubHome.php?ID={$_SESSION['clubID']}";

	switch ($format)
		{
		case 'EMAIL':
					//   Remember, when we are running in CRON, we are not running on the
					//web server. So pre-defined variables coming from the web server are
					//not available.
			$serverTxt = "http://laketennis.com";
			$homePg = $serverTxt . "/ClubHome.php?ID={$clubID}";
			$htmltxt = "{$emCRLF}Useful Links:{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * HOME: ";
			$htmltxt .= "[{$homePg}]{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * Full RSVP Grid: ";
			$htmltxt .= "[{$serverTxt}/tennis/listSeriesRoster.php?ID={$seriesID}]{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * Mobile Phone View: ";
			$htmltxt .= "[{$serverTxt}/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}]{$emCRLF}";
			break;
		
		default:
		case 'FORM':
			$htmltxt = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/listSeriesRoster.php?ID={$seriesID}\">";
			$htmltxt .= "Full RSVP Grid</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/dispSeries.php?ID={$seriesID}\">";
			$htmltxt .= "Recreational Play Notes</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}\">";
			$htmltxt .= "Mobile Phone View</A><BR>{$CRLF}";
			
			$htmltxt .= "<BR>&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/listEmails.php?OBJ=SERIES&ID={$seriesID}\">";
			$htmltxt .= "Make Email Address List</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF=\"{$homePg}\">";
			$htmltxt .= "Club Home Page</A><BR>{$CRLF}";
			
			$htmltxt .= "</P>{$CRLF}";
			break;
		}

	return $htmltxt;
}








?> 
