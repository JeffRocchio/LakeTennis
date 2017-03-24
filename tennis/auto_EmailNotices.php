<?php
/*
	This script is part of the Automated Actions system and the 
	autoActions table.
	
	This script's role is to read and process automated events related
	to sending email notices out.
	
	Bear in mind that when running in CRON we are *not* running on the
	web server, so we don't have access to $_SERVER[] variables like
	$_SERVER['HTTP_HOST'], etc.
	
	Also, when running in CRON we don't have a user logged in. So beware
	the use of $_SESSION[] variables.
	
	11/27/2011 jrr: ver 1.0.
	   *	Just starting to build the bones of it.
---------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_OBJ_autoAction.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----INITIALIZE GLOBAL VARIABLES---------------------------------------------->

				//   Declare and instiantiate the AutoAction object.
$objAction = new AUTO_AutoAction();
$objAction->DEBUG = $DEBUG;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   For knowing if we are running in Web-Browser vs CRON.
$RunningInCron = TRUE;

				//   Variables to help with outputting status for Web vs CRON.
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;



//----DECLARE LOCAL VARIABLES-------------------------------------------------->
$clubID = 0;
$seriesID = 0;

				//   Used to supply a message about the status of the requested
				//action. Generally this is used to pass status messages back
				//from a locally called function. Can be used to output to screen
				//or in CRON email.
$statusMessage = "";
				//   For general use in building text to output to the screen
				//or CRON email.
$outText = "";

				//   For general use in building text to output for debugging
				//purposes.
$debugText = "";

				//   Returned result from call to fetch next autoAction record.
$getNextActResult;

				//   General purpose counter.
$i = 0;



//----DETERMINE RUN ENVIRONMENT------------------------------------------------>
$RunningInCron = !isset($_SERVER['HTTP_HOST']);
if ($RunningInCron)
	{
	$LineFeed = LF;
	$OpenPara = LF;
	$ClosePara = LF;
	$nbSpace = " ";
	$objAction->debugSetOutputType("TEXT");
	}


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo $OpenPara . $lstErrMsg . $ClosePara;
	include './INCL_footer.php';
	exit;
	}


//----OPEN DISPLAY/NOTICE OUTPUT "PAGE"---------------------------------------->
				//   Set page header text and create the page, being sensitive
				//to what run environment we are in.
$tbar = "Tennis - Testing autoActions Script";
$pgL1 = "Admin Function";
$pgL2 = "autoActions Script";
$pgL3 = "Sending Email Notices";
if ($RunningInCron)
	{
	echo "{$tbar}{$LineFeed}";
	echo "{$pgL1}{$LineFeed}";
	echo "{$pgL2}{$LineFeed}";
	echo "{$pgL3}{$ClosePara}";
	}
else
	{
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	}
				//   With page 'open', output any pending info or notices.
$outText = "NOTE: We are Running In ";
if ($RunningInCron) { $outText .= "CRON."; }
else { $outText .= "WEB BROWSER."; }
echo $OpenPara . $outText . $ClosePara;







//----PROCESS THE AUTO-ACTIONS------------------------------------------------->

				//   (1) Get next (possibly first) autoAction record from the
				//dbms.
				//   BUT - I don't know what to do about the params yet, in terms
				//of how to handle that in the object and how to store and 
				//present them to the main script. The issue is that every
				//action record has a potentially different set of params, so
				//I cannot statically define variables for them. So for the
				//moment I am hard-coding them here in the main script.
	$getNextActResult = $objAction->getNextAction();
	if ($getNextActResult == RTN_WARNING)
		{
		$lstErrMsg = "Atempt to get next autoAction Generated a Warning.";
		if ($objAction->warnTxtAvail) { $lstErrMsg = $objAction->warnTxt; }
		echo $OpenPara . $lstErrMsg . $ClosePara;
		}
	if ($getNextActResult == RTN_FAILURE)
		{
		$lstErrMsg = "Failure attempting to fetch next autoAction Record. Processing of Automate Actions Halted.";
		if ($objAction->errorTxtAvail) { $lstErrMsg = $objAction->errorTxt; }
		echo $OpenPara . $lstErrMsg . $ClosePara;
		include './INCL_footer.php';
		exit;
		}


//----SET NEEDED VARIABLES FROM autoAction RECORD------------------------------>
	$clubID = $objAction->actRow['ClubID'];
	$actOnID = $objAction->actRow['TrggrObjID'];


//----DECODE ACTION CLASS AND ROUTE-------------------------------------------->

	switch ($objAction->actRow['AutoActClassID']) {

		case 61: //Roll Dates
			$lstErrMsg = "This action class not handled by this script.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;
			break;
			
		case 62: //Request RSVPs or Availability.
			$lstErrMsg = "Script Not Yet Defined for this Action.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;
			break;
			
		case 63: //Send RSVPs or Playing Assignments.
			if(!$resultStatus = local_63_tbd($actOnID, $objAction->actParams, $statusMessage))
				{
				$lstErrMsg = "Function local_63_tbd() Failed. {$statusMessage}";
				echo $OpenPara . $lstErrMsg . $ClosePara;
				include './INCL_footer.php';
				exit;
				}
			else
				{
				$outText = "{$nbSpace}{$nbSpace}{$nbSpace}ACTION ID {$objAction->actRow['ID']}";
				$outText .= " || {$objAction->actRow['ActTitle']} || {$statusMessage}";
				if ($DEBUG) { $outText .= "{$LineFeed}Function local_63_tbd() returned status code: {$resultStatus}"; }
				echo $OpenPara . $outText . $ClosePara;
				}
			break;
			
		default:
			//Output an error.
			$lstErrMsg = "Incorrect (or no) action specified in autoAction record.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;

		}


//----CLOSE OUT THE DISPLAY/NOTICE OUTPUT "PAGE"------------------------------->

if ($RunningInCron)
	{
	$outText = "---END JOB";
	echo $OpenPara . $outText . $ClosePara;
	}
else
	{
	echo  Tennis_BuildFooter('ADMIN', "auto_EmailNotices.php?ID={$seriesID}");
	}







//------------------------------------------------------------------------------
//====FUNCTIONS ================================================================


function local_63_tbd($seriesID, $NoticeParams, &$statusMessage)
	{
	/*
		This function builds and sends the specified RSVP Results email notice.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Series ID.
		2) An array which will contain parameter-specs for the notice.
		3) Pointer to string var to hold a status message.
		
	RETURNS:
		1) TRUE if success, FALSE otherwise.
		
	*/
	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $link;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$thisFunction = __FUNCTION__;

	$row = array();
	$recID = array();
	$seriesType = "";
	$seriesName =  "";
	$seriesViewLevel =  "";
	$seriesDescript =  "";
	$seriesNotes =  "";

	$statusMessage = "Action: SendNotice_RSVPresults. For SeriesID: {$seriesID}.";

	if ($DEBUG) { echo "{$OpenPara}Entering Function: {$thisFunction}{$ClosePara}"; }


				//   (1) Get series record so we can get needed info.
	if ($DEBUG) { echo "{$OpenPara}Begin Step 1.{$ClosePara}"; }
	if (!$qryResult = Tennis_GetSingleRecord($row, "series", $seriesID))
		{
		$statusMessage = "Failed to open Tennis_GetSingleRecord()";
		return RTN_FAILURE;
		}
	
				//   (2) Extract needed and useful info from the series record.
	if ($DEBUG) { echo "{$OpenPara}Begin Step 2.{$ClosePara}"; }
	$NumEvtsToRoll = $row["EvtsIREmail"];
	$seriesType = $row["Type"];
	$seriesName = $row["LongName"];
	$seriesViewLevel = $row["ViewLevel"];
	$seriesDescript = $row["Description"];
	$seriesNotes = $row["Notes"];
	
	$statusMessage .= " Result: Notice Has Been Sent.";
	if ($DEBUG) { echo "{$OpenPara}Exiting Function: {$thisFunction}() with Success{$ClosePara}"; }
	return RTN_SUCCESS;

} //END FUNCTION



function local_ListNames($eventID, $title, &$ListStringArray)
	{
	/*
		This function creates a string that contains the enumerated list of
		RSVPs. Meaning, a list of names and their RSVP status ('Playing,' 
		'Tentative,' etc. Each name is on a new line. There are actually two
		strings returned. One is formatted for plain-text; the other is 
		formatted in HTML.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Event Record ID.
		1) The title to put at the top of the list (e.g., the Event name).
		2) A pointer to an array where the two strings will be written.
		
	RETURNS:
		1) TRUE if success, FALSE otherwise.
		
	*/
	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $link;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$thisFunction = __FUNCTION__;
	
						//   Array to hold DB records.
	$row = array();
	
						//   The array key for the person names we want to use.
						//E.g., the full name, or only public name, etc.
	$keyPrsnName = 'prsnFullName';

						//   Counter.
	$numResponses = 0;


	if ($DEBUG) { echo "{$OpenPara}Entering Function: {$thisFunction}(){$ClosePara}"; }

	$ListStringArray['html'] = "<P>$title<BR>{$CRLF}";
	$ListStringArray['text'] = LF . $title . LF;

	$qryResult = local_getRSVPSet($eventID, 'PLAYING');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$ListStringArray['html'] .= "&nbsp;&nbsp;&nbsp;*&nbsp;{$row[$keyPrsnName]}<BR>{$CRLF}";
			$ListStringArray['text'] .= "   * {$row[$keyPrsnName]}" . EMAILCRLF;
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
			$ListStringArray['html'] .= "&nbsp;&nbsp;&nbsp;*&nbsp;will be late> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$ListStringArray['text'] .= "   * will be late> {$row[$keyPrsnName]}" . EMAILCRLF;
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
			$ListStringArray['html'] .= "&nbsp;&nbsp;&nbsp;*&nbsp;tentative> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$ListStringArray['text'] .= "   * tentative> {$row[$keyPrsnName]}" . EMAILCRLF;
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}


	if ($numResponses == 0)
		{
		$ListStringArray['html'] .= "&nbsp;&nbsp;&nbsp;*** NO RSVP'S POSTED FOR THIS EVENT ***{$CRLF}";
		$ListStringArray['text'] .= "   *** NO RSVP'S POSTED FOR THIS EVENT ***" . EMAILCRLF;
		}
	
	$ListStringArray['html'] .= "</P>{$CRLF}{$CRLF}";
	$ListStringArray['text'] .= EMAILCRLF;


	if ($DEBUG) { echo "{$OpenPara}Exiting Function: {$thisFunction}() with Success{$ClosePara}"; }
	return TRUE;

} //END FUNCTION



function local_getRSVPSet($eventID, $subset)
	{
	/*
		This function gets a sub-set of RSVP cells for a given event.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Event Record ID.
		2) A code for which sub-set of RSVPs to fetch.
		
	RETURNS:
		1) TRUE if success, FALSE otherwise.
		
	*/
	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $link;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$thisFunction = __FUNCTION__;

	if ($DEBUG) { echo "{$OpenPara}Entering Function: {$thisFunction}(){$ClosePara}"; }

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
		echo $OpenPara . $lstErrMsg . $ClosePara;
		if ($DEBUG) { echo "{$OpenPara}Exiting Function: local_getRSVPSet() with Failure.{$ClosePara}"; }
		include './INCL_footer.php';
		exit;
		}
	
	if ($DEBUG) { echo "{$OpenPara}Exiting Function: {$thisFunction}() with Success.{$ClosePara}"; }
	return $qryResult;

} //END FUNCTION


?> 
