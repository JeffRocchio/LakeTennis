<?php
/*
	This is the script to run in CRON for processing automated actions.
	
	02/19/2012 Version 0.1 --
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./classdefs/error.class.php');
include_once('./classdefs/debug.class.php');
include_once('./clsdef_mdl/database.class.php');
include_once('./clsdef_mdl/simulatedRecordset.class.php');
include_once('./clsdef_mdl/recordset.class.php');
include_once('./clsdef_mdl/series.class.php');
include_once('./clsdef_mdl/event.class.php');
include_once('./clsdef_mdl/rsvp.class.php');
include_once('./clsdef_mdl/autoAction.class.php');
include_once('./clsdef_mdl/link.class.php');
include_once('./clsdef_mdl/txtBlock.class.php');
include_once('./clsdef_view/eventViewChunks.class.php');
include_once('./clsdef_view/html2text.class.php');
include_once('./clsdef_view/linkViews.class.php');
include_once('./clsdef_ctrl/emailNotice.class.php');
include_once('./clsdef_ctrl/eventViewRequests.class.php');
include_once('./clsdef_ctrl/c_eventRecFPCstatus.class.php');
include_once('./clsdef_ctrl/viewFromTemplate.class.php');
include_once('./clsdef_ctrl/txtBlockViewRequests.class.php');
include_once('./clsdef_ctrl/autoActionHandler.class.php');
include_once('./clsdef_ctrl/rsvpUpdateViaEmailLink.class.php');
include_once('./INCL_Tennis_GLOBALS.php');
Session_Initalize();

$rtnpg = Session_SetReturnPage();


//----USEFUL GLOBAL VARIABLES-------------------------------------------------->
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;
$indent = $nbSpace . $nbSpace . $nbSpace;

				//   For knowing if we are running in Web-Browser vs CRON.
$RunningInCron = TRUE;



//----DECLARE SCRATCH VARIABLES------------------------------------------------>
	$message = "";
	
	
//----DETERMINE RUN ENVIRONMENT------------------------------------------------>
$RunningInCron = !isset($_SERVER['HTTP_HOST']);
if ($RunningInCron)
	{
	$LineFeed = LF;
	$OpenPara = LF;
	$ClosePara = LF;
	$nbSpace = " ";
	$indent = $nbSpace . $nbSpace . $nbSpace;
	}


//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----OPEN DISPLAY/NOTICE OUTPUT "PAGE"---------------------------------------->
				//   Set page header text and create the page, being sensitive
				//to what run environment we are in.
$tbar = "Process Auto Actions";
$pgL1 = "Automated Actions";
$pgL2 = "";
$pgL3 = "Processing Auto Actions";
if ($RunningInCron)
	{
	$outText = $tbar . $LineFeed;
	$outText .= $pgL1 . $LineFeed;
	$outText .= $pgL2 . $LineFeed;
	$outText .= $pgL3;
	dm($outText);
	}
else
	{
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	}
				//   With page 'open', output any pending info or notices.
$outText = "NOTE: We are Running In ";
if ($RunningInCron) { $outText .= "CRON."; }
else { $outText .= "WEB BROWSER."; }
dm($outText);

//==============================================================================
//   Process Action Items
//==============================================================================


	global $objError;
	global $objDebug;

	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$actionResult = FALSE;
	$actionArray = array();
	
	$iMaxActions = 10;
	$iCurrActions = 0;

	$autoActionHandler = new autoActionHandler();

	do	{
		$iCurrActions++;
		$message = "Loop Pass #{$iCurrActions}" . $LineFeed;
		$actionResult = $autoActionHandler->handleNextRequest();
		$message .= "---RESULT: handleNextRequest Returned: ";
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
				 $message .= "Undefined Value: {$actionResult}";
				}
		dm($message);

		if(($actionResult == RTN_SUCCESS) or ($actionResult == RTN_NOACTION))
			{
			$actionArray = $autoActionHandler->get_actionRecArray();
			$message = "---RESULT: Returned Action Record: ";
			$message .= $actionArray['ID'] . " || ";
			$message .= $actionArray['ActTitle'];
			dm($message);
			}
		$objError->ReportAllErrs(0);

		if($iCurrActions == $iMaxActions)
			{
			$message = "------------------------------" . $LineFeed;
			$message .= "Potential Infinite Loop." . $LineFeed;
			$message .= " Breaking out due to max limit" . $LineFeed;
			$message .= "({$iMaxActions})" . $LineFeed;
			$message .= "------------------------------";
			dm($message);
			break;
			}
		} while ($actionResult != RTN_EOF);
		

//----CLOSE OUT THE DISPLAY/NOTICE OUTPUT "PAGE"------------------------------->

if ($RunningInCron)
	{
	$outText = "---END JOB";
	dm($outText);
	}
else
	{
	echo  Tennis_BuildFooter('ADMIN', "");
	}



//==============================================================================
// Utility Functions
//==============================================================================

function dm($displayText, $newPara=TRUE)
					//Display a message on the console.
	{
	global $CRLF;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;
	global $indent;

	if($newPara) $textToDisplay = $OpenPara . $displayText . $ClosePara;
	else $textToDisplay = $displayText;
	echo $textToDisplay;
	return;
	}


?> 
